<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo: Evento
 *
 * Representa un evento programado de servicio de Raspados Yeti.
 * Cada evento sigue un flujo de estados:
 *   Cotizado → Anticipo Pagado → Confirmado → Completado
 *
 * @property int         $id
 * @property string      $cliente_nombre
 * @property string|null $cliente_telefono
 * @property \Carbon\Carbon $fecha_evento
 * @property string      $hora_inicio
 * @property string|null $hora_fin
 * @property string      $municipio
 * @property string      $colonia
 * @property string      $calle_numero
 * @property string|null $descripcion_lugar
 * @property string      $paquete_contratado
 * @property int         $total_invitados
 * @property float       $total_precio
 * @property string      $estado
 * @property string|null $notas
 * @property int|null    $cotizacion_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Evento extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'eventos';

    /**
     * Estados válidos del flujo de trabajo del evento.
     */
    public const ESTADO_COTIZADO         = 'Cotizado';
    public const ESTADO_ANTICIPO_PAGADO  = 'Anticipo Pagado';
    public const ESTADO_CONFIRMADO       = 'Confirmado';
    public const ESTADO_COMPLETADO       = 'Completado';

    /**
     * Lista ordenada de estados (flujo secuencial).
     *
     * @var array<int, string>
     */
    public const ESTADOS = [
        self::ESTADO_COTIZADO,
        self::ESTADO_ANTICIPO_PAGADO,
        self::ESTADO_CONFIRMADO,
        self::ESTADO_COMPLETADO,
    ];

    /**
     * Transiciones válidas de estado.
     * Clave = estado actual, Valor = estados a los que puede transicionar.
     *
     * @var array<string, array<string>>
     */
    public const TRANSICIONES = [
        self::ESTADO_COTIZADO        => [self::ESTADO_ANTICIPO_PAGADO],
        self::ESTADO_ANTICIPO_PAGADO => [self::ESTADO_CONFIRMADO],
        self::ESTADO_CONFIRMADO      => [self::ESTADO_COMPLETADO],
        self::ESTADO_COMPLETADO      => [],
    ];

    /**
     * Atributos que se pueden asignar masivamente.
     *
     * @var array<string>
     */
    protected $fillable = [
        'cliente_nombre',
        'cliente_telefono',
        'fecha_evento',
        'hora_inicio',
        'hora_fin',
        'municipio',
        'colonia',
        'calle_numero',
        'descripcion_lugar',
        'paquete_contratado',
        'total_invitados',
        'total_precio',
        'estado',
        'notas',
        'cotizacion_id',
    ];

    /**
     * Conversión de tipos (casting) para los atributos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_evento'    => 'date',
            'total_precio'    => 'decimal:2',
            'total_invitados' => 'integer',
        ];
    }

    // ─── Relaciones ──────────────────────────────────────────────────────

    /**
     * Cotización de la cual se originó este evento (opcional).
     */
    public function cotizacion(): BelongsTo
    {
        return $this->belongsTo(Cotizacion::class);
    }

    // ─── Métodos de negocio ──────────────────────────────────────────────

    /**
     * Verificar si el evento puede transicionar a un estado dado.
     *
     * @param string $nuevoEstado
     * @return bool
     */
    public function puedeTransicionarA(string $nuevoEstado): bool
    {
        $transicionesPermitidas = self::TRANSICIONES[$this->estado] ?? [];

        return in_array($nuevoEstado, $transicionesPermitidas, true);
    }

    /**
     * Cambiar el estado del evento si la transición es válida.
     *
     * @param string $nuevoEstado
     * @return bool True si se cambió, false si la transición no es válida.
     */
    public function cambiarEstado(string $nuevoEstado): bool
    {
        if (!$this->puedeTransicionarA($nuevoEstado)) {
            return false;
        }

        $this->estado = $nuevoEstado;
        $this->save();

        return true;
    }

    /**
     * Obtener la dirección completa formateada.
     *
     * @return string
     */
    public function getDireccionCompletaAttribute(): string
    {
        $partes = [
            $this->calle_numero,
            $this->colonia,
            $this->municipio,
        ];

        return implode(', ', array_filter($partes));
    }
}
