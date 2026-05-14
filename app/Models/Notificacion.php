<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    use HasFactory;

    protected $table = 'notificaciones';

    protected $fillable = [
        'tipo',
        'titulo',
        'mensaje',
        'accion_url',
        'contexto',
        'leida_at',
    ];

    protected function casts(): array
    {
        return [
            'contexto' => 'array',
            'leida_at' => 'datetime',
        ];
    }

    public function scopeNoLeidas($query)
    {
        return $query->whereNull('leida_at');
    }

    public function marcarLeida(): bool
    {
        $this->leida_at = now();

        return $this->save();
    }
}
