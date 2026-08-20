# Registro de cambios

Todos los cambios relevantes de este proyecto se documentan aquí.

El formato sigue [Keep a Changelog](https://keepachangelog.com/es-ES/1.1.0/) y
el versionado sigue [Semantic Versioning](https://semver.org/lang/es/).

## [Sin publicar]

Todo lo de abajo está en `main` y saldrá en la primera versión publicada.

### Añadido

- Recuperación de contraseña en el panel de administración. Antes solo estaba
  `->login()`, así que un administrador que olvidaba su contraseña dependía de
  que alguien con acceso a la base de datos se la cambiara a mano.
- Suplantación de usuarios para el rol `super_admin`, vía
  `stechstudio/filament-impersonate`, con una acción en la tabla de usuarios.
- Columna `active` en `users`, como interruptor de cuenta independiente del
  rol. Suspender a alguien le cierra el panel sin tocar sus roles.
- `SuperAdminSeeder`, que crea el rol `super_admin` y el primer administrador.
  Sin él, una instalación nueva quedaba en un panel donde nadie podía entrar.
- Filtro y columna de estado activo en la tabla de usuarios.
- Fichero `LICENSE` (MIT). El `composer.json` lo declaraba, pero el fichero que
  concede el permiso no existía.
- `CONTRIBUTING.md`, plantilla de Pull Request y `.claude/rules/git-workflow.md`.
- `.claude/rules/comments.md`, con la norma de comentarios del proyecto.
- Configuración de Dependabot para Composer, npm y las actions del workflow,
  con las actualizaciones menores agrupadas y las mayores separadas.

### Corregido

- **El panel estaba abierto a cualquier usuario autenticado.**
  `canAccessPanel()` devolvía `true` sin condiciones. Ahora exige cuenta activa
  y al menos un rol asignado.
- **`UserPolicy` nunca se aplicaba, y fallaba abierto.** Laravel resuelve las
  policies por la convención `App\Models\X` → `App\Policies\XPolicy`, y el
  modelo vive en `Domain\Identity\Models`, así que `Gate::getPolicyFor()`
  devolvía `null`. Filament interpreta la ausencia de policy como permiso
  concedido, de modo que ningún permiso de Shield se evaluaba nunca. Resuelto
  con el atributo `#[UsePolicy]` en el modelo.

### Cambiado

- El paquete declara su orientación al español en la descripción, las
  *keywords* y el README. Siempre venía con `APP_LOCALE=es`, etiquetas y
  mensajes de validación en español, pero nada lo decía.
- `lang:update` deja de ejecutarse en cada `composer update`. Regeneraba los 14
  ficheros de `lang/` y enterraba cada actualización de dependencias bajo el
  ruido. Las traducciones se quedan; ejecútalo a mano cuando toque.
- `define_via_gate` de Shield pasa a `true`, de modo que `super_admin` atraviesa
  las comprobaciones de permisos por su interceptor de gate.
- Dependencias actualizadas: Laravel 13.26, Filament 5.7.6, Shield 4.3.1,
  Pulse 1.8, laravel-backup 10.3.2, Pest 4.7.8, vite 8 con
  `laravel-vite-plugin` 3. `composer audit` pasó de 8 advisorías a ninguna.
- Metadatos del paquete completados: `homepage`, `authors` y `support`.

### Eliminado

- El espejo de skills `.junie/`. Boost solo genera para los agentes listados en
  `boost.json`, y junie no estaba entre ellos, así que eran 24 ficheros
  huérfanos que viajaban en cada instalación.
- El usuario «Test User» sin rol del seeder, sustituido por el administrador
  real que crea `SuperAdminSeeder`.
