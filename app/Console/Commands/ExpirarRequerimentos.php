<?php

namespace App\Console\Commands;

use App\Models\HistoricoRequerimento;
use App\Models\Requerimento;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpirarRequerimentos extends Command
{
    protected $signature = 'requerimentos:expirar';

    protected $description = 'Arquiva processos sem resposta à complementação há pelo menos 120 dias';

    public function handle(): int
    {
        $limite = now()->subDays(Requerimento::DIAS_LIMITE_COMPLEMENTACAO);
        $arquivados = 0;

        Requerimento::query()
            ->where('status', Requerimento::STATUS_AGUARDANDO_COMPLEMENTACAO)
            ->whereNotNull('data_devolucao')
            ->where('data_devolucao', '<=', $limite)
            ->orderBy('id')
            ->chunkById(100, function ($requerimentos) use (&$arquivados) {
                foreach ($requerimentos as $requerimento) {
                    $foiArquivado = DB::transaction(function () use ($requerimento) {
                        $atual = Requerimento::query()
                            ->lockForUpdate()
                            ->findOrFail($requerimento->id);

                        if (!$atual->arquivarSeComplementacaoVencida()) {
                            return false;
                        }

                        HistoricoRequerimento::create([
                            'requerimento_id' => $atual->id,
                            'user_id' => $atual->usuario_id,
                            'status' => Requerimento::STATUS_ARQUIVADO_COMPLEMENTACAO,
                            'observacao' => 'Processo arquivado após 120 dias sem resposta à solicitação de complementação.',
                        ]);

                        return true;
                    });

                    if ($foiArquivado) {
                        $arquivados++;
                    }
                }
            });

        $this->info("Processos arquivados: {$arquivados}.");

        return self::SUCCESS;
    }
}
