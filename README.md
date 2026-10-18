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
- Migraciones de las seis entidades principales implementadas:
  roles, usuarios, categorías, proveedores, productos y movimientos.
- Modelos Eloquent y relaciones implementados.
- Modelo User adaptado a la tabla usuarios.
- Seeders de demostración y factory de usuarios implementados.
- Relaciones verificadas con datos iniciales.

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

## Productos y movimientos

### Productos

La tabla `productos` almacena código único, nombre, descripción,
categoría, proveedor, precios, existencia disponible, existencia mínima,
fecha de ingreso y estado.

Los precios utilizan DECIMAL(10,2) y restricciones CHECK que impiden
valores negativos. Las cantidades utilizan enteros sin signo.

Los estados administrativos son activo y descontinuado.
Los productos agotados se identifican por existencia disponible igual
a cero.

Las categorías y proveedores asociados no pueden eliminarse mientras
tengan productos relacionados.

### Movimientos

La tabla `movimientos` almacena producto, usuario responsable, tipo,
motivo, cantidad, existencia resultante, fecha y observaciones.

Los tipos son entrada y salida.

Los motivos disponibles son compra, reposicion, devolucion, venta,
perdida, dano y ajuste.

La cantidad debe ser mayor que cero y la existencia resultante no
puede ser negativa.

No se permite eliminar productos ni usuarios que tengan movimientos
asociados.

### Integración con la lógica de inventario

El servicio de movimientos deberá verificar la disponibilidad antes
de una salida y realizar, dentro de una transacción, la actualización
de existencias y el registro del movimiento.

También deberá validar la correspondencia entre tipo y motivo,
los permisos del usuario y la conservación del historial.

Las migraciones crean la estructura; no actualizan automáticamente
las existencias.

## Modelos Eloquent

| Modelo | Tabla | Clave primaria |
|---|---|---|
| Rol | roles | id_rol |
| User | usuarios | id_usuario |
| Categoria | categorias | id_categoria |
| Proveedor | proveedores | id_proveedor |
| Producto | productos | id_producto |
| Movimiento | movimientos | id_movimiento |

Las relaciones permiten consultar usuarios por rol, productos por
categoría y proveedor, y movimientos por producto y usuario.

Los modelos definen campos permitidos para asignación masiva mediante
`fillable`. Los controladores y servicios deben aplicar además las
validaciones y autorizaciones correspondientes.

### Usuario y autenticación

El modelo `App\Models\User` utiliza la tabla `usuarios` y la clave
primaria `id_usuario`.

El campo de contraseña es `contrasena_hash`. Su cast `hashed` protege
las contraseñas asignadas mediante Eloquent y el campo se oculta al
convertir el modelo a un arreglo o JSON.

Las escrituras directas mediante SQL o Query Builder no ejecutan los
casts del modelo.

No se utiliza token de recordar sesión.

El módulo de autenticación deberá validar el campo `usuario`,
la contraseña y el estado activo de la cuenta.

### Inventario

Los modelos no actualizan automáticamente el stock al crear movimientos.
Esa operación deberá implementarse en el servicio de inventario,
utilizando una transacción.

La factory y el seeder predeterminados de usuarios se adaptarán antes
de generar los datos iniciales.

## Datos de demostración

Desde la carpeta `backend`, con APP_ENV=local y las migraciones aplicadas:

```powershell
php artisan db:seed
```

La carga incluye:

- 2 roles: Administrador y Empleado.
- 2 usuarios: admin.libreria y empleado.libreria.
- 3 categorías.
- 2 proveedores ficticios.
- 3 productos.
- 3 entradas iniciales de inventario.

Las contraseñas se solicitan en la terminal al crear las cuentas.
Deben tener al menos 12 caracteres y no se almacenan en el repositorio.

Orden de ejecución:

1. RolSeeder.
2. UsuarioSeeder.
3. CatalogoSeeder.
4. InventarioDemoSeeder.

La carga completa se ejecuta dentro de una transacción.

Repetirla conserva los registros identificados por los seeders:
no cambia contraseñas, no repone existencias y no duplica las
entradas iniciales de productos existentes.

Los productos de ejemplo permiten probar existencias sobre el mínimo,
en el mínimo y por debajo del mínimo.

Esta carga está destinada al desarrollo local.
No implementa el servicio operativo de movimientos ni el login.

La factory UserFactory utiliza los campos de la tabla usuarios y
asigna el rol Empleado, creándolo si todavía no existe.

## Pruebas de persistencia e integridad

Las pruebas utilizan MySQL y una base independiente:

`libreria_independencia_testing`

### Preparación

Crear la base y conceder acceso al usuario técnico:

```sql
CREATE DATABASE libreria_independencia_testing
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES
ON libreria_independencia_testing.*
TO 'libreria_app'@'127.0.0.1';
```

Desde `backend`, crear la configuración local de pruebas:

```powershell
Copy-Item .env .env.testing
```

Configurar en `.env.testing`:

- APP_ENV=testing
- DB_DATABASE=libreria_independencia_testing
- SESSION_DRIVER=array
- CACHE_STORE=array
- QUEUE_CONNECTION=sync

Conservar las credenciales locales de MySQL.

Aplicar las migraciones:

```powershell
php artisan config:clear
php artisan migrate --env=testing
```

No se requieren los seeders de demostración.

### Ejecución

```powershell
php artisan test --filter=PersistenciaInventarioTest
```

Las pruebas comprueban persistencia, relaciones, protección del hash,
unicidad del código, precios y existencias no negativos, cantidades
positivas, claves foráneas, protección de productos con historial y
reversión de operaciones mediante transacciones.

Cada prueba revierte sus cambios mediante DatabaseTransactions.

El archivo `.env.testing` contiene configuración privada y no se
incluye en el repositorio.

Las pruebas del servicio operativo de movimientos corresponderán
a la implementación de ese módulo.

## Entrega técnica

Consultar docs/ENTREGA.md para la instalación e integración de los módulos.

## Respaldo y recuperación local

Ejecutar estos comandos desde la raíz del repositorio en PowerShell.
Ajustar la ruta de MySQL a la instalación de cada computadora.

```powershell
$mysqlBin = 'C:\wamp64\bin\mysql\mysql8.4.7\bin'
New-Item -ItemType Directory -Path backups -Force
```

Crear un respaldo completo:

```powershell
& "$mysqlBin\mysqldump.exe" --host=127.0.0.1 --port=3306 --user=libreria_app --password --default-character-set=utf8mb4 --single-transaction --no-tablespaces --set-gtid-purged=OFF --result-file=backups/respaldo_commit_10.sql libreria_independencia
```

Introducir la contraseña técnica cuando se solicite.
Comprobar que $LASTEXITCODE sea 0.

El archivo incluye datos y hashes de cuentas y se conserva fuera de Git.
Este nombre de archivo se sobrescribe si se repite el comando; utilizar
nombres distintos para conservar respaldos de otras fechas.

Durante este respaldo local no se modifican datos ni se ejecutan migraciones.

### Recuperación de comprobación

Crear una base vacía libreria_independencia_restauracion y conceder acceso
a libreria_app desde la consola administrativa de MySQL.

Conectarse desde PowerShell:

```powershell
& "$mysqlBin\mysql.exe" --host=127.0.0.1 --port=3306 --user=libreria_app --password --default-character-set=utf8mb4 libreria_independencia_restauracion
```

Dentro del cliente MySQL, ejecutar source seguido de la ruta absoluta
del respaldo. Utilizar barras / en la ruta:

```sql
source C:/Users/DELL/Projects/Libreria-Independencia/backups/respaldo_commit_10.sql
```

Adaptar la ruta al equipo utilizado.

Comprobar la base seleccionada y los registros restaurados.
No cambiar el .env de desarrollo para esta comprobación.

La recuperación fue comprobada en una base independiente.