# Veterinaria a Domicilio

Sistema para una veterinaria que atiende a domicilio: dueños, mascotas, historial médico, vacunas y desparasitaciones, agenda de visitas, cobros, fotos/archivos y recordatorios.

Hecho con **Laravel 13 + Filament 5** (panel de administración responsive, pensado para usarse desde el celular).

## Qué incluye

| Sección | Qué hace |
|---|---|
| **Escritorio** | Visitas de hoy (ruta del día con botón a Google Maps y WhatsApp), vacunas por vencer, saldo por cobrar e ingresos del mes. |
| **Calendario** | Vista semanal/mensual de visitas; en el celular abre en lista. Se pueden arrastrar visitas para cambiarlas de hora. |
| **Visitas** | Agendar, completar, cancelar. **Completar** registra en un solo paso la consulta, las vacunas aplicadas (con botones de "próxima dosis en 1/3/6/12 meses") y el cobro. |
| **Mascotas** | Ficha clínica: datos, foto, dueño, próximas dosis, y pestañas de historial médico, vacunas, visitas y fotos/archivos (imágenes y PDF). |
| **Dueños** | Contacto, dirección/ubicación, mascotas y cobros; saldo pendiente. |
| **Cobros** | Pendientes / cobrado este mes / todos, con totales y "marcar como cobrado". |
| **Recordatorios** | Correo automático al dueño el día antes de cada visita y cuando una vacuna está por vencer (7 días antes). Botones de WhatsApp con el mensaje ya escrito (gratis, sin API). |
| **Respaldos** | Copia diaria de la base de datos y de los archivos subidos (`spatie/laravel-backup`). |

## Instalación local

Requisitos: PHP 8.3+, Composer, Node 20+.

```bash
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate --seed       # en local crea datos de ejemplo
php artisan storage:link
php artisan serve
```

Entrar a http://localhost:8000 con `admin@veterinaria.test` / `password`.

Para crear el usuario real de la doctora:

```bash
php artisan make:filament-user
```

Tests: `php artisan test`.

## Configuración (`.env`)

| Variable | Para qué |
|---|---|
| `APP_TIMEZONE` | Zona horaria del país (ej. `America/Tegucigalpa`, `America/Bogota`, `America/Mexico_City`). Importante para la agenda y los recordatorios. |
| `APP_CURRENCY` | Moneda de los cobros (`USD`, `HNL`, `MXN`, `COP`, ...). |
| `VET_WHATSAPP_COUNTRY_CODE` | Código de país que se agrega a los números sin él para los enlaces de WhatsApp (El Salvador: `503`). |
| `VET_VACCINE_REMINDER_DAYS` | Días de anticipación para avisar vacunas (por defecto 7). |
| `MAIL_*` | Servidor de correo para los recordatorios (por ejemplo una cuenta de Gmail con contraseña de aplicación, Brevo, Resend...). |
| `BACKUP_DISKS`, `BACKUP_NOTIFY_EMAIL` | Dónde guardar los respaldos (`local`, o `s3` configurando AWS) y a quién avisar si fallan. |

## Publicar en Laravel Cloud (recomendado)

1. Entrar a https://cloud.laravel.com con la cuenta de GitHub y crear una aplicación nueva desde el repositorio `veterinaria` (rama `main`), en una región de EE. UU. (la más cercana a El Salvador).
2. En el entorno, agregar:
   - **Base de datos**: MySQL o Postgres. Cloud pone solo las variables `DB_*`.
   - **Bucket (almacenamiento de archivos)**: privado. Anotar el nombre de disco que se le da (por ejemplo `s3`).
3. **Comandos de build**: además de `composer install`, que incluya `npm ci && npm run build`.
4. **Comandos de deploy**: `php artisan migrate --force && php artisan db:seed --force`.
5. **Variables de entorno**:
   ```
   APP_NAME="Veterinaria a Domicilio"
   APP_LOCALE=es
   APP_FALLBACK_LOCALE=es
   APP_TIMEZONE=America/El_Salvador
   APP_CURRENCY=USD
   VET_WHATSAPP_COUNTRY_CODE=503
   VET_UPLOADS_DISK=s3
   BACKUP_DISKS=s3
   VET_ADMIN_NAME=Doctora
   VET_ADMIN_EMAIL=correo-de-la-doctora@ejemplo.com
   VET_ADMIN_PASSWORD=una-clave-larga-y-segura
   ```
   `VET_UPLOADS_DISK` y `BACKUP_DISKS` llevan el nombre de disco del bucket. Para los recordatorios por correo agregar también `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD` y `MAIL_FROM_ADDRESS` (si no, la app funciona igual y solo no salen los correos).
6. Activar el **Scheduler** del entorno (recordatorios a las 8:00 y respaldos diarios).
7. **Deploy**. El comando de deploy crea la cuenta de la doctora con `VET_ADMIN_EMAIL` / `VET_ADMIN_PASSWORD`. En producción no se crean datos de ejemplo ni el usuario `admin@veterinaria.test`.
8. Pasarle a la doctora el enlace `https://<tu-app>.laravel.cloud/admin` con su correo y contraseña. En el celular: abrirlo en Chrome o Safari → menú → **Agregar a pantalla de inicio**.

## Puesta en producción (otro hosting)

Sirve cualquier hosting que soporte Laravel (hosting compartido con PHP 8.3 y SSH, o un VPS pequeño).

1. Base de datos MySQL/MariaDB: poner `DB_CONNECTION=mysql` y sus datos en `.env`.
2. `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://tu-dominio`.
3. Desplegar:
   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci && npm run build
   php artisan migrate --force
   php artisan storage:link
   php artisan db:seed --force      # crea la cuenta con VET_ADMIN_EMAIL / VET_ADMIN_PASSWORD
   # o, en su lugar: php artisan make:filament-user
   php artisan optimize
   php artisan filament:optimize
   ```
4. **Cron** (recordatorios a las 8:00 y respaldos de madrugada):
   ```
   * * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
   ```
5. Para respaldos de MySQL el servidor necesita `mysqldump` (con SQLite, `sqlite3`). Conviene copiar los respaldos fuera del servidor (disco `s3` o descargarlos periódicamente).

## Comandos útiles

```bash
php artisan vet:send-reminders   # enviar recordatorios ahora
php artisan backup:run           # respaldo manual
php artisan backup:list          # ver respaldos
```
