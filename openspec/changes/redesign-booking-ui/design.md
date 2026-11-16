# Design

## Context

Фронтенд: Nuxt 3 (SPA), PrimeVue 4.5.5, `@primeuix/themes` 3.1.1 (Aura, `darkModeSelector: false`; в рамках этого change закрепляется MIT-версия 2.0.3, см. «Решения Alex»), страница `pages/index.vue`, компоненты
`components/booking/{SlotList,DateFilter,BookingDialog}.vue`, `layouts/default.vue`. Имена классов уже близки к БЭМ (`slot__time`, `form__field`,
`layout__header`), но стили лежат в `scoped`-блоках без общих токенов, цвета захардкожены, вид — «стандартный».

## Goals / Non-Goals

**Goals:** аккуратная светлая тема по визуальному ориентиру Sakai; БЭМ с проверяемыми правилами; общие токены; адаптивность 360 / 768 / 1280; минимум нового кода и зависимостей.

**Non-Goals:** копирование шаблона Sakai (layout с боковым меню, дашборд, конфигуратор тем), тёмная тема, переключатель тем, новые функции, смена логики и API, смена Nuxt-проекта и UI-библиотеки.

## Решения Alex

| Вопрос | Решение | Статус |
|---|---|---|
| Направление | Sakai как ориентир + Aura + БЭМ | решение Alex |
| `@primeuix/themes` | закрепить MIT-версию 2.0.x: `2.0.3` (последняя в линии; 2.0.0–2.0.3 имеют лицензию MIT) | решение Alex |
| Акцентный цвет | фиолетовый, оттенок подобран по контрасту: `#6D28D9` (Aura `violet.700`) | «фиолетовый» — решение Alex, оттенок подобран мной |
| Логотип / иллюстрация | нет; в шапке только заголовок и вторичная подпись, иконки календаря в шапке нет | решение Alex, иконка убрана мной |
| Шрифт | Alex просил Lato; у Lato нет кириллицы (проверено: `@fontsource/lato` v25, подсеты только `latin`, `latin-ext`), поэтому берём близкий по характеру гуманистический шрифт с кириллицей — Nunito Sans (`@fontsource/nunito-sans` 5.3.0, OFL-1.1), подключается локально, без внешних запросов | Alex просил Lato; замена на Nunito Sans из-за кириллицы подтверждена Alex |
| Данные клиента на карточках | не показываем: ТЗ прямо запрещает имя и email в списке слотов | ограничение ТЗ |
| Раскладка карточки | время слева сверху, статус справа сверху, кнопки внизу карточки | решение Alex |
| stylelint с БЭМ-плагином | не ставим, проверка grep-командами | принято по умолчанию |

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
  граница поля `#CBD5E1` на белом — 1.48:1 (нужно 3:1). Поэтому: акцент задаётся свой — фиолетовый `#6D28D9`, вторичный текст темнее (slate-600), граница полей — slate-500.
- **Размер шрифта.** Sakai использует `html { font-size: 14px }`. Мы оставляем 16px: поля ввода меньше 16px на iOS вызывают принудительное увеличение страницы; 16px читаемее на телефоне.
- **Шрифт.** Sakai использует Lato (как он подключается к демо — не проверено). Alex просил Lato, но у Lato нет кириллицы (подсеты `@fontsource/lato`: `latin`, `latin-ext`), а интерфейс русский: кириллица отображалась бы запасным шрифтом, и текст выглядел бы разнородно. Берём Nunito Sans (гуманистический гротеск близкого характера; подсеты `cyrillic`, `latin`; веса 400/600/700; OFL-1.1; npm `@fontsource/nunito-sans` 5.3.0, подключается из `node_modules` без обращения к CDN; подключаются общие файлы весов `400.css`, `600.css`, `700.css` — в них у каждого подсета есть `unicode-range`, а отдельные файлы `cyrillic-400.css` и `latin-400.css` `unicode-range` не содержат (проверено чтением пакета) и перекрывали бы друг друга). Насколько он «похож на Lato» визуально — мой вывод по характеру шрифтов, не проверялся на экране. Запасные шрифты: `system-ui`, `-apple-system`, `Segoe UI`, `Roboto`, `sans-serif`. Альтернативы с кириллицей (OFL-1.1, у `fontsource` есть `cyrillic`): Source Sans 3, Open Sans, PT Sans, Fira Sans, Golos Text.
- **Шапка и отступы на телефоне.** Sakai везде использует `padding: 0 2rem`; на 360 px это съедает 64 px ширины — берём 16 px, на ≥768 px — 24–32 px.
- **Шапка не фиксируется.** Контент одностраничный, `position: sticky`/fixed не нужны; не требуется отступ `6rem`.
- **Логотип Sakai** (SVG PrimeTek) не используется; логотипа и иллюстрации в шапке нет (решение Alex), иконки календаря тоже нет: шапка — заголовок и вторичная подпись.
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
slot-card         карточка слота               slot-card__header, slot-card__time, slot-card__status, slot-card__footer, slot-card__action; --available | --booked | --past
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
--mb-color-accent        #6D28D9   акцент, фиолетовый (кнопки, фокус, выбранная дата) = Aura violet.700
--mb-color-accent-hover  #5B21B6   hover = Aura violet.800
--mb-color-accent-active #4C1D95   active = Aura violet.900
--mb-color-accent-soft   #EDE9FE   светлый фон акцентных элементов = Aura violet.100
--mb-color-available-fg  #166534   --mb-color-available-bg #DCFCE7
--mb-color-booked-fg     #9A3412   --mb-color-booked-bg    #FFEDD5
--mb-color-past-fg       #475569   --mb-color-past-bg      #E2E8F0
--mb-color-danger        #B91C1C   --mb-color-danger-bg    #FEE2E2
--mb-space-1..6          4, 8, 12, 16, 24, 32 px
--mb-radius-sm|md|lg     4, 6, 12 px                  (Aura border.radius sm / md / xl; md — как у Sakai)
--mb-shadow-card         0 3px 5px rgba(0,0,0,.02), 0 0 2px rgba(0,0,0,.05), 0 1px 4px rgba(0,0,0,.08)   (Sakai)
--mb-shadow-overlay      0 4px 6px -1px rgba(0,0,0,.1), 0 2px 4px -2px rgba(0,0,0,.1)                     (Aura)
--mb-font-family         'Nunito Sans', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif
--mb-font-size-base      1rem (16px)
--mb-topbar-height       4rem                          (Sakai)
--mb-container-max       1120px
--mb-tap-min             44px
```

Пресет PrimeVue использует те же значения. Шкала `primary` строится из палитры Aura `violet` (`violet.50`…`950`), но в `semantic.colorScheme.light.primary` значения `color`, `hoverColor`, `activeColor`
переопределяются на `{violet.700}`, `{violet.800}`, `{violet.900}`, а `contrastColor` остаётся `#ffffff`: при стандартном отображении Aura (`primary.500` = `violet.500` `#8B5CF6`) белый текст даёт только 4.23:1 (ниже AA).
В линии 2.0.x токены живут в `semantic.colorScheme.light` (без `light-dark()`); как именно `definePreset` объединяет такие переопределения в 2.0.3 — проверить при реализации. `color-scheme: light` в `base.css` остаётся обязательным.

### Контраст (WCAG 2.1: L = 0.2126 R + 0.7152 G + 0.0722 B, отношение (L1+0.05)/(L2+0.05))

Пары нашей палитры:

| Пара | Цвета | Контраст | Порог |
|---|---|---|---|
| Основной текст на странице | `#334155` на `#F1F5F9` | 9.45 | 4.5 |
| Основной текст на карточке | `#334155` на `#FFFFFF` | 10.35 | 4.5 |
| Вспомогательный текст на карточке | `#475569` на `#FFFFFF` | 7.58 | 4.5 |
| Вспомогательный текст на странице | `#475569` на `#F1F5F9` | 6.92 | 4.5 |
| Белый текст на акценте (кнопка) | `#FFFFFF` на `#6D28D9` | 7.10 | 4.5 |
| Белый текст на акценте при hover | `#FFFFFF` на `#5B21B6` | 8.98 | 4.5 |
| Белый текст на акценте при active | `#FFFFFF` на `#4C1D95` | 10.95 | 4.5 |
| Акцентный текст на белом | `#6D28D9` на `#FFFFFF` | 7.10 | 4.5 |
| Акцентный текст на странице | `#6D28D9` на `#F1F5F9` | 6.49 | 4.5 |
| Акцентный текст на светлом фоне акцента | `#6D28D9` на `#EDE9FE` | 5.98 | 4.5 |
| Статус «Свободен» | `#166534` на `#DCFCE7` | 6.49 | 4.5 |
| Статус «Занят» | `#9A3412` на `#FFEDD5` | 6.38 | 4.5 |
| Статус «Прошёл» | `#475569` на `#E2E8F0` | 6.15 | 4.5 |
| Текст ошибки на белом | `#B91C1C` на `#FFFFFF` | 6.47 | 4.5 |
| Текст ошибки на фоне ошибки | `#991B1B` на `#FEE2E2` | 6.80 | 4.5 |
| Белый на кнопке «Отменить бронь» | `#FFFFFF` на `#B91C1C` | 6.47 | 4.5 |
| Граница поля ввода на белом | `#64748B` на `#FFFFFF` | 4.76 | 3.0 (не текст) |
| Кольцо фокуса на белом | `#6D28D9` на `#FFFFFF` | 7.10 | 3.0 |
| Кольцо фокуса на странице | `#6D28D9` на `#F1F5F9` | 6.49 | 3.0 |

Для сравнения — значения Sakai/Aura по умолчанию (то, почему отходим; белый на `violet.500` `#8B5CF6` — 4.23 (провал), на `violet.600` `#7C3AED` — 5.70 (проходит), выбран более тёмный `violet.700` для запаса): белый на `emerald.500` `#10B981` — 2.54 (провал); белый на `emerald.600` `#059669` — 3.77 (провал для обычного текста); `muted` `#64748B` на `#F1F5F9` — 4.34 (провал);
`muted` на белом — 4.76 (проходит); граница `#CBD5E1` на белом — 1.48 (провал для границ полей, нужно 3:1).
Расчёт выполнен скриптом на Python при подготовке спецификации; пересчитывается в задаче 2.3, если акцент изменится.

### Статусы не только цветом

`slot-card__status`: иконка + подпись + цвет — «Свободен» (`pi-check-circle`), «Занят» (`pi-lock`), «Прошёл» (`pi-clock`). У прошедших слотов дополнительно приглушённая карточка и нет кнопок действий.

### Карточка слота (решение Alex)

Колонка (`flex-direction: column`, `justify-content: space-between`, равная высота карточек в строке сетки):

```
┌──────────────────────────────┐
│ 10:00–11:00         [Свободен]│   slot-card__header: время слева (slot-card__time), статус справа (slot-card__status)
│                              │
│ [      Забронировать       ] │   slot-card__footer: кнопка внизу на всю ширину (slot-card__action)
└──────────────────────────────┘
```

- Кнопка «Забронировать» (свободный) и «Отменить бронь» (занятый будущий) всегда в `slot-card__footer`, прижатом к нижнему краю (`margin-top: auto`), поэтому кнопки в соседних карточках выровнены по одной линии; у прошедшего слота `footer` отсутствует, высота карточки сохраняется сеткой.
- Кнопка отмены: `danger`, контур; цвет текста и границы — `--mb-color-danger` (`#B91C1C`, 6.47:1 на белом). В Aura (проверено чтением `components.button.outlined.danger` в 2.0.3 и 3.1.1) контурная danger-кнопка использует текст `red.500` (`#EF4444`, 3.76:1 на белом) и границу `red.200`, поэтому на присланном Alex скриншоте она плохо читается; цвет задаётся переопределением токенов компонента в пресете (`components.button.colorScheme.light.outlined.danger`), а не классом на `.p-button`.
- Имя и email забронировавшего на карточке не показываются: ТЗ (п. 3) требует «Имя и email в список слотов не включайте». `GET /api/slots` их не возвращает, и спецификация backend не меняется. Варианты на будущее (вне объёма, нужна отдельная правка спецификации и согласование Alex): (а) запоминать в браузере созданные брони (localStorage: ID брони → имя) и показывать «Ваша бронь» только тому, кто бронировал, без персональных данных с сервера; (б) отдельный эндпоинт `GET /api/bookings/{id}` — не рекомендуется: расширяет API за пределы ТЗ и выдаёт персональные данные без авторизации (отмена в демо работает по известному ID).

### Композиция страницы (по мотивам Sakai)

Серый фон страницы `--mb-color-bg`; белая шапка высотой `--mb-topbar-height` (заголовок 1.5rem/500, вторичная подпись; без логотипа и иконки); под шапкой контейнер; карточка «Расписание» с фильтром дат и сеткой карточек слотов;
каждая карточка — белая, радиус `--mb-radius-md`, `--mb-shadow-card`, внутренний отступ `--mb-space-4`; диалог бронирования с радиусом/тенью оверлея Aura.

### Адаптивность

- Mobile-first, базовая ширина 360 px; медиазапросы `min-width: 768px` и `min-width: 1280px` (Sakai использует 576/768/992/1200/1920 — берём подмножество, достаточное для одной страницы).
- Сетка слотов: `grid-template-columns: 1fr` → `repeat(2, 1fr)` (≥768) → `repeat(3, 1fr)` (≥1280); контейнер `max-width: var(--mb-container-max)`, отступы 16 px (телефон) / 24 px.
- Фильтр дат: самостоятельный БЭМ-блок на `<button role="radio">` вместо `SelectButton` (нужна сетка и высота 44 px без переопределения `.p-*`); сетка `repeat(auto-fit, minmax(64px, 1fr))` (7 дат на 360 px укладываются в 2 строки, измерено), высота кнопки ≥ 44 px, без горизонтального скролла.
- Шапка: на телефоне заголовок и подпись в колонку, на ≥768 px — в строку.
- Кнопки и поля формы на ≤767 px: `min-height: var(--mb-tap-min)`. Диалог — `width: min(26rem, 100vw - 32px)`; настройка PrimeVue-диалога на телефоне через `breakpoints`/`pt`, не `.p-dialog` (как именно в 4.5.5 — не проверено, проверить при реализации).

### Проверяемость

Команды (запускаются из `frontend/`, зависимостей не требуют, должны возвращать пусто):

```bash
grep -rnE ":deep|>>>|/deep/|!important" components pages layouts
for f in $(grep -rl "<style" components pages layouts --include=*.vue); do awk '/<style/{s=1;next}/<\/style>/{s=0}s' "$f" | grep -E "^\s*[^ \t}@/].*[{,]\s*$" | grep -vE ":\s" | grep -vE "^\s*\.[a-z][a-z0-9]*(-[a-z0-9]+)*(__[a-z0-9]+(-[a-z0-9]+)*)?(--[a-z0-9]+(-[a-z0-9]+)*)?(:[a-z-]+(\(:[a-z-]+\))?)*(,| \{|,? \{)$" | sed "s|^|$f: |"; done   # селекторы, не являющиеся одноклассовыми БЭМ (теги, id, .p-*)
grep -rnE "#[0-9a-fA-F]{3,8}\b|rgb\(" components pages layouts --include=*.vue    # цветовые литералы
grep -rLn "scoped" components pages layouts --include=*.vue | xargs grep -ln "<style"   # <style> без scoped
```

Вторая команда разбирает только блоки `<style>` (через `awk`) и пропускает объявления свойств; допустимым считается селектор вида `.block`, `.block__element`, `.block--modifier` с псевдоклассами. Для цветовых литералов третья команда в реализации также ограничена блоками `<style>`.
Отклонённая альтернатива (решение: не ставим): `stylelint` + `stylelint-selector-bem-pattern` (npm: `stylelint-selector-bem-pattern` 5.0.0, MIT, peer `stylelint ^17`; `stylelint` 17.x требует Node ≥ 20.19 — у Alex Node 22.22, подходит).
Работа плагина с `.vue` (нужен `postcss-html`) не проверена. Проверка выполняется grep-командами выше.

## Risks / Trade-offs

- **`@primeuix/themes`: закрепление на MIT-версии 2.0.3 (решение Alex).** Установленная 3.1.1 распространяется по «PrimeUI License» PrimeTek (Community-лицензия для небольших организаций, физических лиц, студентов, некоммерческих проектов с ежегодным подтверждением; требуется ключ,
  без него «may cause the software to display a license notice»; пакет скомпилирован). Версии 2.0.0–2.0.3 имеют лицензию MIT (npm). Что 3.1.1 показывает без ключа — не проверено.
  Проверено по npm: `@primeuix/themes@2.0.3` (MIT) зависит от `@primeuix/styled ^0.7.4`, а `primevue@4.5.5` — от `@primeuix/styled ^0.7.4` (и `@primeuix/styles ^2.0.3`), линия `styled` совпадает; Sakai-Vue использует `primevue ^4.5.4` с `@primeuix/themes ^2.0.0`.
  В 2.0.3 есть экспорт Aura (`dist/aura`) и палитры `violet` и `slate` (прочитан скачанный пакет). Фактический запуск с `primevue` 4.5.5 не проверялся — задача 1.2.
  Итоговые версии после реализации: `@primeuix/themes` 2.0.3 (точная, без `^`), `primevue` 4.5.5, `primeicons` без изменений, `nuxt` 3.21.x.
- **Расхождения значений Aura 3.1.1 и 2.0.3** (сверено чтением обоих пакетов): совпадают радиусы (`md` 6px, `xl` 12px), палитры `slate` и `violet`, поверхности светлой темы (`surface.*` = slate), текст (`surface.700`, muted `surface.500`), границы контента (`surface.200`), границы полей (`surface.300`), тень оверлея, фокус (1px solid, offset 2px).
  Отличаются: в 3.1.1 цвета заданы через `light-dark()` на уровне `semantic`, в 2.0.3 — в `semantic.colorScheme.light/dark`; padding оверлея `0.625rem` (3.1.1) и `0.75rem` (2.0.3); блок `semantic.typography` (`fontSize: 0.875rem`) есть только в 3.1.1 — размер шрифта в 2.0.3 токеном не задан (используем наш `--mb-font-size-base`).
- Файл лицензии у `primefaces/sakai-assets` не найден (GitHub API), поэтому код из него не переносится — только числовые значения.
- Живое демо [sakai.primevue.org](https://sakai.primevue.org) отвечает HTTP 200, но визуально не изучалось (значения взяты из исходников, не со скриншотов); итоговый вид сверяется с демо вручную в задаче 4.4.
- Переименование блоков/классов может задеть тесты и скриншоты; логических тестов фронтенда нет, регресс проверяется вручную по `booking-ui`.
- Расчёт контраста относится к выбранным по умолчанию цветам; при смене акцента цифры меняются.

## Open Questions

Открытых вопросов нет; осталось явное согласование спецификации Alex перед реализацией.
