# Comentarios — cuándo un comentario se gana su sitio

> Aplica a todo el código del proyecto: `src/`, `app/`, `database/`, `tests/`.
> La skill `laravel-best-practices` que instala `laravel/boost` trae su propia versión
> reducida de esta norma (`rules/style.md`, sección «No Unnecessary Comments»). Este
> fichero la extiende y **declara explícitamente su excepción de ficheros de config**
> para que los dos documentos no se contradigan.

## Principio

El código se documenta solo, mediante nombres descriptivos de variables y de métodos.
**Añadir un comentario no es nunca la primera táctica para hacer legible un fragmento.**
Los comentarios se quedan obsoletos y terminan mintiendo; un nombre no puede quedarse
obsoleto sin que el compilador o el test se enteren.

Un comentario solo se gana su sitio cuando explica un **porqué no evidente** que no cabe
en ningún nombre.

---

## Las seis reglas

| # | Regla | Motivo |
|---|---|---|
| 1 | Ningún comentario describe **qué** hace el código | Es información que el propio código ya da, duplicada en un sitio que no se verifica |
| 2 | Código corto y legible no lleva comentario | Si hace falta explicarlo, primero se intenta reescribirlo |
| 3 | Nombre descriptivo antes que nombre genérico + comentario | El nombre viaja con el valor; el comentario se queda arriba |
| 4 | Se comenta el **porqué** de algo no evidente, nunca el qué | Es lo único que no se puede deducir leyendo |
| 5 | **Nunca** hay comentarios en los tests | El nombre del test carga el significado; si no lo carga, el nombre está mal |
| 6 | **Nunca** se referencia una clave de ticket, un número de ADR, una letra de decisión ni un id de hallazgo de revisión | Ese identificador no es resoluble desde el repositorio |

---

## Reglas 3 y 5 — antes y después

Caso real de este repositorio, en
`tests/Unit/Domain/Identity/DataTransferObjects/UserDataTest.php`. El test comprueba que
el DTO deja la contraseña en `null` cuando el formulario la manda vacía.

Antes:

```php
it('leaves the password null when the field is submitted empty', function (): void {
    // The edit form keeps the password input on screen but sends it blank when
    // the user does not intend to change it.
    $data = UserData::fromArray([...]);
```

Después:

```php
it('leaves the password null when the edit form submits the field blank', function (): void {
    $data = UserData::fromArray([...]);
```

El arreglo fue **nombrar el test**, no reformular el comentario. Todo lo que decía el
comentario cabía en el nombre, y ahí no puede quedarse obsoleto sin que se note.

---

## Regla 6 — el comentario tiene que ser autocontenido

Un comentario se entiende **con solo el repositorio delante**. Las claves de JIRA, los
identificadores de artefactos SDD y los ids de hallazgos de revisión viven fuera del
repositorio: quien lee el código no puede ir a mirarlos, y el puntero se pudre en cuanto
el artefacto se archiva o se renumera.

Mal:

```php
// El rol manda sobre la columna. Ver decisión B del diseño.
```

Bien:

```php
// El rol ya responde "qué puede hacer este usuario": una columna is_admin
// dejaría dos fuentes de verdad para la misma pregunta.
```

La prueba es directa: **quita el identificador y lee lo que queda**. Si sigue explicando
el porqué, el identificador sobraba. Si se queda sin decir nada, ese comentario nunca se
ganó su sitio — se borra, no se reescribe.

---

## Cuándo un comentario SÍ se gana su sitio

Ejemplos reales del repositorio. Los dos explican una decisión que no está en ningún
nombre y que la siguiente persona desharía sin querer.

```php
// database/seeders/SuperAdminSeeder.php
'password' => self::PASSWORD,
// Hashed by the model's `password` cast, not here.
```

Sin ese comentario, alguien «arregla» la línea envolviéndola en `Hash::make()` y la
contraseña queda hasheada dos veces.

```php
// src/Domain/Identity/Models/User.php
/**
 * `active` is the account kill switch: a suspended user is turned away
 * while its roles stay intact for the day it comes back. The role carries
 * the authority. Keeping the two apart is deliberate — an `is_admin`
 * column would duplicate what the role already states, leaving two
 * sources of truth for the same question.
 */
public function canAccessPanel(Panel $panel): bool
```

El método se lee solo; lo que no se deduce es **por qué** son dos preguntas separadas y
no una columna. Eso es lo único que el comentario dice.

Un tercer patrón, el más fácil de perder: un método vacío sin comentario parece un olvido
y alguien lo «arregla».

```php
/**
 * Deliberate no-op: la retirada es definitiva, no un estado reversible.
 */
public function down(): void {}
```

---

## Excepciones

| Caso | Qué aplica |
|---|---|
| Ficheros de `config/` | Los comentarios descriptivos **sí se esperan**. Un array de configuración no tiene nombres de variable donde apoyarse, y el lector suele ser quien administra, no quien programó. Es la excepción que declara la skill de boost en `rules/style.md`, y la respetamos |
| PHPDoc exigido por PHPStan (`@param array<string, mixed>`, genéricos, formas de array) | Es tipado, no comentario. Se queda |
| PHPDoc de intención en una clase o método público | Admitido si explica el porqué o el contrato. Un `@param` que solo repite la firma, no |
| Tests | Sin excepción: nunca |

Ejemplo de la primera fila, en `config/filament-shield.php`:

```php
| Here you may define a super admin that has unrestricted access to your
| application. You can choose to implement this via Laravel's gate system
| or as a traditional role with all permissions explicitly assigned.
```

---

## Al editar código que ya existe

La regla gobierna el **código nuevo**. No es una licencia para reformar comentarios
ajenos al cambio que se está haciendo, ni para meter ruido en un PR que iba de otra cosa.
El scaffold que trae Laravel de fábrica —`// use Illuminate\Contracts\Auth\MustVerifyEmail;`
en `User.php`, los `//` de `tests/TestCase.php`— entra en esa categoría.

Ahora bien, si el cambio ya toca esa línea, el momento de borrar un comentario de «qué»
es ese, no el de reformularlo.

---

## Lo que NO se hace

- **No** se comenta lo que el código ya dice.
- **No** se deja código comentado: para eso está el historial de git.
- **No** se comenta un test. Si el test necesita explicación, el nombre está mal.
- **No** se escribe una clave de ticket, un `ADR-n`, una `decision X` ni un id de hallazgo
  de revisión dentro de un comentario.
- **No** se «actualiza» un comentario de «qué» al editar el fichero: se borra.
- **No** se aplica la excepción de `config/` fuera de `config/`.

---

## Al delegar escritura de código

Reenviar este fichero, literal, a todo sub-agente de implementación. Un agente que no lo
recibe narra lo obvio en comentarios por defecto, y la skill de boost que sí recibe solo
cubre la regla 1.
