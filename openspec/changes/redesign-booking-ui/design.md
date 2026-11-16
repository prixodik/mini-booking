# Design

## Context

Фронтенд: Nuxt 3 (SPA), PrimeVue 4.5.5, `@primeuix/themes` 3.1.1 (Aura, `darkModeSelector: false`), страница `pages/index.vue`, компоненты
`components/booking/{SlotList,DateFilter,BookingDialog}.vue`, `layouts/default.vue`. Имена классов уже близки к БЭМ (`slot__time`, `form__field`,
`layout__header`), но стили лежат в `scoped`-блоках без общих токенов, цвета захардкожены, вид — «стандартный».

## Goals / Non-Goals

**Goals:** аккуратная светлая тема по визуальному ориентиру Sakai; БЭМ с проверяемыми правилами; общие токены; адаптивность 360 / 768 / 1280; минимум нового кода и зависимостей.

**Non-Goals:** копирование шаблона Sakai (layout с боковым меню, дашборд, конфигуратор тем), тёмная тема, переключатель тем, новые функции, смена логики и API, смена Nuxt-проекта и UI-библиотеки.

## Выбранное направление (решение Alex)

**Sakai как визуальный ориентир + тема PrimeVue Aura + БЭМ-вёрстка собственными компонентами.**

- Тема PrimeVue Aura (`@primeuix/themes`), только светлый режим, акцент задаётся через `definePreset`.
- Из Sakai берём оформление: светлый серо-голубой фон страницы, белые карточки с малым радиусом и мягкой тенью, белая шапка 4rem с круглыми иконками-действиями, отступы и типографику.
- Шаблон Sakai целиком не копируется, Nuxt-проект не заменяется. Код Sakai не переносится: берутся числовые значения (ниже), разметка и БЭМ-классы наши.
- Sakai-Vue: [github.com/primefaces/sakai-vue](https://github.com/primefaces/sakai-vue), лицензия MIT. Его стили лежат в git-подмодуле [primefaces/sakai-assets](https://github.com/primefaces/sakai-assets)
  (коммит `eaa70ec`, GitHub API не показывает файла лицензии у этого репозитория) — поэтому из него заимствуются только числовые значения, а не код.

### Что взято из исходного кода Sakai и Aura (проверено)

| Что | Значение | Источник |
|---|---|---|
| Фон страницы | `--surface-ground` = `--p-surface-100` (в светлой теме Aura = slate-100 `#F1F5F9`) | `sakai-assets/layout/variables/_light.scss`; палитра — `@primeuix/themes` 3.1.1 (`semantic.surface`) |
| Фон карточки и шапки | `--surface-card` = `--p-content-background` (= `surface.0` = `#FFFFFF`) | `variables/_common.scss`; Aura `semantic.content` |
| Радиус карточек | `--content-border-radius` = `--p-content-border-radius` = `border.radius.md` = 6px | `_common.scss`; Aura `semantic.content.borderRadius`, `primitive.borderRadius.md` |
| Карточка | padding 2rem, `border-radius` как у контента, отступ между карточками 2rem | `sakai-assets/layout/_utils.scss` (класс `.card`) |
| Шапка | fixed, высота 4rem, padding `0 2rem`, фон карточки, элементы по центру | `layout/_topbar.scss` |
| Логотип | шрифт 1.5rem, вес 500, зазор 0.5rem | `_topbar.scss` |
| Кнопки-иконки шапки | круг 2.5rem, иконка 1.25rem, hover — `--surface-hover` (`surface.100`), акцентная кнопка — `--primary-color` | `_topbar.scss`; Aura `content.hoverBackground` |
| Отступ контента от шапки | `padding: 6rem 2rem 0` (4rem шапка + 2rem) | `layout/_main.scss` |
| Базовая типографика | `html { font-size: 14px }`, `line-height: 1.2`, `font-family: 'Lato', sans-serif`; заголовки вес 700, `line-height: 1.5`, h1 2.5rem … h6 1rem; абзац `margin: 0 0 1rem`, `line-height: 1.5` | `layout/_core.scss`, `_typography.scss` |
| Тень выдвижной панели | `0 3px 5px rgba(0,0,0,.02), 0 0 2px rgba(0,0,0,.05), 0 1px 4px rgba(0,0,0,.08)` | `layout/_responsive.scss` |
| Тень оверлеев Aura | `0 4px 6px -1px rgba(0,0,0,.1), 0 2px 4px -2px rgba(0,0,0,.1)` | Aura `semantic.overlay.popover.shadow` |
| Границы контента | `surface.200` (slate-200 `#E2E8F0`) | Aura `semantic.content.borderColor` |
| Цвет текста / вторичный | `text.color` = `surface.700` (`#334155`), `mutedColor` = `surface.500` (`#64748B`) | Aura `semantic.text` |
| Радиус и поля ввода | `formField.borderRadius` = md (6px), border `surface.300` (`#CBD5E1`) | Aura `semantic.formField` |
| Основной цвет по умолчанию | `primary` → `emerald` (500 = `#10B981`), контрастный текст `#ffffff`, hover — `primary.600` | Aura `semantic.primary` |
| Брейкпоинты Sakai | 576 / 768 / 992 / 1200 / 1920 px | `sakai-assets/tailwind.css` |
| Тема Sakai | `Aura`, `@primeuix/themes ^2.0.0`, `darkModeSelector: '.app-dark'` | `sakai-vue/src/main.js`, `package.json` |

Все значения получены чтением исходных файлов (`raw.githubusercontent.com`) и импортом установленного Aura; значения палитры slate подтверждены самим пакетом.

### Где мы сознательно отходим от Sakai/Aura (причины)

- **Контраст.** По расчёту ниже у значений по умолчанию есть провалы AA: белый текст на `emerald.500` — 2.54:1, `muted` (`#64748B`) на фоне страницы `#F1F5F9` — 4.34:1,
  граница поля `#CBD5E1` на белом — 1.48:1 (нужно 3:1). Поэтому: акцент задаётся свой (по умолчанию `#2F5BEA`), вторичный текст темнее (slate-600), граница полей — slate-500.
- **Размер шрифта.** Sakai использует `html { font-size: 14px }`. Мы оставляем 16px: поля ввода меньше 16px на iOS вызывают принудительное увеличение страницы; 16px читаемее на телефоне.
- **Шрифт.** Sakai использует Lato (как он подключается к демо — не проверено). Мы берём системный стек без внешних ресурсов; Lato — только по согласованию с Alex.
- **Шапка и отступы на телефоне.** Sakai везде использует `padding: 0 2rem`; на 360 px это съедает 64 px ширины — берём 16 px, на ≥768 px — 24–32 px.
- **Шапка не фиксируется.** Контент одностраничный, `position: sticky`/fixed не нужны; не требуется отступ `6rem`.
- **Логотип Sakai** (SVG PrimeTek) не используется; в шапке наш заголовок и иконка `pi-calendar` (или логотип по решению Alex).
- **Боковое меню, конфигуратор тем, переключатель тёмного режима** не переносятся.

## Рассмотренные и отклонённые варианты

### Sakai-Vue целиком — отклонён

Проверено по репозиторию (GitHub API и `package.json`, 2026-11-16): MIT, 1134 звезды, не архивирован, последний push 2026-02-02; Vite + `vue-router`, Tailwind CSS 4 (`tailwindcss-primeui`), Sass; в `src/` — `layout/`, `views/`.
Админ-шаблон (боковое меню, дашборд). Не Nuxt (репозитория `primefaces/sakai-nuxt` нет — запрос вернул 404); Tailwind как вторая система стилизации противоречит БЭМ; для одной страницы избыточен. Совместимость с Nuxt 3 не проверялась.

### Nuxt UI — отклонён

[github.com/nuxt/ui](https://github.com/nuxt/ui): MIT, 7084 звезды; npm `@nuxt/ui` 4.12.2: Tailwind 4, зависит от `@nuxt/kit ^4`. Смена библиотеки перекрывает согласованный стек (PrimeVue 4), требует переписать диалог, `ConfirmDialog`, `Toast`, `SelectButton`;
Tailwind конфликтует с БЭМ-требованием. Совместимость с Nuxt 3 не проверена.

### Пресет Lara — отклонён

Документация PrimeVue ([primevue.dev/theming/styled](https://primevue.dev/theming/styled), на него перенаправляет primevue.org): доступны Aura, Material, Lara, Nora; Lara основан на Bootstrap, Nora вдохновлён enterprise-приложениями.
Исходная жалоба Alex — вид «как из Bootstrap», поэтому Lara не берём; Material/Nora не подходят по характеру (нет ориентира Sakai). Подтверждено также: `definePreset(base, tokens)` переопределяет токены, менять токены лучше, чем перекрывать компоненты классами.

## Decisions

### БЭМ и размещение стилей

- Формат `block__element--modifier`, kebab-case, глубина — один уровень элемента.
- Стили в `<style scoped>` внутри `.vue` (а не в отдельных файлах): так делает Vue/Nuxt по умолчанию, стиль лежит рядом с разметкой (проще объяснять и править за 10–15 минут на защите), изоляция исключает утечки.
- Классы на PrimeVue-компонентах попадают на их корневой элемент (наследование атрибутов Vue); для частей внутри компонента используется `pt`. Что именно попадает на корень у `Dialog` и `SelectButton` — не проверено, проверить при реализации.
- Глобально только `tokens.css` и `base.css` (`html`, `body`, `box-sizing`, `color-scheme: light`). Подключаются в `nuxt.config.js` → `css`.

Блоки (по компонентам):

```
layout            шапка и контейнер            layout__header, layout__title, layout__subtitle, layout__content
page              страница бронирования        page__filter, page__message, page__loading, page__empty, page__tz
date-filter       фильтр дат                   date-filter__item, date-filter__item--active
slot-list         список                       slot-list__item
slot-card         карточка слота               slot-card__time, slot-card__status, slot-card__action; --available | --booked | --past
booking-dialog    форма брони                  booking-dialog__slot, booking-dialog__form, booking-dialog__field, booking-dialog__error, booking-dialog__actions
```

(`SlotList.vue` делится на `SlotList` и `SlotCard`; окончательные имена блоков фиксируются в `tasks.md` при реализации.)

### Токены (`assets/css/tokens.css`, префикс `--mb-`)

```
--mb-color-bg            #F1F5F9   фон страницы        (Sakai/Aura: surface-100, slate-100)
--mb-color-surface       #FFFFFF   карточки, шапка, диалог (surface.0)
--mb-color-text          #334155   основной текст       (Aura text.color, slate-700)
--mb-color-text-muted    #475569   вспомогательный текст (slate-600; у Aura slate-500 не проходит AA на фоне страницы)
--mb-color-border        #E2E8F0   границы карточек     (Aura content.borderColor, slate-200)
--mb-color-border-input  #64748B   границы полей ввода (slate-500, 4.76:1 к белому; у Aura slate-300 — 1.48:1)
--mb-color-accent        #2F5BEA   акцент (кнопки, фокус, выбранная дата) — решение Alex ожидается
--mb-color-accent-hover  #2549C4
--mb-color-available-fg  #166534   --mb-color-available-bg #DCFCE7
--mb-color-booked-fg     #9A3412   --mb-color-booked-bg    #FFEDD5
--mb-color-past-fg       #475569   --mb-color-past-bg      #E2E8F0
--mb-color-danger        #B91C1C   --mb-color-danger-bg    #FEE2E2
--mb-space-1..6          4, 8, 12, 16, 24, 32 px
--mb-radius-sm|md|lg     4, 6, 12 px                  (Aura border.radius sm / md / xl; md — как у Sakai)
--mb-shadow-card         0 3px 5px rgba(0,0,0,.02), 0 0 2px rgba(0,0,0,.05), 0 1px 4px rgba(0,0,0,.08)   (Sakai)
--mb-shadow-overlay      0 4px 6px -1px rgba(0,0,0,.1), 0 2px 4px -2px rgba(0,0,0,.1)                     (Aura)
--mb-font-family         system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif (Lato — только по согласованию)
--mb-font-size-base      1rem (16px)
--mb-topbar-height       4rem                          (Sakai)
--mb-container-max       1120px
--mb-tap-min             44px
```

Пресет PrimeVue использует те же значения: `semantic.primary.500 = #2F5BEA` (белый текст на ней 5.52:1), `primary.600 = #2549C4` для hover. Полная шкала 50–950 генерируется при реализации.
Aura 3.x в светлой теме использует CSS-функцию `light-dark()`: поэтому `color-scheme: light` обязателен, а как Aura ведёт себя при `darkModeSelector: false` и системной тёмной теме — не проверено, проверить при реализации (задача 4.5).

### Контраст (WCAG 2.1: L = 0.2126 R + 0.7152 G + 0.0722 B, отношение (L1+0.05)/(L2+0.05))

Пары нашей палитры:

| Пара | Цвета | Контраст | Порог |
|---|---|---|---|
| Основной текст на странице | `#334155` на `#F1F5F9` | 9.45 | 4.5 |
| Основной текст на карточке | `#334155` на `#FFFFFF` | 10.35 | 4.5 |
| Вспомогательный текст на карточке | `#475569` на `#FFFFFF` | 7.58 | 4.5 |
| Вспомогательный текст на странице | `#475569` на `#F1F5F9` | 6.92 | 4.5 |
| Белый текст на акценте (кнопка) | `#FFFFFF` на `#2F5BEA` | 5.52 | 4.5 |
| Белый текст на акценте при hover | `#FFFFFF` на `#2549C4` | 7.42 | 4.5 |
| Акцентный текст на странице | `#2F5BEA` на `#F1F5F9` | 5.04 | 4.5 |
| Статус «Свободен» | `#166534` на `#DCFCE7` | 6.49 | 4.5 |
| Статус «Занят» | `#9A3412` на `#FFEDD5` | 6.38 | 4.5 |
| Статус «Прошёл» | `#475569` на `#E2E8F0` | 6.15 | 4.5 |
| Текст ошибки на белом | `#B91C1C` на `#FFFFFF` | 6.47 | 4.5 |
| Текст ошибки на фоне ошибки | `#991B1B` на `#FEE2E2` | 6.80 | 4.5 |
| Белый на кнопке «Отменить бронь» | `#FFFFFF` на `#B91C1C` | 6.47 | 4.5 |
| Граница поля ввода на белом | `#64748B` на `#FFFFFF` | 4.76 | 3.0 (не текст) |
| Фокус (акцент) на странице | `#2F5BEA` на `#F1F5F9` | 5.04 | 3.0 |

Для сравнения — значения Sakai/Aura по умолчанию (то, почему отходим): белый на `emerald.500` `#10B981` — 2.54 (провал); белый на `emerald.600` `#059669` — 3.77 (провал для обычного текста); `muted` `#64748B` на `#F1F5F9` — 4.34 (провал);
`muted` на белом — 4.76 (проходит); граница `#CBD5E1` на белом — 1.48 (провал для границ полей, нужно 3:1).
Расчёт выполнен скриптом на Python при подготовке спецификации; пересчитывается в задаче 2.3, если Alex выберет другой акцент.

### Статусы не только цветом

`slot-card__status`: иконка + подпись + цвет — «Свободен» (`pi-check-circle`), «Занят» (`pi-lock`), «Прошёл» (`pi-clock`). У прошедших слотов дополнительно приглушённая карточка и нет кнопок действий.

### Композиция страницы (по мотивам Sakai)

Серый фон страницы `--mb-color-bg`; белая шапка высотой `--mb-topbar-height` (заголовок 1.5rem/500, иконка, вторичная подпись); под шапкой контейнер; карточка «Расписание» с фильтром дат и сеткой карточек слотов;
каждая карточка — белая, радиус `--mb-radius-md`, `--mb-shadow-card`, внутренний отступ `--mb-space-4`; диалог бронирования с радиусом/тенью оверлея Aura.

### Адаптивность

- Mobile-first, базовая ширина 360 px; медиазапросы `min-width: 768px` и `min-width: 1280px` (Sakai использует 576/768/992/1200/1920 — берём подмножество, достаточное для одной страницы).
- Сетка слотов: `grid-template-columns: 1fr` → `repeat(2, 1fr)` (≥768) → `repeat(3, 1fr)` (≥1280); контейнер `max-width: var(--mb-container-max)`, отступы 16 px (телефон) / 24 px.
- Фильтр дат: сетка `repeat(auto-fit, minmax(76px, 1fr))` (7 дат на 360 px укладываются в 2 строки), высота кнопки ≥ 44 px, без горизонтального скролла.
- Шапка: на телефоне заголовок и подпись в колонку, на ≥768 px — в строку.
- Кнопки и поля формы на ≤767 px: `min-height: var(--mb-tap-min)`. Диалог — `width: min(26rem, 100vw - 32px)`; настройка PrimeVue-диалога на телефоне через `breakpoints`/`pt`, не `.p-dialog` (как именно в 4.5.5 — не проверено, проверить при реализации).

### Проверяемость

Команды (запускаются из `frontend/`, зависимостей не требуют, должны возвращать пусто):

```bash
grep -rnE ":deep|>>>|/deep/|!important" components pages layouts
grep -rnE "^\s*(\.p-|#|[a-z]+\s*[{,])" components pages layouts --include=*.vue   # селекторы по .p-*, id и тегам внутри <style>
grep -rnE "#[0-9a-fA-F]{3,8}\b|rgb\(" components pages layouts --include=*.vue    # цветовые литералы
grep -rLn "scoped" components pages layouts --include=*.vue | xargs grep -ln "<style"   # <style> без scoped
```

Вторая команда приблизительна (возможны ложные срабатывания в `<script>`/`<template>`); уточняется при реализации (например, ограничение поиска блоками `<style>` через `awk`).
Строгая альтернатива: `stylelint` + `stylelint-selector-bem-pattern` (npm: `stylelint-selector-bem-pattern` 5.0.0, MIT, peer `stylelint ^17`; `stylelint` 17.x требует Node ≥ 20.19 — у Alex Node 22.22, подходит).
Работа плагина с `.vue` (нужен `postcss-html`) не проверена. Устанавливаем только по согласованию с Alex.

## Risks / Trade-offs

- **Лицензия `@primeuix/themes` 3.x.** Установлена версия 3.1.1; её `LICENSE.md` («PrimeUI License», PrimeTek) говорит о двух лицензиях: бесплатной Community
  (организации до 1 млн USD выручки, менее 5 разработчиков, менее 10 сотрудников; физические лица, студенты, некоммерческие проекты — с ежегодным подтверждением права) и платной Commercial;
  также «a valid license key is required»: отсутствие ключа «may cause the software to display a license notice»; пакет распространяется скомпилированным.
  Версии `@primeuix/themes` 1.2.5, 2.0.0 и 2.0.3 имеют лицензию MIT (по npm); Sakai-Vue использует `@primeuix/themes ^2.0.0`. `primevue` 4.5.5 — MIT; его зависимость `@primeuix/styles ^2.0.3`.
  Что именно показывает 3.1.1 без ключа (видимое уведомление или нет) — **не проверено**; Alex ничего подобного пока не сообщал.
  Рекомендация: закрепить `@primeuix/themes` на MIT-линии `~2.0.3` (совместимость с `primevue` 4.5.5 не проверена: Sakai-Vue использует `primevue ^4.5.4` вместе с `@primeuix/themes ^2.0.0`).
  Решение — за Alex; это отдельное изменение зависимостей и `package-lock.json`. Заметка: значения Aura, приведённые выше, получены из 3.1.1; в 2.0.x могут отличаться (не проверялось).
- Шрифт Lato/Inter (если подключать) — внешний ресурс; без согласования используем системный стек.
- Файл лицензии у `primefaces/sakai-assets` не найден (GitHub API), поэтому код из него не переносится — только числовые значения.
- Живое демо [sakai.primevue.org](https://sakai.primevue.org) отвечает HTTP 200, но визуально не изучалось (значения взяты из исходников, не со скриншотов); итоговый вид сверяется с демо вручную в задаче 4.4.
- Переименование блоков/классов может задеть тесты и скриншоты; логических тестов фронтенда нет, регресс проверяется вручную по `booking-ui`.
- Расчёт контраста относится к выбранным по умолчанию цветам; при смене акцента цифры меняются.

## Open Questions

См. `proposal.md`: акцентный цвет, логотип/иллюстрация, шрифт, решение по `@primeuix/themes`.
