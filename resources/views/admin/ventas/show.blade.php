<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ticket de venta #{{ $venta->id }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            @page { margin: 8mm; }
            .ticket-actions, .ticket-status { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .ticket { border: 0 !important; box-shadow: none !important; max-width: 80mm !important; padding: 0 !important; }
        }
    </style>
</head>
<body class="bg-slate-100 px-4 py-8 text-slate-900">
    <div class="ticket-actions mx-auto mb-6 flex max-w-md flex-wrap gap-3">
        @can('ventas.crear')<a href="{{ route('admin.ventas.create') }}" class="min-h-11 rounded-lg bg-blue-700 px-4 py-3 text-sm font-semibold text-white">Nueva venta</a>@endcan
        @can('ventas.ver')<a href="{{ route('admin.ventas.index') }}" class="min-h-11 rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm">Historial</a>@endcan
        <button type="button" onclick="window.print()" class="min-h-11 rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm font-semibold">Imprimir ticket</button>
    </div>
    @if(session('status'))<p role="status" class="ticket-status mx-auto mb-4 max-w-md text-sm text-emerald-800">{{ session('status') }}</p>@endif
    <main class="ticket mx-auto max-w-md rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <header class="border-b border-dashed border-slate-300 pb-4 text-center">
            <p class="text-sm">Sucursal: {{ $venta->sucursal->nombre }}</p>
            <h1 class="text-xl font-bold">{{ config('app.name') }}</h1>
            <p class="mt-2 text-sm">Ticket de venta #{{ $venta->id }}</p>
            <p class="text-sm">{{ $venta->created_at->format('d/m/Y H:i') }}</p>
        </header>
        <dl class="grid grid-cols-[auto_1fr] gap-x-3 gap-y-2 py-4 text-sm">
            <dt class="text-slate-500">Cliente</dt><dd class="break-words">{{ $venta->cliente->nombre }}</dd>
            @if($venta->cliente->nit_ci)<dt class="text-slate-500">NIT / CI</dt><dd>{{ $venta->cliente->nit_ci }}</dd>@endif
            <dt class="text-slate-500">Atendido por</dt><dd>{{ $venta->usuario->nombre }}</dd>
            <dt class="text-slate-500">Pago</dt><dd>{{ ['efectivo' => 'Efectivo', 'qr' => 'QR', 'transferencia' => 'Transferencia'][$venta->tipo_pago] ?? $venta->tipo_pago }}</dd>
        </dl>
        <ul class="divide-y divide-dashed divide-slate-300 border-y border-dashed border-slate-300">
            @foreach($venta->detalles as $detalle)
            <li class="py-3 text-sm">
                <p class="break-words font-semibold">{{ $detalle->marca_producto }} {{ $detalle->nombre_producto }}</p>
                <div class="mt-1 flex justify-between gap-3 tabular-nums"><span>{{ $detalle->cantidad }} × Bs {{ number_format((float) $detalle->precio_unitario, 2, ',', '.') }} <span class="text-xs text-slate-500">({{ $detalle->tipo_precio === 'mayor' ? 'Mayor' : 'Menor' }})</span></span><span>Bs {{ number_format((float) $detalle->subtotal, 2, ',', '.') }}</span></div>
            </li>
            @endforeach
        </ul>
        <div class="flex justify-between gap-4 py-5 text-lg font-bold tabular-nums"><span>Total</span><span>Bs {{ number_format((float) $venta->total, 2, ',', '.') }}</span></div>
        <p class="text-center text-xs text-slate-500">Gracias por su compra.</p>
    </main>
</body>
</html>
