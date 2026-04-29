<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Modelo: Producto (Snack Final)
 *
 * Representa un producto terminado que se vende al cliente.
 * Su precio puede calcularse automáticamente a partir de la receta
 * (suma de costo de cada insumo * cantidad utilizada).
 *
 * @property int    $id
 * @property string $nombre          - Nombre del producto final
 * @property float  $precio_sugerido - Precio de venta sugerido/calculado
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Insumo[] $insumos
 */
class Producto extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada al modelo.
     * Laravel infiere "productos" automáticamente, pero lo definimos explícitamente
     * por claridad al trabajar con nombres en español.
     *
     * @var string
     */
    protected $table = 'productos';

    /**
     * Atributos que se pueden asignar masivamente.
     *
     * @var array<string>
     */
    protected $fillable = [
        'nombre',
        'precio_sugerido',
    ];

    /**
     * Conversión de tipos (casting) para los atributos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'precio_sugerido' => 'decimal:2',
        ];
    }

    /**
     * Relación: Insumos que componen la receta de este producto.
     *
     * Un producto utiliza muchos insumos en su receta.
     * La tabla pivote 'insumo_producto' almacena la cantidad exacta
     * de cada insumo necesaria para producir una unidad de este producto.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function insumos(): BelongsToMany
    {
        return $this->belongsToMany(Insumo::class, 'insumo_producto')
                    ->withPivot('cantidad')
                    ->withTimestamps();
    }

    public function cotizaciones(): BelongsToMany
    {
        return $this->belongsToMany(Cotizacion::class, 'cotizacion_producto')
            ->withPivot('cantidad')
            ->withTimestamps();
    }

    /**
     * Calcular el costo de producción base del producto.
     *
     * Suma el costo de cada insumo multiplicado por la cantidad
     * requerida según la receta. Este método permite automatizar
     * el cálculo del costo de producción para definir precios.
     *
     * Ejemplo:
     *   Jarabe de Fresa: $50.00/litro * 0.25 litros = $12.50
     *   Hielo:           $15.00/kg    * 0.15 kg      = $ 2.25
     *   Vaso 8oz:        $ 3.00/pieza * 1 pieza      = $ 3.00
     *   ─────────────────────────────────────────────────────
     *   Costo de producción base:                      $17.75
     *
     * @return float
     */
    public function calcularCostoProduccion(): float
    {
        return $this->insumos->sum(function ($insumo) {
            return $insumo->costo_adquisicion * $insumo->pivot->cantidad;
        });
    }
}
