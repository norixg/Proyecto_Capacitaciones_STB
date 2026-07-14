# Sistema de Capacitaciones STB

Aplicación web para administrar el ciclo completo de capacitación del personal de STB: catálogo y construcción de cursos, asignación por empleado o puesto, consumo de contenido, ejercicios, evaluaciones, seguimiento, avisos por correo y reportes.

El proyecto está construido con Laravel 13, PHP 8.3 o superior, SQL Server, Blade, Tailwind CSS y Vite. El entorno Docker incluido levanta la aplicación, SQL Server 2022 y la inicialización automática del esquema.

## Funcionalidades principales

- Administración de usuarios, empleados, instructores y roles.
- Creación y archivo de capacitaciones.
- Constructor de cursos por módulos y secciones.
- Recursos y contenido teórico, incluidos archivos e imágenes.
- Ejercicios y evaluaciones con preguntas, opciones, intentos y porcentaje mínimo de aprobación.
- Asignación manual de capacitaciones a empleados.
- Matriz puesto-capacitación y generación automática de asignaciones obligatorias.
- Detección de necesidades de capacitación según el puesto del empleado.
- Registro del avance por contenido, módulo y capacitación.
- Revisión de respuestas e intentos que requieren calificación manual.
- Historial de cambios de estado y expediente de capacitación por empleado.
- Avisos de asignación, próximos vencimientos, vencimientos y finalización.
- Reportes filtrables con exportación CSV (compatible con Excel) y PDF.
- Panel con indicadores de usuarios, capacitaciones, asignaciones, avance y cumplimiento.

## Roles y funcionamiento

### Administrador

Gestiona usuarios e instructores, crea y asigna capacitaciones, configura la matriz por puestos, consulta necesidades, revisa el seguimiento, genera reportes y procesa avisos por correo.

### Instructor

Administra únicamente las capacitaciones asociadas a su registro de instructor. Puede construir módulos, recursos, ejercicios y evaluaciones, y revisar el avance o intentos de sus participantes.

Para que esta restricción funcione, un instructor interno debe estar relacionado con un empleado y este, a su vez, con su usuario.

### Empleado/usuario

Consulta sus capacitaciones asignadas, recorre los módulos, marca o registra contenido completado, resuelve ejercicios y evaluaciones, y revisa sus resultados y calificaciones.

### Flujo general

1. El administrador registra usuarios, empleados e instructores.
2. Un administrador o instructor construye una capacitación y sus módulos.
3. Cada módulo puede contener secciones teóricas, recursos, ejercicios y evaluaciones.
4. El administrador asigna la capacitación directamente o la vincula a puestos de trabajo.
5. El empleado completa el contenido y realiza los intentos permitidos.
6. El sistema recalcula el progreso y determina el estado de la asignación.
7. Administradores e instructores consultan el seguimiento; el administrador puede exportar reportes y enviar avisos.

## Arquitectura

```text
Navegador
   |
   v
Laravel 13 + Blade + Tailwind/Vite
   |-- autenticación: Laravel Fortify
   |-- autorización: roles propios + Spatie Permission
   |-- documentos: DOMPDF y exportación CSV
   |-- archivos: storage/app/public
   |
   v
Microsoft SQL Server
   |-- tablas y relaciones del dominio
   |-- vistas de reportes
   `-- procedimientos almacenados
```

Directorios relevantes:

```text
app/Http/Controllers/   Controladores de cada módulo
app/Models/             Modelos Eloquent
app/Services/           Progreso, asignaciones, eliminación y avisos
resources/views/        Interfaz Blade y plantillas de correo/PDF
routes/web.php          Rutas web protegidas por autenticación y rol
routes/console.php      Comando y programación de avisos
database/migrations/    Migraciones auxiliares de Laravel/Spatie
capacitaciones_stb.sql  Esquema principal de SQL Server
.docker/                Entrada y configuración PHP de la imagen
```

## Requisitos

### Instalación manual

- PHP 8.3 o superior.
- Composer 2.
- Node.js 22 y npm.
- Microsoft SQL Server accesible desde el equipo.
- ODBC Driver 18 for SQL Server.
- Extensiones PHP: `pdo_sqlsrv`, `sqlsrv`, `bcmath`, `ctype`, `fileinfo`, `gd`, `intl`, `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml` y `zip`.
- `sqlcmd` o SQL Server Management Studio/Azure Data Studio para importar el esquema.

### Instalación con Docker

- Docker Engine o Docker Desktop.
- Docker Compose v2 (`docker compose`).
- Al menos 4 GB de memoria disponibles para Docker; SQL Server requiere arquitectura `amd64` y en equipos Apple Silicon se ejecuta mediante emulación.

## Preparar SQL Server

> **Advertencia:** `capacitaciones_stb.sql` elimina las tablas, vistas y procedimientos existentes antes de reconstruirlos. No debe ejecutarse sobre una base con información que se necesite conservar. Haga un respaldo primero.

El sistema espera, de forma predeterminada:

```text
Base de datos: db_capacitaciones_stb
Usuario SQL:   usuario_laravel
Contraseña:    StbLaravel_2026!
```

Se recomienda cambiar esas credenciales fuera de un entorno local.

El encabezado del archivo SQL intenta crear el login antes que la base y usa la base antes de crearla. Para una instalación nueva, prepare primero la base y el login desde una cuenta administradora de SQL Server:

```sql
USE [master];
GO

IF DB_ID(N'db_capacitaciones_stb') IS NULL
    CREATE DATABASE [db_capacitaciones_stb];
GO

IF SUSER_ID(N'usuario_laravel') IS NULL
    CREATE LOGIN [usuario_laravel] WITH PASSWORD = 'StbLaravel_2026!';
GO

USE [db_capacitaciones_stb];
GO

IF USER_ID(N'usuario_laravel') IS NULL
    CREATE USER [usuario_laravel] FOR LOGIN [usuario_laravel];
GO

ALTER ROLE [db_owner] ADD MEMBER [usuario_laravel];
GO
```

Después ejecute `capacitaciones_stb.sql` en SQL Server Management Studio o Azure Data Studio. Es normal que aparezca un aviso indicando que el login ya existe; el resto de los lotes debe continuar. Al terminar, confirme que existen, entre otras, las tablas `users`, `rol`, `capacitacion`, `capacitacion_modulo` y `empleado_capacitacion`.

Con `sqlcmd` se puede importar así (sin `-b`, para que el aviso del login existente no detenga los lotes posteriores):

```bash
sqlcmd -S localhost,1433 -U sa -P 'CONTRASENA_SA' -C -i capacitaciones_stb.sql
```

Adapte servidor, puerto y credenciales a su instalación.

> El esquema principal no se obtiene ejecutando solamente `php artisan migrate`. El archivo `capacitaciones_stb.sql` es la fuente del modelo de datos de este proyecto e incluye vistas, procedimientos, roles y permisos base.

## Instalación manual

### 1. Instalar dependencias

Desde la raíz del repositorio:

```bash
composer install
npm ci
```

### 2. Crear el archivo de entorno

```bash
cp .env.example .env
php artisan key:generate
```

Edite `.env` y configure al menos:

```dotenv
APP_NAME="Capacitaciones STB"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
APP_LOCALE=es
APP_FALLBACK_LOCALE=es

DB_CONNECTION=sqlsrv
DB_HOST=127.0.0.1
DB_PORT=1433
DB_DATABASE=db_capacitaciones_stb
DB_USERNAME=usuario_laravel
DB_PASSWORD=usuario_laravel
DB_ENCRYPT=no
DB_TRUST_SERVER_CERTIFICATE=true

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local
```

Para producción use `APP_ENV=production`, `APP_DEBUG=false`, HTTPS y credenciales únicas.

### 3. Preparar Laravel y los recursos web

```bash
php artisan storage:link
npm run build
php artisan optimize:clear
```

No ejecute el seeder predeterminado como método de instalación: solo crea un usuario de prueba y no configura toda la relación de roles propia del sistema.

### 4. Crear el primer administrador

El script SQL crea los roles y permisos, pero no una cuenta inicial. Abra Tinker:

```bash
php artisan tinker
```

Para desarrollo puede crear la cuenta `admin@stb.local` con contraseña temporal `Admin123!`:

```php
$user = App\Models\User::create([
    'name' => 'Administrador',
    'email' => 'admin@stb.local',
    'password' => 'Admin123!',
    'estado' => 1,
]);

DB::table('user_rol')->insert([
    'id_user' => $user->id,
    'id_rol' => 1,
]);

$user->assignRole('admin');
```

Salga de Tinker con `exit`. Laravel aplica hash automáticamente a la contraseña mediante el modelo `User`.

Inicie sesión con:

```text
Correo:     admin@stb.local
Contraseña: Admin123!
```

> Esta cuenta no se crea automáticamente al importar el SQL. Ejecute el bloque anterior una sola vez y cambie inmediatamente la contraseña en cualquier entorno compartido o de producción.

### 5. Iniciar el entorno

Opción sencilla, en dos terminales:

```bash
php artisan serve
```

```bash
npm run dev
```

Abra `http://127.0.0.1:8000` e inicie sesión con el administrador creado.

También puede iniciar servidor, Vite y el listener de cola con:

```bash
composer run dev
```

Con la configuración recomendada `QUEUE_CONNECTION=sync`, el listener de cola no es necesario para el funcionamiento actual.

## Instalación con Docker

La imagen incluida compila los recursos con Node 22 y sirve Laravel mediante Apache/PHP 8.4. Compose levanta tres servicios:

- `sqlserver`: SQL Server 2022 con almacenamiento persistente, accesible solo dentro de la red de Compose.
- `sqlserver_init`: tarea de una sola ejecución que importa `capacitaciones_stb.sql` cuando el esquema aún no existe y después termina.
- `capacitaciones_app`: aplicación Laravel, iniciada después de que la base esté saludable e inicializada.

El SQL Server del proyecto no publica el puerto `1433` en el host, por lo que no entra en conflicto con otras instancias de SQL Server que ya estén ejecutándose en Docker.

### 1. Construir y levantar

```bash
docker compose up --build
```

La primera ejecución descarga las imágenes, compila PHP y frontend, crea `db_capacitaciones_stb` e importa el SQL. Cuando aparezca Apache en los logs, abra `http://localhost:8080`.

Para dejarlo en segundo plano:

```bash
docker compose up -d --build
```

No es obligatorio crear `.env.docker`. Compose proporciona la configuración interna y el punto de entrada genera y conserva `APP_KEY` automáticamente.

Si el puerto 8080 está ocupado:

```bash
APP_PORT=8081 docker compose up -d --build
```

En ese caso la aplicación estará en `http://localhost:8081`. Debe usar el mismo `APP_PORT` en los siguientes comandos que puedan recrear el contenedor:

```bash
APP_PORT=8081 docker compose up -d
```

SQL Server no se publica hacia el host. Laravel se conecta internamente a `sqlserver:1433`; para administrar esta base desde el host puede publicar manualmente un puerto libre si realmente lo necesita.

### 2. Crear el primer administrador

La importación crea el esquema, los roles y los permisos, pero no crea usuarios. Después del primer arranque, abra Tinker dentro del contenedor:

```bash
docker compose exec capacitaciones_app php artisan tinker
```

Pegue:

```php
$user = App\Models\User::create([
    'name' => 'Administrador',
    'email' => 'admin@stb.local',
    'password' => 'Admin123!',
    'estado' => 1,
]);

DB::table('user_rol')->insert([
    'id_user' => $user->id,
    'id_rol' => 1,
]);

$user->assignRole('admin');
```

Escriba `exit` para salir y acceda a `/login` con:

```text
Correo:     admin@stb.local
Contraseña: Admin123!
```

Ejecute este procedimiento solo una vez. Cambie la contraseña antes de utilizar el sistema fuera de un entorno local.

### Persistencia e inicialización

Los datos viven en el volumen `sqlserver_data`. En arranques posteriores, el inicializador comprueba el esquema y omite el SQL destructivo si la base ya está preparada. El servicio `sqlserver_init` debe terminar con estado `Exited (0)`; esto es normal porque es una tarea de inicialización, no un servidor permanente.

### Comandos Docker frecuentes

```bash
# Ver registros
docker compose logs -f capacitaciones_app

# Limpiar cachés de Laravel
docker compose exec capacitaciones_app php artisan optimize:clear

# Probar conexión y estado de la aplicación
docker compose exec capacitaciones_app php artisan about

# Detener los contenedores
docker compose down

# Estado de aplicación, inicializador y base
docker compose ps -a

# Logs de la importación inicial
docker compose logs sqlserver_init

# Reconstruir después de cambiar PHP, Composer, Node o recursos frontend
docker compose up -d --build
```

Los archivos públicos, logs y sesiones están montados desde el host. El punto de entrada crea permisos, el enlace `public/storage` y una clave de aplicación persistente al arrancar.

## Configuración de correo y avisos

Por defecto el correo usa `MAIL_MAILER=log`: los mensajes no salen a destinatarios reales y se escriben en `storage/logs/laravel.log`.

Ejemplo SMTP:

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=usuario
MAIL_PASSWORD=contraseña
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=capacitaciones@example.com
MAIL_FROM_NAME="Capacitaciones STB"
```

Puede generar y enviar avisos manualmente desde el panel de administrador o por consola:

```bash
php artisan avisos:procesar
```

El comando está programado en `routes/console.php`. Para que la programación automática funcione en una instalación manual, agregue al cron del servidor:

```cron
* * * * * cd /ruta/Proyecto_Capacitaciones_STB && php artisan schedule:run >> /dev/null 2>&1
```

El Compose actual no incluye un proceso de scheduler. En Docker puede ejecutarlo desde el cron del host:

```cron
* * * * * cd /ruta/Proyecto_Capacitaciones_STB && docker compose exec -T capacitaciones_app php artisan schedule:run >> /dev/null 2>&1
```

## Archivos y límites de carga

Los archivos públicos se almacenan en `storage/app/public` y requieren el enlace `public/storage`. La imagen Docker configura aproximadamente:

- Carga máxima por archivo: 250 MB.
- Tamaño máximo de solicitud: 260 MB.
- Memoria PHP: 512 MB.

En una instalación manual ajuste `php.ini` y el servidor web si necesita los mismos límites.

## Verificación y mantenimiento

Comandos útiles:

```bash
# Información del entorno
php artisan about

# Confirmar rutas cargadas
php artisan route:list

# Limpiar cachés después de modificar .env
php artisan optimize:clear

# Formato y pruebas automatizadas disponibles
composer run lint:check
php artisan test
```

Antes de producción:

```bash
npm run build
php artisan optimize
```

Además, configure respaldos de SQL Server y de `storage/app/public`, SMTP real, HTTPS, rotación de logs y ejecución periódica del scheduler.

## Solución de problemas

### `could not find driver` o error de `sqlsrv`

Verifique que PHP cargue las extensiones correctas:

```bash
php -m | grep -E 'sqlsrv|pdo_sqlsrv'
```

El PHP usado por Composer/Artisan y el PHP del servidor web deben tener ambas extensiones habilitadas.

### Error de certificado de SQL Server

En desarrollo confirme:

```dotenv
DB_ENCRYPT=no
DB_TRUST_SERVER_CERTIFICATE=true
```

En producción se recomienda cifrado y un certificado válido.

### SQL Server no inicia en Docker

- Revise `docker compose logs sqlserver` y `docker compose logs sqlserver_init`.
- Asigne suficiente memoria a Docker Desktop.
- El servicio no publica `1433` en el host; un SQL Server existente puede seguir utilizando ese puerto sin conflicto.
- En Apple Silicon, mantenga `platform: linux/amd64` y habilite la emulación de Docker Desktop.
- Si la inicialización quedó incompleta y no hay datos que conservar, ejecute `docker compose down -v` y vuelva a levantar.

### `sqlserver_init` aparece detenido en Docker Desktop

Es el comportamiento esperado si `docker compose ps -a` muestra `Exited (0)`. Es una tarea de inicialización y no debe permanecer ejecutándose. Si muestra `Exited (1)`, revise `docker compose logs sqlserver_init`.

### La aplicación no abre en el navegador

Revise el puerto publicado:

```bash
docker compose ps -a
```

Si 8080 está ocupado, recree la aplicación en 8081:

```bash
APP_PORT=8081 docker compose up -d --force-recreate capacitaciones_app
```

Luego abra `http://localhost:8081`. Puede comprobar la respuesta desde la terminal:

```bash
curl -I http://localhost:8081/login
```

### La interfaz aparece sin estilos

En manual ejecute `npm run build` o mantenga `npm run dev` activo. En Docker reconstruya la imagen con `docker compose up -d --build`.

### No se pueden ver archivos subidos

```bash
php artisan storage:link
```

En Docker, reinicie el servicio si el enlace no existe:

```bash
docker compose restart capacitaciones_app
```

### Los correos no llegan

Si `MAIL_MAILER=log`, esto es el comportamiento esperado. Revise `storage/logs/laravel.log` o configure SMTP y luego ejecute `php artisan optimize:clear`.

### Error 403 para un usuario o instructor

Compruebe que el usuario esté activo, tenga su relación en `user_rol` y el rol equivalente de Spatie en `model_has_roles`. Para instructores internos, confirme también la relación usuario → empleado → instructor.

## Consideraciones de seguridad

- Nunca suba `.env` ni `.env.docker` al repositorio.
- Cambie las credenciales de ejemplo de SQL Server.
- Use una contraseña fuerte para el primer administrador y cámbiela tras el primer acceso.
- Mantenga `APP_DEBUG=false` en producción.
- Restrinja el acceso a SQL Server y use TLS en producción.
- Respalde la base antes de ejecutar `capacitaciones_stb.sql`; el script reconstruye el esquema.
- El registro público está deshabilitado: las cuentas se gestionan desde administración.

## Tecnologías

- Laravel 13 y PHP 8.3+
- Microsoft SQL Server y ODBC Driver 18
- Laravel Fortify
- Spatie Laravel Permission
- Blade, Alpine.js, Tailwind CSS y Vite
- DOMPDF
- Apache (imagen Docker)
