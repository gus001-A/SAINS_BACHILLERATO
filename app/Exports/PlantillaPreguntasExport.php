<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Plantilla con 1 pregunta de ejemplo + hoja de instrucciones para cargar preguntas. */
class PlantillaPreguntasExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [new PlantillaPreguntasHoja(), new PlantillaInstruccionesHoja()];
    }
}

class PlantillaPreguntasHoja implements FromArray, WithTitle, WithColumnWidths, WithStyles
{
    public function title(): string
    {
        return 'Preguntas';
    }

    public function array(): array
    {
        return [
            ['ID', 'Área', 'Pregunta', 'Opción A', 'Opción B', 'Opción C', 'Opción D', 'Opción E', 'Respuesta correcta', 'Justificación'],
            ['', 'Matemáticas', '¿Cuánto es 7 × 8?', '54', '56', '64', '58', '', 'B', '7 × 8 = 56.'],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 8, 'B' => 20, 'C' => 55, 'D' => 24, 'E' => 24, 'F' => 24, 'G' => 24, 'H' => 24, 'I' => 20, 'J' => 40];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A2');

        return [1 => [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1851AD']],
        ]];
    }
}

class PlantillaInstruccionesHoja implements FromArray, WithTitle, WithColumnWidths, WithStyles
{
    public function title(): string
    {
        return 'Instrucciones';
    }

    public function array(): array
    {
        return [
            ['Cómo llenar la plantilla'],
            [''],
            ['• Una pregunta por fila, en la hoja «Preguntas». Reemplaza la pregunta de ejemplo por las tuyas (o bórrala).'],
            ['• Obligatorios: Área, Pregunta, al menos 2 opciones y la Respuesta correcta.'],
            ['• Respuesta correcta: la letra (A-E), el número (1-5) o el texto exacto de la opción.'],
            ['• ID: déjalo vacío para crear preguntas nuevas. Si pones el ID de una pregunta existente, se actualiza.'],
            ['• Si el Área no existe, se crea sola.'],
            ['• Las preguntas que ya están en el banco (mismo texto y misma área) se detectan como duplicadas.'],
            ['• Puedes cambiar el orden de las columnas: se reconocen por su encabezado.'],
            ['• Antes de guardar verás una vista previa con las filas nuevas, actualizadas, duplicadas y con error.'],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 110];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '0E2D66']]]];
    }
}
