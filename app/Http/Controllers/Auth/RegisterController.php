<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Endereco;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Controller de Cadastro de Usuário (Pessoa Física e Pessoa Jurídica)
 * Conforme Seção 2.2 do Manual do Usuário SECAMB.
 */
class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $tipoRegistro = $request->input('tipo_registro', 'fisica');

        $rules = [
            'tipo_registro' => 'required|in:fisica,juridica',
            'email'         => 'required|email|max:255|unique:usuarios,email',
            'endereco'      => 'required|string|max:255',
            'bairro'        => 'required|string|max:100',
            'cep'           => 'required|string|max:20',
            'password'      => 'required|string|min:6|confirmed',
        ];

        if ($tipoRegistro === 'juridica') {
            $rules['razao_social'] = 'required|string|max:255';
            $rules['cnpj']         = 'required|string|max:25|unique:usuarios,cnpj';
        } else {
            $rules['nome'] = 'required|string|max:255';
            $rules['cpf']  = 'required|string|max:20|unique:usuarios,cpf';
        }

        $messages = [
            'tipo_registro.required' => 'Selecione o tipo de registro.',
            'nome.required'          => 'O nome completo é obrigatório.',
            'razao_social.required'  => 'A razão social é obrigatória.',
            'cpf.required'           => 'O CPF é obrigatório.',
            'cpf.unique'             => 'Este CPF já está cadastrado.',
            'cnpj.required'          => 'O CNPJ é obrigatório.',
            'cnpj.unique'            => 'Este CNPJ já está cadastrado.',
            'email.required'         => 'O e-mail é obrigatório.',
            'email.email'            => 'Informe um e-mail válido.',
            'email.unique'           => 'Este e-mail já está cadastrado no sistema.',
            'endereco.required'      => 'O endereço é obrigatório.',
            'bairro.required'        => 'O bairro é obrigatório.',
            'cep.required'           => 'O CEP é obrigatório.',
            'password.required'      => 'A senha é obrigatória.',
            'password.min'           => 'A senha deve ter no mínimo 6 caracteres.',
            'password.confirmed'     => 'A confirmação de senha não confere.',
        ];

        $validated = $request->validate($rules, $messages);

        DB::transaction(function () use ($validated, $tipoRegistro) {
            $nome = $tipoRegistro === 'juridica' ? $validated['razao_social'] : $validated['nome'];

            $usuario = Usuario::create([
                'tipo_registro' => $tipoRegistro,
                'nome'          => $nome,
                'razao_social'  => $tipoRegistro === 'juridica' ? $validated['razao_social'] : null,
                'cpf'           => $tipoRegistro === 'fisica' ? $validated['cpf'] : null,
                'cnpj'          => $tipoRegistro === 'juridica' ? $validated['cnpj'] : null,
                'email'         => $validated['email'],
                'password'      => Hash::make($validated['password']),
                'role'          => 'cidadao',
                'ativo'         => true,
            ]);

            Endereco::create([
                'usuario_id' => $usuario->id,
                'rua'        => $validated['endereco'],
                'bairro'     => $validated['bairro'],
                'cidade'     => 'Seabra',
                'estado'     => 'BA',
                'cep'        => $validated['cep'],
            ]);
        });

        return redirect()->route('login')->with('success', 'Cadastro realizado com sucesso! Faça login para continuar.');
    }
}
