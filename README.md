# Construction Workspace


The application combines a public construction services website with an authenticated project workspace. Public users can browse services, view portfolio work, and submit contact inquiries. Internal users can manage projects, tasks, project members, customer inquiries, project chat, attachments, progress images, calendars, profile avatars, and web push notifications.

## Tech Stack

- PHP 8.3+
- Laravel 12
- Laravel Breeze authentication
- Inertia.js 2 with Vue 3
- Vite 7, Tailwind CSS 3, PostCSS, Autoprefixer
- Ziggy for named Laravel routes in Vue
- SQLite by default for local development; MySQL is used in Laravel Cloud
- Database-backed sessions, cache, queues, and jobs by default
- Web Push notifications through `laravel-notification-channels/webpush`
- Public file storage through Laravel's `public` disk, with optional S3 support in `config/filesystems.php`

## Main Capabilities

- Public website: home, services, portfolio, contact, and language switching.
- Internationalization: Estonian, English, and Finnish copy for public and workspace views.
- Customer inquiries: contact form submissions, status workflow, and conversion into projects.
- Project workspace: projects, project members, roles, tasks, due dates, statuses, comments, and progress images.
- Task views: user-specific task list and calendar view across assigned project work.
- Project chat: per-project channels, unread counts, file attachments, older-message loading, and message deletion by author.
- Notifications: queued web push notifications for new project chat messages.
- Profiles: account details, password updates, avatar upload/removal, and device push subscription management.
- PWA basics: manifest, icons, screenshots, installability metadata, and service worker push handling.

## Requirements

- PHP 8.2 or newer
- Composer
- Node.js and npm
- SQLite for local development, or another Laravel-supported database
- A queue worker for queued notifications

## Local Setup

Install PHP and JavaScript dependencies:

```bash
composer install
npm install
```

Create the environment file and application key:

```bash
cp .env.example .env
php artisan key:generate
```

For the default SQLite setup, create the local database file if it does not already exist:

```bash
touch database/database.sqlite
```

Run migrations and seed the default test user:

```bash
php artisan migrate --seed
```

Expose uploaded files from the `public` disk:

```bash
php artisan storage:link
```

Build frontend assets once:

```bash
npm run build
```

The seeder creates a user with `test@example.com` and password `password`.

## Development Server

The Composer development script starts the Laravel server, queue listener, log tail, and Vite server together:

```bash
composer run dev
```

The Vite development server is configured on port `5174` in `vite.config.js`.

You can also run the processes separately:

```bash
php artisan serve
npm run dev
php artisan queue:listen --tries=1
php artisan pail --timeout=0
```

## Environment

Important local defaults from `.env.example`:

```dotenv
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=sqlite
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local
MAIL_MAILER=log
VITE_APP_NAME="${APP_NAME}"
```

For production, set at least:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL` to the deployed HTTPS URL
- database connection credentials
- `QUEUE_CONNECTION` to the deployed queue backend
- mail transport credentials if password reset or verification email is used
- `VAPID_SUBJECT`, `VAPID_PUBLIC_KEY`, and `VAPID_PRIVATE_KEY` for browser push notifications

Generate local VAPID keys with:

```bash
php artisan webpush:vapid
```

For Safari and iOS push support, `VAPID_SUBJECT` must be a valid URL or `mailto:` address.

## Application Structure

```text
app/
  Http/Controllers/       Request handlers for public pages, projects, tasks, chat, inquiries, profile, search, and push subscriptions
  Http/Middleware/        Inertia shared props and locale setup
  Models/                 Eloquent models for users, projects, tasks, inquiries, comments, chat, attachments, and progress images
  Notifications/          Queued web push notification classes
  Policies/               Project, task, comment, and project message authorization rules
bootstrap/app.php         Laravel 12 bootstrap configuration and middleware registration
config/                   Application, database, queue, filesystem, mail, session, and web push configuration
database/migrations/      Schema for users, projects, tasks, comments, inquiries, chat, files, and push subscriptions
database/seeders/         Local seed data
public/                   Front controller, PWA manifest, service worker, icons, screenshots, and static images
resources/js/             Inertia/Vue application, pages, layouts, components, composables, and translations
resources/css/app.css     Tailwind and application styles
routes/web.php            Public, authenticated workspace, profile, push, chat attachment, and locale routes
routes/auth.php           Breeze authentication routes
tests/                    PHPUnit unit and feature tests
```

## Backend Architecture

### Routing

Public routes are defined in `routes/web.php`:

- `GET /` renders the public home page.
- `GET /services`, `/portfolio`, `/contact` render public marketing pages.
- `POST /contact` stores a customer inquiry.
- `GET|POST /locale/{locale?}` switches the current session locale between `et`, `en`, and `fi`.

Authenticated workspace routes include:

- `/dashboard` for recent project, task, chat, and inquiry activity.
- `/projects` and `/projects/{project}` for project management.
- `/tasks/{task}` and nested task comment/progress-image routes.
- `/my-tasks` for assigned work.
- `/calendar` for due-date views.
- `/chat` and `/chat/{project}` for project channels.
- `/inquiries` for inquiry review, status updates, and conversion to projects.
- `/search` for global workspace search JSON.
- `/profile` and `/profile/avatar` for user settings and avatar files.
- `/push-subscriptions` for browser push subscription storage.

Authentication routes are provided by Laravel Breeze in `routes/auth.php`.

### Middleware

`bootstrap/app.php` appends two web middleware classes:

- `SetLocale` reads the selected session locale and applies a safe fallback to Estonian.
- `HandleInertiaRequests` shares authenticated user data, current locale, flash messages, and the VAPID public key with Vue pages.

### Authorization

Project access is membership-based. A user can view a project only when they are attached through the `project_user` pivot table.

Project roles currently used by the application:

- `office`: full project management, member management, project update/delete, task creation, and destructive project actions.
- `worker`: task update permissions and task status changes.
- `member`: task status changes.
- `client`: project visibility without task management permissions.

Message and comment deletion are author-owned actions. Task progress images can be edited or deleted by the uploader or by users who can update the task.

## Domain Model

Core tables and relationships:

- `users`: authenticated workspace users. Includes optional `avatar_path` and web push subscriptions through the package trait.
- `projects`: construction projects with `name`, `description`, `status`, and optional `inquiry_id`.
- `project_user`: project membership pivot with a `role` column.
- `tasks`: project tasks with assignee, status (`todo`, `doing`, `done`), due date, and optional description.
- `comments`: project-level or task-level comments authored by users.
- `task_progress_images`: uploaded task progress photos with disk, path, original name, caption, MIME type, and size.
- `inquiries`: public contact requests with status (`new`, `contacted`, `converted`).
- `project_messages`: project chat messages.
- `project_message_reads`: per-user read markers for unread chat counts.
- `project_message_attachments`: files attached to chat messages.
- `push_subscriptions`: browser push subscriptions used by the web push notification package.

Important model relationships:

- `User belongsToMany Project` through `project_user`.
- `Project hasMany Task`, `Comment`, and `ProjectMessage`.
- `Project belongsTo Inquiry`.
- `Task belongsTo Project` and optionally belongs to an assignee `User`.
- `Task hasMany Comment` and `TaskProgressImage`.
- `ProjectMessage hasMany ProjectMessageAttachment`.
- `Inquiry hasOne Project`.

## Frontend Architecture

The Vue/Inertia entry point is `resources/js/app.js`. It registers Inertia page resolution, Ziggy route helpers, and GSAP plugins.

Authenticated pages use `resources/js/Layouts/AuthenticatedLayout.vue`, which provides:

- Sidebar navigation
- Global search
- Locale switcher
- Theme toggle
- PWA manifest metadata
- Service worker registration
- Flash messages

Public pages use `resources/js/Layouts/PublicLayout.vue` and public translations from `resources/js/i18n/public.js`.

Workspace translations are defined in `resources/js/i18n/workspace.js` and consumed through `resources/js/i18n/useT.js`. When adding copy, keep keys present in all three locales.

Vue pages are organized by feature under `resources/js/Pages/`:

- `Public/`
- `Projects/`
- `MyTasks/`
- `Calendar/`
- `Chat/`
- `Inquiries/`
- `Profile/`
- `Auth/`

## File Storage

Uploaded files use Laravel's `public` disk:

- Avatars: `storage/app/public/avatars`
- Chat attachments: `storage/app/public/chat/{project_id}`
- Task progress images: `storage/app/public/tasks/{task_id}/progress`

Run `php artisan storage:link` so files are available through `/storage/...`.

When deleting a project, the controller removes related task progress images and chat attachments from storage before deleting the project record.

The filesystem configuration supports switching the `public` disk to S3 by setting `PUBLIC_DISK_DRIVER=s3` and the standard AWS environment variables.

## Web Push and PWA

The service worker lives at `public/sw.js`. It handles:

- immediate activation
- push payload parsing
- notification display
- notification clicks that focus or open the target workspace URL

The manifest lives at `public/manifest.webmanifest` and points to `/dashboard` as the app start URL.

New chat messages dispatch `App\Notifications\NewProjectMessage`, which implements `ShouldQueue`. Keep a queue worker running in development and production.

## Search

`SearchController` returns JSON results for authenticated users. Results are scoped as follows:

- Projects, tasks, and chat messages are limited to the current user's project memberships.
- Inquiries are searched globally for authenticated users.
- Queries shorter than two characters return an empty result set.

## Testing and Quality

Run the PHP test suite:

```bash
composer test
```

or directly:

```bash
php artisan test
```

The PHPUnit environment uses an in-memory SQLite database, array cache/session drivers, synchronous queues, and array mail.

Format PHP code with Laravel Pint:

```bash
./vendor/bin/pint
```

Verify the frontend production build:

```bash
npm run build
```

## Deployment Notes

Typical deployment steps:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Also configure a long-running queue worker, for example with Supervisor or the platform's worker process manager:

```bash
php artisan queue:work --tries=3
```

Make sure `public/` is the web server document root. The service worker must be served from `/sw.js`, and the application should be served over HTTPS for production push notifications and installability.

## Common Development Tasks

Create a new authenticated page:

1. Add a route in `routes/web.php`.
2. Add a controller method or route closure returning `Inertia::render(...)`.
3. Add the Vue page under `resources/js/Pages`.
4. Add sidebar navigation and translation keys if the page is a workspace module.

Add a project-scoped feature:

1. Check project membership with `ProjectPolicy::view` or a dedicated policy method.
2. Scope queries to projects the current user belongs to.
3. Keep role checks aligned with `office`, `worker`, `member`, and `client`.
4. Return Inertia props with only the fields needed by the frontend.

Add uploaded files:

1. Validate file type and size in the controller.
2. Store files on the `public` disk unless the feature needs another disk.
3. Save disk, path, original name, MIME type, and size.
4. Delete physical files when deleting the owning record.

Add translations:

1. Add the key to every locale in `resources/js/i18n/public.js` or `resources/js/i18n/workspace.js`.
2. Use `useT()` in workspace pages and the public layout translation helpers in public pages.
3. Do not hard-code user-facing strings in Vue templates unless the text is intentionally not localized.
