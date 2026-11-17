// Общий HTTP-клиент для всех API-сервисов. Компоненты сюда не обращаются напрямую.

const API_BASE = '/api'
const TIMEOUT_MS = 15000 // запрос, не получивший ответа за 15 с, считается сбоем сети

/** Ошибка API. status === 0 — сетевой сбой (сервер недоступен или не ответил вовремя). */
export class ApiError extends Error {
  constructor({ status, message, errors = null, timedOut = false }) {
    super(message)
    this.name = 'ApiError'
    this.status = status
    this.errors = errors
    this.timedOut = timedOut
  }

  get isNetwork() {
    return this.status === 0
  }

  get isTimeout() {
    return this.timedOut
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

function timeoutError() {
  return new ApiError({ status: 0, timedOut: true, message: 'Сервер не ответил вовремя. Повторите попытку.' })
}

const isTimeout = (error) => error?.name === 'TimeoutError'

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
      // Прерывает и ожидание ответа, и чтение тела.
      signal: AbortSignal.timeout(TIMEOUT_MS),
    })
  } catch (error) {
    if (isTimeout(error)) throw timeoutError()
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
  } catch (error) {
    if (isTimeout(error)) throw timeoutError()
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
