# Filament — Patrones de este proyecto

## Estructura de un Resource

Cada Resource vive en su propio directorio bajo `app/Filament/Resources/{Nombre}/`:

```
app/Filament/Resources/Users/
├── UserResource.php          ← Registro central (modelo, nav, pages)
├── Pages/
│   ├── ListUsers.php         ← Override de handleRecord* para delegar a Actions
│   ├── CreateUser.php
│   └── EditUser.php
├── Schemas/
│   └── UserForm.php          ← Definición del formulario (campos)
└── Tables/
    └── UsersTable.php        ← Definición de la tabla (columnas, filtros, acciones)
```

---

## Cómo los Resources delegan a las Domain Actions

### Creación — `CreateUser.php`
```php
final class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // 1. Construir DTO desde los datos del formulario
        // 2. Resolver la Action desde el contenedor (NUNCA new)
        // 3. Ejecutar
        return resolve(CreateUserAction::class)->execute(UserData::fromArray($data));
    }
}
```

### Edición — `EditUser.php`
```php
final class EditUser extends EditRecord
{
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var User $record */
        return resolve(UpdateUserAction::class)->execute($record, UserData::fromArray($data));
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->action(fn (User $record) => resolve(DeleteUserAction::class)->execute($record)),
        ];
    }
}
```

### Eliminación inline en tabla — `UsersTable.php`
```php
DeleteAction::make()
    ->using(fn (User $record, DeleteUserAction $deleteUserAction): bool =>
        $deleteUserAction->execute($record)
    ),
// Filament inyecta DeleteUserAction automáticamente desde el contenedor
```

---

## Dónde va cada cosa

| Qué | Dónde |
|-----|-------|
| Campos del formulario, labels, validación UI | `Schemas/UserForm.php` |
| Columnas, filtros, ordenamiento, acciones de tabla | `Tables/UsersTable.php` |
| Navegación, modelo, páginas registradas | `UserResource.php` |
| Lógica de creación / actualización / eliminación | Domain Actions |
| Transformación de datos de formulario a DTO | `UserData::fromArray($data)` en la Page |
| Autorización | `app/Policies/UserPolicy.php` (Spatie Permission) |

---

## Patrones observados en el código existente

### Form separado en clase propia
```php
// UserResource.php
public static function form(Schema $schema): Schema
{
    return UserForm::configure($schema);  // Delega a clase dedicada
}
```
No se define el formulario inline en el Resource. Siempre en `Schemas/`.

### Table separada en clase propia
```php
public static function table(Table $table): Table
{
    return UsersTable::configure($table);  // Delega a clase dedicada
}
```

### Password condicional en formulario
```php
TextInput::make('password')
    ->required(fn (string $operation): bool => $operation === 'create')
    ->dehydrated(fn (?string $state): bool => filled($state))
```
El campo es requerido solo en creación y no se envía si está vacío en edición.

### Roles con Select múltiple
```php
Select::make('roles')
    ->multiple()
    ->relationship(titleAttribute: 'name')
    ->preload()
    ->searchable(),
```
Filament gestiona la relación many-to-many directamente.

### Iconos con el enum de Filament
```php
use Filament\Support\Icons\Heroicon;

protected static string|null|BackedEnum $navigationIcon = Heroicon::OutlinedUsers;
```
Siempre usar `Heroicon::` enum, nunca strings.

---

## Namespaces correctos en Filament 5

| Tipo | Namespace |
|------|-----------|
| Form fields (`TextInput`, `Select`…) | `Filament\Forms\Components\` |
| Infolist entries (`TextEntry`…) | `Filament\Infolists\Components\` |
| Layout (`Grid`, `Section`, `Tabs`…) | `Filament\Schemas\Components\` |
| Utilities (`Get`, `Set`) | `Filament\Schemas\Components\Utilities\` |
| Actions (todas) | `Filament\Actions\` |
| Columnas de tabla | `Filament\Tables\Columns\` |

---

## Autorización con Filament Shield

Las policies **no se crean a mano**. Shield las genera automáticamente:

```bash
# Al crear un nuevo Resource
php artisan shield:generate --resource=NombreResource --panel=admin --no-interaction

# Regenerar todo (si se añaden resources/pages/widgets)
php artisan shield:generate --all --panel=admin --ignore-existing-policies --no-interaction
```

Patrón generado (ejemplo real de `UserPolicy`):
```php
public function viewAny(AuthUser $authUser): bool
{
    return $authUser->can('ViewAny:User');  // Spatie Permission
}
```

Convención de nombres de permiso: `{Acción}:{Modelo}` — e.g. `ViewAny:User`, `Delete:User`.

El rol `super_admin` tiene acceso irrestricto (configurado en `config/filament-shield.php`).

---

## Lo que NO se hace

- **No** poner lógica de negocio en un Resource, Page, Form o Table.
- **No** llamar a `User::create()` directamente desde una Page. Siempre vía Action.
- **No** usar `new CreateUserAction()`. Siempre `resolve()` o inyección de dependencias.
- **No** definir columnas de tabla inline en `UserResource::table()`. Van en `Tables/UsersTable.php`.
- **No** usar `Filament\Tables\Actions\DeleteAction`. La clase correcta es `Filament\Actions\DeleteAction`.
