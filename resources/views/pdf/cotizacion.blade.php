<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Cotizacion de Evento - Raspados Yeti</title>
  <style>
    body { font-family: "DejaVu Sans", sans-serif; font-size: 12px; color: #0f172a; margin: 0; padding: 24px; }
    .card { border: 1px solid #e2e8f0; padding: 20px; }
    .muted { color: #64748b; }
    .title { font-size: 18px; font-weight: 700; margin: 0; }
    .section { margin-top: 16px; }
    .section-title { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #64748b; font-weight: 700; margin: 0 0 6px 0; }
    .table { width: 100%; border-collapse: collapse; }
    .table th { text-align: left; font-size: 11px; text-transform: uppercase; color: #64748b; border-bottom: 1px solid #e2e8f0; padding: 6px 4px; }
    .table td { border-bottom: 1px solid #e2e8f0; padding: 8px 4px; }
    .right { text-align: right; }
    .totals td { border-bottom: none; padding: 6px 4px; }
    .grand { font-size: 14px; font-weight: 800; color: #0b5a85; }
    .pill { background: #0b5a85; color: #fff; font-weight: 700; text-align: center; width: 42px; height: 42px; }
    .light-box { background: #f1f5f9; padding: 10px; }
    .footer { margin-top: 16px; font-size: 10px; color: #94a3b8; }
  </style>
</head>
<body>
  <div class="card">
    <table width="100%" cellpadding="0" cellspacing="0">
      <tr>
        <td width="50%" valign="middle">
          <table cellpadding="0" cellspacing="0">
            <tr>
              <td class="pill" valign="middle" align="center" style="background: #0b5a85; color: #fff; font-size: 14px;">RY</td>
              <td style="padding-left: 12px;">
                <div class="title">Raspados Yeti</div>
                <div class="muted" style="font-size: 11px;">Cotizacion de Evento</div>
              </td>
            </tr>
          </table>
        </td>
        <td width="50%" align="right" valign="middle">
          <table cellpadding="0" cellspacing="0">
            <tr>
              <td class="muted" style="font-size: 11px;">Folio</td>
              <td style="padding-left: 8px; font-weight: 700;">{{ $folio }}</td>
            </tr>
            <tr>
              <td class="muted" style="font-size: 11px;">Emision</td>
              <td style="padding-left: 8px;">{{ $fecha_emision }}</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>

    <div class="section light-box">
      <div class="section-title">Datos del Evento</div>
      <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td width="25%" class="muted">Cliente:</td>
          <td width="75%" style="font-weight: 700;">{{ $cliente['nombre'] }}</td>
        </tr>
        <tr>
          <td class="muted">Fecha y Hora:</td>
          <td>{{ $evento['fecha'] }} - {{ $evento['hora'] }}</td>
        </tr>
        <tr>
          <td class="muted">Ubicacion:</td>
          <td>{{ $evento['calle_numero'] }}, {{ $evento['colonia'] }}, {{ $evento['municipio'] }}</td>
        </tr>
        <tr>
          <td class="muted">Ref:</td>
          <td>{{ $evento['descripcion_lugar'] ?: 'Sin referencias' }}</td>
        </tr>
      </table>
    </div>

    <div class="section">
      <div class="section-title">Productos Cotizados</div>
      <table class="table" cellpadding="0" cellspacing="0">
        <thead>
          <tr>
            <th width="20%">Cantidad</th>
            <th width="60%">Concepto</th>
            <th width="20%" class="right">Subtotal</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($productos as $item)
            <tr>
              <td>{{ number_format($item['cantidad'], 2) }}</td>
              <td>{{ $item['nombre'] }}</td>
              <td class="right">${{ number_format($item['subtotal'], 2) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="section">
      <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td width="60%"></td>
          <td width="40%">
            <table width="100%" cellpadding="0" cellspacing="0" class="totals">
              <tr>
                <td class="muted">Costo de Snacks</td>
                <td class="right">${{ number_format($totales['snacks'], 2) }}</td>
              </tr>
              <tr>
                <td class="muted">Logistica y Operacion</td>
                <td class="right">${{ number_format($totales['logistica'], 2) }}</td>
              </tr>
              <tr>
                <td class="grand">Gran Total</td>
                <td class="right grand">${{ number_format($totales['total'], 2) }}</td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </div>

    <div class="footer">
      Cotizacion valida por 15 dias. Para asegurar la fecha del evento, se requiere un anticipo del 50%.
    </div>
  </div>
</body>
</html>
