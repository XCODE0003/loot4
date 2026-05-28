import { createI18n } from 'vue-i18n'

export const SUPPORTED = { EN: 'en', RU: 'ru', DE: 'de', ES: 'es', NL: 'nl', AR: 'ar', IT: 'it', FR: 'fr' }

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
  es: {
    nav: { home: 'Inicio', chooseGame: 'Elegir Juego', contact: 'Contacto', sell: 'Vender', reviews: 'Reseñas' },
    header: { login: 'Iniciar sesión', logout: 'Cerrar sesión' },
    common: { ourGuarantees: 'Nuestras Garantías' },
    cart: {
      title: 'Carrito', items: '{n} artículos', empty: 'Tu carrito está vacío.',
      totalItems: 'Total artículos', promocode: 'Tengo un código promocional', promocodePlaceholder: 'Código promo',
      apply: 'Aplicar', total: 'Total', checkout: 'Pago seguro',
      applied: 'Código {code} aplicado', invalid: 'Código no válido',
    },
    checkout: {
      title: 'Pago', empty: 'Tu carrito está vacío.', browse: 'Ver juegos',
      summary: 'Resumen del pedido', items: 'Artículos', subtotal: 'Subtotal', discount: 'Descuento', total: 'Total',
      email: 'Email de entrega', method: 'Método de pago', card: 'Tarjeta de crédito / débito', paypal: 'PayPal',
      pay: 'Pagar {total}', processing: 'Redirigiendo al pago…',
      note: 'Protegido por IceNox Pay. Serás redirigido para completar el pago.',
    },
    success: {
      thanks: '¡Gracias! Pedido realizado', order: 'Pedido', sentTo: 'se envió una confirmación a {email}.',
      subtotal: 'Subtotal', discount: 'Descuento', total: 'Total', continue: 'Continuar comprando',
    },
    account: {
      overview: 'Resumen', myOrders: 'Mis Pedidos', profile: 'Perfil', logout: 'Cerrar sesión',
      orders: 'Pedidos', totalSpent: 'Total gastado', pending: 'Pendiente',
      recentOrders: 'Pedidos recientes', viewAll: 'Ver todos', noOrders: 'Sin pedidos aún.', startShopping: 'Empezar a comprar →',
      order: 'Pedido', date: 'Fecha', items: 'Artículos', status: 'Estado', payment: 'Pago', total: 'Total', view: 'Ver',
      noOrdersList: 'No tienes pedidos aún.', browseCatalog: 'Ver catálogo →', back: '← Volver a pedidos',
      details: 'Detalles', delivery: 'Entrega', email: 'Email',
      profileInfo: 'Información del perfil', name: 'Nombre', save: 'Guardar cambios', saved: 'Guardado ✓',
      changePassword: 'Cambiar contraseña', currentPassword: 'Contraseña actual', newPassword: 'Nueva contraseña',
      confirmPassword: 'Confirmar nueva contraseña', updatePassword: 'Actualizar contraseña', updated: 'Actualizado ✓',
    },
    auth: {
      welcomeBack: 'Bienvenido de nuevo', loginSub: 'Inicia sesión en tu cuenta Loot4you',
      createAccount: 'Crear cuenta', registerSub: 'Únete a Loot4you y empieza a jugar',
      email: 'Correo electrónico', password: 'Contraseña', forgot: '¿Olvidaste tu contraseña?', remember: 'Recuérdame',
      login: 'Iniciar sesión', loggingIn: 'Iniciando sesión…', signup: 'Registrarse', noAccount: '¿No tienes cuenta?',
      haveAccount: '¿Ya tienes cuenta?', name: 'Nombre', confirm: 'Confirmar contraseña', creating: 'Creando cuenta…',
      createBtn: 'Crear cuenta',
    },
  },
  nl: {
    nav: { home: 'Startpagina', chooseGame: 'Kies Spel', contact: 'Contact', sell: 'Verkopen', reviews: 'Beoordelingen' },
    header: { login: 'Inloggen', logout: 'Uitloggen' },
    common: { ourGuarantees: 'Onze Garanties' },
    cart: {
      title: 'Winkelwagen', items: '{n} artikel(en)', empty: 'Je winkelwagen is leeg.',
      totalItems: 'Totaal artikelen', promocode: 'Ik heb een promotiecode', promocodePlaceholder: 'Promotiecode',
      apply: 'Toepassen', total: 'Totaal', checkout: 'Veilig afrekenen',
      applied: 'Code {code} toegepast', invalid: 'Ongeldige promotiecode',
    },
    checkout: {
      title: 'Afrekenen', empty: 'Je winkelwagen is leeg.', browse: 'Spellen bekijken',
      summary: 'Besteloverzicht', items: 'Artikelen', subtotal: 'Subtotaal', discount: 'Korting', total: 'Totaal',
      email: 'E-mail voor levering', method: 'Betaalmethode', card: 'Creditcard / Debitcard', paypal: 'PayPal',
      pay: '{total} betalen', processing: 'Doorsturen naar betaling…',
      note: 'Beveiligd door IceNox Pay. Je wordt doorgestuurd om de betaling te voltooien.',
    },
    success: {
      thanks: 'Bedankt! Bestelling geplaatst', order: 'Bestelling', sentTo: 'een bevestiging is verstuurd naar {email}.',
      subtotal: 'Subtotaal', discount: 'Korting', total: 'Totaal', continue: 'Doorgaan met winkelen',
    },
    account: {
      overview: 'Overzicht', myOrders: 'Mijn Bestellingen', profile: 'Profiel', logout: 'Uitloggen',
      orders: 'Bestellingen', totalSpent: 'Totaal uitgegeven', pending: 'In afwachting',
      recentOrders: 'Recente bestellingen', viewAll: 'Alle', noOrders: 'Nog geen bestellingen.', startShopping: 'Begin met winkelen →',
      order: 'Bestelling', date: 'Datum', items: 'Artikelen', status: 'Status', payment: 'Betaling', total: 'Totaal', view: 'Bekijken',
      noOrdersList: 'Je hebt nog geen bestellingen.', browseCatalog: 'Catalogus bekijken →', back: '← Terug naar bestellingen',
      details: 'Details', delivery: 'Levering', email: 'E-mail',
      profileInfo: 'Profielinformatie', name: 'Naam', save: 'Wijzigingen opslaan', saved: 'Opgeslagen ✓',
      changePassword: 'Wachtwoord wijzigen', currentPassword: 'Huidig wachtwoord', newPassword: 'Nieuw wachtwoord',
      confirmPassword: 'Nieuw wachtwoord bevestigen', updatePassword: 'Wachtwoord bijwerken', updated: 'Bijgewerkt ✓',
    },
    auth: {
      welcomeBack: 'Welkom terug', loginSub: 'Log in op je Loot4you-account',
      createAccount: 'Account aanmaken', registerSub: 'Word lid van Loot4you',
      email: 'E-mailadres', password: 'Wachtwoord', forgot: 'Wachtwoord vergeten?', remember: 'Onthoud mij',
      login: 'Inloggen', loggingIn: 'Inloggen…', signup: 'Registreren', noAccount: 'Nog geen account?',
      haveAccount: 'Al een account?', name: 'Naam', confirm: 'Wachtwoord bevestigen', creating: 'Account aanmaken…',
      createBtn: 'Account aanmaken',
    },
  },
  ar: {
    nav: { home: 'الرئيسية', chooseGame: 'اختر لعبة', contact: 'اتصل بنا', sell: 'بيع لنا', reviews: 'التقييمات' },
    header: { login: 'تسجيل الدخول', logout: 'تسجيل الخروج' },
    common: { ourGuarantees: 'ضماناتنا' },
    cart: {
      title: 'سلة التسوق', items: '{n} عناصر', empty: 'سلة التسوق فارغة.',
      totalItems: 'إجمالي العناصر', promocode: 'لدي رمز ترويجي', promocodePlaceholder: 'الرمز الترويجي',
      apply: 'تطبيق', total: 'المجموع', checkout: 'الدفع الآمن',
      applied: 'تم تطبيق الرمز {code}', invalid: 'رمز ترويجي غير صالح',
    },
    checkout: {
      title: 'الدفع', empty: 'سلة التسوق فارغة.', browse: 'تصفح الألعاب',
      summary: 'ملخص الطلب', items: 'العناصر', subtotal: 'المجموع الفرعي', discount: 'الخصم', total: 'المجموع',
      email: 'البريد الإلكتروني للتسليم', method: 'طريقة الدفع', card: 'بطاقة ائتمان / خصم', paypal: 'PayPal',
      pay: 'دفع {total}', processing: 'جارٍ التوجيه للدفع…',
      note: 'مؤمّن بواسطة IceNox Pay. ستُوجَّه لإتمام الدفع.',
    },
    success: {
      thanks: 'شكراً! تم تقديم الطلب', order: 'الطلب', sentTo: 'تم إرسال تأكيد إلى {email}.',
      subtotal: 'المجموع الفرعي', discount: 'الخصم', total: 'المجموع', continue: 'مواصلة التسوق',
    },
    account: {
      overview: 'نظرة عامة', myOrders: 'طلباتي', profile: 'الملف الشخصي', logout: 'تسجيل الخروج',
      orders: 'الطلبات', totalSpent: 'إجمالي الإنفاق', pending: 'قيد الانتظار',
      recentOrders: 'الطلبات الأخيرة', viewAll: 'عرض الكل', noOrders: 'لا طلبات بعد.', startShopping: 'ابدأ التسوق →',
      order: 'الطلب', date: 'التاريخ', items: 'العناصر', status: 'الحالة', payment: 'الدفع', total: 'المجموع', view: 'عرض',
      noOrdersList: 'ليس لديك طلبات بعد.', browseCatalog: 'تصفح الكتالوج →', back: '→ العودة إلى الطلبات',
      details: 'التفاصيل', delivery: 'التسليم', email: 'البريد الإلكتروني',
      profileInfo: 'معلومات الملف الشخصي', name: 'الاسم', save: 'حفظ التغييرات', saved: 'تم الحفظ ✓',
      changePassword: 'تغيير كلمة المرور', currentPassword: 'كلمة المرور الحالية', newPassword: 'كلمة المرور الجديدة',
      confirmPassword: 'تأكيد كلمة المرور الجديدة', updatePassword: 'تحديث كلمة المرور', updated: 'تم التحديث ✓',
    },
    auth: {
      welcomeBack: 'مرحباً بعودتك', loginSub: 'سجّل الدخول إلى حساب Loot4you',
      createAccount: 'إنشاء حساب', registerSub: 'انضم إلى Loot4you وابدأ اللعب',
      email: 'عنوان البريد الإلكتروني', password: 'كلمة المرور', forgot: 'نسيت كلمة المرور؟', remember: 'تذكرني',
      login: 'تسجيل الدخول', loggingIn: 'جارٍ الدخول…', signup: 'التسجيل', noAccount: 'ليس لديك حساب؟',
      haveAccount: 'لديك حساب بالفعل؟', name: 'الاسم', confirm: 'تأكيد كلمة المرور', creating: 'جارٍ الإنشاء…',
      createBtn: 'إنشاء حساب',
    },
  },
  it: {
    nav: { home: 'Home', chooseGame: 'Scegli Gioco', contact: 'Contatti', sell: 'Vendi a Noi', reviews: 'Recensioni' },
    header: { login: 'Accedi', logout: 'Esci' },
    common: { ourGuarantees: 'Le Nostre Garanzie' },
    cart: {
      title: 'Carrello', items: '{n} articoli', empty: 'Il tuo carrello è vuoto.',
      totalItems: 'Totale articoli', promocode: 'Ho un codice promozionale', promocodePlaceholder: 'Codice promo',
      apply: 'Applica', total: 'Totale', checkout: 'Pagamento sicuro',
      applied: 'Codice {code} applicato', invalid: 'Codice non valido',
    },
    checkout: {
      title: 'Pagamento', empty: 'Il tuo carrello è vuoto.', browse: 'Sfoglia giochi',
      summary: 'Riepilogo ordine', items: 'Articoli', subtotal: 'Subtotale', discount: 'Sconto', total: 'Totale',
      email: 'Email per la consegna', method: 'Metodo di pagamento', card: 'Carta di credito / debito', paypal: 'PayPal',
      pay: 'Paga {total}', processing: 'Reindirizzamento al pagamento…',
      note: 'Protetto da IceNox Pay. Sarai reindirizzato per completare il pagamento.',
    },
    success: {
      thanks: 'Grazie! Ordine effettuato', order: 'Ordine', sentTo: 'una conferma è stata inviata a {email}.',
      subtotal: 'Subtotale', discount: 'Sconto', total: 'Totale', continue: 'Continua gli acquisti',
    },
    account: {
      overview: 'Panoramica', myOrders: 'I Miei Ordini', profile: 'Profilo', logout: 'Esci',
      orders: 'Ordini', totalSpent: 'Totale speso', pending: 'In attesa',
      recentOrders: 'Ordini recenti', viewAll: 'Vedi tutti', noOrders: 'Nessun ordine ancora.', startShopping: 'Inizia gli acquisti →',
      order: 'Ordine', date: 'Data', items: 'Articoli', status: 'Stato', payment: 'Pagamento', total: 'Totale', view: 'Visualizza',
      noOrdersList: 'Non hai ancora ordini.', browseCatalog: 'Sfoglia il catalogo →', back: '← Torna agli ordini',
      details: 'Dettagli', delivery: 'Consegna', email: 'Email',
      profileInfo: 'Informazioni profilo', name: 'Nome', save: 'Salva modifiche', saved: 'Salvato ✓',
      changePassword: 'Cambia password', currentPassword: 'Password attuale', newPassword: 'Nuova password',
      confirmPassword: 'Conferma nuova password', updatePassword: 'Aggiorna password', updated: 'Aggiornato ✓',
    },
    auth: {
      welcomeBack: 'Bentornato', loginSub: 'Accedi al tuo account Loot4you',
      createAccount: 'Crea account', registerSub: 'Unisciti a Loot4you e inizia a giocare',
      email: 'Indirizzo email', password: 'Password', forgot: 'Password dimenticata?', remember: 'Ricordami',
      login: 'Accedi', loggingIn: 'Accesso…', signup: 'Registrati', noAccount: 'Non hai un account?',
      haveAccount: 'Hai già un account?', name: 'Nome', confirm: 'Conferma password', creating: 'Creazione…',
      createBtn: 'Crea account',
    },
  },
  fr: {
    nav: { home: 'Accueil', chooseGame: 'Choisir un jeu', contact: 'Contact', sell: 'Vendre', reviews: 'Avis' },
    header: { login: 'Se connecter', logout: 'Se déconnecter' },
    common: { ourGuarantees: 'Nos Garanties' },
    cart: {
      title: 'Panier', items: '{n} articles', empty: 'Votre panier est vide.',
      totalItems: 'Total articles', promocode: "J'ai un code promo", promocodePlaceholder: 'Code promo',
      apply: 'Appliquer', total: 'Total', checkout: 'Paiement sécurisé',
      applied: 'Code {code} appliqué', invalid: 'Code promo invalide',
    },
    checkout: {
      title: 'Paiement', empty: 'Votre panier est vide.', browse: 'Voir les jeux',
      summary: 'Récapitulatif', items: 'Articles', subtotal: 'Sous-total', discount: 'Remise', total: 'Total',
      email: 'Email de livraison', method: 'Mode de paiement', card: 'Carte bancaire', paypal: 'PayPal',
      pay: 'Payer {total}', processing: 'Redirection vers le paiement…',
      note: 'Sécurisé par IceNox Pay. Vous serez redirigé pour finaliser le paiement.',
    },
    success: {
      thanks: 'Merci ! Commande passée', order: 'Commande', sentTo: 'une confirmation a été envoyée à {email}.',
      subtotal: 'Sous-total', discount: 'Remise', total: 'Total', continue: 'Continuer mes achats',
    },
    account: {
      overview: 'Aperçu', myOrders: 'Mes Commandes', profile: 'Profil', logout: 'Se déconnecter',
      orders: 'Commandes', totalSpent: 'Total dépensé', pending: 'En attente',
      recentOrders: 'Commandes récentes', viewAll: 'Tout voir', noOrders: 'Aucune commande.', startShopping: 'Commencer →',
      order: 'Commande', date: 'Date', items: 'Articles', status: 'Statut', payment: 'Paiement', total: 'Total', view: 'Voir',
      noOrdersList: "Vous n'avez pas encore de commandes.", browseCatalog: 'Voir le catalogue →', back: '← Retour aux commandes',
      details: 'Détails', delivery: 'Livraison', email: 'Email',
      profileInfo: 'Informations du profil', name: 'Nom', save: 'Enregistrer', saved: 'Enregistré ✓',
      changePassword: 'Changer le mot de passe', currentPassword: 'Mot de passe actuel', newPassword: 'Nouveau mot de passe',
      confirmPassword: 'Confirmer le mot de passe', updatePassword: 'Mettre à jour', updated: 'Mis à jour ✓',
    },
    auth: {
      welcomeBack: 'Bon retour', loginSub: 'Connectez-vous à votre compte Loot4you',
      createAccount: 'Créer un compte', registerSub: 'Rejoignez Loot4you',
      email: 'Adresse e-mail', password: 'Mot de passe', forgot: 'Mot de passe oublié ?', remember: 'Se souvenir de moi',
      login: 'Se connecter', loggingIn: 'Connexion…', signup: "S'inscrire", noAccount: 'Pas de compte ?',
      haveAccount: 'Déjà un compte ?', name: 'Nom', confirm: 'Confirmer le mot de passe', creating: 'Création…',
      createBtn: 'Créer un compte',
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

/** Map a stored locale code ('en'/'ru'/…) to the switcher label ('EN'/'RU'/…). */
export function labelForCode(code) {
  const entry = Object.entries(SUPPORTED).find(([, c]) => c === code)
  return entry ? entry[0] : 'EN'
}
