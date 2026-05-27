import { createI18n } from 'vue-i18n'

export const SUPPORTED = { EN: 'en', RU: 'ru', DE: 'de' }

const messages = {
  en: {
    nav: { home: 'Home', chooseGame: 'Choose Game', contact: 'Contact Us', sell: 'Sell to Us', reviews: 'Reviews' },
    header: { login: 'Login', logout: 'Logout' },
    common: { ourGuarantees: 'Our Guarantees' },
    cart: {
      title: 'Shopping Cart', items: '{n} items', empty: 'Your cart is empty.',
      totalItems: 'Total items', promocode: 'I have a promocode', promocodePlaceholder: 'Promocode',
      apply: 'Apply', total: 'Total', checkout: 'Secure checkout',
      applied: 'Code {code} applied', invalid: 'Invalid promocode',
    },
    checkout: {
      title: 'Checkout', empty: 'Your cart is empty.', browse: 'Browse games',
      summary: 'Order summary', items: 'Items', subtotal: 'Subtotal', discount: 'Discount', total: 'Total',
      email: 'Email for delivery', method: 'Payment method', card: 'Credit / debit card', paypal: 'PayPal',
      pay: 'Pay {total}', processing: 'Redirecting to payment…',
      note: "Secured by IceNox Pay. You'll be redirected to complete the payment.",
    },
    success: {
      thanks: 'Thank you! Order placed', order: 'Order', sentTo: 'a confirmation was sent to {email}.',
      subtotal: 'Subtotal', discount: 'Discount', total: 'Total', continue: 'Continue shopping',
    },
    account: {
      overview: 'Overview', myOrders: 'My Orders', profile: 'Profile', logout: 'Logout',
      orders: 'Orders', totalSpent: 'Total spent', pending: 'Pending',
      recentOrders: 'Recent orders', viewAll: 'View all', noOrders: 'No orders yet.', startShopping: 'Start shopping →',
      order: 'Order', date: 'Date', items: 'Items', status: 'Status', payment: 'Payment', total: 'Total', view: 'View',
      noOrdersList: 'You have no orders yet.', browseCatalog: 'Browse the catalog →', back: '← Back to orders',
      details: 'Details', delivery: 'Delivery', email: 'Email',
      profileInfo: 'Profile information', name: 'Name', save: 'Save changes', saved: 'Saved ✓',
      changePassword: 'Change password', currentPassword: 'Current password', newPassword: 'New password',
      confirmPassword: 'Confirm new password', updatePassword: 'Update password', updated: 'Updated ✓',
    },
    auth: {
      welcomeBack: 'Welcome back', loginSub: 'Log in to your Loot4you account',
      createAccount: 'Create your account', registerSub: 'Join Loot4you and start gaming',
      email: 'Email address', password: 'Password', forgot: 'Forgot password?', remember: 'Remember me',
      login: 'Log in', loggingIn: 'Logging in…', signup: 'Sign up', noAccount: "Don't have an account?",
      haveAccount: 'Already have an account?', name: 'Name', confirm: 'Confirm password', creating: 'Creating account…',
      createBtn: 'Create account',
    },
  },
  ru: {
    nav: { home: 'Главная', chooseGame: 'Выбрать игру', contact: 'Контакты', sell: 'Продать нам', reviews: 'Отзывы' },
    header: { login: 'Вход', logout: 'Выйти' },
    common: { ourGuarantees: 'Наши гарантии' },
    cart: {
      title: 'Корзина', items: '{n} тов.', empty: 'Корзина пуста.',
      totalItems: 'Всего товаров', promocode: 'У меня есть промокод', promocodePlaceholder: 'Промокод',
      apply: 'Применить', total: 'Итого', checkout: 'Оформить заказ',
      applied: 'Промокод {code} применён', invalid: 'Неверный промокод',
    },
    checkout: {
      title: 'Оформление', empty: 'Корзина пуста.', browse: 'Перейти в каталог',
      summary: 'Сводка заказа', items: 'Товаров', subtotal: 'Сумма', discount: 'Скидка', total: 'Итого',
      email: 'Email для доставки', method: 'Способ оплаты', card: 'Банковская карта', paypal: 'PayPal',
      pay: 'Оплатить {total}', processing: 'Переход к оплате…',
      note: 'Оплата через IceNox Pay. Вы будете перенаправлены для завершения оплаты.',
    },
    success: {
      thanks: 'Спасибо! Заказ оформлен', order: 'Заказ', sentTo: 'подтверждение отправлено на {email}.',
      subtotal: 'Сумма', discount: 'Скидка', total: 'Итого', continue: 'Продолжить покупки',
    },
    account: {
      overview: 'Обзор', myOrders: 'Мои заказы', profile: 'Профиль', logout: 'Выйти',
      orders: 'Заказы', totalSpent: 'Потрачено', pending: 'В ожидании',
      recentOrders: 'Последние заказы', viewAll: 'Все', noOrders: 'Заказов пока нет.', startShopping: 'Начать покупки →',
      order: 'Заказ', date: 'Дата', items: 'Товаров', status: 'Статус', payment: 'Оплата', total: 'Итого', view: 'Открыть',
      noOrdersList: 'У вас пока нет заказов.', browseCatalog: 'Перейти в каталог →', back: '← Назад к заказам',
      details: 'Детали', delivery: 'Доставка', email: 'Email',
      profileInfo: 'Данные профиля', name: 'Имя', save: 'Сохранить', saved: 'Сохранено ✓',
      changePassword: 'Смена пароля', currentPassword: 'Текущий пароль', newPassword: 'Новый пароль',
      confirmPassword: 'Повторите пароль', updatePassword: 'Обновить пароль', updated: 'Обновлено ✓',
    },
    auth: {
      welcomeBack: 'С возвращением', loginSub: 'Войдите в аккаунт Loot4you',
      createAccount: 'Создать аккаунт', registerSub: 'Зарегистрируйтесь в Loot4you',
      email: 'Email', password: 'Пароль', forgot: 'Забыли пароль?', remember: 'Запомнить меня',
      login: 'Войти', loggingIn: 'Вход…', signup: 'Регистрация', noAccount: 'Нет аккаунта?',
      haveAccount: 'Уже есть аккаунт?', name: 'Имя', confirm: 'Повторите пароль', creating: 'Создание…',
      createBtn: 'Создать аккаунт',
    },
  },
  de: {
    nav: { home: 'Startseite', chooseGame: 'Spiel wählen', contact: 'Kontakt', sell: 'An uns verkaufen', reviews: 'Bewertungen' },
    header: { login: 'Anmelden', logout: 'Abmelden' },
    common: { ourGuarantees: 'Unsere Garantien' },
    cart: {
      title: 'Warenkorb', items: '{n} Artikel', empty: 'Dein Warenkorb ist leer.',
      totalItems: 'Artikel gesamt', promocode: 'Ich habe einen Gutschein', promocodePlaceholder: 'Gutscheincode',
      apply: 'Anwenden', total: 'Gesamt', checkout: 'Sicher bezahlen',
      applied: 'Code {code} angewendet', invalid: 'Ungültiger Gutschein',
    },
    checkout: {
      title: 'Kasse', empty: 'Dein Warenkorb ist leer.', browse: 'Spiele ansehen',
      summary: 'Bestellübersicht', items: 'Artikel', subtotal: 'Zwischensumme', discount: 'Rabatt', total: 'Gesamt',
      email: 'E-Mail für Lieferung', method: 'Zahlungsart', card: 'Kredit- / Debitkarte', paypal: 'PayPal',
      pay: '{total} bezahlen', processing: 'Weiterleitung zur Zahlung…',
      note: 'Gesichert durch IceNox Pay. Du wirst zur Zahlung weitergeleitet.',
    },
    success: {
      thanks: 'Danke! Bestellung aufgegeben', order: 'Bestellung', sentTo: 'eine Bestätigung wurde an {email} gesendet.',
      subtotal: 'Zwischensumme', discount: 'Rabatt', total: 'Gesamt', continue: 'Weiter einkaufen',
    },
    account: {
      overview: 'Übersicht', myOrders: 'Meine Bestellungen', profile: 'Profil', logout: 'Abmelden',
      orders: 'Bestellungen', totalSpent: 'Ausgegeben', pending: 'Ausstehend',
      recentOrders: 'Letzte Bestellungen', viewAll: 'Alle', noOrders: 'Noch keine Bestellungen.', startShopping: 'Jetzt einkaufen →',
      order: 'Bestellung', date: 'Datum', items: 'Artikel', status: 'Status', payment: 'Zahlung', total: 'Gesamt', view: 'Ansehen',
      noOrdersList: 'Du hast noch keine Bestellungen.', browseCatalog: 'Katalog ansehen →', back: '← Zurück zu Bestellungen',
      details: 'Details', delivery: 'Lieferung', email: 'E-Mail',
      profileInfo: 'Profilinformationen', name: 'Name', save: 'Änderungen speichern', saved: 'Gespeichert ✓',
      changePassword: 'Passwort ändern', currentPassword: 'Aktuelles Passwort', newPassword: 'Neues Passwort',
      confirmPassword: 'Neues Passwort bestätigen', updatePassword: 'Passwort aktualisieren', updated: 'Aktualisiert ✓',
    },
    auth: {
      welcomeBack: 'Willkommen zurück', loginSub: 'Melde dich bei deinem Loot4you-Konto an',
      createAccount: 'Konto erstellen', registerSub: 'Registriere dich bei Loot4you',
      email: 'E-Mail-Adresse', password: 'Passwort', forgot: 'Passwort vergessen?', remember: 'Angemeldet bleiben',
      login: 'Anmelden', loggingIn: 'Anmeldung…', signup: 'Registrieren', noAccount: 'Noch kein Konto?',
      haveAccount: 'Schon ein Konto?', name: 'Name', confirm: 'Passwort bestätigen', creating: 'Wird erstellt…',
      createBtn: 'Konto erstellen',
    },
  },
}

export const i18n = createI18n({
  legacy: false,
  globalInjection: true,
  locale: 'en',
  fallbackLocale: 'en',
  messages,
})

export function setLocale(label) {
  const code = SUPPORTED[label] ?? (Object.values(SUPPORTED).includes(label) ? label : 'en')
  i18n.global.locale.value = code
  if (typeof document !== 'undefined') {
    document.cookie = `locale=${code};path=/;max-age=${60 * 60 * 24 * 365}`
  }
}

/** Map a stored locale code ('en'/'ru'/'de') to the switcher label ('EN'/'RU'/'DE'). */
export function labelForCode(code) {
  const entry = Object.entries(SUPPORTED).find(([, c]) => c === code)
  return entry ? entry[0] : 'EN'
}
