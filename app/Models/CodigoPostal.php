<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Un asentamiento (colonia) del catálogo SEPOMEX. Un CP puede tener varias colonias. */
class CodigoPostal extends Model
{
    protected $table = 'codigos_postales';

    public $timestamps = false;

    protected $fillable = ['cp', 'colonia', 'tipo_asentamiento', 'municipio', 'estado', 'ciudad'];

    /** Nombres de SEPOMEX → nombres de la lista de entidades del sistema (DatosIssfam::ENTIDADES). */
    public const ESTADOS_SEPOMEX = [
        'México' => 'Estado de México',
        'Coahuila de Zaragoza' => 'Coahuila',
        'Michoacán de Ocampo' => 'Michoacán',
        'Veracruz de Ignacio de la Llave' => 'Veracruz',
    ];

    public static function normalizarEstado(string $estado): string
    {
        return self::ESTADOS_SEPOMEX[$estado] ?? $estado;
    }

    /**
     * Datos de un CP para el formulario: estado, municipio y sus colonias.
     *
     * @return array{cp:string, estado:string, municipio:string, ciudad:?string, colonias:array<int,string>}|null
     */
    public static function buscar(string $cp): ?array
    {
        $filas = static::where('cp', $cp)->orderBy('colonia')->get();

        if ($filas->isEmpty()) {
            return null;
        }

        $primera = $filas->first();

        return [
            'cp' => $cp,
            'estado' => $primera->estado,
            // Casi siempre es uno solo; si hubiera más, se usa el más común.
            'municipio' => $filas->countBy('municipio')->sortDesc()->keys()->first(),
            'ciudad' => $primera->ciudad,
            'colonias' => $filas->pluck('colonia')->unique()->values()->all(),
        ];
    }
}
