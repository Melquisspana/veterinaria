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
| `VET_WHATSAPP_COUNTRY_CODE` | Código de país que se agrega a los números sin él para los enlaces de WhatsApp (ej. `504`). |
| `VET_VACCINE_REMINDER_DAYS` | Días de anticipación para avisar vacunas (por defecto 7). |
| `MAIL_*` | Servidor de correo para los recordatorios (por ejemplo una cuenta de Gmail con contraseña de aplicación, Brevo, Resend...). |
| `BACKUP_DISKS`, `BACKUP_NOTIFY_EMAIL` | Dónde guardar los respaldos (`local`, o `s3` configurando AWS) y a quién avisar si fallan. |

## Puesta en producción

Sirve cualquier hosting que soporte Laravel (hosting compartido con PHP 8.3 y SSH, un VPS pequeño, Laravel Cloud o Forge).

1. Base de datos MySQL/MariaDB: poner `DB_CONNECTION=mysql` y sus datos en `.env`.
2. `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://tu-dominio`.
3. Desplegar:
   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci && npm run build
   php artisan migrate --force
   php artisan storage:link
   php artisan make:filament-user
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
