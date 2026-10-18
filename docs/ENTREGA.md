# Entrega de infraestructura y persistencia

## Tecnologías verificadas

- PHP 8.4.
- Laravel 13.
- Composer 2.
- MySQL 8.4.7 mediante WampServer.
- InnoDB como motor de las nuevas tablas.
- Servidor local mediante php artisan serve.

## Componentes implementados

- Estructura del Back-End Laravel.
- Configuración mediante variables de entorno.
- Ruta GET /api/estado.
- Respuestas de excepciones de la API en JSON.
- Migraciones de roles, usuarios, categorías, proveedores,
  productos y movimientos.
- Claves primarias y foráneas.
- Restricciones de unicidad y valores numéricos.
- Modelos Eloquent y relaciones.
- Modelo User adaptado a usuarios y contrasena_hash.
- Seeders de demostración.
- Factory de usuarios.
- Pruebas automatizadas sobre MySQL.
- Exportación SQL de la estructura.
- Respaldo y recuperación local verificados.

## Instalación desde una copia nueva

Clonar el repositorio y seleccionar la rama o versión que contenga
estos cambios.

Desde la carpeta backend:

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
```

Iniciar MySQL y crear la base y el usuario técnico siguiendo el README.

Completar DB_PASSWORD y verificar el resto de la conexión en .env.

```powershell
php artisan config:clear
php artisan migrate
php artisan db:seed
php artisan serve
```

El seeder solicita las contraseñas de las cuentas que todavía no existen:

- admin.libreria
- empleado.libreria

La carga de demostración requiere APP_ENV=local.

Comprobar http://127.0.0.1:8000/api/estado.

## Integración de autenticación

Utilizar App\Models\User.

- Tabla: usuarios.
- Clave primaria: id_usuario.
- Nombre de acceso: usuario.
- Hash de contraseña: contrasena_hash.
- Estado permitido para iniciar sesión: activo.
- Relación con el rol: rol().
- Roles iniciales: Administrador y Empleado.

El modelo genera hashes mediante su cast hashed al asignar contraseñas
con Eloquent y oculta contrasena_hash al serializar.

Al utilizar Auth::attempt, la contraseña recibida se pasa en la clave
password; el modelo indica que el hash se encuentra en contrasena_hash.

Las rutas de login, logout, autorizaciones y la configuración de Sanctum,
si se utiliza, corresponden al módulo de autenticación.

## Integración de productos y catálogos

Utilizar los modelos Categoria, Proveedor y Producto.

Los estados de categorías y proveedores son activo e inactivo.
Los estados de productos son activo y descontinuado.

Los productos agotados se identifican por cantidad_disponible igual a cero.
Los productos con existencias bajas se identifican por
cantidad_disponible menor o igual a existencia_minima.

Validar las peticiones y los permisos antes de guardar cambios.
Usar únicamente campos validados para la asignación masiva.

## Integración de movimientos

Utilizar Movimiento y Producto.

Cada movimiento registra tipo, motivo, producto, usuario, cantidad,
existencia resultante, fecha y observaciones.

El servicio del módulo deberá:

1. Comprobar permisos y datos recibidos.
2. Validar la correspondencia entre tipo y motivo.
3. Abrir una transacción.
4. Bloquear el producto para actualización mediante lockForUpdate.
5. Comprobar la existencia disponible antes de una salida.
6. Actualizar la existencia.
7. Registrar el movimiento y su existencia resultante.
8. Confirmar ambas operaciones juntas.

Si ocurre un error, ambas operaciones deben revertirse.

Las migraciones y modelos no actualizan el stock automáticamente.
El historial debe conservarse; las correcciones se realizan mediante
movimientos de ajuste autorizados.

## Pruebas

Preparar .env.testing y la base libreria_independencia_testing
siguiendo el README.

Desde backend:

```powershell
php artisan migrate --env=testing
php artisan test --configuration=phpunit.xml --filter=PersistenciaInventarioTest
```

## Estructura SQL y respaldo

docs/database/estructura.sql contiene la estructura sin registros.

Para instalar el proyecto se recomienda utilizar las migraciones.
La exportación SQL sirve como referencia y alternativa de creación:
no debe importarse en una base vacía y después ejecutar las mismas
migraciones, porque las tablas ya existirían.

El respaldo completo local está excluido del repositorio mediante
/backups/. Contiene tablas, registros y el historial de migraciones.

Las cuentas técnicas y permisos del servidor MySQL se crean por separado;
el respaldo de la base de la aplicación no los incluye.

## Funcionalidades a integrar

- Autenticación y control de acceso.
- CRUD y pantallas de los módulos.
- Servicio operativo de movimientos.
- Alertas y reportes.
- Pruebas del flujo completo con los módulos del equipo.