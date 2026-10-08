<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login - SECAMB (Prefeitura Municipal de Seabra)
    |--------------------------------------------------------------------------
    | Todos os usuários (cidadãos, servidores e administradores) fazem login
    | com e-mail e senha cadastrados localmente no sistema.
    |
    | Perfis de redirecionamento após login:
    |   admin    → painel administrativo
    |   servidor → painel de gestão de processos
    |   cidadao  → painel do cidadão (processos)
    */

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ], [
            'email.required'    => 'O e-mail é obrigatório.',
            'email.email'       => 'Informe um e-mail válido.',
            'password.required' => 'A senha é obrigatória.',
        ]);

        $usuario = Usuario::where('email', $request->email)->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            return back()->withErrors([
                'email' => 'E-mail ou senha inválidos.',
            ])->withInput($request->only('email'));
        }

        if (isset($usuario->ativo) && !$usuario->ativo) {
            return back()->withErrors([
                'email' => 'Sua conta está desativada. Entre em contato com a Prefeitura.',
            ])->withInput($request->only('email'));
        }

        Auth::login($usuario, $request->boolean('lembrar'));

        return $this->redirecionarPorPerfil($usuario);
    }

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Você saiu do sistema.');
    }

    /*
    |--------------------------------------------------------------------------
    | Redirecionamento por perfil
    |--------------------------------------------------------------------------
    */

    private function redirecionarPorPerfil(Usuario $usuario)
    {
        if ($usuario->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($usuario->isServidor()) {
            // Se o servidor é também responsável de setor, vai direto ao dashboard do setor
            if ($usuario->ehResponsavel()) {
                $primeiroSetor = $usuario->setoresSobResponsabilidade()->first();
                if ($primeiroSetor) {
                    return redirect()->route('setor.responsavel.dashboard', $primeiroSetor->id);
                }
            }
            return redirect()->route('servidor.dashboard');
        }

        // Cidadão → painel de processos
        return redirect()->route('requerimentos.cidadao.meusRequerimentos');
    }
}
