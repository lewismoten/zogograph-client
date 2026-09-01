/* Core card package: status and table labels for watched URLs. */
window.ZogographUrlMonitorStatusCard = {
  manifest: Object.freeze({
    id: 'core-url-monitor-status',
    locales: ['core-url-monitor-status.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-url-monitor-status.switched_off': 'Switched off.',
        'core-url-monitor-status.not_checked_yet': 'Not checked yet.',
        'core-url-monitor-status.answered_expected':
          'Answered {{status, number}} as expected.',
        'core-url-monitor-status.answered': 'Answered {{status, number}}.',
        'core-url-monitor-status.no_redirects': 'no redirects',
        'core-url-monitor-status.conditional': 'conditional',
        'core-url-monitor-status.not_encrypted': 'not encrypted',
        'core-url-monitor-status.last_checked': 'Last checked {{ago}}.',
        'core-url-monitor-status.what': 'What',
        'core-url-monitor-status.how_often': 'How often',
        'core-url-monitor-status.last_checked_label': 'Last checked',
        'core-url-monitor-status.never': 'never',
        'core-url-monitor-status.overdue_cron':
          "Due before now. The account's cron may not be running.",
      }),
      es: Object.freeze({
        'core-url-monitor-status.switched_off': 'Desactivado.',
        'core-url-monitor-status.not_checked_yet': 'Aún no se ha comprobado.',
        'core-url-monitor-status.answered_expected':
          'Respondió {{status, number}} como se esperaba.',
        'core-url-monitor-status.answered': 'Respondió {{status, number}}.',
        'core-url-monitor-status.no_redirects': 'sin redirecciones',
        'core-url-monitor-status.conditional': 'condicional',
        'core-url-monitor-status.not_encrypted': 'no cifrada',
        'core-url-monitor-status.last_checked': 'Última comprobación {{ago}}.',
        'core-url-monitor-status.what': 'Qué',
        'core-url-monitor-status.how_often': 'Frecuencia',
        'core-url-monitor-status.last_checked_label': 'Última comprobación',
        'core-url-monitor-status.never': 'nunca',
        'core-url-monitor-status.overdue_cron':
          'Venció antes de ahora. Puede que el cron de la cuenta no esté funcionando.',
      }),
      hi: Object.freeze({
        'core-url-monitor-status.switched_off': 'बंद है।',
        'core-url-monitor-status.not_checked_yet': 'अभी जाँचा नहीं गया।',
        'core-url-monitor-status.answered_expected':
          'अपेक्षा के अनुसार {{status, number}} उत्तर दिया।',
        'core-url-monitor-status.answered': '{{status, number}} उत्तर दिया।',
        'core-url-monitor-status.no_redirects': 'रीडायरेक्ट नहीं',
        'core-url-monitor-status.conditional': 'सशर्त',
        'core-url-monitor-status.not_encrypted': 'एन्क्रिप्टेड नहीं',
        'core-url-monitor-status.last_checked': 'अंतिम जाँच {{ago}}।',
        'core-url-monitor-status.what': 'क्या',
        'core-url-monitor-status.how_often': 'कितनी बार',
        'core-url-monitor-status.last_checked_label': 'अंतिम जाँच',
        'core-url-monitor-status.never': 'कभी नहीं',
        'core-url-monitor-status.overdue_cron':
          'यह अब से पहले देय था। खाते का cron शायद नहीं चल रहा है।',
      }),
      ar: Object.freeze({
        'core-url-monitor-status.switched_off': 'موقوف.',
        'core-url-monitor-status.not_checked_yet': 'لم يُفحص بعد.',
        'core-url-monitor-status.answered_expected':
          'أجاب بـ {{status, number}} كما هو متوقع.',
        'core-url-monitor-status.answered': 'أجاب بـ {{status, number}}.',
        'core-url-monitor-status.no_redirects': 'بدون إعادة توجيه',
        'core-url-monitor-status.conditional': 'مشروط',
        'core-url-monitor-status.not_encrypted': 'غير مشفّر',
        'core-url-monitor-status.last_checked': 'آخر فحص {{ago}}.',
        'core-url-monitor-status.what': 'ما',
        'core-url-monitor-status.how_often': 'عدد المرات',
        'core-url-monitor-status.last_checked_label': 'آخر فحص',
        'core-url-monitor-status.never': 'أبدًا',
        'core-url-monitor-status.overdue_cron':
          'كان موعده قبل الآن. قد لا يكون cron الخاص بالحساب يعمل.',
      }),
      zh: Object.freeze({
        'core-url-monitor-status.switched_off': '已关闭。',
        'core-url-monitor-status.not_checked_yet': '尚未检查。',
        'core-url-monitor-status.answered_expected':
          '按预期返回了 {{status, number}}。',
        'core-url-monitor-status.answered': '返回了 {{status, number}}。',
        'core-url-monitor-status.no_redirects': '不跟随重定向',
        'core-url-monitor-status.conditional': '条件请求',
        'core-url-monitor-status.not_encrypted': '未加密',
        'core-url-monitor-status.last_checked': '上次检查 {{ago}}。',
        'core-url-monitor-status.what': '事项',
        'core-url-monitor-status.how_often': '频率',
        'core-url-monitor-status.last_checked_label': '上次检查',
        'core-url-monitor-status.never': '从未',
        'core-url-monitor-status.overdue_cron':
          '已逾期。该帐户的 cron 可能未运行。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const fallback =
      this.manifest.catalogs.en[`core-url-monitor-status.${key}`];
    return i18n.t(`core-url-monitor-status.${key}`, values, fallback);
  },
};
