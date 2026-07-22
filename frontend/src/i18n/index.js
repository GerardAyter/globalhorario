import { createI18n } from 'vue-i18n'
import ca from './ca.json'
import es from './es.json'
import en from './en.json'

// Locale inicial: llegim del sessionStorage (persistit pel auth store) o 'ca' per defecte
function getInitialLocale() {
  try {
    const user = JSON.parse(sessionStorage.getItem('user') || 'null')
    return user?.locale || 'ca'
  } catch {
    return 'ca'
  }
}

export const i18n = createI18n({
  legacy: false,        // Composition API mode
  locale: getInitialLocale(),
  fallbackLocale: 'ca',
  messages: { ca, es, en },
})

/** Crida això cada cop que l'usuari canviï d'idioma (login o desar perfil) */
export function setLocale(locale) {
  i18n.global.locale.value = locale || 'ca'
}
