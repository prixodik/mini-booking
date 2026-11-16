// Общий HTTP-клиент для всех API-сервисов. Компоненты сюда не обращаются напрямую.

const API_BASE = '/api'

/** Ошибка API. status === 0 — сетевой сбой (сервер недоступен). */
export class ApiError extends Error {
  constructor({ status, message, errors = null }) {
    super(message)
    this.name = 'ApiError'
    this.status = status
    this.errors = errors
  }

  get isNetwork() {
    return this.status === 0
  }

  get isServer() {
    return this.status >= 500
  }

  /** Сбой сети или сервера: имеет смысл предложить «Повторить». */
  get isRetryable() {
    return this.isNetwork || this.isServer
  }

  get isValidation() {
    return this.status === 422
  }

  get isConflict() {
    return this.status === 409
  }

  get isNotFound() {
    return this.status === 404
  }
}

export async function request(path, { method = 'GET', body } = {}) {
  let response
  try {
    response = await fetch(`${API_BASE}${path}`, {
      method,
      headers: {
        Accept: 'application/json',
        ...(body ? { 'Content-Type': 'application/json' } : {}),
      },
      body: body ? JSON.stringify(body) : undefined,
    })
  } catch {
    throw new ApiError({
      status: 0,
      message: 'Нет связи с сервером. Проверьте подключение и повторите попытку.',
    })
  }

  if (response.status === 204) {
    return null
  }

  let data = null
  try {
    data = await response.json()
  } catch {
    // тело не JSON — используем сообщение по умолчанию
  }

  if (!response.ok) {
    throw new ApiError({
      status: response.status,
      message: data?.message || (response.status >= 500 ? 'Ошибка сервера. Повторите попытку позже.' : 'Не удалось выполнить запрос.'),
      errors: data?.errors ?? null,
    })
  }

  return data
}
