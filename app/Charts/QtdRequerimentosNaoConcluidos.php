<?php

namespace App\Charts;

use ArielMejiaDev\LarapexCharts\LarapexChart;
use App\Models\Setor;
use App\Models\Requerimento;

class QtdRequerimentosNaoConcluidos
{
    public function build(): \ArielMejiaDev\LarapexCharts\BarChart
    {

        // Buscar todos os setores
        $setores = Setor::pluck('setor_sigla')->toArray();

        foreach ($setores as $sigla) {
            $naoConcluidos = Requerimento::whereHas('setor', function ($q) use ($sigla) {
                $q->where('setor_sigla', $sigla);
            })->where('status', '!=', 'concluido')->count();
        }

        // Converte os requerimentos não concluídos em array;
        $emAnalise[] = $naoConcluidos;

        return (new LarapexChart)->barChart()
            ->setTitle('Requerimentos não concluídos (por setor)')
            ->setHeight(400)
            ->setSubtitle('Requerimentos que estão em aberto, em análise ou em andamento.')
            ->addData($emAnalise, 'Requerimentos não concluídos')
            ->setXAxis($setores);
    }
}
