<?php

namespace App\Support;

use App\Models\CarreraBachillerato;
use Illuminate\Http\Request;

/**
 * Datos que pide ISSFAM además de los de SAINS: carrera, CURP y domicilio.
 * Las mismas reglas se usan en el registro del estudiante, en su perfil y en el
 * alta/edición desde el panel de administración.
 */
class DatosIssfam
{
    public const CAMPOS = ['carrera_id', 'curp', 'calle_numero', 'colonia', 'codigo_postal', 'municipio', 'entidad_federativa'];

    public const ENTIDADES = [
        'Aguascalientes', 'Baja California', 'Baja California Sur', 'Campeche', 'Chiapas', 'Chihuahua',
        'Ciudad de México', 'Coahuila', 'Colima', 'Durango', 'Estado de México', 'Guanajuato', 'Guerrero',
        'Hidalgo', 'Jalisco', 'Michoacán', 'Morelos', 'Nayarit', 'Nuevo León', 'Oaxaca', 'Puebla',
        'Querétaro', 'Quintana Roo', 'San Luis Potosí', 'Sinaloa', 'Sonora', 'Tabasco', 'Tamaulipas',
        'Tlaxcala', 'Veracruz', 'Yucatán', 'Zacatecas',
    ];


    /** Normaliza mayúsculas/espacios antes de validar. */
    public static function normalizar(Request $request): void
    {
        $request->merge([
            'curp' => $request->filled('curp') ? strtoupper(preg_replace('/\s+/', '', $request->curp)) : null,
            'codigo_postal' => $request->filled('codigo_postal') ? preg_replace('/\D/', '', $request->codigo_postal) : null,
        ]);
    }

    /**
     * @param bool $requerido   true en el registro; el admin puede dejar campos vacíos
     * @param int|null $ignorarEstudianteId  para la regla unique de la CURP al editar
     */
    public static function reglas(bool $requerido = true, ?int $ignorarEstudianteId = null): array
    {
        $req = $requerido ? 'required' : 'nullable';
        $unica = 'unique:estudiante,curp' . ($ignorarEstudianteId ? ',' . $ignorarEstudianteId : '');

        return [
            'carrera_id' => [$req, 'exists:carreras_bachillerato,id'],
            // Solo se pide que tenga 18 caracteres (sin validar el formato oficial).
            'curp' => [$req, 'string', 'size:18', $unica],
            'calle_numero' => [$req, 'string', 'max:150'],
            'colonia' => [$req, 'string', 'max:120'],
            'codigo_postal' => [$req, 'digits:5'],
            'municipio' => [$req, 'string', 'max:120'],
            'entidad_federativa' => [$req, 'in:' . implode(',', self::ENTIDADES)],
        ];
    }

    public static function mensajes(): array
    {
        return [
            'curp.size' => 'La CURP debe tener exactamente 18 caracteres.',
            'curp.unique' => 'Esta CURP ya está registrada con otra cuenta.',
            'codigo_postal.digits' => 'El código postal debe tener 5 dígitos.',
            'entidad_federativa.in' => 'Selecciona una entidad federativa de la lista.',
        ];
    }

    public static function atributos(): array
    {
        return [
            'carrera_id' => 'carrera', 'curp' => 'CURP', 'calle_numero' => 'calle y número',
            'colonia' => 'colonia', 'codigo_postal' => 'código postal', 'municipio' => 'municipio',
            'entidad_federativa' => 'entidad federativa',
        ];
    }

    /** Props para los formularios del frontend. */
    public static function catalogos(): array
    {
        return [
            'carreras' => CarreraBachillerato::opciones(),
            'entidades' => self::ENTIDADES,
        ];
    }

    /** Datos ya guardados de un estudiante, para precargar formularios. */
    public static function valores($estudiante): array
    {
        return collect(self::CAMPOS)->mapWithKeys(fn ($c) => [$c => $estudiante?->{$c}])->all();
    }
}
