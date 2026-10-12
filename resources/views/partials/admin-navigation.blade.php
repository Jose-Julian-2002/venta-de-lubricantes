@php
    $navigationGroups = [
        'Resumen' => [
            ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'permission' => null, 'icon' => 'grid', 'active' => request()->routeIs('admin.dashboard')],
        ],
        'Operaciones' => [
            ['label' => 'Clientes', 'route' => 'admin.clientes.index', 'permission' => 'clientes.ver', 'icon' => 'users', 'active' => request()->routeIs('admin.clientes.*')],
            ['label' => 'Punto de venta', 'route' => 'admin.ventas.create', 'permission' => 'ventas.crear', 'icon' => 'plus', 'active' => request()->routeIs('admin.ventas.create')],
            ['label' => 'Historial de ventas', 'route' => 'admin.ventas.index', 'permission' => 'ventas.ver', 'icon' => 'grid', 'active' => request()->routeIs('admin.ventas.index', 'admin.ventas.show')],
            ['label' => 'Compras', 'route' => 'admin.compras.index', 'permission' => 'compras.ver', 'icon' => 'grid', 'active' => request()->routeIs('admin.compras.*')],
            ['label' => 'Registrar compra', 'route' => 'admin.compras.create', 'permission' => 'compras.crear', 'icon' => 'plus', 'active' => request()->routeIs('admin.compras.create'), 'hidden' => auth()->user()->can('compras.ver')],
            ['label' => 'Proveedores', 'route' => 'admin.proveedores.index', 'permission' => 'proveedores.ver', 'icon' => 'users', 'active' => request()->routeIs('admin.proveedores.*')],
        ],
        'Catálogo' => [
            ['label' => 'Categorías', 'route' => 'admin.categorias.index', 'permission' => 'categorias.ver', 'icon' => 'grid', 'active' => request()->routeIs('admin.categorias.*')],
            ['label' => 'Marcas', 'route' => 'admin.marcas.index', 'permission' => 'marcas.ver', 'icon' => 'grid', 'active' => request()->routeIs('admin.marcas.*')],
            ['label' => 'Productos', 'route' => 'admin.productos.index', 'permission' => 'productos.ver', 'icon' => 'grid', 'active' => request()->routeIs('admin.productos.*')],
        ],
        'Inventario' => [
            ['label' => 'Stock y Kardex', 'route' => 'admin.stock.index', 'permission' => 'stock.ver', 'icon' => 'grid', 'active' => request()->routeIs('admin.stock.*')],
        ],
        'Administración' => [
            ['label' => 'Roles', 'route' => 'admin.roles.index', 'permission' => 'roles.ver', 'icon' => 'shield', 'active' => request()->routeIs('admin.roles.*')],
            ['label' => 'Usuarios', 'route' => 'admin.usuarios.index', 'permission' => 'usuarios.ver', 'icon' => 'users', 'active' => request()->routeIs('admin.usuarios.*')],
        ],
        'Reportes' => [
            ['label' => 'Reportes básicos', 'route' => 'admin.reportes.index', 'permission' => 'reportes.ver', 'icon' => 'grid', 'active' => request()->routeIs('admin.reportes.*')],
        ],
    ];
@endphp
@foreach ($navigationGroups as $groupName => $groupLinks)
    @php
        $visibleLinks = array_filter($groupLinks, fn ($link) => ! ($link['hidden'] ?? false) && ($link['permission'] === null || auth()->user()->can($link['permission'])));
        $groupId = ($mobile ? 'mobile-' : 'desktop-').Illuminate\Support\Str::slug($groupName);
    @endphp
    @if ($visibleLinks !== [])
        <section aria-labelledby="{{ $groupId }}" class="space-y-1">
            <h2 id="{{ $groupId }}" @class(['px-3 pt-4 pb-1 text-xs font-semibold', 'text-slate-500 dark:text-slate-400' => $mobile, 'text-slate-400' => ! $mobile])>{{ $groupName }}</h2>
            @foreach ($visibleLinks as $link)
                <a href="{{ route($link['route']) }}" @if($link['active']) aria-current="page" @endif
                    @class([
                        'flex min-h-11 items-center gap-3 rounded-lg px-3 py-2.5 text-sm focus-visible:outline-2 focus-visible:outline-offset-2',
                        'bg-blue-50 font-medium text-blue-800 dark:bg-blue-500/15 dark:text-blue-200' => $mobile && $link['active'],
                        'text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800' => $mobile && ! $link['active'],
                        'focus-visible:outline-blue-600' => $mobile,
                        'bg-blue-500/20 font-medium text-blue-200' => ! $mobile && $link['active'],
                        'text-slate-200 hover:bg-white/10' => ! $mobile && ! $link['active'],
                        'focus-visible:outline-blue-400' => ! $mobile,
                    ])>
                    <x-icon :name="$link['icon']" class="size-[18px] shrink-0" />
                    {{ $link['label'] }}
                </a>
            @endforeach
        </section>
    @endif
@endforeach
