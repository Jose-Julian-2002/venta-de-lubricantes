# DOCUMENTO DE ARQUITECTURA E IMPLEMENTACIÓN - SISTEMA POS LUBRICANTES Y REPUESTOS

Este documento sirve como especificación técnica y guía de código para el desarrollo del sistema POS monolítico en Laravel con Livewire, enfocado en dispositivos móviles (mobile-first) y pc, stock centralizado en unidades base y manejo de compras por cajas con factores dinámicos.
UTILIZA TODOS LOS AGENTES QUE TENGAS DISPONIBLE PARA UN TRABAJO MAS EFICIENTE
---

## Modelo Relacional (ERD)
para crear las migraciones y tablas sigue las normalizaciones de las base de datos en todos los aspectos nombre, modelos, etc
* **Módulo 1: Administración y Seguridad**
* **Módulo 2: Catálogo de Productos** 
* **Módulo 3: Compras y Proveedores** 
* **Módulo 4: Stock y Auditoría (Kardex)**
* **Módulo 5: Ventas (Punto de Venta)**

---

## 2. Estructura de Base de Datos por Módulos

### Módulo 1: Administración y Seguridad
Controla el acceso al sistema, la autenticación y los perfiles de usuario.

* **`roles`**
  * `id` (INT, PK)
  * `nombre` (VARCHAR 50, UNIQUE) — *Ej: Administrador, Vendedor*

* **`usuarios`**
  * `id` (INT, PK)
  * `rol_id` (INT, **FK** $\to$ `roles.id`) — *Relación directa: Un usuario pertenece a un rol.*
  * `nombre` (VARCHAR 100)
  * `email` (VARCHAR 150, UNIQUE)
  * `password` (VARCHAR 255)
  * `estado` (TINYINT: 1 = Activo, 0 = Inactivo)

* **`permisos`**
  * `id` (INT, PK)
  * `nombre` (VARCHAR 100, UNIQUE) — *Ej: `ventas.crear`*

* **`rol_permiso` (Relación Rol $\leftrightarrow$ Permiso)**
  * `rol_id` (INT, PK, **FK** $\to$ `roles.id`)
  * `permiso_id` (INT, PK, **FK** $\to$ `permisos.id`)

---

### Módulo 2: Catálogo de Productos
Gestiona las categorías, marcas y el inventario centralizado en unidades base.

* **`categorias`**
  * `id` (INT, PK)
  * `nombre` (VARCHAR 100, UNIQUE) — *Ej: Aceites de auto, Refrigerantes*

* **`marcas`**
  * `id` (INT, PK)
  * `nombre` (VARCHAR 100, UNIQUE) — *Ej: Castrol, Shell, Lubrax*

* **`productos`**
  * `id` (INT, PK)
  * `categoria_id` (INT, **FK** $\to$ `categorias.id`)
  * `marca_id` (INT, **FK** $\to$ `marcas.id`)
  * `nombre` (VARCHAR 150) — *Ej: edge 5W-30 1L, sin repetir la marca*
  * `stock_actual` (INT, Default: 0) — *Gestionado estrictamente en unidades sueltas.*
  * `precio_venta_menor` (DECIMAL 10,2) — *Precio por unidad suelta, nullable: puede registrarse sin precio inicial.*
  * `precio_venta_mayor` (DECIMAL 10,2) — *Precio por volumen o caja, nullable: puede registrarse sin precio inicial.*
  * `estado` (TINYINT: 1 = Activo, 0 = Inactivo)

---

### Módulo 3: Compras y Proveedores 
Gestiona el ingreso de mercadería mediante compras por cajas y factores dinámicos.

* **`proveedores`**
  * `id` (INT, PK)
  * `nombre_empresa` (VARCHAR 150)
  * `nombre` (VARCHAR 150)
  * `telefono` (VARCHAR 20)
  * `nit_ci` (VARCHAR 20)
  * `direccion` (TEXT)

* **`compras` (Cabecera)**
  * `id` (INT, PK)
  * `proveedor_id` (INT, **FK** $\to$ `proveedores.id`)
  * `usuario_id` (INT, **FK** $\to$ `usuarios.id`)
  * `total` (DECIMAL 10,2)
  * `fecha_compra` (DATETIME)

* **`compra_detalles` (Detalle)**
  * `id` (INT, PK)
  * `compra_id` (INT, **FK** $\to$ `compras.id` - Cascade Delete)
  * `producto_id` (INT, **FK** $\to$ `productos.id`)
  * `cantidad_cajas` (INT) — *Cantidad de cajas compradas.*
  * `unidades_por_caja` (INT) — *Variable dinámica de la marca (ej. 24).*
  * `cantidad_total_unidades` (INT) — *Calculado: $\text{cantidad\_cajas} \times \text{unidades\_por\_caja}$ (se suma al stock).*
  * `costo_unitario` (DECIMAL 10,2)
  * `subtotal` (DECIMAL 10,2)

---

### Módulo 4: Stock y Auditoría - Kardex

* **`stock_movimientos`**
  * `id` (INT, PK)
  * `producto_id` (INT, **FK** $\to$ `productos.id`)
  * `usuario_id` (INT, **FK** $\to$ `usuarios.id`)
  * `tipo` (VARCHAR 20) — *'entrada' o 'salida'*
  * `cantidad` (INT) — *Unidades que ingresaron o salieron.*
  * `stock_anterior` (INT)
  * `stock_nuevo` (INT)
  * `motivo` (VARCHAR 100) — *'compra', 'venta', 'ajuste_inventario'*
  * `created_at` (TIMESTAMP)

---

### Módulo 5: Ventas - Punto de Venta Móvil

* **`clientes`**
  * `id` (INT, PK)
  * `nombre` (VARCHAR 150) — *Predeterminado: "Cliente General"*
  * `nit_ci` (VARCHAR 50)
  * `telefono` (VARCHAR 30)

* **`ventas` (Cabecera)**
  * `id` (INT, PK)
  * `usuario_id` (INT, **FK** $\to$ `usuarios.id`) — *Vendedor que atendió.*
  * `cliente_id` (INT, **FK** $\to$ `clientes.id`)
  * `tipo_pago` (VARCHAR 20) — *'efectivo', 'qr', 'transferencia'*
  * `total` (DECIMAL 10,2)
  * `estado` (VARCHAR 20, Default: 'completada') — *'completada', 'anulada'*
  * `created_at` (TIMESTAMP)

* **`venta_detalles` (Detalle)**
  * `id` (INT, PK)
  * `venta_id` (INT, **FK** $\to$ `ventas.id` - Cascade Delete)
  * `producto_id` (INT, **FK** $\to$ `productos.id`)
  * `cantidad` (INT) — *Unidades vendidas.*
  * `precio_unitario` (DECIMAL 10,2) — *Precio menor o mayor aplicado.*
  * `subtotal` (DECIMAL 10,2)

---

## 3. Guía de Implementación para Codex (Lógica de Negocio Crítica)

### A. Lógica de Compras (Entrada de Inventario)
Cuando se registra una compra, por cada detalle:
1. Calcular unidades totales: $\text{cantidad\_total\_unidades} = \text{cantidad\_cajas} \times \text{unidades\_por\_caja}$.
2. Actualizar el stock del producto sumando el total: $\text{stock\_actual} = \text{stock\_actual} + \text{cantidad\_total\_unidades}$.
3. Registrar el movimiento en `stock_movimientos` con tipo `'entrada'` y motivo `'compra'`.

### B. Lógica de Ventas (Salida de Inventario)
Cuando se confirma una venta (dentro de una transacción `DB::transaction`):
1. Validar que $\text{stock\_actual} \ge \text{cantidad solicitada}$ para cada producto. Si no alcanza, lanzar excepción.
2. Descontar stock: $\text{stock\_actual} = \text{stock\_actual} - \text{cantidad}$.
3. Registrar en `venta_detalles` y la cabecera en `ventas`.
4. Registrar el movimiento en `stock_movimientos` con tipo `'salida'` y motivo `'venta'`.