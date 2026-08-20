# Guía de contribución

Este documento define el flujo de trabajo del repositorio: cómo crear ramas,
cómo escribir commits y cómo abrir un Pull Request. El objetivo es que `main`
esté siempre instalable y que cada cambio sea trazable leyendo el historial.

Es un **starter kit**: el código que entra aquí lo va a heredar todo proyecto
generado desde él. Eso descarta lo específico de un cliente, de un dominio de
negocio concreto o del entorno de una máquina.

---

## 1. Flujo general

1. `main` está siempre instalable. **Nadie pushea directo a `main`.**
2. Todo trabajo sale de una rama propia creada desde `main` actualizada.
3. Se integra mediante un Pull Request hacia `main`.
4. El CI debe estar en verde para poder mergear.
5. El merge se hace con **merge commit**, no con squash.

Sobre el punto 5: los commits de una rama cuentan cosas distintas —el
comportamiento, sus tests, su documentación— y merece la pena conservarlos
separados. Un squash los funde en un mensaje que ya no explica ninguno.

### Aprobación

| Tipo de cambio | Qué hace falta |
|----------------|----------------|
| Toca comportamiento, datos o autorización (`feat`, `fix`, `refactor`, migraciones) | Revisión antes de mergear |
| Trivial y autoexplicativo (`docs`, `style`, `chore` de formato) | Self-merge con el CI en verde |

---

## 2. Ramas

```
<tipo>/<descripcion-corta-en-kebab-case>
```

```
feat/impersonation-and-password-reset
fix/panel-access-control
chore/update-dependencies
docs/release-documentation
```

El tipo es uno de los de la tabla de la sección 3.4. Sin claves de ticket: este
repositorio no las usa, y un comentario o una rama que referencia un
identificador externo deja de entenderse en cuanto ese identificador se archiva.

Antes de empezar, actualiza `main`:

```bash
git checkout main
git pull origin main
git checkout -b feat/mi-cambio
```

---

## 3. Commits

Se usa [Conventional Commits](https://www.conventionalcommits.org/).

### 3.1. Encabezado

```
<tipo>(<alcance>): <descripción corta en imperativo>
```

El alcance es opcional y nombra la parte afectada (`identity`, `boost`,
`dependabot`). La descripción va en imperativo y no pasa de 72 caracteres.

```
feat(identity): enable password reset on the admin panel
```

### 3.2. Cuerpo

Explica **qué problema resuelve y por qué así**, no qué líneas cambiaron: eso
ya se ve en el diff. Sin nombres de clase ni de método salvo que sean el punto.

Cada línea se envuelve a **máximo 72 caracteres**. Un párrafo en una sola línea
larga es ilegible en `git log` y en cualquier terminal estrecha.

```
An administrator who forgot their password had no way back in: the panel
registered `->login()` and nothing else, so recovery meant someone with
database access resetting it by hand.
```

### 3.3. Idioma

**Los artefactos técnicos van en inglés**: mensajes de commit, cuerpos de PR,
nombres de clase, de método y de test, y comentarios en el código.

**La copia de interfaz va en español**: etiquetas, textos de ayuda y mensajes
que ve quien usa el panel. Este starter está orientado a proyectos en español
—`APP_LOCALE=es`—, y esa es la única parte donde el idioma del usuario manda.

La documentación del repositorio, incluido este fichero, va en español.

### 3.4. Tipos

| Tipo | Para qué |
|------|----------|
| `feat` | Nueva funcionalidad |
| `fix` | Corrección de un defecto |
| `docs` | Documentación |
| `style` | Formato, sin cambio de comportamiento |
| `refactor` | Reestructuración, sin cambio de comportamiento |
| `perf` | Rendimiento |
| `test` | Añadir o cambiar pruebas |
| `build` | Sistema de build o dependencias de front |
| `ci` | Integración continua |
| `chore` | Configuración y tareas menores |

No se añaden líneas de coautoría de herramientas ni de asistentes al mensaje.

---

## 4. Pull Request

1. Sube la rama y abre el PR hacia `main`.
2. Completa la plantilla (`.github/pull_request_template.md`).
3. El título sigue Conventional Commits, igual que un commit.
4. **Un PR por funcionalidad.** Un PR gigante recibe una revisión superficial.
   El tamaño se controla acotando el alcance del cambio, no partiendo un cambio
   coherente para llegar a un número de líneas.
5. Espera el CI en verde.
6. Merge con merge commit.

---

## 5. Calidad

Antes de pedir revisión, ejecuta en local lo mismo que valida el CI.

| Acción | Comando | Qué hace |
|--------|---------|----------|
| Corregir estilo | `composer lint` | Rector + Pint, **modifican ficheros** |
| Verificar estilo | `composer test:lint` | Rector en dry-run + Pint en modo test |
| Análisis estático | `composer analyse` | PHPStan vía Larastan |
| Tests | `composer test:unit` | Pest en paralelo |
| Todo junto | `composer test` | Limpia config, tests y verificación de estilo |

La diferencia que importa: `composer lint` **corrige**, `composer test:lint`
solo **verifica**. El CI ejecuta la versión que verifica, así que corrige en
local antes de subir.

### 5.1. Orden antes de commitear

```
composer lint  →  composer analyse  →  composer test
```

`lint` va **primero** porque Rector y Pint modifican ficheros. Si commiteas
antes de ejecutarlo, sus correcciones caen en un commit posterior y ensucian el
historial y el diff del PR.

Si alguno de los tres falla, para y arréglalo. No se commitea en rojo.

El CI (`.github/workflows/ci.yml`) ejecuta dos jobs en orden: primero
`composer test:lint` y `composer analyse`, y solo si pasan, `composer test:unit`.

---

## 6. Convenciones de código

No están en este fichero. Viven en `.claude/rules/`, y aplican a cualquiera que
escriba código aquí, con agente o sin él:

| Fichero | Qué cubre |
|---------|-----------|
| `ddd.md` | Estructura de `src/Domain/`, responsabilidad de cada capa, cómo crear un dominio nuevo |
| `filament.md` | Patrones de Resources, dónde va cada cosa, namespaces de Filament 5 |
| `testing.md` | Regla de espejo 1:1 entre `src/Domain/` y `tests/Unit/Domain/`, cuándo Unit y cuándo Feature |
| `comments.md` | Cuándo un comentario se gana su sitio; nunca en tests |

Leerlas antes de escribir ahorra una ronda de revisión.

---

## 7. Checklist antes de abrir un PR

- [ ] La rama salió de `main` actualizada.
- [ ] `composer lint`, `composer analyse` y `composer test` en verde, en ese orden.
- [ ] Hay tests para lo que cambié, y viven donde manda `testing.md`.
- [ ] El título del PR sigue Conventional Commits.
- [ ] El diff es acotado y revisable de una sentada.
- [ ] Nada de lo que añadí es específico de un cliente, un negocio o mi máquina.
