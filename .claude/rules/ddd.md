# DDD — Arquitectura de Dominio

> Basada en **Laravel Beyond CRUD** de Brent Roose (Spatie).
> Referencia canónica para decisiones arquitectónicas en este proyecto.

## Mapa de carpetas reales

```
src/Domain/
└── Identity/                        ← único dominio actual
    ├── Actions/
    │   ├── CreateUserAction.php
    │   ├── UpdateUserAction.php
    │   └── DeleteUserAction.php
    ├── Collections/                  ← Eloquent Collections tipadas (vacío)
    ├── DataTransferObjects/
    │   └── UserData.php
    ├── Events/                       ← (vacío, por usar)
    ├── Exceptions/                   ← (vacío, por usar)
    ├── Listeners/                    ← (vacío, por usar)
    ├── Models/
    │   └── User.php
    ├── QueryBuilders/                ← (vacío, por usar)
    ├── Rules/                        ← Validation rules de dominio (vacío)
    └── States/                       ← Máquinas de estado (vacío)
```

Namespace raíz: `Domain\` → mapeado desde `src/Domain/` en `composer.json`.

---

## Responsabilidad de cada capa

### Models (`Models/`)
Eloquent puro: relaciones, casts, scopes, factory. Sin lógica de negocio.

```php
// src/Domain/Identity/Models/User.php
final class User extends Authenticatable implements FilamentUser
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = ['name', 'email', 'password'];

    // Solo casts y relaciones aquí. Nada de lógica de negocio.
    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }
}
```

### DataTransferObjects (`DataTransferObjects/`)
Clases `final readonly`. Transportan datos validados entre capas. Siempre tienen `fromArray()`.

```php
// src/Domain/Identity/DataTransferObjects/UserData.php
final readonly class UserData
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $password = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: isset($data['password']) && filled($data['password']) ? $data['password'] : null,
        );
    }
}
```

### Actions (`Actions/`)
Una responsabilidad. Método público `execute()`. Sin estado interno. Se resuelven desde el contenedor con `resolve()`.

```php
// src/Domain/Identity/Actions/CreateUserAction.php
final class CreateUserAction
{
    public function execute(UserData $data): User
    {
        return User::query()->create([
            'name'     => $data->name,
            'email'    => $data->email,
            'password' => $data->password,
        ]);
    }
}
```

### QueryBuilders (`QueryBuilders/`)
Extienden `Illuminate\Database\Eloquent\Builder`. Encapsulan queries reutilizables para no ensuciar los modelos.

### Events / Listeners (`Events/`, `Listeners/`)
Eventos de dominio y sus escuchadores. Se registran en `AppServiceProvider`.

### States (`States/`)
Para modelos con máquinas de estado (paquete `spatie/laravel-model-states`).

### Rules (`Rules/`)
Reglas de validación específicas del dominio que no encajan en un Form Request genérico.

---

## Crear un nuevo dominio paso a paso

**1. Generar la estructura con el comando artisan:**
```bash
php artisan domain:make NombreDominio
```
Esto crea `src/Domain/NombreDominio/` con todos los subdirectorios.

**2. Crear el modelo:**
```bash
php artisan make:model --no-interaction NombreDominio/NombreModelo
# Mover manualmente de app/Models/ a src/Domain/NombreDominio/Models/
# Cambiar namespace a Domain\NombreDominio\Models\
```
> Nota: `make:model` no ubica en `src/Domain/` automáticamente. Muévelo y ajusta el namespace.

**3. Crear la migración:**
```bash
php artisan make:migration --no-interaction create_nombre_tabla_table
```

**4. Crear el DTO:**
```bash
php artisan make:class --no-interaction src/Domain/NombreDominio/DataTransferObjects/NombreData
```
Implementar como `final readonly` con `fromArray()`.

**5. Crear las Actions:**
```bash
php artisan make:class --no-interaction src/Domain/NombreDominio/Actions/CreateNombreAction
php artisan make:class --no-interaction src/Domain/NombreDominio/Actions/UpdateNombreAction
php artisan make:class --no-interaction src/Domain/NombreDominio/Actions/DeleteNombreAction
```

**6. Crear el Resource de Filament:**
```bash
php artisan make:filament-resource --no-interaction NombreModelo
# Reorganizar en app/Filament/Resources/NombrePlural/
```

**7. Generar permisos y Policy con Filament Shield:**
```bash
php artisan shield:generate --resource=NombreResource --panel=admin --no-interaction
php artisan shield:seeder --force --no-interaction
```
Shield crea automáticamente la Policy en `app/Policies/` y registra todos los permisos Spatie.
**No usar `make:policy` manualmente** — Shield es la fuente de verdad para policies de recursos Filament.

El segundo comando **no es opcional**. `shield:generate` solo escribe en la base de datos que
tengas delante; `shield:seeder` regenera `database/seeders/ShieldSeeder.php` para que los permisos
viajen en el repositorio. Olvidarlo deja el resource funcionando en tu máquina y roto en las demás.

Va **sin `--option`**: con `--option=permissions_via_roles` el bloque de permisos directos se salta
entero, y como los roles de este starter no llevan permisos propios, el snapshot saldría vacío.
Nunca `--with-users`: mete usuarios reales en un fichero versionado.

`ShieldSeeder.php` es un volcado regenerable, no un fichero que se edite a mano. Está excluido de
Rector (`rector.php`, por path) y de Pint (`pint.json`, `notPath`) para que siga siendo fiel al stub
del vendor; si lo normalizas, cada regeneración vuelve como un diff contra tus propias reglas.

**7b. Conectar la Policy al modelo — OBLIGATORIO:**
```php
use App\Policies\NombreModeloPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;

#[UsePolicy(NombreModeloPolicy::class)]
final class NombreModelo extends Model
```
El autodescubrimiento de policies de Laravel asume `App\Models\X` → `App\Policies\XPolicy`. Como
nuestros modelos viven en `Domain\{Dominio}\Models\`, **la resolución falla en silencio** y
`Gate::getPolicyFor()` devuelve `null`.

Y esto falla **abierto**, no cerrado: cuando Filament no encuentra policy para un modelo, permite la
acción. Sin este atributo, cada permiso de Shield que escribas en la Policy es código muerto y
cualquier usuario con acceso al panel tiene CRUD completo sobre el recurso.

Shield solo auto-registra su propio modelo `Role`, nunca los tuyos. Verifícalo siempre:
```bash
php artisan tinker --execute 'var_dump(get_class(Illuminate\Support\Facades\Gate::getPolicyFor(Domain\NombreDominio\Models\NombreModelo::class)));'
```
Y déjalo cubierto con un test, como `tests/Unit/Domain/Identity/Models/UserTest.php`.

**8. Crear tests:**
```bash
php artisan make:test --pest --no-interaction NombreDominio/CreateNombreTest
php artisan make:test --pest --no-interaction NombreDominio/UpdateNombreTest
```

---

## Reglas de oro

- **Nunca** poner lógica de negocio en un Model. Solo Eloquent.
- **Nunca** usar `new CreateUserAction()`. Siempre `resolve(CreateUserAction::class)`.
- **Nunca** acceder a `$request` dentro de una Action. La Action recibe un DTO.
- El DTO es la frontera: valida y normaliza datos de entrada antes de pasarlos a la Action.
- Las Actions son `final`. No se extienden, se componen.
- Todo modelo de dominio con Policy lleva `#[UsePolicy(...)]`. Sin él la autorización falla abierto.
- El acceso al panel se decide con el rol, nunca con una columna `is_admin`: el rol ya responde
  "qué puede hacer este usuario", y duplicarlo crea dos fuentes de verdad. La columna `active` es
  otra pregunta distinta —"¿sigue viva esta cuenta?"— y por eso sí es una columna.
