// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-01-01',
  // Одностраничное SPA: данные расписания зависят от клиентских запросов,
  // SSR не нужен (см. openspec/changes/archive/2026-11-17-scaffold-project/design.md).
  ssr: false,
  devtools: { enabled: false },
  // Явные импорты: всё, что использует файл, видно в его import (проще читать и править).
  // Встроенные NuxtLayout и NuxtPage остаются доступны без импорта.
  imports: { autoImport: false },
  components: { dirs: [] },
  css: [
    'primeicons/primeicons.css',
    // Шрифт Nunito Sans из node_modules (400/600/700), без обращения к CDN. Берём общие файлы весов (400.css и т.д.):
    // в них у каждого подсета (cyrillic, latin, ...) есть unicode-range, и браузер грузит только нужные файлы.
    // Отдельные файлы cyrillic-400.css / latin-400.css unicode-range не содержат и перекрывали бы друг друга.
    '@fontsource/nunito-sans/400.css',
    '@fontsource/nunito-sans/600.css',
    '@fontsource/nunito-sans/700.css',
    '~/assets/css/tokens.css',
    '~/assets/css/base.css',
  ],
  app: {
    head: {
      htmlAttrs: { lang: 'ru' },
      title: 'Mini Booking — бронирование переговорной',
      meta: [
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
      ],
    },
  },
  // В разработке проксируем /api на Laravel, чтобы не настраивать CORS.
  nitro: {
    devProxy: {
      '/api': {
        target: process.env.NUXT_API_PROXY_TARGET || 'http://localhost:8000/api',
        changeOrigin: true,
      },
    },
  },
})
