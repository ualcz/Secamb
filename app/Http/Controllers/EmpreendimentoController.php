<?php

namespace App\Http\Controllers;

use App\Mail\TecnicoVinculadoEmpreendimentoMail;
use App\Models\Empreendimento;
use App\Models\Usuario;
use App\Rules\CnpjValido;
use App\Support\DocumentoHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmpreendimentoController extends Controller
{
    /**
     * Lista todos os empreendimentos do usuário logado (criados ou representados).
     * Corresponde à seção 1.2.1 do Manual do Usuário.
     */
    public function index(Request $request)
    {
        $usuario = auth()->user();

        $query = $usuario->todosEmpreendimentos();

        if ($request->filled('busca')) {
            $termo = trim($request->input('busca'));
            $query->where(function ($q) use ($termo) {
                $q->where('nome', 'LIKE', "%{$termo}%")
                  ->orWhere('cnpj', 'LIKE', "%{$termo}%")
                  ->orWhere('bairro', 'LIKE', "%{$termo}%");
            });
        }

        $empreendimentos = $query->latest()->get();

        return view('empreendimentos.index', compact('empreendimentos'));
    }

    /**
     * Exibe o formulário de cadastro de novo empreendimento.
     * Corresponde à seção 1.2.3 do Manual do Usuário.
     */
    public function create(Request $request)
    {
        $retorno = $request->query('retorno');

        return view('empreendimentos.create', compact('retorno'));
    }

    /**
     * Salva o novo empreendimento no banco de dados e vincula o usuário como criador e representante.
     */
    public function store(Request $request)
    {
        $usuario = auth()->user();

        if ($request->filled('cnpj')) {
            $request->merge([
                'cnpj' => DocumentoHelper::formatarCnpj($request->input('cnpj')),
            ]);
        }

        $validated = $request->validate([
            // Dados do Empreendimento (Figura 8 do manual)
            'nome'               => 'required|string|max:255',
            'cnpj'               => ['nullable', 'string', 'max:18', new CnpjValido],
            'fase_operacao'      => 'nullable|string|max:100',
            'tipo_atividade'     => 'nullable|string|max:255',
            'porte'              => 'nullable|string|max:30',
            'cnae'               => 'nullable|string|max:20',
            'endereco'           => 'nullable|string|max:255',
            'bairro'             => 'nullable|string|max:255',
            'cep'                => 'nullable|string|max:10',
            'cidade'             => 'nullable|string|max:100',
            'estado'             => 'nullable|string|max:2',
            // Dados de Contato / Responsável (Figura 9 do manual)
            'contato_nome'       => 'nullable|string|max:255',
            'contato_telefone'   => 'nullable|string|max:30',
            'contato_celular'    => 'nullable|string|max:30',
            'contato_email'      => 'nullable|email|max:255',
            'tecnico_email'      => 'nullable|email|max:255',

            // Aceite do termo ao cadastrar
            'termo_aceito'       => 'nullable|boolean',
        ]);

        $tecnico = null;
        if (!empty($validated['tecnico_email'])) {
            $tecnico = $this->buscarTecnicoElegivel($validated['tecnico_email']);
            if (!$tecnico) {
                return back()
                    ->withInput()
                    ->withErrors(['tecnico_email' => 'Não encontramos uma conta de cidadão Pessoa Física com esse e-mail. O técnico precisa se cadastrar antes de ser vinculado.']);
            }

            if ((int) $tecnico->id === (int) $usuario->id) {
                return back()
                    ->withInput()
                    ->withErrors(['tecnico_email' => 'Informe o e-mail de outro usuário para vinculá-lo como técnico.']);
            }
        }

        try {
            DB::beginTransaction();

            $dados = array_merge($validated, [
                'usuario_id' => $usuario->id,
                'cidade'     => $validated['cidade'] ?? 'Seabra',
                'estado'     => $validated['estado'] ?? 'BA',
                'ativo'      => true,
            ]);
            unset($dados['tecnico_email']);

            $empreendimento = Empreendimento::create($dados);

            // Vincula o criador na tabela pivot de representantes com termo aceito
            $empreendimento->representantes()->syncWithoutDetaching([
                $usuario->id => [
                    'termo_aceito'    => true,
                    'termo_aceito_em' => now(),
                    'status'          => 'ativo',
                ],
            ]);

            if ($tecnico) {
                $empreendimento->tecnicos()->syncWithoutDetaching([$tecnico->id]);
            }

            DB::commit();

            $emailTecnicoEnviado = $tecnico
                ? $this->notificarTecnicoVinculado($tecnico, $empreendimento, $usuario)
                : null;
            $mensagemTecnico = !$tecnico
                ? ''
                : ($emailTecnicoEnviado
                    ? " Técnico {$tecnico->nome} vinculado e notificado por e-mail."
                    : " Técnico {$tecnico->nome} vinculado, mas não foi possível enviar o e-mail de notificação.");

            if ($request->input('retorno') === 'requerimento') {
                return redirect()
                    ->route('requerimentos.cidadao.novo', ['empreendimento_id' => $empreendimento->id])
                    ->with('sucesso', "Empreendimento '{$empreendimento->nome}' cadastrado e selecionado com sucesso!{$mensagemTecnico}");
            }

            return redirect()
                ->route('empreendimentos.index')
                ->with('sucesso', "Empreendimento '{$empreendimento->nome}' cadastrado com sucesso!{$mensagemTecnico}");

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['geral' => 'Erro ao cadastrar empreendimento: ' . $e->getMessage()]);
        }
    }

    /**
     * Exibe o formulário de edição de um empreendimento existente.
     */
    public function edit(Empreendimento $empreendimento)
    {
        $usuario = auth()->user();

        if (!$usuario->podeGerenciarEmpreendimento($empreendimento)) {
            abort(403, 'Você não tem permissão para editar este empreendimento.');
        }

        $tecnicos = $empreendimento->tecnicos()->orderBy('nome')->get();

        return view('empreendimentos.edit', compact('empreendimento', 'tecnicos'));
    }

    /**
     * Autoriza um cidadão cadastrado como pessoa física a atuar como técnico.
     */
    public function adicionarTecnico(Request $request, Empreendimento $empreendimento)
    {
        $usuario = $request->user();
        if (!$usuario->podeGerenciarEmpreendimento($empreendimento)) {
            abort(403, 'Você não tem permissão para gerenciar os técnicos deste empreendimento.');
        }

        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $tecnico = $this->buscarTecnicoElegivel($validated['email']);
        if (!$tecnico) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'Não encontramos uma conta de cidadão Pessoa Física com esse e-mail. O técnico precisa se cadastrar antes de ser vinculado.']);
        }

        if ((int) $tecnico->id === (int) $usuario->id) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'Informe o e-mail de outro usuário para vinculá-lo como técnico.']);
        }

        $jaVinculado = $empreendimento->tecnicos()->whereKey($tecnico->id)->exists();
        $empreendimento->tecnicos()->syncWithoutDetaching([$tecnico->id]);
        $emailTecnicoEnviado = $jaVinculado
            ? null
            : $this->notificarTecnicoVinculado($tecnico, $empreendimento, $usuario);
        $mensagem = "Técnico {$tecnico->nome} vinculado ao empreendimento.";
        if (!$jaVinculado) {
            $mensagem .= $emailTecnicoEnviado
                ? ' A notificação foi enviada por e-mail.'
                : ' Não foi possível enviar o e-mail de notificação.';
        }

        return redirect()
            ->route('empreendimentos.edit', $empreendimento)
            ->with('sucesso', $mensagem);
    }

    /**
     * Revoga o acesso de um técnico ao empreendimento.
     */
    public function removerTecnico(Request $request, Empreendimento $empreendimento, int $tecnico)
    {
        if (!$request->user()->podeGerenciarEmpreendimento($empreendimento)) {
            abort(403, 'Você não tem permissão para gerenciar os técnicos deste empreendimento.');
        }

        if (!$empreendimento->tecnicos()->whereKey($tecnico)->exists()) {
            abort(404);
        }

        $empreendimento->tecnicos()->detach($tecnico);

        return redirect()
            ->route('empreendimentos.edit', $empreendimento)
            ->with('sucesso', 'Acesso do técnico revogado.');
    }

    /**
     * Atualiza os dados do empreendimento.
     */
    public function update(Request $request, Empreendimento $empreendimento)
    {
        $usuario = auth()->user();

        if (!$usuario->podeGerenciarEmpreendimento($empreendimento)) {
            abort(403, 'Você não tem permissão para editar este empreendimento.');
        }

        if ($request->filled('cnpj')) {
            $request->merge([
                'cnpj' => DocumentoHelper::formatarCnpj($request->input('cnpj')),
            ]);
        }

        $validated = $request->validate([
            'nome'               => 'required|string|max:255',
            'cnpj'               => ['nullable', 'string', 'max:18', new CnpjValido],
            'fase_operacao'      => 'nullable|string|max:100',
            'tipo_atividade'     => 'nullable|string|max:255',
            'porte'              => 'nullable|string|max:30',
            'cnae'               => 'nullable|string|max:20',
            'endereco'           => 'nullable|string|max:255',
            'bairro'             => 'nullable|string|max:255',
            'cep'                => 'nullable|string|max:10',
            'cidade'             => 'nullable|string|max:100',
            'estado'             => 'nullable|string|max:2',
            'contato_nome'       => 'nullable|string|max:255',
            'contato_telefone'   => 'nullable|string|max:30',
            'contato_celular'    => 'nullable|string|max:30',
            'contato_email'      => 'nullable|email|max:255',
        ]);

        $empreendimento->update(array_merge($validated, [
            'cidade' => 'Seabra',
            'estado' => 'BA',
        ]));

        return redirect()
            ->route('empreendimentos.index')
            ->with('sucesso', "Dados do empreendimento '{$empreendimento->nome}' atualizados com sucesso!");
    }

    /**
     * Tela de busca de empreendimento por CNPJ para representação.
     * Corresponde às seções 1.2.2 e 1.2.2.1 do Manual do Usuário.
     */
    public function buscar(Request $request)
    {
        $cnpjBusca = DocumentoHelper::normalizarCnpj((string) $request->input('cnpj'));
        $empreendimento = null;

        if (!empty($cnpjBusca)) {
            $cnpjFormatado = DocumentoHelper::formatarCnpj($cnpjBusca);
            $empreendimento = Empreendimento::whereIn('cnpj', array_unique([$cnpjBusca, $cnpjFormatado]))
                ->first();
        }

        $usuario = auth()->user();
        $jaRepresenta = false;

        if ($empreendimento) {
            $jaRepresenta = $usuario->podeGerenciarEmpreendimento($empreendimento);
        }

        return view('empreendimentos.buscar', compact('empreendimento', 'cnpjBusca', 'jaRepresenta'));
    }

    /**
     * Solicitar representação de um empreendimento existente com aceite do termo.
     * Corresponde à seção 1.2.2.1 do Manual do Usuário.
     */
    public function solicitarRepresentacao(Request $request, Empreendimento $empreendimento)
    {
        $usuario = auth()->user();

        $request->validate([
            'termo_aceito' => 'accepted',
        ], [
            'termo_aceito.accepted' => 'É necessário concordar com a Declaração do Representante Legal para representar a empresa.',
        ]);

        $empreendimento->representantes()->syncWithoutDetaching([
            $usuario->id => [
                'termo_aceito'    => true,
                'termo_aceito_em' => now(),
                'status'          => 'ativo',
            ],
        ]);

        return redirect()
            ->route('empreendimentos.index')
            ->with('sucesso', "Representação do empreendimento '{$empreendimento->nome}' adicionada com sucesso à sua lista!");
    }

    private function buscarTecnicoElegivel(string $email): ?Usuario
    {
        $tecnico = Usuario::whereRaw('LOWER(email) = ?', [strtolower(trim($email))])->first();

        return $tecnico && $tecnico->isCidadao() && $tecnico->tipo_registro === 'fisica'
            ? $tecnico
            : null;
    }

    private function notificarTecnicoVinculado(
        Usuario $tecnico,
        Empreendimento $empreendimento,
        Usuario $vinculador
    ): bool {
        try {
            Mail::to($tecnico->email)->send(
                new TecnicoVinculadoEmpreendimentoMail($tecnico, $empreendimento, $vinculador)
            );

            return true;
        } catch (\Throwable $exception) {
            Log::error('Falha ao notificar técnico sobre vínculo com empreendimento.', [
                'tecnico_id' => $tecnico->id,
                'empreendimento_id' => $empreendimento->id,
                'vinculador_id' => $vinculador->id,
                'erro' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
