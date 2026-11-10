<?php

namespace App\Console\Commands;

use App\Models\HistoricoRequerimento;
use App\Models\Requerimento;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpirarRequerimentos extends Command
{
    protected $signature = 'requerimentos:expirar';

    protected $description = 'Expira processos sem resposta há pelo menos 90 dias';

    public function handle(): int
    {
        $limite = now()->subDays(90);
        $expirados = 0;

        Requerimento::query()
            ->where('status', Requerimento::STATUS_AGUARDANDO_COMPLEMENTACAO)
            ->whereNotNull('data_devolucao')
            ->where('data_devolucao', '<=', $limite)
            ->orderBy('id')
            ->chunkById(100, function ($requerimentos) use (&$expirados) {
                foreach ($requerimentos as $requerimento) {
                    $foiExpirado = DB::transaction(function () use ($requerimento) {
                        $atual = Requerimento::query()
                            ->lockForUpdate()
                            ->findOrFail($requerimento->id);

                        if (!$atual->verificarExpiracao()) {
                            return false;
                        }

                        HistoricoRequerimento::create([
                            'requerimento_id' => $atual->id,
                            'user_id' => $atual->usuario_id,
                            'status' => Requerimento::STATUS_EXPIRADO,
                            'observacao' => 'Processo expirado após 90 dias sem resposta à solicitação de complementação.',
                        ]);

                        return true;
                    });

                    if ($foiExpirado) {
                        $expirados++;
                    }
                }
            });

        $this->info("Processos expirados: {$expirados}.");

        return self::SUCCESS;
    }
}
