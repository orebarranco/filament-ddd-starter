# Testing — Convenciones

> Complementa `.claude/rules/ddd.md`. Define dónde vive cada test y cuándo usar Unit vs Feature.

## Regla de espejo 1:1 (obligatoria)

Todo test unitario de una clase de dominio debe vivir en la misma ruta relativa que
la clase que testea, cambiando `src/Domain/` por `tests/Unit/Domain/` y añadiendo
el sufijo `Test`:

```
src/Domain/{Dominio}/{Capa}/{Nombre}.php
tests/Unit/Domain/{Dominio}/{Capa}/{Nombre}Test.php
```

Ejemplo real de este proyecto:

```
src/Domain/Identity/Actions/CreateUserAction.php
tests/Unit/Domain/Identity/Actions/CreateUserActionTest.php

src/Domain/Identity/Actions/UpdateUserAction.php
tests/Unit/Domain/Identity/Actions/UpdateUserActionTest.php

src/Domain/Identity/Actions/DeleteUserAction.php
tests/Unit/Domain/Identity/Actions/DeleteUserActionTest.php
```

No se permite agrupar tests de distintas capas en una sola carpeta, ni testear
una Action desde `tests/Feature/`.

---

## Cuándo usar Unit vs Feature

### Unit (`tests/Unit/`)
Testea **una clase aislada**: una Action, un DTO, un método de un Model. No pasa
por HTTP, no monta Livewire, y solo toca la base de datos si la clase bajo test
la necesita directamente (ej. una Action que hace `Model::query()->create()`).

```php
// tests/Unit/Domain/Identity/Actions/CreateUserActionTest.php
it('creates a user from a UserData DTO', function () {
    $data = UserData::fromArray([...]);

    $user = resolve(CreateUserAction::class)->execute($data);

    expect($user)->toBeInstanceOf(User::class);
});
```

### Feature (`tests/Feature/`)
Testea **comportamiento end-to-end** que cruza Livewire, HTTP o rutas: páginas de
Filament, comandos Artisan, controladores. Aquí sí se permite montar componentes
Livewire completos y hacer aserciones sobre la respuesta HTTP o el estado de la UI.

```
tests/Feature/Filament/Resources/Users/CreateUserTest.php
tests/Feature/Filament/Resources/Users/EditUserTest.php
tests/Feature/Filament/Resources/Users/ListUsersTest.php
tests/Feature/Console/Domain/MakeDomainCommandTest.php
```

Regla práctica: si el test necesita `livewire()`, `actingAs()` + una ruta, o
`Artisan::call()`, es Feature. Si solo necesita `resolve()` o `new`, es Unit.

---

## Convención de carpetas para Filament

Hoy este starter tiene un único panel (`admin`), así que los tests de recursos
Filament viven directamente bajo:

```
tests/Feature/Filament/Resources/{Recurso}/
```

Si en el futuro este starter crece a más de un panel, agregar un nivel adicional
con el nombre del panel **antes** de `Resources`, y nunca mezclar recursos de
paneles distintos en la misma carpeta:

```
tests/Feature/Filament/{Panel}/Resources/{Recurso}/
```

---

## Generar tests

El comando estándar (ya usado en `.claude/rules/ddd.md`, paso 8) es:

```bash
php artisan make:test --pest --no-interaction NombreDominio/NombreTest
```

`make:test` no siempre ubica el archivo en la ruta que respeta la regla de
espejo 1:1. Si el archivo generado cae en `tests/Feature/` por defecto, muévelo
manualmente a `tests/Unit/Domain/{Dominio}/{Capa}/` y ajusta el namespace.

---

## Reglas de oro

- **Nunca** romper la regla de espejo 1:1 entre `src/Domain/` y `tests/Unit/Domain/`.
- **Nunca** testear una Action, DTO o método de Model aislado desde `tests/Feature/`.
- Un test de Filament Resource siempre vive bajo `tests/Feature/Filament/Resources/{Recurso}/`
  (o `tests/Feature/Filament/{Panel}/Resources/{Recurso}/` si hay más de un panel).
- Si `make:test` no genera el archivo en la ruta correcta, muévelo — no dejes el
  test en el lugar por defecto solo porque ya está ahí.
