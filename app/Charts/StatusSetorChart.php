<?php

namespace App\Charts;

use ArielMejiaDev\LarapexCharts\LarapexChart;
use App\Models\Setor;
use App\Models\Requerimento;

class StatusSetorChart
{
    public function build()
    {
        // Buscar todos os setores
        $setores = Setor::pluck('setor_sigla')->toArray();

        $emAnalise = [];

        foreach ($setores as $sigla) {
            $total = Requerimento::whereHas('setor', function ($q) use ($sigla) {
                $q->where('setor_sigla', $sigla);
            })->count();

            $analise = Requerimento::whereHas('setor', function ($qtdAnalise) use ($sigla) {
                $qtdAnalise->where('setor_sigla', $sigla);
            })->where('status', 'em análise')->count();

            $emAnalise[] = $total > 0 ? round($analise) : 0;
        }
        
        $progressBarChart = (new LarapexChart)->horizontalBarChart()
            ->setTitle('Status por Setor')
            ->setSubtitle('Requerimentos em análise (por setor)')
            ->addData($emAnalise, 'Em Análise')
            ->setXAxis($setores)
            ->setHeight(220)
            ->setStacked()
            ->setOptions([
                'grid' => ['show' => false],
                'plotOptions' => [
                    'bar' => [
                        'horizontal' => true,
                        'barHeight' => '60%',
                        'borderRadius' => 6,
                        'borderRadiusWhenStacked' => 'last',
                    ],
                ],
                'dataLabels' => [
                    'enabled' => true,
                    'formatter' => 'function (val) { return val + "%"; }',
                    'position' => 'center',
                ],
                'xaxis' => [
                    'labels' => ['show' => false],
                    'axisBorder' => ['show' => false],
                    'axisTicks' => ['show' => false],
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
                'chart' => [
                    'stacked' => true,
                    'stackType' => '100%',
                    'toolbar' => ['show' => false],
                ],
            ]);
        return $progressBarChart;
    }
}