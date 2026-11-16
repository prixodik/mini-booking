<template>
  <div class="layout">
    <header class="layout__header">
      <h1 class="layout__title">Mini Booking</h1>
      <p class="layout__subtitle">Бронирование переговорной комнаты</p>
    </header>
    <main class="layout__content">
      <slot />
    </main>
  </div>
</template>

<style scoped>
.layout {
  max-width: 960px;
  margin: 0 auto;
  padding: 16px;
}
.layout__header {
  margin-bottom: 16px;
}
.layout__title {
  margin: 0;
  font-size: 1.5rem;
}
.layout__subtitle {
  margin: 4px 0 0;
  color: #52606d;
}
</style>
