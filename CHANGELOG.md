# Registro de cambios

Todos los cambios relevantes de este proyecto se documentan aquí.

El formato sigue [Keep a Changelog](https://keepachangelog.com/es-ES/1.1.0/) y
el versionado sigue [Semantic Versioning](https://semver.org/lang/es/).

## [1.0.4] - 2026-10-21

### Seguridad

- `symfony/yaml` actualizado a 8.1.9, que cierra CVE-2026-109413: una
  recursión sin límite en el lexer de YAML inline permitía tumbar el proceso
  con un solo documento. Llega como dependencia de desarrollo vía
  `laravel/sail` y `laravel/roster`.

### Cambiado

- Dependencias actualizadas. Guzzle pasa a 8.x como dependencia transitiva del
  framework; el kit no lo usa directamente.
- `laravel-lang/common` acotado a `^6.8`: la restricción abierta `>=6.8` dejaba
  entrar una versión mayor futura sin elegirla.
- `User` y `UserFactory` se enlazan con los atributos `#[UseFactory]` y
  `#[UseModel]` en lugar de `newFactory()` y `$model`. Las reglas de DDD exigen
  el par en todo modelo de dominio nuevo.
- La skill `pest-testing` de Boost pasa a ser `testing-best-practices`.

## [1.0.3] - 2026-10-20

Mismo código que 1.0.2. Packagist registró 1.0.2 sobre el commit de 1.0.1 y
no permite cambiar el commit de una versión ya publicada, así que 1.0.2 nunca
llegó a instalarse con `composer create-project`. Esta versión es la que lleva
de verdad los cambios de 1.0.2.

## [1.0.2] - 2026-10-19

No disponible en Packagist: usa 1.0.3.

### Seguridad

- Filament actualizado a 5.10.2. Hasta la 5.8.2, las acciones de gestión de la
  autenticación multifactor por app se ejecutaban sin volver a pedir la
  contraseña. El kit no activa MFA, pero cualquier proyecto que lo active
  heredaba el fallo desde el `composer.lock`.

### Cambiado

- Assets del panel regenerados para Filament 5.10.2, sin las fuentes Inter de
  la versión anterior, que ya nada referenciaba.
- Las guidelines de Filament para agentes pasan a cargarse como la skill
  `filament-development` de Boost en lugar de ir dentro de `AGENTS.md` y
  `CLAUDE.md`.

## [1.0.1] - 2026-10-19

### Seguridad

- Actualizados `league/commonmark` a 2.10.4, `league/flysystem` a 3.36.0 y
  `laravel/framework` a 13.37.0, que cierran cuatro advisorías: una denegación
  de servicio y un bypass del filtro de HTML en commonmark, un bypass del
  normalizador de rutas en flysystem y un XSS en la página de debug de
  Laravel. El `composer.lock` viaja con el kit y `create-project` instala desde
  él, así que todo proyecto nuevo heredaba esas versiones.

### Corregido

- **Una instalación nueva acababa en un panel en el que nadie podía entrar.**
  Los dos puntos de entrada del instalador migraban y se detenían ahí: sin
  permisos, sin roles y sin administrador. Ahora también siembran la base de
  datos.

### Cambiado

- El README empieza por el patrón de Actions sobre el que se construye el kit,
  y deja de listar pasos de instalación que el propio kit ya ejecuta.
- Dependencias de desarrollo de npm actualizadas: vite 8.3.4, axios 1.20.0 y
  concurrently.

## [1.0.0] - 2026-08-22

Primera versión publicada.

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
- `ShieldSeeder` versionado con el catálogo completo de permisos. Antes
  `shield:generate` solo escribía en la base de datos que tuvieras delante, así
  que una instalación nueva arrancaba con cero permisos y las policies no tenían
  nada que evaluar.

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
- El rol `panel_user` de Shield queda desactivado. No llevaba ningún permiso, y
  un rol vacío solo sirve para que alguien lo asigne y crea haber concedido algo.
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

[1.0.4]: https://github.com/orebarranco/filament-ddd-starter/releases/tag/v1.0.4
[1.0.3]: https://github.com/orebarranco/filament-ddd-starter/releases/tag/v1.0.3
[1.0.2]: https://github.com/orebarranco/filament-ddd-starter/releases/tag/v1.0.2
[1.0.1]: https://github.com/orebarranco/filament-ddd-starter/releases/tag/v1.0.1
[1.0.0]: https://github.com/orebarranco/filament-ddd-starter/releases/tag/v1.0.0
