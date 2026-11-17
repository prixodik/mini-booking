# Design

## Context

`redesign-booking-ui` (design.md, «Фильтр дат») заменил `SelectButton` самописным блоком `date-filter` на
`<button role="radio">` ради сетки и высоты 44 px, а статус слота сделал собственным `<span class="slot-card__status">`.
Alex решил вернуть готовые компоненты (ТЗ п. 4: «соберите страницу из готовых элементов»). Этот change отменяет то решение
и описывает, как уложить `SelectButton` в телефон без переопределения `.p-*` классов.

Проверено по исходникам PrimeVue 4.5.5 и `@primeuix/themes` 2.0.3 в `node_modules`:

- `SelectButton` рендерит `div role="group"` с `ToggleButton` внутри; у каждой кнопки `aria-pressed`. Стили Aura:
  `.p-selectbutton { display: inline-flex }`, кнопки склеены (`border-radius: 0`, скругление только у первой и последней),
  переноса строк нет. Свойство `fluid` растягивает группу на ширину, кнопки делят её поровну (`flex: 1 1 0`).
- `allowEmpty` по умолчанию `true`: повторный клик по выбранной кнопке сбрасывает значение в `null`.
- Токены `togglebutton`: `root.padding 0.25rem`, `content.padding 0.25rem 0.75rem`, `root.color {surface.500}` на
  `root.background {surface.100}`, `content.checkedBackground {surface.0}`.
- Токены `tag` (light): `success` green.100/green.700, `warn` orange.100/orange.700, `secondary` surface.100/surface.600; свойство `icon`.

## Goals / Non-Goals

**Goals:** готовые компоненты вместо самописных; до 7 дат одной строкой на 360 px без горизонтальной прокрутки;
контраст не ниже 4.5:1; никаких правил на `.p-*`; минимум кода.

**Non-Goals:** навигация стрелками внутри фильтра (у `SelectButton` её нет: каждая дата — отдельная кнопка в порядке Tab,
этого достаточно для доступности кнопочной группы); смена вида карточек слотов; правки API.

## Decisions

### 1. Фильтр: `SelectButton` с `fluid`, две строки в кнопке

```vue
<SelectButton
  v-model="model" :options="options" option-label="label" option-value="value"
  :allow-empty="false" :disabled="disabled" fluid
  aria-labelledby="date-filter-label" class="date-filter"
>
  <template #option="{ option }">
    <span class="date-filter__weekday">{{ option.weekday }}</span>
    <span class="date-filter__day">{{ option.day }}</span>
  </template>
</SelectButton>
```

- Ширина на 360 px: контейнер 360 − 2×16 (поля `layout__content`) − 2×16 (поля `page__panel`) = 296 px; на кнопку 296 / 7 ≈ 42 px.
  При стандартном `content.padding 0.25rem 0.75rem` минимум кнопки ≈ 8 + 24 + 16 (текст «17») = 48 px — не помещается.
  Поэтому токен `togglebutton.content.padding` уменьшается до `0.25rem 0.375rem` (≈ 8 + 12 + 16 = 36 px). Итоговую ширину измерить в браузере.
- Высота: две строки (0.75rem и 1rem) + отступы дают ≈ 48–52 px ≥ 44 px; измерить в браузере.
- Метка группы — визуально скрытый `<span id="date-filter-label">Дата</span>` внутри блока `date-filter` (класс `date-filter__label`),
  или видимая подпись «Дата» над фильтром; выбрать при реализации, без глобальных утилитарных классов.
- `allow-empty="false"` — повторный клик не сбрасывает дату (сценарий «Повторный выбор той же даты»).
- Почему не перенос строк: склеенные кнопки Aura при переносе выглядят обрезанными (скругление только у первой и последней),
  исправить это можно только правилами на `.p-togglebutton`, а это запрещено `ui-styling`.
- Почему не `Select` (выпадающий список): все даты перестают быть видны сразу, на телефоне нужен лишний тап.

### 2. Только сегодня и будущие даты

Обеспечивается backend-ом: change `slots-from-today` (ревью Ж-2) ограничивает `GET /api/slots` слотами с 00:00 сегодняшнего дня
в поясе расписания. `useBookingSlots` строит `dates` из ответа как сейчас, отдельная фильтрация на клиенте не нужна, composable
не меняется. Seeder всегда создаёт расписание на 7 дней от дня запуска, поэтому дат от сегодня не больше 7 — они всегда помещаются
в строку. Слоты сегодняшнего дня, которые уже начались, остаются в списке со статусом «Прошёл» (ТЗ п. 2).

Порядок: `slots-from-today` реализуется до этого change. Если его не одобрят, вернуться к фильтру на клиенте
(`dateKey(starts_at) >= dateKey(meta.server_time, meta.timezone)`) — сначала правка этого раздела.

### 3. Полная дата над списком

`pages/index.vue`: `<h2 class="page__date">` с выбранной датой, например «Вторник, 17 ноября» (`Intl.DateTimeFormat('ru-RU',
{ weekday: 'long', day: 'numeric', month: 'long', timeZone: 'UTC' })` по ключу `YYYY-MM-DD`, как `formatDateLabel`). Новая функция
`formatDateLong` в `utils/datetime.js`. Нужна, потому что на кнопках фильтра нет месяца.

### 4. Статус: `Tag`

```vue
<Tag class="slot-card__status" :value="status.label" :icon="`pi ${status.icon}`" :severity="status.severity" />
```

`STATUS`: `available → success, pi-check-circle`, `booked → warn, pi-lock`, `past → secondary, pi-history`. Класс
`slot-card__status` остаётся только для положения в шапке карточки; модификаторы `slot-card__status--*` и их стили удаляются.

### 5. Цвета токенами пресета

| Пара | Значение | Контраст |
|---|---|---|
| Невыбранная дата: `togglebutton.root.color` на `root.background` | **`{surface.600}`** (было `surface.500`, 4.34 — ниже AA) на `{surface.100}` | 6.92 |
| Выбранная дата: `root.checkedColor` на `content.checkedBackground` | `{primary.contrastColor}` (#fff) на `{primary.color}` (violet.700) | 7.10 |
| «Свободен»: `tag.success.color` на `background` | `{green.800}` на `{green.100}` (рекомендуется; Aura: green.700 — 4.57) | 6.49 |
| «Занят»: `tag.warn.color` на `background` | `{orange.800}` на `{orange.100}` (рекомендуется; Aura: orange.700 — 4.52) | 6.38 |
| «Прошёл»: `tag.secondary` | `{surface.600}` на `{surface.100}` (значение Aura, не меняется) | 6.92 |

Для выбранной даты также `root.checkedBackground`/`checkedBorderColor` = `{surface.100}` (фон группы не меняется) и
`content.checkedBackground` = `{primary.color}`; иконок в кнопках нет. Расчёт — формула WCAG 2.1 по hex-значениям палитры Aura
(slate для `surface`), тот же скрипт, что в `redesign-booking-ui`.

`tokens.css`: удаляются `--mb-color-available-*`, `--mb-color-booked-*`, `--mb-color-past-*` (больше нигде не используются,
проверить поиском). `--mb-color-accent-soft` удаляется, если после замены фильтра не используется.

## Risks / Trade-offs

- Кнопки фильтра узкие (≈ 42 px) — ниже рекомендованных 44 px по ширине, но выше по высоте; WCAG 2.5.8 (AA) требует 24 px, условие выполнено.
- Если когда-нибудь дат от сегодня станет больше 7 (например, `days_ahead` увеличат в `config/booking.php`), кнопки сожмутся;
  при 10+ датах — горизонтальная прокрутка страницы. Тогда нужен перенос или другой компонент; в README это отмечается.
- Токен `togglebutton` влияет на все `ToggleButton`/`SelectButton` страницы; других нет.

## Migration Plan

Только frontend; откат — revert коммита. Данные и API не меняются.

Дельты здесь — `MODIFIED` для `booking-ui` и `ui-styling`, которых ещё нет в `openspec/specs/`. `validate --strict` проходит,
но архивировать этот change можно только после архивации `add-booking-frontend` и `redesign-booking-ui` (иначе OpenSpec откажет).

## Open Questions

См. `proposal.md`: три пункта ждут подтверждения Alex; рекомендации даны в таблице и разделах 2 и 5.
