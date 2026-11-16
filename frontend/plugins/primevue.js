import PrimeVue from 'primevue/config'
import ToastService from 'primevue/toastservice'
import ConfirmationService from 'primevue/confirmationservice'
import { definePreset } from '@primeuix/themes'
import Aura from '@primeuix/themes/aura'

// Светлая тема Aura с фиолетовым акцентом. Внешний вид компонентов PrimeVue меняется только токенами
// (контраст рассчитан в openspec/changes/redesign-booking-ui/design.md), классы .p-* не переопределяются.
const MiniBookingPreset = definePreset(Aura, {
  semantic: {
    primary: {
      50: '{violet.50}',
      100: '{violet.100}',
      200: '{violet.200}',
      300: '{violet.300}',
      400: '{violet.400}',
      500: '{violet.500}',
      600: '{violet.600}',
      700: '{violet.700}',
      800: '{violet.800}',
      900: '{violet.900}',
      950: '{violet.950}',
    },
    // Кольцо фокуса 2px (по умолчанию в Aura — 1px).
    focusRing: { width: '2px' },
    colorScheme: {
      light: {
        // violet.500 (стандартное primary.500) даёт белому тексту лишь 4.23:1, поэтому акцент — violet.700.
        primary: {
          color: '{violet.700}',
          contrastColor: '#ffffff',
          hoverColor: '{violet.800}',
          activeColor: '{violet.900}',
        },
        text: {
          mutedColor: '{surface.600}',
          hoverMutedColor: '{surface.700}',
        },
        // Границы полей ввода не ниже 3:1 к белому (slate.500 — 4.76:1).
        formField: {
          borderColor: '{slate.500}',
          hoverBorderColor: '{slate.600}',
          placeholderColor: '{surface.600}',
          invalidBorderColor: '{red.700}',
        },
      },
    },
  },
  components: {
    toast: {
      colorScheme: {
        light: {
          // Заголовок, иконка и кнопка закрытия тоста: у Aura (500/600-е оттенки) контраст к фону тоста ниже 4.5:1
          // (success 3.16, warn 2.85, error 4.43). Берём более тёмные оттенки палитры Aura (расчёт — в design.md).
          success: { color: '{green.800}', closeButton: { focusRing: { color: '{green.800}' } } },
          info: { color: '{blue.700}', closeButton: { focusRing: { color: '{blue.700}' } } },
          warn: { color: '{yellow.800}', closeButton: { focusRing: { color: '{yellow.800}' } } },
          error: { color: '{red.700}', closeButton: { focusRing: { color: '{red.700}' } } },
        },
      },
    },
    button: {
      colorScheme: {
        light: {
          root: {
            // Кнопка опасного действия: белый текст на red.700 даёт 6.47:1 (red.500 — лишь 3.76:1).
            danger: {
              background: '{red.700}',
              hoverBackground: '{red.800}',
              activeBackground: '{red.900}',
              borderColor: '{red.700}',
              hoverBorderColor: '{red.800}',
              activeBorderColor: '{red.900}',
              focusRing: { color: '{red.700}' },
            },
          },
          outlined: {
            // «Отменить бронь»: текст и граница red.700 (6.47:1 на белом); у Aura — red.500 и бледная red.200.
            danger: {
              hoverBackground: '{red.50}',
              activeBackground: '{red.100}',
              borderColor: '{red.700}',
              color: '{red.700}',
            },
          },
        },
      },
    },
  },
})

export default defineNuxtPlugin((nuxtApp) => {
  nuxtApp.vueApp.use(PrimeVue, {
    theme: { preset: MiniBookingPreset, options: { darkModeSelector: false } },
  })
  nuxtApp.vueApp.use(ToastService)
  nuxtApp.vueApp.use(ConfirmationService)
})
