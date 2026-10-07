<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Social – Google (SECAMB)
    |--------------------------------------------------------------------------
    | O login social via Google é exclusivo para cidadãos.
    | Servidores e administradores devem usar login com e-mail e senha.
    |
    | Fluxo:
    |   1. Redireciona para o OAuth do Google
    |   2. Google retorna ao callback
    |   3. Localiza ou cria o usuário com role=cidadao
    |   4. Autentica e redireciona ao painel do cidadão
    */

    /**
     * Redireciona o usuário para a página de autenticação do Google.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Processa o retorno do Google após a autenticação.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Não foi possível autenticar com o Google. Tente novamente.']);
        }

        // Busca o usuário pelo google_id ou pelo e-mail
        $usuario = Usuario::where('google_id', $googleUser->getId())->first();

        if (! $usuario) {
            $usuario = Usuario::where('email', $googleUser->getEmail())->first();
        }

        if ($usuario) {
            // Conta existente: atualiza o google_id e avatar se ainda não vinculados
            if (! $usuario->google_id) {
                $usuario->update([
                    'google_id' => $googleUser->getId(),
                    'avatar'    => $googleUser->getAvatar(),
                ]);
            }

            // Servidores/admins não podem acessar via OAuth (proteção de perfil sensível)
            if ($usuario->isServidor() || $usuario->isAdmin()) {
                return redirect()->route('login')
                    ->withErrors(['email' => 'Contas de servidor/administrador devem usar e-mail e senha.']);
            }

            if (isset($usuario->ativo) && ! $usuario->ativo) {
                return redirect()->route('login')
                    ->withErrors(['email' => 'Sua conta está desativada. Entre em contato com a Prefeitura.']);
            }
        } else {
            // Novo usuário — cria automaticamente como cidadão
            $usuario = Usuario::create([
                'nome'      => $googleUser->getName(),
                'email'     => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar'    => $googleUser->getAvatar(),
                'role'      => 'cidadao',
                'ativo'     => true,
                // password fica null — usuário só usa login social
            ]);
        }

        Auth::login($usuario, true);

        return redirect()->route('requerimentos.aluno');
    }
}
