# filament-ddd-starter

Starter kit de producción para Laravel basado en **FilamentPHP v5** con arquitectura **Domain-Driven Design**.

## Stack

| Capa | Tecnología |
|------|------------|
| Framework | Laravel 13, PHP 8.4 |
| Admin UI | Filament 5 (Livewire 4 + Alpine.js + Tailwind v4) |
| Auth / RBAC | Spatie Permission + Filament Shield |
| Testing | Pest 4 |
| Formatter | Laravel Pint |
| Análisis estático | Rector 2 + Larastan 3 (PHPStan) |

## Instalación

```bash
laravel new mi-proyecto --using=orebarranco/filament-ddd-starter
cd mi-proyecto
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run build

# Servidor de desarrollo
composer run dev
```

El panel de administración queda en `/admin`.

## Arquitectura

> Basada en **Laravel Beyond CRUD** de Brent Roose (Spatie).

La lógica de negocio vive en `src/Domain/`, completamente separada de la capa HTTP y de Filament:

```
src/Domain/
└── Identity/           ← Dominio de usuarios y roles
    ├── Actions/        ← Casos de uso (CreateUserAction, UpdateUserAction…)
    ├── DataTransferObjects/   ← UserData (readonly, fromArray)
    └── Models/         ← User (Eloquent puro)

app/Filament/Resources/ ← UI delegando a Domain Actions
app/Policies/           ← Autorización vía Spatie Permission
```

### Flujo de una operación

```
Filament Page → DTO::fromArray($data) → resolve(XxxAction::class)->execute(dto) → Model
```

Las Pages de Filament solo construyen el DTO y delegan. Nunca contienen lógica de negocio.

## Crear un nuevo dominio

```bash
php artisan domain:make NombreDominio
```

Ver `.claude/rules/ddd.md` para el paso a paso completo.

## Comandos frecuentes

```bash
# Entorno de desarrollo (server + queue + pail + vite, en paralelo)
composer dev

# Aplicar formato y refactor (pint + rector, modifica archivos)
composer lint

# Verificar sin modificar (pint --test + rector --dry-run)
composer test:lint

# Solo tests con cobertura
composer test:unit

# Suite completa: tests + lint (lo que corre en CI)
composer test

# Análisis estático con Larastan
composer analyse

# Listar rutas del panel
php artisan route:list --path=admin --except-vendor
```

## Convenciones

- Usar `resolve()` (no `app()` ni `new`) para instanciar Actions.
- Toda nueva funcionalidad requiere test Pest antes de hacer merge.
- Ver `CLAUDE.md` y `.claude/rules/` para guías detalladas de IA.
