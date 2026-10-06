<?php

namespace App\Http\Controllers;

use App\Models\Requerimento;
use Illuminate\Http\Request;
use App\Models\Setor;
use App\Services\DocumentoRequerimentoService;

class RequerimentoController extends Controller
{
    public function create(Request $request)
    {
        $modelos = Setor::obterSetoresFormatados();

        $matricula = strtoupper($request->user()->matricula ?? '');
        $coordenacaoPermitida = null;

        // Remove os 5 primeiros caracteres (4 dígitos do ano + 1 do período)
        // Exemplo: '20211180001' vira '180001'
        $sufixoMatricula = substr($matricula, 5);

        // Identifica o setor com base no início do código do curso
        if (str_starts_with($sufixoMatricula, '18')) {
            $coordenacaoPermitida = 'COINF';
        } elseif (str_starts_with($sufixoMatricula, 'SEAADS')) {
            $coordenacaoPermitida = 'COADS';
        } elseif (str_starts_with($sufixoMatricula, '28')) {
            $coordenacaoPermitida = 'COMAM';
        } else {
            $coordenacaoPermitida = 'COCIE';
        }

        // Siglas de todas as coordenações que dependem do curso do aluno
        $coordenacoesRestritas = ['COINF', 'COADS', 'COMAM', 'COCIE'];

        // 1. Filtra a lista de modelos
        $modelos = array_filter($modelos, function ($mod) use ($coordenacaoPermitida, $coordenacoesRestritas) {
            $sigla = strtoupper($mod['setor_sigla'] ?? '');

            // Se o setor for uma coordenação de curso, só mantém se for a do aluno
            if (in_array($sigla, $coordenacoesRestritas)) {
                return $sigla === $coordenacaoPermitida;
            }

            // Outros setores (ex: Biblioteca, SRA, DAE) aparecem normalmente
            return true;
        });

        // 2. Define o setor selecionado via query string (?setor=...)
        $setorParam = $request->query('setor', $request->query('modelo'));
        $modeloAtivo = null;

        if ($setorParam) {
            if (isset($modelos[$setorParam])) {
                $modeloAtivo = $modelos[$setorParam];
            } else {
                foreach ($modelos as $mod) {
                    if (strcasecmp($mod['setor_sigla'], $setorParam) === 0) {
                        $modeloAtivo = $mod;
                        break;
                    }
                }
            }
        }

        // 3. Se o setor selecionado for inválido/indisponível para o aluno, pega o primeiro da lista filtrada
        if (!$modeloAtivo) {
            $modeloAtivo = !empty($modelos) ? reset($modelos) : null;
        }

        $modeloChave = $modeloAtivo['id'] ?? null;
        $setorDestino = [
            'nome' => $modeloAtivo['setor_nome'] ?? 'Setor Responsável',
            'email' => $modeloAtivo['email'] ?? 'protocolos.seabra@ifba.edu.br',
        ];

        // Carrega os empreendimentos do usuário para vincular à solicitação
        $empreendimentos = $request->user()->todosEmpreendimentos()->get();
        $empreendimentoSelecionadoId = $request->query('empreendimento_id');

        return view('requerimentos.form', compact(
            'modelos',
            'modeloChave',
            'modeloAtivo',
            'setorDestino',
            'empreendimentos',
            'empreendimentoSelecionadoId'
        ));
    }

    //Método para mostrar requerimentos que já foram realizados pelo usuário;
    public function index(Request $request)
    {
        //Implementação da lógica de que o usuário logado só pode ver os seus próprios requerimentos;
        //Uso de chave estrangeira na tabela requerimentos;
        $query = auth()->user()->requerimentos()->with('empreendimento');

        if ($request->filled('busca')) {
            $busca = $request->input('busca');
            $query->where(function($q) use ($busca) {
                $q->where('objetoDoRequerimento', 'LIKE', '%' . $busca . '%')
                  ->orWhere('numero_protocolo', 'LIKE', '%' . $busca . '%')
                  ->orWhere('status', 'LIKE', '%' . $busca . '%');

                if (stripos('aberto', $busca) !== false) {
                    $q->orWhere('status', 'Despacho');
                }
            });
        }
        if ($request->filled('objetoDoRequerimento')) {
            $query->where('objetoDoRequerimento', 'LIKE', '%' . $request->input('objetoDoRequerimento') . '%');
        }
        if ($request->filled('status')) {
            $statusFiltro = $request->input('status');
            if (strcasecmp($statusFiltro, 'Aberto') === 0) {
                $query->whereIn('status', ['Aberto', 'Despacho']);
            } else {
                $query->where('status', 'LIKE', '%' . $statusFiltro . '%');
            }
        }
        if ($request->filled('numero_protocolo')) {
            $query->where('numero_protocolo', 'LIKE', '%' . $request->input('numero_protocolo') . '%');
        }

        $requerimentos = $query->orderByRaw("CASE WHEN LOWER(status) = 'indeferido' THEN 0 ELSE 1 END")
                               ->latest()
                               ->get();
        return view('requerimentos.meusRequerimentos', compact('requerimentos'));
    }
    public function show(Requerimento $requerimento)
    {
        $donoId = $requerimento->usuario_id ?? $requerimento->user_id;
        if ((int) $donoId !== (int) auth()->id()) {
            abort(403, 'Acesso não autorizado.');
        }
        $requerimento->load(['usuario.endereco', 'assunto', 'historicos.usuario', 'historicos.documentos', 'empreendimento']);
        return view('requerimentos.show', compact('requerimento'));
    }

    public function showHistorico($id)
    {
        $requerimento = Requerimento::with([
            'historicos.usuario',
            'historicos.documentos',
            'usuario.endereco',
            'setor',
            'empreendimento'
        ])->findOrFail($id);
        $user = auth()->user();
        if ($user && $user->ehResponsavelDoSetor($requerimento->setor_id)) {
            return redirect()->route('setor.requerimentos.show', [
                'setor'        => $requerimento->setor_id,
                'requerimento' => $requerimento->id,
            ]);
        }
        $historicos = $requerimento->historicos;
        return view('admin.historico', compact('requerimento', 'historicos'));
    }

    public function reenviarRequerimento(Request $request, Requerimento $requerimento)
    {
        if ((int) $requerimento->usuario_id !== (int) auth()->id()) {
            abort(403, 'Acesso não autorizado.');
        }

        $ultimoHistorico = $requerimento->historicos()->reorder()->latest('created_at')->first();
        $exigeDocumentos = $requerimento->status === 'Indeferido'
            || (bool) $ultimoHistorico?->solicita_novo_documento;

        $validated = $request->validate([
            'motivo_correcao' => 'required|string|max:3000',
            'arquivos'        => [$exigeDocumentos ? 'required' : 'nullable', 'array', 'min:1'],
            'arquivos.*'      => [$exigeDocumentos ? 'required' : 'nullable', 'file', 'max:51200'],
        ]);
        $requerimento->update([
            'status' => 'Em Análise',
        ]);

        $arquivos = $request->file('arquivos', []);

        $novoHistorico = \App\Models\HistoricoRequerimento::create([
            'requerimento_id'           => $requerimento->id,
            'user_id'                   => auth()->id() ?? $requerimento->usuario_id,
            'status'                    => $requerimento->status,
            'observacao'                => $validated['motivo_correcao'],
            'solicita_novo_documento'   => false,
            'nome_documento_solicitado' => null,
        ]);

        // Salva os documentos enviados vinculados ao histórico
        if (!empty($arquivos)) {
            $titulo = $ultimoHistorico?->nome_documento_solicitado ?? 'Documento de Correção';
            app(DocumentoRequerimentoService::class)->salvarArquivos(
                requerimento: $requerimento,
                historico: $novoHistorico,
                arquivos: is_array($arquivos) ? $arquivos : [$arquivos],
                usuario: auth()->user(),
                titulo: $titulo
            );
        }

        $requerimento->notificarPartes(
            mensagem: $validated['motivo_correcao'],
            remetente: 'aluno',
            arquivos: is_array($arquivos) ? $arquivos : [$arquivos]
        );

        return redirect()
            ->back()
            ->with('success', 'Correção e novos documentos enviados ao setor com sucesso!');
    }
}
