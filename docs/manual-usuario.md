# Manual de usuario — POS Lubricantes

Esta guía explica cómo iniciar sesión, administrar accesos, mantener el catálogo, registrar compras, cobrar desde el POS y consultar reportes. Las opciones visibles pueden variar según los permisos de tu rol.

## 1. Acceso e inicio de sesión

1. Abre en el navegador la dirección del POS que te proporcionó el administrador. En un teléfono o una computadora se usa la misma aplicación; la pantalla se adapta al tamaño del dispositivo.
2. En **Correo electrónico**, escribe el correo de tu cuenta.
3. En **Contraseña**, escribe tu contraseña y pulsa **Iniciar sesión**.
4. Si los datos son correctos y tu cuenta está activa, se abrirá el panel. Si aparece un error, verifica el correo y la contraseña o solicita ayuda al administrador.
5. Para cambiar entre modo claro y oscuro, usa el botón con el icono de sol o luna. La selección queda guardada en ese navegador.
6. Para salir, pulsa **Cerrar sesión** en la parte superior.

Solo las cuentas activas pueden ingresar. Si una opción del menú no aparece o indica falta de permiso, solicita al administrador que revise el rol asignado; no significa necesariamente que la página esté dañada.

## 2. Gestión de usuarios y roles

Estas tareas requieren los permisos de administración correspondientes.

### Crear o actualizar un usuario

1. Abre **Administración → Usuarios**.
2. Pulsa **Crear usuario** para registrar una cuenta. Para cambiar una existente, pulsa **Editar** en su fila.
3. Completa el nombre y el correo electrónico.
4. Selecciona el **Rol** que corresponda a las tareas de esa persona.
5. Selecciona **Activo** para permitir el acceso o **Inactivo** para bloquearlo sin borrar su historial.
6. Al crear una cuenta, escribe una contraseña de al menos 12 caracteres y confírmala. Al editar, deja la contraseña en blanco para conservar la actual; escribe una nueva y confírmala solo si quieres cambiarla.
7. Pulsa **Guardar usuario**. Si hay campos incorrectos o faltantes, corrígelos según los mensajes que aparecen en pantalla.

No se puede eliminar la cuenta con la que tienes abierta la sesión ni una cuenta asociada a compras, ventas o movimientos de inventario. En esos casos, desactívala en lugar de eliminarla.

### Crear o actualizar un rol

1. Abre **Administración → Roles** y pulsa **Crear rol**. Para modificar uno existente, pulsa **Editar**.
2. Escribe un nombre claro, por ejemplo, “Cajero” o “Encargado”.
3. En la lista de permisos, marca las acciones que podrá realizar ese rol. Los permisos están agrupados por áreas, como Operaciones, Catálogo, Inventario, Reportes y Administración.
4. Pulsa **Guardar rol**.
5. Asigna el rol desde el formulario del usuario, o edita los usuarios que deban recibir esos permisos.

Un rol puede reunir permisos de consulta, creación, edición o eliminación según el área. Revisa cuidadosamente las casillas antes de guardar: los cambios afectan a todas las cuentas que usan ese rol. Un rol con usuarios asignados no se puede eliminar; primero reasigna esos usuarios a otro rol.

## 3. Administración del catálogo y precios

### Preparar el catálogo

Antes de crear productos, registra las categorías y marcas necesarias:

1. Abre **Catálogo → Categorías** y pulsa **Crear categoría**.
2. Escribe el nombre y pulsa **Guardar categoría**.
3. Abre **Catálogo → Marcas**, pulsa **Crear marca**, escribe el nombre y pulsa **Guardar marca**.
4. Para corregir un nombre, usa **Editar** en la fila correspondiente.

No se puede eliminar una categoría o marca mientras tenga productos asociados.

### Crear o editar productos

1. Abre **Catálogo → Productos** y pulsa **Crear producto**, o pulsa **Editar** en la fila del producto que quieras actualizar.
2. Escribe el nombre de la presentación o línea, por ejemplo, “edge 5W-30 1L”. La marca se selecciona por separado; no hace falta repetirla en el nombre.
3. Selecciona la categoría y la marca.
4. Ingresa el **precio de venta por unidad** y, si corresponde, el **precio de venta mayorista por unidad dentro de la caja**. Son precios opcionales, pero el POS no podrá vender con una modalidad cuyo precio no esté definido.
5. Selecciona **Activo** para ofrecer el producto en compras y ventas, o **Inactivo** para retirarlo de esas operaciones.
6. Pulsa **Guardar producto**.

El stock se muestra en unidades sueltas y no se cambia desde este formulario. Se actualiza al registrar compras y ventas. Si un producto tiene stock, no se puede eliminar; registra la salida correspondiente mediante las operaciones del sistema en vez de intentar borrarlo.

En la lista de productos puedes buscar por nombre y filtrar por categoría o marca. Pulsa **Filtrar** para aplicar los criterios o **Limpiar** para volver a ver el catálogo completo.

## 4. Registro de compras a proveedores

### Registrar un proveedor

1. Abre **Operaciones → Proveedores** y pulsa **Crear proveedor**.
2. Escribe el nombre de la empresa, que es obligatorio.
3. Si tienes los datos, completa la persona de contacto, teléfono, NIT/CI y dirección.
4. Pulsa **Guardar proveedor**.

No se puede eliminar un proveedor que ya tenga compras registradas.

### Registrar la mercadería recibida

1. Abre **Operaciones → Registrar compra**. También puedes entrar por **Compras → Registrar compra** si tienes permiso para consultar el historial.
2. Selecciona el proveedor y revisa la fecha y hora de la compra.
3. En **Productos de la compra**, selecciona el producto recibido.
4. Indica cuántas cajas llegaron, cuántas unidades contiene cada caja y el precio de compra por caja.
5. Si la compra debe actualizar los precios de venta, escribe el nuevo precio menor y/o mayor. Deja esos campos vacíos para conservar los precios actuales.
6. Usa **Agregar producto** para incluir otros artículos en la misma compra. Revisa las cajas, el factor de unidades por caja, los subtotales y el total.
7. Pulsa **Registrar compra** una sola vez y espera la confirmación.

El sistema calcula las unidades recibidas multiplicando **cajas × unidades por caja** y suma esas unidades al stock. El precio de compra se registra por caja; los precios de venta se expresan por unidad, incluso el precio mayorista. Después de guardar, revisa el detalle de la compra y, si lo necesitas, abre el Kardex del producto para confirmar la entrada.

Para consultar operaciones anteriores, abre **Operaciones → Compras** y pulsa **Ver detalle** en la compra deseada. Si no hay proveedores o productos activos, regístralos antes de iniciar la compra.

## 5. POS móvil: ventas y reportes

### Cobrar una venta desde el teléfono

1. En el menú, abre **Operaciones → Punto de venta**. Si el menú lateral no está visible, pulsa el botón de menú en la parte superior. El POS necesita JavaScript habilitado en el navegador.
2. Busca un producto por nombre o marca. Si lo prefieres, limita los resultados por categoría o marca.
3. En **Presentación para agregar**, elige **Unidad** o **Caja** antes de tocar el producto:
   - **Unidad** usa el precio menor y agrega una unidad.
   - **Caja** usa el precio mayorista y agrega una caja completa. El total mostrado para esa opción corresponde a todas las unidades de la caja.
4. Toca la tarjeta del producto para agregarlo al carrito. Si cambias la presentación en el carrito, la cantidad se ajusta a una unidad o una caja. Usa **+** y **−** para cambiar la cantidad; en presentación de caja, cada toque agrega o quita una caja completa. Usa **Quitar** para retirar el producto.
5. Selecciona el cliente. **Cliente General** suele estar seleccionado por defecto. Para registrar uno durante la venta, pulsa **Nuevo cliente**, escribe su nombre y, si corresponde, NIT/CI y teléfono; pulsa **Guardar cliente**. El nuevo cliente quedará seleccionado para esta venta.
6. Selecciona el método de pago: **Efectivo**, **QR** o **Transferencia**.
7. Revisa los productos, las cantidades, la presentación y el total que aparece en la barra inferior.
8. Pulsa **Cobrar / Finalizar venta** una sola vez y espera a que el sistema confirme la operación.
9. En el ticket, revisa el cliente, los artículos, el método de pago y el total. Pulsa **Imprimir ticket** si necesitas imprimirlo; desde esa pantalla también puedes abrir una nueva venta o el historial si tu rol tiene permiso.

El sistema comprueba nuevamente el stock y los precios al cobrar. No se puede finalizar una venta si el stock es insuficiente, falta el precio de esa modalidad o no se ha configurado cuántas unidades contiene la caja. En ese caso, revisa el mensaje; solicita que actualicen el catálogo o registren la compra pendiente. Verifica el carrito antes de cobrar: la pantalla no ofrece una acción para editar o anular una venta ya completada.

### Consultar historial y reportes

- **Historial de ventas:** abre **Operaciones → Historial de ventas**. Verás fecha, cliente, vendedor, pago y total. Pulsa **Ver ticket** para consultar o imprimir el comprobante.
- **Reportes básicos:** abre **Reportes → Reportes básicos** y selecciona **Ventas**, **Compras** o **Existencias actuales**.
  1. Para ventas o compras, establece las fechas **Desde** y **Hasta**. De forma predeterminada se propone desde el inicio del mes actual hasta hoy.
  2. Para existencias actuales, las fechas no se aplican; el reporte muestra el stock actual.
  3. Pulsa **Consultar** para actualizar los indicadores y la tabla. Usa **Limpiar** para volver a los valores predeterminados.
  4. Pulsa **Imprimir página** si quieres imprimir el reporte.

El reporte de ventas presenta cantidades y montos del período, incluida una **ganancia bruta estimada** cuando hay costos de compra anteriores disponibles. Esta estimación usa el costo de la última compra registrada antes de cada venta y no descuenta gastos operativos ni impuestos. Si faltan costos, aparecerá un aviso y la ganancia completa se mostrará como pendiente.

En el **Dashboard** puedes elegir un mes para consultar las ventas completadas, la ganancia bruta estimada y las cinco marcas y productos con más unidades vendidas. Si el resumen de ventas no aparece, puede que tu rol no tenga el permiso necesario.

## Ayuda rápida

| Situación | Qué revisar |
| --- | --- |
| No puedes iniciar sesión | Verifica el correo, la contraseña y solicita al administrador que confirme que la cuenta está activa. |
| No aparece una sección | Pide al administrador que compruebe los permisos del rol asignado. |
| No puedes vender un producto | Confirma que esté activo, que tenga stock y que el precio de la presentación elegida esté configurado. Para vender cajas, debe existir una compra con unidades por caja registradas. |
| No puedes registrar una compra | Confirma que exista un proveedor y al menos un producto activo. |
| El total o la ganancia del reporte no está completo | Revisa los filtros de fecha y, para la ganancia, que existan compras registradas antes de las ventas correspondientes. |
