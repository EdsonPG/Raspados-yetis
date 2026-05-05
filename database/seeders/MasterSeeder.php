<?php

namespace Database\Seeders;

use App\Models\Insumo;
use App\Models\Producto;
use App\Models\Cotizacion;
use App\Models\Evento;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder Maestro: Datos completos para Raspados Yeti ERP
 *
 * Genera datos realistas y coherentes para todos los módulos:
 *   1. Insumos (20) — Materia prima con stock y categorías
 *   2. Productos (10) — Snacks con recetas vinculadas a insumos
 *   3. Cotizaciones (15) — Históricas y recientes con productos
 *   4. Eventos (30) — Distribuidos en el mes actual con 4 estados
 *
 * Orden de ejecución: Catálogos → Pivotes → Transacciones
 */
class MasterSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🧊 Iniciando poblado masivo de Raspados Yeti ERP...');
        $this->command->newLine();

        // ══════════════════════════════════════════════════════════════
        // 1. INSUMOS (20) — Materia Prima
        // ══════════════════════════════════════════════════════════════

        $this->command->info('📦 Creando 20 insumos...');

        Insumo::query()->delete();

        $insumos = [];
        $insumosData = [
            // Jarabes
            ['nombre' => 'Jarabe de Fresa',           'unidad_medida' => 'litros',  'costo_adquisicion' => 45.00,  'stock_actual' => 25.0,  'stock_minimo' => 5.0,  'categoria' => 'Jarabes'],
            ['nombre' => 'Jarabe de Mango',            'unidad_medida' => 'litros',  'costo_adquisicion' => 48.00,  'stock_actual' => 20.0,  'stock_minimo' => 5.0,  'categoria' => 'Jarabes'],
            ['nombre' => 'Jarabe de Tamarindo',        'unidad_medida' => 'litros',  'costo_adquisicion' => 52.00,  'stock_actual' => 18.0,  'stock_minimo' => 5.0,  'categoria' => 'Jarabes'],
            ['nombre' => 'Jarabe de Limón',            'unidad_medida' => 'litros',  'costo_adquisicion' => 42.00,  'stock_actual' => 22.0,  'stock_minimo' => 5.0,  'categoria' => 'Jarabes'],
            ['nombre' => 'Jarabe de Grosella',         'unidad_medida' => 'litros',  'costo_adquisicion' => 50.00,  'stock_actual' => 12.0,  'stock_minimo' => 3.0,  'categoria' => 'Jarabes'],

            // Base y Toppings
            ['nombre' => 'Hielo Triturado',            'unidad_medida' => 'kg',      'costo_adquisicion' => 12.00,  'stock_actual' => 80.0,  'stock_minimo' => 20.0, 'categoria' => 'Base'],
            ['nombre' => 'Leche Condensada',           'unidad_medida' => 'litros',  'costo_adquisicion' => 65.00,  'stock_actual' => 15.0,  'stock_minimo' => 4.0,  'categoria' => 'Toppings'],
            ['nombre' => 'Chamoy Premium',             'unidad_medida' => 'litros',  'costo_adquisicion' => 55.00,  'stock_actual' => 10.0,  'stock_minimo' => 3.0,  'categoria' => 'Toppings'],
            ['nombre' => 'Chile en Polvo Tajín',       'unidad_medida' => 'kg',      'costo_adquisicion' => 95.00,  'stock_actual' => 5.0,   'stock_minimo' => 1.5,  'categoria' => 'Toppings'],
            ['nombre' => 'Gomitas Surtidas',           'unidad_medida' => 'kg',      'costo_adquisicion' => 85.00,  'stock_actual' => 8.0,   'stock_minimo' => 2.0,  'categoria' => 'Toppings'],
            ['nombre' => 'Lunetas de Chocolate',       'unidad_medida' => 'kg',      'costo_adquisicion' => 110.00, 'stock_actual' => 4.0,   'stock_minimo' => 1.0,  'categoria' => 'Toppings'],

            // Contenedores
            ['nombre' => 'Vaso Trole 8oz',             'unidad_medida' => 'piezas',  'costo_adquisicion' => 3.50,   'stock_actual' => 500.0, 'stock_minimo' => 100.0,'categoria' => 'Contenedores'],
            ['nombre' => 'Vaso Trole 16oz',            'unidad_medida' => 'piezas',  'costo_adquisicion' => 5.00,   'stock_actual' => 400.0, 'stock_minimo' => 80.0, 'categoria' => 'Contenedores'],
            ['nombre' => 'Vaso Elotero Térmico',       'unidad_medida' => 'piezas',  'costo_adquisicion' => 2.80,   'stock_actual' => 600.0, 'stock_minimo' => 100.0,'categoria' => 'Contenedores'],
            ['nombre' => 'Cuchara Larga Desechable',   'unidad_medida' => 'piezas',  'costo_adquisicion' => 0.50,   'stock_actual' => 1000.0,'stock_minimo' => 200.0,'categoria' => 'Contenedores'],
            ['nombre' => 'Palito de Madera para Elote', 'unidad_medida' => 'piezas', 'costo_adquisicion' => 0.30,   'stock_actual' => 800.0, 'stock_minimo' => 150.0,'categoria' => 'Contenedores'],

            // Elotes
            ['nombre' => 'Elote Precocido',            'unidad_medida' => 'piezas',  'costo_adquisicion' => 6.00,   'stock_actual' => 100.0, 'stock_minimo' => 30.0, 'categoria' => 'Elotes'],
            ['nombre' => 'Mayonesa McCormick',         'unidad_medida' => 'kg',      'costo_adquisicion' => 75.00,  'stock_actual' => 6.0,   'stock_minimo' => 2.0,  'categoria' => 'Elotes'],
            ['nombre' => 'Queso Rallado Cotija',       'unidad_medida' => 'kg',      'costo_adquisicion' => 120.00, 'stock_actual' => 4.0,   'stock_minimo' => 1.0,  'categoria' => 'Elotes'],
            ['nombre' => 'Mantequilla',                'unidad_medida' => 'kg',      'costo_adquisicion' => 90.00,  'stock_actual' => 3.0,   'stock_minimo' => 1.0,  'categoria' => 'Elotes'],
        ];

        foreach ($insumosData as $data) {
            $insumos[$data['nombre']] = Insumo::create($data);
        }

        $this->command->info('   ✅ 20 insumos creados en 5 categorías.');

        // ══════════════════════════════════════════════════════════════
        // 2. PRODUCTOS + RECETAS (10)
        // ══════════════════════════════════════════════════════════════

        $this->command->info('🍧 Creando 10 productos con recetas...');

        Producto::query()->delete();

        // Helper para vincular recetas
        $crearProducto = function (string $nombre, float $precio, array $receta) use ($insumos) {
            $producto = Producto::create([
                'nombre' => $nombre,
                'precio_sugerido' => $precio,
            ]);

            $pivot = [];
            foreach ($receta as $insumoNombre => $cantidad) {
                $pivot[$insumos[$insumoNombre]->id] = ['cantidad' => $cantidad];
            }
            $producto->insumos()->attach($pivot);

            return $producto;
        };

        $productos = [];

        // ── Troles (Raspados) ────────────────────────────────
        $productos[] = $crearProducto('Trole de Fresa 8oz', 35.00, [
            'Jarabe de Fresa'           => 0.2000,
            'Hielo Triturado'           => 0.1500,
            'Leche Condensada'          => 0.0300,
            'Vaso Trole 8oz'            => 1.0000,
            'Gomitas Surtidas'          => 0.0200,
            'Cuchara Larga Desechable'  => 1.0000,
        ]);

        $productos[] = $crearProducto('Trole de Mango 16oz', 55.00, [
            'Jarabe de Mango'           => 0.4000,
            'Hielo Triturado'           => 0.3000,
            'Leche Condensada'          => 0.0500,
            'Chamoy Premium'            => 0.0200,
            'Vaso Trole 16oz'           => 1.0000,
            'Gomitas Surtidas'          => 0.0400,
            'Cuchara Larga Desechable'  => 1.0000,
        ]);

        $productos[] = $crearProducto('Raspa de Tamarindo', 40.00, [
            'Jarabe de Tamarindo'       => 0.2500,
            'Hielo Triturado'           => 0.2500,
            'Chamoy Premium'            => 0.0300,
            'Chile en Polvo Tajín'      => 0.0050,
            'Vaso Trole 8oz'            => 1.0000,
            'Cuchara Larga Desechable'  => 1.0000,
        ]);

        $productos[] = $crearProducto('Raspa de Limón con Chamoy', 38.00, [
            'Jarabe de Limón'           => 0.2200,
            'Hielo Triturado'           => 0.2000,
            'Chamoy Premium'            => 0.0400,
            'Chile en Polvo Tajín'      => 0.0060,
            'Vaso Trole 8oz'            => 1.0000,
            'Cuchara Larga Desechable'  => 1.0000,
        ]);

        $productos[] = $crearProducto('Yeti Especial Premium', 75.00, [
            'Jarabe de Fresa'           => 0.1500,
            'Jarabe de Mango'           => 0.1500,
            'Jarabe de Tamarindo'       => 0.1000,
            'Hielo Triturado'           => 0.3500,
            'Leche Condensada'          => 0.0800,
            'Chamoy Premium'            => 0.0400,
            'Vaso Trole 16oz'           => 1.0000,
            'Gomitas Surtidas'          => 0.0500,
            'Lunetas de Chocolate'      => 0.0300,
            'Chile en Polvo Tajín'      => 0.0080,
            'Cuchara Larga Desechable'  => 1.0000,
        ]);

        $productos[] = $crearProducto('Trole de Grosella', 38.00, [
            'Jarabe de Grosella'        => 0.2200,
            'Hielo Triturado'           => 0.2000,
            'Leche Condensada'          => 0.0250,
            'Vaso Trole 8oz'            => 1.0000,
            'Cuchara Larga Desechable'  => 1.0000,
        ]);

        // ── Elotes ───────────────────────────────────────────
        $productos[] = $crearProducto('Elote en Vaso', 30.00, [
            'Elote Precocido'           => 1.0000,
            'Mayonesa McCormick'        => 0.0300,
            'Queso Rallado Cotija'      => 0.0200,
            'Chile en Polvo Tajín'      => 0.0050,
            'Mantequilla'              => 0.0100,
            'Vaso Elotero Térmico'      => 1.0000,
            'Cuchara Larga Desechable'  => 1.0000,
        ]);

        $productos[] = $crearProducto('Elote en Palito', 25.00, [
            'Elote Precocido'           => 1.0000,
            'Mayonesa McCormick'        => 0.0250,
            'Queso Rallado Cotija'      => 0.0200,
            'Chile en Polvo Tajín'      => 0.0040,
            'Mantequilla'              => 0.0100,
            'Palito de Madera para Elote' => 1.0000,
        ]);

        $productos[] = $crearProducto('Esquite Preparado', 35.00, [
            'Elote Precocido'           => 1.5000,
            'Mayonesa McCormick'        => 0.0400,
            'Queso Rallado Cotija'      => 0.0300,
            'Chile en Polvo Tajín'      => 0.0060,
            'Chamoy Premium'            => 0.0150,
            'Mantequilla'              => 0.0150,
            'Vaso Elotero Térmico'      => 1.0000,
            'Cuchara Larga Desechable'  => 1.0000,
        ]);

        $productos[] = $crearProducto('Combo Yeti Fiesta (Trole + Elote)', 55.00, [
            'Jarabe de Fresa'           => 0.2000,
            'Hielo Triturado'           => 0.1500,
            'Leche Condensada'          => 0.0300,
            'Vaso Trole 8oz'            => 1.0000,
            'Gomitas Surtidas'          => 0.0200,
            'Elote Precocido'           => 1.0000,
            'Mayonesa McCormick'        => 0.0250,
            'Queso Rallado Cotija'      => 0.0200,
            'Chile en Polvo Tajín'      => 0.0040,
            'Palito de Madera para Elote' => 1.0000,
            'Cuchara Larga Desechable'  => 1.0000,
        ]);

        $this->command->info('   ✅ 10 productos creados: 6 troles/raspas + 3 elotes + 1 combo.');

        // ══════════════════════════════════════════════════════════════
        // 3. COTIZACIONES (15) con productos vinculados
        // ══════════════════════════════════════════════════════════════

        $this->command->info('📋 Creando 15 cotizaciones...');

        DB::table('cotizacion_producto')->delete();
        Cotizacion::query()->delete();

        $colonias = ['San Felipe', 'Quintas del Sol', 'Campanario', 'Centro', 'Las Granjas',
                     'Riberas del Sacramento', 'Lomas del Santuario', 'Nombre de Dios',
                     'Panamericana', 'Valle de la Madrid', 'Cumbres', 'Complejo Industrial'];

        $municipios = ['Chihuahua', 'Chihuahua', 'Chihuahua', 'Aldama', 'Delicias', 'Cuauhtémoc'];

        $calles = ['Av. Trasviña y Retes', 'Calle Independencia', 'Blvd. Ortiz Mena',
                   'Av. de las Industrias', 'Periférico de la Juventud', 'Calle Ojinaga',
                   'Av. Tecnológico', 'Blvd. Juan Pablo II', 'Misión de San Diego',
                   'Calle Presa el Rejón', 'Av. Universidad', 'Calle Aldama'];

        $clientes = [
            'María García López', 'Roberto Mendoza Fierro', 'Ana Luisa Torres',
            'Carlos Hernández Vega', 'Familia Ramírez', 'Daniela Sánchez',
            'Sofía Gómez Duarte', 'Luis Fernando Orozco', 'Paola Villanueva',
            'Jessica Meléndez', 'Familia Domínguez', 'Rodrigo Ávila Terrazas',
            'Club de Leones Chihuahua', 'Iglesia San Francisco', 'DIF Municipal',
        ];

        $estados = ['Pendiente', 'Confirmado', 'Cancelado'];

        for ($i = 0; $i < 15; $i++) {
            // Fecha entre hace 60 días y dentro de 30 días
            $diasOffset = rand(-60, 30);
            $fecha = now()->addDays($diasOffset);

            // Seleccionar 1-4 productos aleatorios para esta cotización
            $numProductos = rand(1, 4);
            $productosSeleccionados = collect($productos)->random($numProductos);

            // Calcular el total simulado
            $totalPrecio = 0;
            $pivot = [];
            foreach ($productosSeleccionados as $prod) {
                $cantidad = rand(20, 150);
                $totalPrecio += $prod->precio_sugerido * $cantidad;
                $pivot[$prod->id] = ['cantidad' => $cantidad];
            }

            // Agregar costos operativos simulados (transporte + nómina + margen)
            $totalPrecio = round($totalPrecio * 1.45, 2); // ~45% margen+operativos

            $cotizacion = Cotizacion::create([
                'cliente_nombre'  => $clientes[$i],
                'fecha_evento'    => $fecha->toDateString(),
                'hora_evento'     => sprintf('%02d:00', rand(9, 20)),
                'municipio'       => $municipios[array_rand($municipios)],
                'colonia'         => $colonias[array_rand($colonias)],
                'calle_numero'    => $calles[array_rand($calles)] . ' #' . rand(100, 9999),
                'descripcion_lugar' => rand(0, 1) ? 'Salón de eventos principal.' : null,
                'total_invitados' => rand(30, 300),
                'total_precio'    => $totalPrecio,
                'estado'          => $estados[array_rand($estados)],
            ]);

            $cotizacion->productos()->attach($pivot);
        }

        $this->command->info('   ✅ 15 cotizaciones con productos vinculados.');

        // ══════════════════════════════════════════════════════════════
        // 4. EVENTOS (30) — Mes actual con apilamiento
        // ══════════════════════════════════════════════════════════════

        $this->command->info('📅 Creando 30 eventos para ' . now()->format('F Y') . '...');

        Evento::query()->delete();

        $anio = (int) date('Y');
        $mes  = (int) date('m');
        $diasEnMes = (int) date('t');

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
            'Combo Elotes y Esquites — 100 personas',
            'Paquete Escolar — Troles para kermés',
        ];

        $nombresEvento = [
            'María García López', 'Roberto Mendoza Fierro', 'Ana Luisa Torres',
            'Carlos Hernández', 'Familia Ramírez', 'Daniela Sánchez (XV Años)',
            'Sofía Gómez', 'Corporativo Jabil', 'Escuela Primaria Revolución',
            'Luis Fernando Orozco', 'Paola Villanueva', 'Colegio Everest',
            'Jessica Meléndez', 'Familia Domínguez', 'Rodrigo Ávila',
            'Club de Leones', 'Iglesia San Francisco', 'DIF Municipal',
            'Guardería IMSS', 'Empresa Lala', 'Boda Martínez-López',
            'Cumpleaños de Santiago', 'Familia Torres', 'Fiesta de Karen',
            'Parroquia San José', 'Gobierno del Estado', 'Hospital Ángeles',
            'Universidad UACH', 'Prepa Federal #2', 'Rancho Los Álamos',
        ];

        $descripciones = [
            'Jardín trasero, acceso por portón blanco.',
            'Salón de fiestas, segundo piso.',
            'Área de piscina al aire libre.',
            'Patio central del colegio.',
            'Comedor de empleados.',
            'Terraza del restaurante.',
            'Parque principal de la cerrada.',
            'Cancha techada del fraccionamiento.',
            'Estacionamiento del centro comercial.',
            'Explanada del centro comunitario.',
            null, null,
        ];

        $notas = [
            'Llevar 2 carritos Yeti y toldo.',
            'Cliente frecuente, aplicar descuento.',
            'Llevar iluminación extra para el carrito.',
            'Requiere factura fiscal.',
            'Pendiente confirmar anticipo.',
            'Alérgico a nueces, cuidar contaminación cruzada.',
            'Evento al aire libre, llevar sombrillas.',
            'Acceso por portón trasero.',
            'Incluir servilletas y bolsas para basura.',
            'El cliente paga al finalizar.',
            null, null, null,
        ];

        // Días con apilamiento forzado (3 eventos c/u)
        $diasApilados = [5, 12, 18, 24];

        // 1) Crear 12 eventos forzados en los 4 días apilados
        foreach ($diasApilados as $dia) {
            $fecha = sprintf('%04d-%02d-%02d', $anio, $mes, min($dia, $diasEnMes));

            for ($j = 0; $j < 3; $j++) {
                $horaInicio = rand(9, 19);
                Evento::create([
                    'cliente_nombre'    => array_shift($nombresEvento) ?? 'Cliente ' . rand(1, 99),
                    'cliente_telefono'  => '614-' . rand(100, 999) . '-' . rand(1000, 9999),
                    'fecha_evento'      => $fecha,
                    'hora_inicio'       => sprintf('%02d:00', $horaInicio),
                    'hora_fin'          => sprintf('%02d:00', min($horaInicio + rand(2, 4), 23)),
                    'municipio'         => $municipios[array_rand($municipios)],
                    'colonia'           => $colonias[array_rand($colonias)],
                    'calle_numero'      => $calles[array_rand($calles)] . ' #' . rand(100, 9999),
                    'descripcion_lugar' => $descripciones[array_rand($descripciones)],
                    'paquete_contratado' => $paquetes[array_rand($paquetes)],
                    'total_invitados'   => [30, 50, 60, 80, 100, 120, 150, 200][array_rand([30, 50, 60, 80, 100, 120, 150, 200])],
                    'total_precio'      => round(rand(2500, 18000) + rand(0, 99) / 100, 2),
                    'estado'            => Evento::ESTADOS[array_rand(Evento::ESTADOS)],
                    'notas'             => $notas[array_rand($notas)],
                ]);
            }
        }

        // 2) Crear los 18 restantes distribuidos aleatoriamente
        for ($i = 0; $i < 18; $i++) {
            $dia = rand(1, $diasEnMes);
            $fecha = sprintf('%04d-%02d-%02d', $anio, $mes, $dia);
            $horaInicio = rand(9, 20);

            Evento::create([
                'cliente_nombre'    => array_shift($nombresEvento) ?? 'Cliente ' . rand(1, 99),
                'cliente_telefono'  => '614-' . rand(100, 999) . '-' . rand(1000, 9999),
                'fecha_evento'      => $fecha,
                'hora_inicio'       => sprintf('%02d:00', $horaInicio),
                'hora_fin'          => sprintf('%02d:00', min($horaInicio + rand(2, 5), 23)),
                'municipio'         => $municipios[array_rand($municipios)],
                'colonia'           => $colonias[array_rand($colonias)],
                'calle_numero'      => $calles[array_rand($calles)] . ' #' . rand(100, 9999),
                'descripcion_lugar' => $descripciones[array_rand($descripciones)],
                'paquete_contratado' => $paquetes[array_rand($paquetes)],
                'total_invitados'   => [30, 50, 80, 100, 150, 200, 250, 300][array_rand([30, 50, 80, 100, 150, 200, 250, 300])],
                'total_precio'      => round(rand(2500, 20000) + rand(0, 99) / 100, 2),
                'estado'            => Evento::ESTADOS[array_rand(Evento::ESTADOS)],
                'notas'             => $notas[array_rand($notas)],
            ]);
        }

        $totalEventos = Evento::count();
        $this->command->info("   ✅ {$totalEventos} eventos creados con apilamiento en días 5, 12, 18 y 24.");

        // ══════════════════════════════════════════════════════════════
        // RESUMEN
        // ══════════════════════════════════════════════════════════════

        $this->command->newLine();
        $this->command->info('═══════════════════════════════════════════');
        $this->command->info('🎉 POBLADO COMPLETO — Raspados Yeti ERP');
        $this->command->info('═══════════════════════════════════════════');
        $this->command->info('   📦 Insumos:       ' . Insumo::count());
        $this->command->info('   🍧 Productos:     ' . Producto::count());
        $this->command->info('   📋 Cotizaciones:  ' . Cotizacion::count());
        $this->command->info('   📅 Eventos:       ' . Evento::count());
        $this->command->info('═══════════════════════════════════════════');
    }
}
