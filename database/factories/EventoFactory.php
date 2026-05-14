<?php

namespace Database\Factories;

use App\Models\Evento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory: Evento
 *
 * Genera eventos aleatorios para el mes actual con datos
 * realistas del negocio de Raspados Yeti en Chihuahua.
 */
class EventoFactory extends Factory
{
    protected $model = Evento::class;

    /**
     * Definir el estado por defecto del modelo.
     */
    public function definition(): array
    {
        // Rango de fechas: mes actual + próximos 3 meses (4 meses total)
        $anioActual = (int) date('Y');
        $mesActual  = (int) date('m');

        // Elegir un mes aleatorio dentro del rango [mesActual, mesActual+3]
        $offsetMes = $this->faker->numberBetween(0, 3);
        $mesObjetivo = $mesActual + $offsetMes;
        $anio = $anioActual;

        // Manejar desbordamiento de año (ej. Nov+3 = Feb del siguiente año)
        if ($mesObjetivo > 12) {
            $mesObjetivo -= 12;
            $anio++;
        }

        $diasEnMes = cal_days_in_month(CAL_GREGORIAN, $mesObjetivo, $anio);

        // Fecha aleatoria dentro del mes seleccionado
        $dia = $this->faker->numberBetween(1, $diasEnMes);
        $fecha = sprintf('%04d-%02d-%02d', $anio, $mesObjetivo, $dia);

        // Hora de inicio entre 9:00 y 20:00
        $horaInicio = $this->faker->numberBetween(9, 20);
        $duracion   = $this->faker->numberBetween(2, 5); // 2-5 horas
        $horaFin    = min($horaInicio + $duracion, 23);

        // Paquetes reales del negocio
        $paquetes = [
            'Paquete Básico 50 personas — Trole de Fresa',
            'Paquete Básico Fiesta Infantil 80 pax',
            'Barra Libre de Troles Estudiantil — 3 horas',
            'Paquete Premium Nocturno — Yeti Especial y Gomitas',
            'Servicio Ejecutivo — Raspas de Tamarindo y Mango',
            'Paquete XV Años 120 personas — Todo incluido',
            'Barra Libre Premium 100 pax — Todas las Variedades',
            'Paquete Corporativo — Raspas y Troles Mixtos',
            'Paquete Boda 200 invitados — Servicio de Mesa',
            'Mini Yeti — Fiesta pequeña 30 personas',
        ];

        // Colonias y calles reales de Chihuahua
        $colonias = [
            'San Felipe', 'Quintas del Sol', 'Campanario', 'Lomas del Santuario',
            'Centro', 'Las Granjas', 'Riberas del Sacramento', 'Complejo Industrial',
            'Nombre de Dios', 'Panamericana', 'Valle de la Madrid', 'Cumbres',
        ];

        $calles = [
            'Av. Trasviña y Retes', 'Calle Independencia', 'Blvd. Ortiz Mena',
            'Av. de las Industrias', 'Periférico de la Juventud', 'Calle Ojinaga',
            'Av. Tecnológico', 'Blvd. Juan Pablo II', 'Calle Aldama',
            'Misión de San Diego', 'Calle Presa el Rejón', 'Av. Universidad',
        ];

        $municipios = ['Chihuahua', 'Chihuahua', 'Chihuahua', 'Aldama', 'Delicias', 'Cuauhtémoc'];

        $descripciones = [
            'Jardín trasero de la casa.',
            'Salón de fiestas, segundo piso.',
            'Área de piscina al aire libre.',
            'Patio central del colegio.',
            'Comedor de empleados.',
            'Terraza del restaurante.',
            'Parque principal de la cerrada.',
            'Cancha techada del fraccionamiento.',
            null,
            null,
        ];

        $nombres = [
            'María García López', 'Roberto Mendoza Fierro', 'Ana Luisa Torres',
            'Carlos Hernández', 'Familia Ramírez', 'Daniela Sánchez',
            'Sofía Gómez Duarte', 'Corporativo Jabil', 'Escuela Primaria Revolución',
            'Luis Fernando Orozco', 'Paola Villanueva', 'Colegio Everest',
            'Jessica Meléndez', 'Familia Domínguez', 'Rodrigo Ávila Terrazas',
            'Club de Leones Chihuahua', 'Iglesia San Francisco', 'DIF Municipal',
        ];

        // Estado lógico según la fecha del evento respecto a hoy
        $hoy = date('Y-m-d');
        $esPasado = $fecha < $hoy;

        // Pasado → puede estar Completado o Confirmado (caso olvidado)
        // Futuro → solo Cotizado, Anticipo Pagado o Confirmado (nunca Completado)
        $estado = $esPasado
            ? $this->faker->randomElement(['Completado', 'Confirmado'])
            : $this->faker->randomElement(['Cotizado', 'Anticipo Pagado', 'Confirmado']);

        return [
            'cliente_nombre'     => $this->faker->randomElement($nombres),
            'cliente_telefono'   => '614-' . $this->faker->numerify('###-####'),
            'fecha_evento'       => $fecha,
            'hora_inicio'        => sprintf('%02d:00', $horaInicio),
            'hora_fin'           => sprintf('%02d:00', $horaFin),
            'municipio'          => $this->faker->randomElement($municipios),
            'colonia'            => $this->faker->randomElement($colonias),
            'calle_numero'       => $this->faker->randomElement($calles) . ' #' . $this->faker->numberBetween(100, 9999),
            'descripcion_lugar'  => $this->faker->randomElement($descripciones),
            'paquete_contratado' => $this->faker->randomElement($paquetes),
            'total_invitados'    => $this->faker->randomElement([30, 50, 60, 80, 100, 120, 150, 200, 250, 300]),
            'total_precio'       => $this->faker->randomFloat(2, 2500, 20000),
            'estado'             => $estado,
            'notas'              => $this->faker->optional(0.7)->randomElement([
                'Llevar 2 carritos Yeti y toldo.',
                'Cliente frecuente, aplicar descuento.',
                'Llevar iluminación extra para el carrito.',
                'Requiere factura fiscal.',
                'Pendiente confirmar anticipo.',
                'Alérgico a nueces, cuidar contaminación cruzada.',
                'Evento al aire libre, llevar sombrillas.',
                'Acceso por portón trasero, preguntar por el Sr. García.',
            ]),
        ];
    }
}
