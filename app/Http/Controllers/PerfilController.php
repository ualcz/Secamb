<?php

namespace App\Http\Controllers;

use App\Models\Endereco;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * Controller de Gestão de Perfil do Usuário
 * Permite que cidadãos, servidores e administradores visualizem e editem
 * seus próprios dados cadastrais e alterem sua senha.
 */
class PerfilController extends Controller
{
    /**
     * Exibe a tela de visualização do perfil com todos os dados cadastrais.
     */
    public function index()
    {
        $usuario = Auth::user();
        $usuario->load('endereco');

        return view('perfil.index', compact('usuario'));
    }

    /**
     * Exibe o formulário para o usuário editar seus próprios dados.
     */
    public function edit()
    {
        $usuario = Auth::user();

        // Administradores gerenciam apenas senha pelo painel
        if ($usuario->isAdmin()) {
            return redirect()->route('perfil.senha');
        }

        $usuario->load('endereco');

        return view('perfil.edit', compact('usuario'));
    }

    /**
     * Exibe a tela dedicada para alteração de senha.
     */
    public function editSenha()
    {
        $usuario = Auth::user();

        return view('perfil.senha', compact('usuario'));
    }

    /**
     * Atualiza os dados cadastrais do próprio usuário autenticado.
     */
    public function update(Request $request)
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();

        // Administradores não alteram dados pessoais por esta rota
        if ($usuario->isAdmin()) {
            return redirect()->route('perfil.index')
                ->withErrors(['geral' => 'Os dados cadastrais de administradores não podem ser alterados por este formulário.']);
        }

        $isCidadao    = $usuario->isCidadao();
        $tipoRegistro = $request->input('tipo_registro', $usuario->tipo_registro ?? 'fisica');

        $rules = [
            'email'   => ['required', 'email', 'max:255', Rule::unique('usuarios', 'email')->ignore($usuario->id)],
            'celular' => [$isCidadao ? 'required' : 'nullable', 'string', 'max:30'],
        ];

        if ($isCidadao) {
            $rules['tipo_registro'] = 'required|in:fisica,juridica';

            if ($tipoRegistro === 'juridica') {
                $rules['razao_social'] = 'required|string|max:255';
                $rules['nome']         = 'nullable|string|max:255';
                $rules['cnpj']         = ['required', 'string', 'max:25', Rule::unique('usuarios', 'cnpj')->ignore($usuario->id)];
            } else {
                $rules['nome'] = 'required|string|max:255';
                $rules['cpf']  = ['required', 'string', 'max:20', Rule::unique('usuarios', 'cpf')->ignore($usuario->id)];
            }

            // Endereço residencial / empresarial do cidadão
            $rules['cep']         = 'required|string|max:20';
            $rules['rua']         = 'required|string|max:255';
            $rules['numero']      = 'nullable|string|max:30';
            $rules['complemento'] = 'nullable|string|max:255';
            $rules['bairro']      = 'required|string|max:100';
            $rules['cidade']      = 'required|string|max:100';
            $rules['estado']      = 'required|string|size:2';
        } else {
            // Servidor ou Administrador
            $rules['nome'] = 'required|string|max:255';
            if ($request->filled('cpf')) {
                $rules['cpf'] = ['nullable', 'string', 'max:20', Rule::unique('usuarios', 'cpf')->ignore($usuario->id)];
            }
            if ($request->filled('rua') || $request->filled('cep')) {
                $rules['rua']    = 'required|string|max:255';
                $rules['bairro'] = 'required|string|max:100';
                $rules['cep']    = 'required|string|max:20';
            }
        }

        $messages = [
            'nome.required'         => 'O nome completo é obrigatório.',
            'razao_social.required' => 'A razão social é obrigatória.',
            'cpf.required'          => 'O CPF é obrigatório.',
            'cpf.unique'            => 'Este CPF já está cadastrado por outro usuário.',
            'cnpj.required'         => 'O CNPJ é obrigatório.',
            'cnpj.unique'           => 'Este CNPJ já está cadastrado por outro usuário.',
            'email.required'        => 'O e-mail é obrigatório.',
            'email.email'           => 'Informe um e-mail válido.',
            'email.unique'          => 'Este e-mail já está em uso por outro usuário.',
            'celular.required'      => 'O telefone ou celular é obrigatório para notificações.',
            'cep.required'          => 'O CEP é obrigatório.',
            'rua.required'          => 'O logradouro/rua é obrigatório.',
            'bairro.required'       => 'O bairro é obrigatório.',
            'cidade.required'       => 'A cidade é obrigatória.',
            'estado.required'       => 'O estado (UF) é obrigatório.',
        ];

        $validated = $request->validate($rules, $messages);

        DB::transaction(function () use ($usuario, $validated, $tipoRegistro, $isCidadao, $request) {
            $updateData = [
                'email'   => $validated['email'],
                'celular' => $validated['celular'] ?? null,
            ];

            if ($isCidadao) {
                $updateData['tipo_registro'] = $tipoRegistro;

                if ($tipoRegistro === 'juridica') {
                    $updateData['razao_social'] = $validated['razao_social'];
                    $updateData['nome']         = !empty($validated['nome']) ? $validated['nome'] : $validated['razao_social'];
                    $updateData['cnpj']         = $validated['cnpj'];
                    $updateData['cpf']          = null;
                } else {
                    $updateData['nome']         = $validated['nome'];
                    $updateData['cpf']          = $validated['cpf'];
                    $updateData['razao_social'] = null;
                    $updateData['cnpj']         = null;
                }
            } else {
                $updateData['nome'] = $validated['nome'];
                if (isset($validated['cpf'])) {
                    $updateData['cpf'] = $validated['cpf'];
                }
            }

            $usuario->update($updateData);

            // Atualiza ou cria endereço vinculado se aplicável
            if ($isCidadao || $request->filled('rua') || $request->filled('cep')) {
                Endereco::updateOrCreate(
                    ['usuario_id' => $usuario->id],
                    [
                        'rua'         => $request->input('rua'),
                        'numero'      => $request->input('numero'),
                        'complemento' => $request->input('complemento'),
                        'bairro'      => $request->input('bairro'),
                        'cidade'      => $request->input('cidade', 'Seabra'),
                        'estado'      => strtoupper((string) $request->input('estado', 'BA')),
                        'cep'         => $request->input('cep'),
                    ]
                );
            }
        });

        return redirect()->route('perfil.index')->with('success', 'Dados cadastrais atualizados com sucesso!');
    }

    /**
     * Atualiza a senha de acesso do próprio usuário autenticado.
     */
    public function updateSenha(Request $request)
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();

        $rules = [
            'password' => 'required|string|min:6|confirmed',
        ];

        $messages = [
            'senha_atual.required' => 'Informe a sua senha atual para confirmar a alteração.',
            'password.required'    => 'Informe a nova senha.',
            'password.min'         => 'A nova senha deve ter no mínimo 6 caracteres.',
            'password.confirmed'   => 'A confirmação de senha não confere.',
        ];

        // Se o usuário possui senha prévia definida, exige a senha atual
        if (!empty($usuario->password)) {
            $rules['senha_atual'] = 'required|string';
        }

        $request->validate($rules, $messages);

        if (!empty($usuario->password) && !Hash::check($request->senha_atual, $usuario->password)) {
            return back()->withErrors(['senha_atual' => 'A senha atual informada está incorreta.'])->withInput();
        }

        $usuario->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('perfil.index')->with('success', 'Sua senha foi atualizada com sucesso!');
    }

    public function completar()
    {
        $usuario = Auth::user();

        // Se o perfil já está completo, vai ao painel
        if (! $this->perfilIncompleto($usuario)) {
            return redirect()->route('requerimentos.cidadao.meusRequerimentos');
        }

        return view('auth.completar-perfil', compact('usuario'));
    }

    public function salvar(Request $request)
    {
        $usuario      = Auth::user();
        $tipoRegistro = $request->input('tipo_registro', $usuario->tipo_registro ?? 'fisica');

        $rules = [
            'tipo_registro' => 'required|in:fisica,juridica',
            'celular'       => 'nullable|string|max:30',
            'rua'           => 'required|string|max:255',
            'bairro'        => 'required|string|max:100',
            'cep'           => 'required|string|max:20',
        ];

        // Validação condicional de contato
        $rules['_contato'] = 'nullable'; // dummy — validação manual abaixo

        if ($tipoRegistro === 'juridica') {
            $rules['razao_social'] = 'required|string|max:255';
            $rules['cnpj']         = 'required|string|max:25|unique:usuarios,cnpj,' . $usuario->id;
        } else {
            $rules['nome'] = 'required|string|max:255';
            $rules['cpf']  = 'required|string|max:20|unique:usuarios,cpf,' . $usuario->id;
        }

        $messages = [
            'nome.required'         => 'O nome completo é obrigatório.',
            'razao_social.required' => 'A razão social é obrigatória.',
            'cpf.required'          => 'O CPF é obrigatório.',
            'cpf.unique'            => 'Este CPF já está cadastrado.',
            'cnpj.required'         => 'O CNPJ é obrigatório.',
            'cnpj.unique'           => 'Este CNPJ já está cadastrado.',
            'rua.required'          => 'O endereço (rua) é obrigatório.',
            'bairro.required'       => 'O bairro é obrigatório.',
            'cep.required'          => 'O CEP é obrigatório.',
        ];

        $validated = $request->validate($rules, $messages);

        // Valida contato manualmente
        if (empty($request->celular)) {
            return back()
                ->withInput()
                ->withErrors(['celular' => 'Informe um telefone ou celular para contato.']);
        }

        DB::transaction(function () use ($usuario, $validated, $tipoRegistro, $request) {
            $updateData = [
                'tipo_registro' => $tipoRegistro,
                'celular'       => $request->celular,
            ];

            if ($tipoRegistro === 'juridica') {
                $updateData['razao_social'] = $validated['razao_social'];
                $updateData['nome']         = $validated['razao_social'];
                $updateData['cnpj']         = $validated['cnpj'];
                $updateData['cpf']          = null;
            } else {
                $updateData['nome'] = $validated['nome'];
                $updateData['cpf']  = $validated['cpf'];
                $updateData['cnpj'] = null;
            }

            $usuario->update($updateData);

            // Cria ou atualiza endereço
            Endereco::updateOrCreate(
                ['usuario_id' => $usuario->id],
                [
                    'rua'    => $validated['rua'],
                    'bairro' => $validated['bairro'],
                    'cidade' => $request->cidade ?: 'Seabra',
                    'estado' => $request->estado ?: 'BA',
                    'cep'    => $validated['cep'],
                ]
            );
        });

        return redirect()->route('requerimentos.cidadao.meusRequerimentos')
            ->with('success', 'Perfil completo! Bem-vindo(a) ao SECAMB.');
    }

    private function perfilIncompleto($usuario): bool
    {
        if ($usuario->isPessoaFisica() && empty($usuario->cpf)) {
            return true;
        }
        if ($usuario->isPessoaJuridica() && empty($usuario->cnpj)) {
            return true;
        }
        if (empty($usuario->celular)) {
            return true;
        }
        if (! $usuario->endereco || empty($usuario->endereco->rua)) {
            return true;
        }
        return false;
    }
}
