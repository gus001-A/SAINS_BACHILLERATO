<?php

namespace App\Exports;

use App\Models\DocumentoEstudiante;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EstudiantesExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles
{
    private const ORDEN_TIPOS = [
        DocumentoEstudiante::TIPO_ACTA_NACIMIENTO,
        DocumentoEstudiante::TIPO_CURP,
        DocumentoEstudiante::TIPO_CERTIFICADO_SECUNDARIA,
        DocumentoEstudiante::TIPO_INE,
    ];

    public function collection()
    {
        return User::where('rol', 'estudiante')
            ->with('estudiante.documentos')
            ->orderBy('id')
            ->get();
    }

    public function headings(): array
    {
        return ['Estudiante', 'Correo', 'Teléfono', 'Acta de nacimiento', 'CURP', 'Certificado', 'INE'];
    }

    public function map($user): array
    {
        $e = $user->estudiante;
        $documentos = $e ? $e->documentos->keyBy('tipo') : collect();

        $fila = [
            $e ? trim("{$e->nombre} {$e->paterno} {$e->materno}") : $user->correo,
            $user->correo,
            $e?->telefono ?: '—',
        ];

        foreach (self::ORDEN_TIPOS as $tipo) {
            $doc = $documentos->get($tipo);
            $fila[] = $this->textoEstatus($doc);
        }

        return $fila;
    }

    private function textoEstatus($doc): string
    {
        if (!$doc) {
            return 'No subido';
        }

        return match ($doc->estatus) {
            DocumentoEstudiante::ESTATUS_APROBADO => 'Aprobado',
            DocumentoEstudiante::ESTATUS_RECHAZADO => 'Rechazado — ' . ($doc->observaciones ?: 'Sin motivo especificado'),
            DocumentoEstudiante::ESTATUS_PENDIENTE => 'Pendiente de revisión',
            default => ucfirst($doc->estatus),
        };
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 30,
            'C' => 15,
            'D' => 35,
            'E' => 35,
            'F' => 35,
            'G' => 35,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
