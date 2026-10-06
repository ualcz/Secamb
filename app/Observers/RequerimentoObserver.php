<?php

namespace App\Observers;

use App\Models\Requerimento;
use App\Models\HistoricoRequerimento;
use Illuminate\Support\Facades\Auth;

class RequerimentoObserver
{
    public function created(Requerimento $requerimento): void
    {
        HistoricoRequerimento::firstOrCreate(
            ['requerimento_id' => $requerimento->id],
            [
                'user_id'    => Auth::id() ?? $requerimento->usuario_id,
                'status'     => $requerimento->status ?? 'Aberto',
                'observacao' => 'Requerimento cadastrado no sistema.',
            ]
        );
    }

}
