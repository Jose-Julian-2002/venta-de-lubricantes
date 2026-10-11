# Diseño: Módulo 1 — Administración y Seguridad

## Intención

Entregar la primera fase operativa del POS de lubricantes: una persona administradora debe poder iniciar sesión y llegar directamente a una interfaz rápida, minimalista e intuitiva desde la que mantener roles y usuarios. El sistema se construye sobre el Laravel existente y usa MySQL en ejecución; la interfaz es Blade y aprovecha Tailwind CSS ya instalado.

## Objetivos

- Implementar autenticación web personalizada para `App\Models\Usuario`, rechazando credenciales de usuarios inactivos (`estado = 0`).
- Implementar CRUD completo de roles y usuarios, incluida la asignación de un rol y el cambio de estado de usuarios.
- Administrar la asignación de permisos del catálogo del módulo 1 a cada rol y exigir esos permisos al ejecutar cada operación administrativa.
- Mantener las validaciones por acción en Form Requests, la lógica de negocio y transacciones en servicios, y controladores delgados.
- Crear las cuatro tablas de Administración y Seguridad con nombres de columnas y relaciones alineados con la guía del POS.
- Ofrecer un layout administrativo responsive en español, con acceso directo al panel y a las gestiones de roles y usuarios.
- Permitir crear de forma explícita y segura la primera cuenta administradora desde un seeder configurado con variables de entorno.

## Fuera de alcance

- Los módulos 2–5 del POS.
- CRUD para definir nombres arbitrarios de permisos y permisos de módulos futuros. El catálogo de permisos de este módulo se entrega versionado con el código; se administra qué permisos tiene cada rol desde el formulario de roles.
- Recuperación de contraseñas, alta pública de cuentas y otros métodos de autenticación.
- Alterar o eliminar la tabla `users` del esqueleto Laravel o reescribir migraciones ya existentes. El modelo de autenticación nuevo usa `usuarios`; las tablas del esqueleto se conservan para evitar una migración destructiva no solicitada.

## Estructura de datos

Se crean migraciones nuevas para:

- `roles`: `id`, `nombre` (varchar 50, único).
- `usuarios`: `id`, `rol_id` (FK a `roles.id`), `nombre` (varchar 100), `email` (varchar 150, único), `password` (varchar 255) y `estado` (boolean/tinyint, por defecto activo).
- `permisos`: `id`, `nombre` (varchar 100, único).
- `rol_permiso`: `rol_id` y `permiso_id` como clave primaria compuesta, con FKs a roles y permisos.

Los esquemas respetan las columnas descritas en la guía y no añaden timestamps a estas cuatro tablas. La relación usuario–rol impide eliminar un rol que aún está asignado; la tabla pivote elimina sus vínculos al borrar un rol o permiso. Modelos: `Rol`, `Usuario` (autenticable), `Permiso`, con `belongsTo`, `hasMany` y `belongsToMany` correspondientes.

El catálogo inicial contiene `roles.ver`, `roles.crear`, `roles.editar`, `roles.eliminar`, `usuarios.ver`, `usuarios.crear`, `usuarios.editar` y `usuarios.eliminar`. Se inicializa de forma idempotente por seeder. Los permisos se seleccionan al crear o editar un rol; no se crean permisos arbitrarios desde la UI para evitar asignaciones que no correspondan a funcionalidades instaladas.

## Flujos y comportamiento

### Inicio y cierre de sesión

El guard web de sesión usará `Usuario` como proveedor Eloquent. El login acepta correo y contraseña, solo autentica registros activos, regenera la sesión al éxito y devuelve un error genérico ante credenciales inválidas o cuentas inactivas. El logout cierra la sesión, invalida el identificador de sesión y regenera el token CSRF. Las rutas administrativas requieren autenticación; el formulario de login es solo para visitantes.

### Administración

El panel muestra accesos directos a las áreas para las que el usuario tenga permiso. Cada ruta de listado, alta, edición y eliminación requiere el permiso de la acción correspondiente; las acciones no se protegen solo ocultando botones en Blade. El CRUD de roles permite listar, crear, editar nombre y permisos, y eliminar roles; no permite borrar roles usados por usuarios. El CRUD de usuarios permite listar, crear, editar y eliminar nombre, correo, contraseña cuando corresponda, estado y rol. La contraseña se almacena con hash y no se reemplaza al editar si se deja vacía. Todas las cuentas activas se autentican, pero solo pueden ejecutar las acciones habilitadas por los permisos del rol actual.

Cada acción con entrada dispone de su Form Request (login, crear/editar rol y crear/editar usuario). Los servicios `AuthService`, `RolService` y `UsuarioService` centralizan autenticación y operaciones de datos; las escrituras que requieran consistencia usan transacciones. Los controladores delegan en dichos servicios y retornan vistas/redirecciones con mensajes de resultado.

### Primer administrador

Un seeder dedicado crea el catálogo, el rol `Administrador` con todos los permisos del módulo 1 y la cuenta inicial con `ADMIN_NAME`, `ADMIN_EMAIL` y `ADMIN_PASSWORD`. No contiene credenciales por defecto; la configuración incompleta debe producir un error claro. La creación será idempotente y no restablecerá contraseñas en ejecuciones posteriores. La guía de ejecución documentará configurar estas variables, apuntar `.env` a MySQL, migrar y sembrar.

## Experiencia visual

Interfaz Blade en español con layout principal responsive, navegación visible hacia el panel, roles y usuarios, formularios accesibles con errores junto a cada campo, y tablas/listados con acciones claras. El estilo será sobrio y mínimo, usando Tailwind CSS y los recursos Vite presentes, sin incorporar una librería visual nueva.

## Validación

- Pruebas de autenticación: acceso correcto, contraseña errónea, cuenta inactiva, logout y protección de rutas.
- Pruebas CRUD: validación, persistencia, restricciones de rol en uso, estado y hash de contraseña.
- Pruebas de permisos: cada acción administrativa autoriza al permiso correspondiente y rechaza roles sin él; la asignación de permisos actualiza el acceso efectivo del usuario.
- Ejecutar pruebas del proyecto con SQLite en memoria como base de test independiente de MySQL y comprobar migraciones/modelos. La configuración y las instrucciones de ejecución productiva apuntan a MySQL.
- Ejecutar Laravel Pint y el build de Vite si los cambios de vistas/estilos lo requieren.

## Decisiones del implementador

- Mantener intactas las migraciones y tablas de usuario predeterminadas de Laravel para no borrar datos existentes; se dejan sin uso por la autenticación del POS.
- No migrar registros desde `users` hacia `usuarios`: el proyecto aún solo contiene la autenticación de ejemplo de Laravel, no cuentas del POS. La tabla anterior se conserva, pero sus cuentas no sirven para iniciar sesión en el POS.
- Omitir timestamps en las cuatro tablas del módulo porque la especificación de arquitectura enumera explícitamente sus columnas.
- Usar MySQL como conexión predeterminada documentada y mantener SQLite solo para pruebas automatizadas.
- No añadir paquetes de UI; reutilizar Tailwind CSS y Blade disponibles.
- Usar las claves de permiso `recurso.acción` para roles y usuarios, administrar asignaciones en el CRUD de roles, y no mantener un CRUD separado para editar el catálogo de permisos.
- Conservar la autenticación solo por estado activo; el alcance de acceso a cada función depende exclusivamente de los permisos asignados al rol.
