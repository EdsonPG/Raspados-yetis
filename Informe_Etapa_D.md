# Informe Detallado: Módulo D (Control de Inventario y Logística)

Este documento detalla todas las implementaciones realizadas en la **Etapa D** del proyecto **Raspados Yeti ERP/CRM**, para que todo el equipo de desarrollo y administración tenga claridad de cómo funciona el nuevo módulo de control de inventario.

---

## 1. El Problema que se Resolvió
Antes del Módulo D, no existía un control automatizado que restara los ingredientes (insumos) del almacén virtual cuando se agendaban o completaban eventos. Además, el inventario podía descontarse varias veces por error si un mismo evento se actualizaba o cambiaba de estado repetidamente.

## 2. Solución de "Idempotencia" (Evitar descuentos duplicados)
Se implementó un mecanismo de seguridad (idempotencia) que garantiza que los ingredientes de un evento se descuenten **estrictamente una sola vez**.

*   **Cambio en Base de Datos**: Se creó la migración `2026_05_14_130000_add_inventario_descontado_to_eventos_table.php` que agrega la columna `inventario_descontado` (verdadero/falso) a los eventos.
*   **Lógica de Descuento (`EventoController.php`)**: 
    *   Cuando un evento pasa al estado **"Confirmado"** o **"Completado"**, el sistema verifica la bandera `inventario_descontado`.
    *   Si es `falso`, calcula matemáticamente el consumo exacto: multiplica la cantidad de productos cotizados por la receta exacta del producto.
    *   Resta este total del `stock_actual` de cada insumo.
    *   Finalmente, cambia la bandera `inventario_descontado` a `verdadero`.
    *   Si el evento vuelve a cambiar de estado en el futuro, el sistema ignorará el descuento porque la bandera ya está en verdadero.

## 3. Alertas de Reabastecimiento Automáticas
*   Tras restar el stock, el sistema compara el nuevo `stock_actual` contra el `stock_minimo` de cada insumo.
*   Si el stock actual es igual o menor al mínimo, se genera una notificación en el sistema (campanita del dashboard) alertando al equipo: *"Alerta de Reabastecimiento: El insumo XYZ tiene un stock actual crítico"*.

## 4. Proyecciones Inteligentes de Inventario
Se creó un nuevo endpoint en el backend (`InventarioController.php`) y una nueva vista en el frontend (`inventario.astro`) capaces de **"ver el futuro"** del inventario.

*   **¿Cómo funciona?**: El sistema escanea todos los eventos futuros agendados que aún están en estado *"Cotizado"* o *"Anticipo Pagado"*.
*   Calcula y suma qué tantos insumos van a requerir esos eventos en total. A esto se le llama **Consumo Proyectado**.
*   Resta el Consumo Proyectado al Stock Actual para mostrar un **Stock Proyectado**. Esto le permite al personal saber si, con el inventario actual de la bodega, les alcanzará para cubrir los eventos de la próxima semana, o si se quedarán sin ingredientes antes del día del evento.

## 5. Panel de Logística (Frontend Astro)
*   **KPIs en tiempo real**: Tarjetas que muestran el Total de Insumos, Insumos Críticos, y Eventos impactando el inventario.
*   **Semáforo de Salud**: Cada insumo tiene una barra visual y un estado (`optimo`, `alerta_actual`, `critico`, `agotado`).
*   **Reabastecimiento Ágil**: Los administradores ahora pueden agregar mercancía rápidamente haciendo clic en reabastecer sobre cualquier insumo para sumar unidades directo al stock actual.

---

### Notas para el Equipo de Desarrollo:
El código ha sido probado y está operativo. 
Todos los compañeros deben hacer lo siguiente para ver los cambios:
1. `git pull` para descargar estos cambios a su computadora.
2. `php artisan migrate` para agregar la nueva columna a su base de datos local.
3. Ingresar al portal y navegar a la sección de Inventario desde el menú lateral.
