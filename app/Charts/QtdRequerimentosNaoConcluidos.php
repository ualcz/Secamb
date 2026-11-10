<?php

namespace App\Charts;

use ArielMejiaDev\LarapexCharts\LarapexChart;
use App\Models\Setor;
use App\Models\Requerimento;

class QtdRequerimentosNaoConcluidos
{
    public function build(): \ArielMejiaDev\LarapexCharts\BarChart
    {

        $requerimentosNaoConcluidos = [];

        // Buscar todos os setores
        $setores = Setor::pluck('setor_sigla')->toArray();

        foreach ($setores as $sigla) {
            $naoConcluidos = Requerimento::whereHas('setor', function ($q) use ($sigla) {
                $q->where('setor_sigla', $sigla);
            })->whereNotIn('status', Requerimento::STATUS_TERMINAIS)->count();

            $requerimentosNaoConcluidos[] = $naoConcluidos;
        }      

        return (new LarapexChart)->barChart()
            ->setTitle('Processos em andamento (por setor)')
            ->setHeight(400)
            ->setSubtitle('Processos que ainda não chegaram a um estado terminal.')
            ->addData($requerimentosNaoConcluidos, 'Processos em andamento')
            ->setXAxis($setores);
    }
}
