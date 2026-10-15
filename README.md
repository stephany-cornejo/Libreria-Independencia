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
git clone https://github.com/Jorged99/libreria-independencia.git
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
- Migraciones del dominio pendientes de implementar.

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