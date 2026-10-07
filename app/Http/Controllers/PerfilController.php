<?php

namespace App\Http\Controllers;

use App\Models\Endereco;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Exibe e salva o formulário de completar perfil para
 * cidadãos que entraram via login social (Google).
 */
class PerfilController extends Controller
{
    public function completar()
    {
        $usuario = Auth::user();

        // Se o perfil já está completo, vai ao painel
        if (! $this->perfilIncompleto($usuario)) {
            return redirect()->route('requerimentos.aluno');
        }

        return view('auth.completar-perfil', compact('usuario'));
    }

    public function salvar(Request $request)
    {
        $usuario     = Auth::user();
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

        return redirect()->route('requerimentos.aluno')
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
