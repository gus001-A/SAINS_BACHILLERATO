<?php

namespace App\Exports;

use App\Models\Pregunta;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PreguntasExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles
{
    private const LETRAS = ['A', 'B', 'C', 'D'];

    public function collection()
    {
        return Pregunta::with('opciones')->orderBy('id')->get();
    }

    public function headings(): array
    {
        return ['ID', 'Área', 'Pregunta', 'Opción A', 'Opción B', 'Opción C', 'Opción D', 'Respuesta Correcta (A-D)', 'Justificación'];
    }

    public function map($pregunta): array
    {
        $opciones = $pregunta->opciones->values();
        $letraCorrecta = '';
        $textos = ['', '', '', ''];

        foreach ($opciones as $i => $op) {
            if ($i > 3) break; // como máximo 4 opciones
            $textos[$i] = $op->texto;
            if ($op->es_correcta) {
                $letraCorrecta = self::LETRAS[$i];
            }
        }

        return [
            $pregunta->id,
            $pregunta->area?->nombre,
            $pregunta->pregunta,
            $textos[0],
            $textos[1],
            $textos[2],
            $textos[3],
            $letraCorrecta,
            $pregunta->justificacion,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 20,
            'C' => 55,
            'D' => 24,
            'E' => 24,
            'F' => 24,
            'G' => 24,
            'H' => 14,
            'I' => 40,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
