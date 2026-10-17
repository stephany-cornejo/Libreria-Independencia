# Librería Independencia

Sistema web para la gestión del inventario de la Librería Independencia.

## Tecnologías

- PHP 8.4
- Laravel 13
- Composer 2
- MySQL 8.4 Incluido en WampServer.
- Conexión verificada con MySQL 8.4.7 de WampServer.
- Base de datos y usuario técnico configurados para desarrollo local.

El Back-End se encuentra en la carpeta `backend/`.
El Front-End React se incorporará en una etapa posterior.

## Requisitos

- PHP 8.4 con la extensión PDO MySQL habilitada.
- Composer 2.
- Git.
- MySQL 8.4 LTS para las funcionalidades de persistencia.

## Instalación en Windows PowerShell

Clonar el repositorio:

```powershell
git clone https://github.com/stephany-cornejo/Libreria-Independencia
```

Entrar al Back-End:

```powershell
Set-Location libreria-independencia\backend
```

Instalar dependencias:

```powershell
composer install
```

Crear la configuración local:

```powershell
Copy-Item .env.example .env
```

Generar la clave local:

```powershell
php artisan key:generate
```

Iniciar el servidor:

```powershell
php artisan serve
```

Abrir http://127.0.0.1:8000 para comprobar la respuesta inicial.

## Estado actual

- Proyecto Laravel inicializado.
- Ruta inicial con respuesta JSON.
- Sesiones y caché almacenadas en archivos.
- Conexión MySQL declarada, pendiente de crear la base y el usuario.
- Migraciones de roles, usuarios, categorías y proveedores implementadas.
- Migraciones de productos y movimientos pendientes.

## Configuración local

Cada integrante debe mantener sus credenciales en `backend/.env`.
Este archivo no debe subirse a Git.

El archivo `backend/.env.example` contiene la configuración de referencia
y no debe incluir contraseñas ni claves de aplicación.

## Base de datos local

Iniciar el servicio MySQL desde WampServer antes de utilizar las
funcionalidades de persistencia.

Configurar en `backend/.env` el puerto real del servidor MySQL.
La aplicación se ejecuta con `php artisan serve`.

## Configuración de MySQL con WampServer

Entorno de desarrollo verificado:

- MySQL: 8.4.7.
- Host: 127.0.0.1.
- Puerto: 3306.
- Base de datos: libreria_independencia.
- Usuario técnico: libreria_app.
- Juego de caracteres: utf8mb4.
- Intercalación: utf8mb4_unicode_ci.

### Preparación local

Iniciar MySQL desde WampServer y abrir su consola con una cuenta
administradora.

En los siguientes comandos, reemplazar CAMBIA_ESTA_CLAVE por una
contraseña local antes de ejecutarlos.

```sql
CREATE DATABASE libreria_independencia
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

CREATE USER 'libreria_app'@'127.0.0.1'
IDENTIFIED BY 'CAMBIA_ESTA_CLAVE';

GRANT ALL PRIVILEGES
ON libreria_independencia.*
TO 'libreria_app'@'127.0.0.1';
```

Estos comandos se ejecutan una sola vez en cada entorno nuevo.

Crear el archivo backend/.env a partir de backend/.env.example,
si todavía no existe, y configurar DB_PASSWORD con la contraseña
asignada al usuario técnico.

Desde la carpeta backend, ejecutar:

```powershell
php artisan config:clear
php artisan tinker
```

Dentro de Tinker, comprobar la conexión:

```php
\Illuminate\Support\Facades\DB::selectOne('SELECT DATABASE() AS base_datos, VERSION() AS version_mysql, @@port AS puerto, CURRENT_USER() AS cuenta');
```

Salir de Tinker:

```php
exit
```

Las contraseñas locales no deben incluirse en el repositorio.

## Organización del Back-End

- `backend/routes/api.php`: rutas de la API con prefijo `/api`.
- `backend/routes/web.php`: rutas web.
- `backend/app/Http/Controllers`: controladores HTTP.
- `backend/app/Services`: reglas y operaciones de negocio.
- `backend/app/Models`: modelos de persistencia.
- `backend/database/migrations`: estructura de las tablas.
- `backend/resources/views`: vistas de Laravel.
- `backend/public`: punto de entrada HTTP y archivos públicos.
- `backend/bootstrap/app.php`: registro de rutas y configuración de errores.

La carpeta de servicios está preparada para incorporar la lógica de
los módulos.

## Comprobación de la API

Iniciar el servidor desde `backend`:

```powershell
php artisan serve
```

Consultar:

```text
GET http://127.0.0.1:8000/api/estado
```

Esta ruta devuelve el nombre de la aplicación y el estado del servidor.
No consulta la base de datos.

Las excepciones de las rutas `/api` y `/api/*` se devuelven en JSON.

Comportamientos verificados:

- `GET /api/estado`: HTTP 200.
- `GET /api/no-existe`: HTTP 404.
- `POST /api/estado`: HTTP 405.

Listar las rutas de la API desde `backend`:

```powershell
php artisan route:list --path=api
```

## Migraciones de roles y usuarios

Se reemplazó la migración predeterminada de `users` por las tablas
`roles` y `usuarios`, siguiendo el diccionario de datos del proyecto.

### Roles

- `id_rol`: clave primaria entera autoincremental.
- `nombre`: obligatorio, único y de hasta 30 caracteres.
- `created_at` y `updated_at`: fechas de seguimiento.

### Usuarios

- `id_usuario`: clave primaria entera autoincremental.
- `nombre`: obligatorio, hasta 100 caracteres.
- `usuario`: obligatorio, único y de hasta 50 caracteres.
- `contrasena_hash`: campo de hasta 255 caracteres para el hash.
- `id_rol`: clave foránea obligatoria hacia `roles.id_rol`.
- `estado`: activo o inactivo; activo por defecto.
- `created_at` y `updated_at`: fechas de seguimiento.

Un rol puede tener muchos usuarios.
No se permite eliminar un rol que tenga usuarios asociados.

Desde la carpeta `backend`, aplicar las migraciones:

```powershell
php artisan migrate
```

Consultar su estado:

```powershell
php artisan migrate:status
```

Los datos iniciales se incorporarán posteriormente mediante seeders.
La adaptación de los modelos y de la configuración de autenticación
a estos nombres de tablas y campos se realizará en la etapa de modelos.

## Categorías y proveedores

Se implementaron las tablas `categorias` y `proveedores` mediante
migraciones.

### Categorías

- Identificador entero autoincremental: `id_categoria`.
- Nombre obligatorio de hasta 50 caracteres.
- Descripción opcional de hasta 150 caracteres.
- Estado activo o inactivo, con activo como valor predeterminado.
- Fechas de creación y modificación.

### Proveedores

- Identificador entero autoincremental: `id_proveedor`.
- Nombre obligatorio de hasta 100 caracteres.
- Teléfono opcional de hasta 20 caracteres.
- Correo opcional de hasta 100 caracteres.
- Dirección opcional de hasta 150 caracteres.
- Estado activo o inactivo, con activo como valor predeterminado.
- Fechas de creación y modificación.

El campo `estado` complementa el diccionario inicial para cumplir los
requerimientos de activación y desactivación de categorías y proveedores.

Ambas tablas utilizan InnoDB. Sus relaciones con productos se definirán
en la migración de productos.

Los registros iniciales se incorporarán mediante seeders.