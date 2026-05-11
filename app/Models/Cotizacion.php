<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo: Cotizacion
 */
class Cotizacion extends Model
{
    use HasFactory;

    protected $table = 'cotizaciones';

    protected $fillable = [
        'cliente_id',
        'cliente_nombre',
        'fecha_evento',
        'hora_evento',
        'municipio',
        'colonia',
        'calle_numero',
        'descripcion_lugar',
        'total_invitados',
        'total_precio',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_evento' => 'date',
            'hora_evento' => 'datetime:H:i',
            'total_precio' => 'decimal:2',
        ];
    }

    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'cotizacion_producto')
            ->withPivot('cantidad')
            ->withTimestamps();
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
