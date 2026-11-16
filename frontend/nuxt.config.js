// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-01-01',
  // Одностраничное SPA: данные расписания зависят от клиентских запросов,
  // SSR не нужен (см. openspec/changes/scaffold-project/design.md).
  ssr: false,
  devtools: { enabled: false },
  css: ['primeicons/primeicons.css', '~/assets/css/main.css'],
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
