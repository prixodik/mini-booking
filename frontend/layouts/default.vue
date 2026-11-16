<template>
  <div class="layout">
    <header class="layout__header">
      <div class="layout__inner">
        <h1 class="layout__title">Mini Booking</h1>
        <p class="layout__subtitle">Бронирование переговорной комнаты</p>
      </div>
    </header>
    <main class="layout__content">
      <slot />
    </main>
  </div>
</template>

<style scoped>
.layout__header {
  min-height: var(--mb-topbar-height);
  display: flex;
  align-items: center;
  padding: var(--mb-space-2) var(--mb-space-4);
  background: var(--mb-color-surface);
  box-shadow: var(--mb-shadow-card);
}
.layout__inner {
  width: 100%;
  max-width: var(--mb-container-max);
  margin: 0 auto;
  display: flex;
  flex-direction: column;
}
.layout__title {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 700;
  line-height: 1.3;
}
.layout__subtitle {
  margin: 0;
  color: var(--mb-color-text-muted);
}
.layout__content {
  max-width: var(--mb-container-max);
  margin: 0 auto;
  padding: var(--mb-space-4);
}

@media (min-width: 768px) {
  .layout__header {
    padding: var(--mb-space-2) var(--mb-space-5);
  }
  .layout__inner {
    flex-direction: row;
    align-items: baseline;
    gap: var(--mb-space-4);
  }
  .layout__content {
    padding: var(--mb-space-5);
  }
}
</style>
