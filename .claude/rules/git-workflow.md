# Git — precedencia entre el agente y el contrato del repositorio

> Este fichero es **solo para agentes**. Todo lo que necesita quien contribuye
> —ramas, commits, PR y el orden de los quality gates— vive en
> `CONTRIBUTING.md`, que no depende de ninguna herramienta.

## Precedencia

`CONTRIBUTING.md` define el formato del repositorio y **manda siempre**. Es el
contrato: lo cumple quien trabaja con un agente y quien no.

Si además hay una capa de orquestación, esa capa gobierna el *proceso* —fases,
pausas, revisión, estrategia de entrega—. No gobierna el formato.

- Conflicto de **proceso** → gana la capa de orquestación.
- Conflicto de **formato** → gana `CONTRIBUTING.md`.
- Sin capa de orquestación → `CONTRIBUTING.md` basta por sí solo.

## Una skill de otro repositorio no aplica aquí

Es el error fácil, y ya se ha cometido en este repositorio. Una skill de flujo
de PR escrita para otro proyecto trae sus propios requisitos —issue aprobada
con una etiqueta concreta, etiquetas `type:*`, una plantilla de PR distinta,
`shellcheck` sobre scripts que aquí no existen— y ninguno es real aquí.

Antes de aplicar una skill de flujo, comprobar si el repositorio tiene esas
piezas. Si no las tiene, gana la convención del repositorio: lo que se deduce
de `CONTRIBUTING.md` y de los PR ya mergeados.

Lo único transferible de una skill así suele ser el naming de ramas y el
formato de commits, y aquí los define `CONTRIBUTING.md`.

## Merge, no squash

Este repositorio integra con **merge commit**. Los commits de una rama cuentan
cosas distintas y se conservan separados. No proponer squash como si fuese el
default.

## Al delegar escritura de código

Reenviar `CONTRIBUTING.md` y `.claude/rules/comments.md` literales al
sub-agente, no un resumen. Un agente de implementación no valida por su cuenta
el largo del encabezado del commit ni el ajuste del cuerpo a 72 columnas.
Verificar ambos antes de dar el commit por bueno.
