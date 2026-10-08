<?php

namespace App\Http\Controllers;

use App\Models\Empreendimento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $validated = $request->validate([
            // Dados do Empreendimento (Figura 8 do manual)
            'nome'               => 'required|string|max:255',
            'cnpj'               => 'nullable|string|max:20',
            'bacia_hidrografica' => 'nullable|string|max:255',
            'recurso_hidrico'    => 'nullable|string|max:255',
            'fase_operacao'      => 'nullable|string|max:100',
            'tipo_atividade'     => 'nullable|string|max:255',
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

            // Aceite do termo ao cadastrar
            'termo_aceito'       => 'nullable|boolean',
        ]);

        try {
            DB::beginTransaction();

            $dados = array_merge($validated, [
                'usuario_id' => $usuario->id,
                'cidade'     => $validated['cidade'] ?? 'Seabra',
                'estado'     => $validated['estado'] ?? 'BA',
                'ativo'      => true,
            ]);

            $empreendimento = Empreendimento::create($dados);

            // Vincula o criador na tabela pivot de representantes com termo aceito
            $empreendimento->representantes()->syncWithoutDetaching([
                $usuario->id => [
                    'termo_aceito'    => true,
                    'termo_aceito_em' => now(),
                    'status'          => 'ativo',
                ],
            ]);

            DB::commit();

            if ($request->input('retorno') === 'requerimento') {
                return redirect()
                    ->route('requerimentos.cidadao.novo', ['empreendimento_id' => $empreendimento->id])
                    ->with('sucesso', "Empreendimento '{$empreendimento->nome}' cadastrado e selecionado com sucesso!");
            }

            return redirect()
                ->route('empreendimentos.index')
                ->with('sucesso', "Empreendimento '{$empreendimento->nome}' cadastrado com sucesso!");

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

        return view('empreendimentos.edit', compact('empreendimento'));
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

        $validated = $request->validate([
            'nome'               => 'required|string|max:255',
            'cnpj'               => 'nullable|string|max:20',
            'bacia_hidrografica' => 'nullable|string|max:255',
            'recurso_hidrico'    => 'nullable|string|max:255',
            'fase_operacao'      => 'nullable|string|max:100',
            'tipo_atividade'     => 'nullable|string|max:255',
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

        $empreendimento->update($validated);

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
        $cnpjBusca = preg_replace('/\D/', '', (string) $request->input('cnpj'));
        $empreendimento = null;

        if (!empty($cnpjBusca)) {
            // Busca tanto pelo formato numérico limpo quanto pelo formatado
            $empreendimento = Empreendimento::whereRaw("REGEXP_REPLACE(cnpj, '[^0-9]', '') = ?", [$cnpjBusca])
                ->orWhere('cnpj', $request->input('cnpj'))
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
}
