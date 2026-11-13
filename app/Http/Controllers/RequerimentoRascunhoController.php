<?php

namespace App\Http\Controllers;

use App\Models\DocumentoAssunto;
use App\Models\AssuntoRequerimento;
use App\Models\RequerimentoRascunho;
use App\Models\RequerimentoRascunhoArquivo;
use App\Models\Setor;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class RequerimentoRascunhoController extends Controller
{
    private const CAMPOS_PERMITIDOS = [
        'email',
        'celular',
        'rua',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'cep',
        'empreendimento_id',
        'objetoDoRequerimento',
        'objeto_outro',
        'motivo',
    ];

    public function show(Request $request, int $setor)
    {
        $setorAutorizado = $this->autorizarSetor($request->user(), $setor);
        $validated = $request->validate([
            'objeto' => 'required|string|max:255',
        ]);
        $objeto = $validated['objeto'];
        $this->autorizarObjeto($setorAutorizado, $objeto);
        $rascunho = $this->buscarRascunho($request->user(), $setor, $objeto);

        return response()->json([
            'rascunho' => $rascunho ? [
                'campos' => $rascunho->campos,
                'etapa' => $rascunho->etapa,
                'objeto' => $rascunho->objeto_chave,
                'atualizado_em' => $rascunho->updated_at,
            ] : null,
            'arquivos' => $rascunho
                ? $rascunho->arquivos()->orderBy('campo')->orderBy('indice')->get()->map(fn ($arquivo) => [
                    'id' => $arquivo->id,
                    'campo' => $arquivo->campo,
                    'indice' => $arquivo->indice,
                    'nome' => $arquivo->nome_original,
                    'tipo' => $arquivo->mime_type,
                    'ultimaModificacao' => $arquivo->updated_at->getTimestamp() * 1000,
                    'url' => route('requerimentos.rascunhos.arquivos.baixar', [$setor, $arquivo->id]),
                ])->values()
                : [],
        ]);
    }

    public function salvar(Request $request, int $setor)
    {
        $setorAutorizado = $this->autorizarSetor($request->user(), $setor);
        $validated = $request->validate([
            'objeto' => 'required|string|max:255',
            'campos' => 'required|array|max:30',
            'campos.*' => 'nullable|string|max:5000',
            'etapa' => 'required|in:1,2,3',
        ]);

        $objeto = $validated['objeto'];
        $this->autorizarObjeto($setorAutorizado, $objeto);
        $rascunhoExistente = $this->buscarRascunho($request->user(), $setor, $objeto);
        if (!$rascunhoExistente && (int) $validated['etapa'] < 2) {
            throw ValidationException::withMessages([
                'etapa' => 'O rascunho só pode ser iniciado após preencher os dados do requerimento.',
            ]);
        }

        $campos = array_intersect_key(
            $validated['campos'],
            array_flip(self::CAMPOS_PERMITIDOS)
        );
        $campos['objetoDoRequerimento'] = $objeto;

        if (!empty($campos['empreendimento_id']) &&
            !$request->user()->todosEmpreendimentos()->whereKey($campos['empreendimento_id'])->exists()) {
            throw ValidationException::withMessages([
                'campos.empreendimento_id' => 'O empreendimento selecionado não está vinculado à sua conta.',
            ]);
        }

        $rascunho = RequerimentoRascunho::updateOrCreate(
            [
                'usuario_id' => $request->user()->id,
                'setor_id' => $setor,
                'objeto_chave' => $objeto,
            ],
            ['campos' => $campos, 'etapa' => (int) $validated['etapa']]
        );

        return response()->json([
            'salvo' => true,
            'objeto' => $objeto,
            'atualizado_em' => $rascunho->updated_at,
        ]);
    }

    public function salvarArquivos(Request $request, int $setor)
    {
        $setorAutorizado = $this->autorizarSetor($request->user(), $setor);
        $validated = $request->validate([
            'objeto' => 'required|string|max:255',
            'etapa' => 'required|in:3',
            'campo' => ['required', 'string', 'max:100', 'regex:/^(documentos\[\d+\]|arquivos\[\])$/'],
            'arquivos' => 'nullable|array|max:20',
            'arquivos.*' => 'file|max:51200',
        ]);

        $objeto = $validated['objeto'];
        $this->autorizarObjeto($setorAutorizado, $objeto);
        $campo = $validated['campo'];
        $this->autorizarCampoArquivo($campo, $setor);
        $rascunho = $this->obterOuCriarRascunho($request->user(), $setor, $objeto);
        $pathsNovos = [];
        $metadadosNovos = [];

        try {
            foreach ($validated['arquivos'] ?? [] as $indice => $arquivo) {
                $caminho = $arquivo->store("rascunhos_requerimentos/{$rascunho->id}", 'local');
                if (!$caminho) {
                    throw new \RuntimeException('Não foi possível armazenar um dos anexos.');
                }

                $pathsNovos[] = $caminho;
                $metadadosNovos[] = [
                    'campo' => $campo,
                    'indice' => $indice,
                    'nome_original' => $arquivo->getClientOriginalName(),
                    'caminho' => $caminho,
                    'mime_type' => $arquivo->getClientMimeType() ?: $arquivo->getMimeType(),
                    'tamanho' => $arquivo->getSize() ?: 0,
                ];
            }

            $pathsAnteriores = DB::transaction(function () use ($rascunho, $campo, $metadadosNovos) {
                $rascunhoBloqueado = RequerimentoRascunho::whereKey($rascunho->id)
                    ->lockForUpdate()
                    ->firstOrFail();
                $rascunhoBloqueado->etapa = 3;
                $rascunhoBloqueado->save();
                $anteriores = $rascunhoBloqueado->arquivos()->where('campo', $campo)->get();
                $rascunhoBloqueado->arquivos()->where('campo', $campo)->delete();

                foreach ($metadadosNovos as $metadados) {
                    $rascunhoBloqueado->arquivos()->create($metadados);
                }

                return $anteriores->pluck('caminho')->all();
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($pathsNovos);
            throw $exception;
        }

        Storage::disk('local')->delete($pathsAnteriores);

        return response()->json(['salvo' => true, 'quantidade' => count($metadadosNovos)]);
    }

    public function baixarArquivo(Request $request, int $setor, int $arquivo)
    {
        $this->autorizarSetor($request->user(), $setor);
        $documento = RequerimentoRascunhoArquivo::whereKey($arquivo)
            ->whereHas('rascunho', fn ($query) => $query
                ->where('usuario_id', $request->user()->id)
                ->where('setor_id', $setor))
            ->firstOrFail();

        abort_unless(Storage::disk('local')->exists($documento->caminho), 404);

        return Storage::disk('local')->download(
            $documento->caminho,
            $documento->nome_original,
            ['Content-Type' => $documento->mime_type ?: 'application/octet-stream']
        );
    }

    public function destruir(Request $request, int $setor)
    {
        $setorAutorizado = $this->autorizarSetor($request->user(), $setor);
        $validated = $request->validate([
            'objeto' => 'required|string|max:255',
        ]);
        $objeto = $validated['objeto'];
        $this->autorizarObjeto($setorAutorizado, $objeto);
        $rascunho = $this->buscarRascunho($request->user(), $setor, $objeto);

        if (!$rascunho) {
            return response()->json(['removido' => true]);
        }

        $caminhos = $rascunho->arquivos()->pluck('caminho')->all();
        $rascunho->delete();
        Storage::disk('local')->delete($caminhos);

        return response()->json(['removido' => true]);
    }

    private function autorizarSetor(Usuario $usuario, int $setorId): Setor
    {
        $modelos = Setor::obterSetoresFormatados();
        $modelo = $modelos[$setorId] ?? null;
        abort_unless($modelo, 404);

        $matricula = strtoupper((string) ($usuario->matricula ?? ''));
        $sufixoMatricula = substr($matricula, 5);
        $coordenacaoPermitida = str_starts_with($sufixoMatricula, '18')
            ? 'COINF'
            : (str_starts_with($sufixoMatricula, 'SEAADS')
                ? 'COADS'
                : (str_starts_with($sufixoMatricula, '28') ? 'COMAM' : 'COCIE'));
        $coordenacoesRestritas = ['COINF', 'COADS', 'COMAM', 'COCIE'];

        abort_if(
            in_array(strtoupper($modelo['setor_sigla'] ?? ''), $coordenacoesRestritas, true)
                && strtoupper($modelo['setor_sigla']) !== $coordenacaoPermitida,
            404
        );

        return Setor::findOrFail($setorId);
    }

    private function autorizarCampoArquivo(string $campo, int $setorId): void
    {
        if ($campo === 'arquivos[]') {
            return;
        }

        preg_match('/^documentos\[(\d+)\]$/', $campo, $correspondencia);
        $documentoId = $correspondencia[1] ?? null;
        abort_unless(
            $documentoId && DocumentoAssunto::whereKey($documentoId)
                ->whereHas('assunto', fn ($query) => $query
                    ->where('setor_id', $setorId)
                    ->where('ativo', true))
                ->exists(),
            404
        );
    }

    private function autorizarObjeto(Setor $setor, string $objeto): void
    {
        abort_unless(
            $objeto === 'outro'
                || AssuntoRequerimento::where('setor_id', $setor->id)
                    ->where('descricao', $objeto)
                    ->where('ativo', true)
                    ->exists(),
            404
        );
    }

    private function buscarRascunho(Usuario $usuario, int $setorId, string $objeto): ?RequerimentoRascunho
    {
        return RequerimentoRascunho::where('usuario_id', $usuario->id)
            ->where('setor_id', $setorId)
            ->where('objeto_chave', $objeto)
            ->first();
    }

    private function obterOuCriarRascunho(Usuario $usuario, int $setorId, string $objeto): RequerimentoRascunho
    {
        return RequerimentoRascunho::firstOrCreate(
            [
                'usuario_id' => $usuario->id,
                'setor_id' => $setorId,
                'objeto_chave' => $objeto,
            ],
            ['campos' => ['objetoDoRequerimento' => $objeto], 'etapa' => 3]
        );
    }
}
