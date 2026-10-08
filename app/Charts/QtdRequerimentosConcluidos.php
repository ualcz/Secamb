<?php

namespace App\Charts;

use ArielMejiaDev\LarapexCharts\LarapexChart;
use App\Models\Requerimento;

class QtdRequerimentosConcluidos
{
    public function build(): \ArielMejiaDev\LarapexCharts\LineChart
    {
        $dados = Requerimento::selectRaw('objetoDoRequerimento, MONTH(created_at) as mes, COUNT(*) as total')
            ->groupBy('objetoDoRequerimento', 'mes')
            ->where('status', 'concluido')
            ->orderBy('mes')
            ->get();

        $objetos = $dados->groupBy('objetoDoRequerimento');

        $meses = [
        'Jan.', 'Fev.', 'Mar.', 'Abr.',
        'Mai.', 'Jun.', 'Jul.', 'Ago.',
        'Set.', 'Out.', 'Nov.', 'Dez.'
        ];

        $series = [];

        foreach ($objetos as $registros) {
            $primeiro = $registros->first();
            if (!$primeiro) continue;

            $nomeObjeto = (string) $primeiro->objetoDoRequerimento;

            $valores = [];
            foreach (range(1, 12) as $m) {
                $registroMes = $registros->firstWhere('mes', $m);
                $valores[] = $registroMes ? $registroMes->total : 0;
            }

            $series[] = [
                'name' => $nomeObjeto,
                'data' => $valores
            ];
        }

        $concluidos = (new LarapexChart)->lineChart()
        ->setTitle('Requerimentos concluídos (por mês)')
        ->setDataset($series)
        ->setXAxis($meses)
        ->setHeight(130);

        return $concluidos;
    }
}
