// Все даты и время показываем в часовом поясе расписания (meta.timezone), а не в поясе браузера.

/** 'YYYY-MM-DD' момента времени в заданном часовом поясе. */
export function dateKey(moment, timeZone) {
  return new Intl.DateTimeFormat('sv-SE', {
    timeZone,
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  }).format(new Date(moment))
}

/** 'HH:mm' момента времени в заданном часовом поясе. */
export function formatTime(moment, timeZone) {
  return new Intl.DateTimeFormat('ru-RU', {
    timeZone,
    hour: '2-digit',
    minute: '2-digit',
    hour12: false,
  }).format(new Date(moment))
}

/** Подпись даты по ключу 'YYYY-MM-DD', например «пн, 5 янв.». */
export function formatDateLabel(key) {
  // Календарная дата не зависит от пояса, поэтому форматируем её в UTC.
  return new Intl.DateTimeFormat('ru-RU', {
    timeZone: 'UTC',
    weekday: 'short',
    day: 'numeric',
    month: 'short',
  }).format(new Date(`${key}T12:00:00Z`))
}
