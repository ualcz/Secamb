<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Garante que cidadãos que fizeram login social (Google) completem
 * seus dados obrigatórios antes de usar o sistema.
 *
 * Campos verificados:
 *   - CPF (pessoa física) ou CNPJ (pessoa jurídica)
 *   - Celular / WhatsApp
 *   - Endereço vinculado
 */
class PerfilCompleto
{
    public function handle(Request $request, Closure $next)
    {
        $usuario = Auth::user();

        // Só aplica a cidadãos autenticados
        if (! $usuario || ! $usuario->isCidadao()) {
            return $next($request);
        }

        // Só obriga o preenchimento para quem entrou via Google
        if (! $usuario->hasLoginSocial()) {
            return $next($request);
        }

        // Já está na rota de completar perfil — deixa passar
        if ($request->routeIs('perfil.completar') || $request->routeIs('perfil.completar.salvar')) {
            return $next($request);
        }

        // Verifica se o perfil está incompleto
        if ($this->perfilIncompleto($usuario)) {
            return redirect()->route('perfil.completar')
                ->with('aviso', 'Complete seu perfil para continuar usando o sistema.');
        }

        return $next($request);
    }

    /**
     * Retorna true se algum campo obrigatório ainda não foi preenchido.
     */
    private function perfilIncompleto($usuario): bool
    {
        // Documento de identificação
        if ($usuario->isPessoaFisica() && empty($usuario->cpf)) {
            return true;
        }
        if ($usuario->isPessoaJuridica() && empty($usuario->cnpj)) {
            return true;
        }

        // Contato
        if (empty($usuario->celular)) {
            return true;
        }

        // Endereço
        if (! $usuario->endereco || empty($usuario->endereco->rua)) {
            return true;
        }

        return false;
    }
}
