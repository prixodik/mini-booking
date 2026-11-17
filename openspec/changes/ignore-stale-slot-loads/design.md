# Design

## Context

```js
async function load({ silent = false } = {}) {
  if (!silent) loading.value = true
  loadError.value = null
  try { const response = await getSlots(); slots.value = response.data; ... }
  catch (error) { loadError.value = error.message }
  finally { loading.value = false }
}
```

Параллельные вызовы: `run()` после действия вызывает `load({ silent: true })`; кнопка «Повторить» у ошибки загрузки вызывает
`load()`; таймаут брони (change `add-request-timeout`) тоже вызывает тихую загрузку.

## Decisions

Счётчик запросов в замыкании composable:

```js
let loadSeq = 0 // номер последней запущенной загрузки; применяется только её результат

async function load({ silent = false } = {}) {
  const seq = ++loadSeq
  if (!silent) loading.value = true
  loadError.value = null
  try {
    const response = await getSlots()
    if (seq !== loadSeq) return false // пришёл ответ на устаревший запрос
    slots.value = response.data
    // ... как сейчас
    return true
  } catch (error) {
    if (seq === loadSeq) loadError.value = error.message
    return false
  } finally {
    if (seq === loadSeq) loading.value = false
  }
}
```

- `let`, а не `ref`: номер не показывается в интерфейсе, реактивность ему не нужна.
- Индикатор `loading` снимает только последний запрос: если пользователь нажал «Повторить» (не тихая загрузка) во время тихой,
  спиннер не пропадёт раньше времени.

Рассмотрено: `AbortController` — прерывать устаревший `fetch`. Корректно и экономит трафик, но требует пробрасывать `signal`
через `bookingApi` и отличать отмену от сетевой ошибки; счётчик решает задачу целиком в одном файле.

## Risks / Trade-offs

Нет заметных: устаревшие запросы всё равно доходят до сервера (GET без побочных эффектов).

## Migration Plan

Только frontend; откат — revert коммита.
