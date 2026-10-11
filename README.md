# POS Lubricantes

Sistema POS monolítico en Laravel. La primera fase implementa Administración y Seguridad; los demás módulos se desarrollarán según [la guía de arquitectura](./pos_architecture_and_implementation_guide.md).

## Requisitos

- PHP 8.3 o superior y Composer.
- MySQL 8 o compatible, con una base de datos creada para la aplicación.
- Node.js y npm para compilar los recursos de Tailwind CSS.

## Configuración local

1. Instala dependencias PHP y crea `.env` a partir de `.env.example`.
2. Configura `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` para tu servidor MySQL.
3. Define `ADMIN_NAME`, `ADMIN_EMAIL` y `ADMIN_PASSWORD` antes de sembrar. La contraseña inicial debe tener al menos 12 caracteres; no hay credenciales por defecto.
4. Ejecuta:

   ```bash
   php artisan key:generate
   php artisan migrate --seed
   npm install
   npm run build
   php artisan serve
   ```

5. Inicia sesión en `/login` con la cuenta inicial configurada. La navegación y las acciones disponibles dependen de los permisos asignados al rol del usuario.

Las pruebas automatizadas usan SQLite en memoria y no requieren conectarse a MySQL:

```bash
php artisan test
```
