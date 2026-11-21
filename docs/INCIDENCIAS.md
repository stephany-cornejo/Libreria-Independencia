# Incidencias, limitaciones y pendientes

Documento de control del incremento actual. Registra lo que quedó
pendiente, lo que no pudo verificarse en este entorno y las
limitaciones conocidas de la implementación.

Fecha de actualización: 2026-11-19.

## Pendientes por falta de información

Estos elementos no existen en el repositorio ni en la documentación
entregada. No se inventaron datos para simular su presencia.

| Elemento | Motivo | Acción requerida |
|---|---|---|
| Lista de integrantes del equipo | No hay archivo de equipo ni datos en el repositorio | Completar la portada del PDF con los nombres reales |
| Enlace al Product Backlog | No hay enlace en el repositorio ni en los documentos | Agregar el enlace al PDF de entrega |
| Enlace al repositorio Git | Existe el remoto `origin` (GitHub) pero no está documentado en la portada | Agregar el enlace al PDF de entrega |
| Reglas del Sprint I | No hay documento de Sprint Planning ni definición del incremento | Integrar las reglas cuando se reciban y contrastarlas con este incremento |
| PDF de entrega con portada | Requiere integrantes y enlaces anteriores | Generar el PDF cuando la información anterior esté disponible |

## Limitaciones de la implementación

- El módulo de usuarios no permite cambiar la contraseña propia desde
  el panel; la edición de contraseñas está reservada al rol
  Administrador.
- La eliminación de una cuenta se realiza como borrado físico. Si la
  cuenta tiene movimientos asociados, la base de datos la protege con
  la clave foránea y el controlador muestra un mensaje. No existe
  borrado lógico para usuarios.
- La alerta de stock bajo se registra en la cola `database`. En este
  entorno la cola no tiene un worker ejecutándose de forma permanente;
  las alertas se materializan al ejecutar `php artisan queue:work` o
  al procesar la cola manualmente.
- El evento `StockBajo` se dispara después de confirmar la transacción
  de inventario. Si el proceso fallara justo después del commit, el
  movimiento quedaría registrado sin su alerta correspondiente; en la
  práctica se recupera con un ajuste o con la revisión manual del
  stock.
- La rama remota `origin/feature/modulos-grupo-2` del equipo contiene
  reportes, historial de movimientos y una versión propia del evento
  `StockBajo`. Este incremento implementa su versión en la rama
  `Katherinne`; la fusión de ambas ramas queda pendiente de
  coordinación con el equipo.
- La exportación `docs/database/estructura.sql` se generó con
  `mysqldump --no-data` sobre la base local. Es una referencia de
  estructura, no un respaldo con datos.

## Pendientes técnicos

- Ejecutar `php artisan queue:work` (o programar un scheduler) para
  materializar las alertas de stock bajo en la tabla de alertas.
- Fusionar `origin/feature/modulos-grupo-2` y resolver los posibles
  conflictos con los módulos de este incremento.
- Completar la portada del PDF de entrega cuando se reciban los datos
  de integrantes y enlaces.
- Verificar el comportamiento en el servidor de producción cuando
  exista: la cola, las variables de entorno y las credenciales de
  MySQL deben configurarse por separado.

## Estado de las pruebas

- Suite completa: 98 pruebas, 563 aserciones, todas en verde.
- Incluye las pruebas de usuarios (15) y de alerta de stock bajo (5)
  agregadas en este incremento.
- Requiere MySQL activo y la base `libreria_independencia_testing`.
