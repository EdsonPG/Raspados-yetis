<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Modelo: Insumo (Materia Prima)
 *
 * Representa un insumo o materia prima a granel utilizada
 * en la producción de los snacks de Raspados Yeti.
 *
 * @property int    $id
 * @property string $nombre           - Nombre descriptivo del insumo
 * @property string $unidad_medida    - Unidad de medida (litros, kg, piezas, ml)
 * @property float  $costo_adquisicion - Costo por unidad de medida
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Producto[] $productos
 */
class Insumo extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada al modelo.
     * Laravel infiere "insumos" automáticamente, pero lo definimos explícitamente
     * por claridad al trabajar con nombres en español.
     *
     * @var string
     */
    protected $table = 'insumos';

    /**
     * Atributos que se pueden asignar masivamente.
     *
     * @var array<string>
     */
    protected $fillable = [
        'nombre',
        'unidad_medida',
        'costo_adquisicion',
        'stock_actual',
        'stock_minimo',
        'categoria',
    ];

    /**
     * Conversión de tipos (casting) para los atributos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'costo_adquisicion' => 'decimal:2',
            'stock_actual' => 'decimal:2',
            'stock_minimo' => 'decimal:2',
        ];
    }

    /**
     * Relación: Productos que utilizan este insumo en su receta.
     *
     * Un insumo puede ser parte de la receta de muchos productos.
     * La tabla pivote 'insumo_producto' almacena la cantidad exacta
     * de este insumo que se requiere para cada producto.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'insumo_producto')
                    ->withPivot('cantidad')
                    ->withTimestamps();
    }
}
