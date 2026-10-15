# Librería Independencia

Sistema web para la gestión del inventario de la Librería Independencia.

## Tecnologías

- PHP 8.4
- Laravel 13
- Composer 2
- MySQL 8.4 Incluido en WampServer.

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