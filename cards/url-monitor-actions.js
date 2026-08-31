/* Core card package: actions for a watched URL row. */
window.ZogographUrlMonitorActionsCard = {
  manifest: Object.freeze({
    id: 'core-url-monitor-actions',
    locales: ['core-url-monitor-actions.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-url-monitor-actions.history': 'History',
        'core-url-monitor-actions.history_help':
          "Show this check's history below",
        'core-url-monitor-actions.check': 'Check',
        'core-url-monitor-actions.check_help': 'Run it now',
        'core-url-monitor-actions.checked_now':
          '{{label}}: answered {{status, number}} in {{ms, number}} ms.',
        'core-url-monitor-actions.check_failed': '{{label}}: {{detail}}.',
        'core-url-monitor-actions.pause': 'Pause',
        'core-url-monitor-actions.pause_help':
          'Stop checking, keeping the history',
        'core-url-monitor-actions.resume': 'Resume',
        'core-url-monitor-actions.resume_help': 'Start checking again',
        'core-url-monitor-actions.edit': 'Edit',
        'core-url-monitor-actions.edit_help': 'Change how it is checked',
        'core-url-monitor-actions.remove': 'Remove',
        'core-url-monitor-actions.remove_help':
          'Stop watching it and discard its history',
        'core-url-monitor-actions.remove_confirmation':
          'Stop watching {{label}}? Its history goes with it.',
        'core-url-monitor-actions.remove_title': 'Stop watching',
      }),
      es: Object.freeze({
        'core-url-monitor-actions.history': 'Historial',
        'core-url-monitor-actions.history_help':
          'Mostrar abajo el historial de esta comprobación',
        'core-url-monitor-actions.check': 'Comprobar',
        'core-url-monitor-actions.check_help': 'Ejecutarla ahora',
        'core-url-monitor-actions.checked_now':
          '{{label}}: respondió {{status, number}} en {{ms, number}} ms.',
        'core-url-monitor-actions.check_failed': '{{label}}: {{detail}}.',
        'core-url-monitor-actions.pause': 'Pausar',
        'core-url-monitor-actions.pause_help':
          'Dejar de comprobar y conservar el historial',
        'core-url-monitor-actions.resume': 'Reanudar',
        'core-url-monitor-actions.resume_help': 'Empezar a comprobar de nuevo',
        'core-url-monitor-actions.edit': 'Editar',
        'core-url-monitor-actions.edit_help': 'Cambiar cómo se comprueba',
        'core-url-monitor-actions.remove': 'Quitar',
        'core-url-monitor-actions.remove_help':
          'Dejar de vigilarla y descartar su historial',
        'core-url-monitor-actions.remove_confirmation':
          '¿Dejar de vigilar {{label}}? Su historial se eliminará también.',
        'core-url-monitor-actions.remove_title': 'Dejar de vigilar',
      }),
      hi: Object.freeze({
        'core-url-monitor-actions.history': 'इतिहास',
        'core-url-monitor-actions.history_help':
          'इस जाँच का इतिहास नीचे दिखाएँ',
        'core-url-monitor-actions.check': 'जाँचें',
        'core-url-monitor-actions.check_help': 'इसे अभी चलाएँ',
        'core-url-monitor-actions.checked_now':
          '{{label}}: {{status, number}} ने {{ms, number}} ms में उत्तर दिया।',
        'core-url-monitor-actions.check_failed': '{{label}}: {{detail}}।',
        'core-url-monitor-actions.pause': 'रोकें',
        'core-url-monitor-actions.pause_help':
          'इतिहास रखते हुए जाँचना बंद करें',
        'core-url-monitor-actions.resume': 'फिर शुरू करें',
        'core-url-monitor-actions.resume_help': 'फिर से जाँचना शुरू करें',
        'core-url-monitor-actions.edit': 'संपादित करें',
        'core-url-monitor-actions.edit_help': 'यह कैसे जाँचा जाता है बदलें',
        'core-url-monitor-actions.remove': 'हटाएँ',
        'core-url-monitor-actions.remove_help':
          'इसे देखना बंद करें और इसका इतिहास हटाएँ',
        'core-url-monitor-actions.remove_confirmation':
          '{{label}} को देखना बंद करें? इसका इतिहास भी हट जाएगा।',
        'core-url-monitor-actions.remove_title': 'देखना बंद करें',
      }),
      ar: Object.freeze({
        'core-url-monitor-actions.history': 'السجل',
        'core-url-monitor-actions.history_help': 'اعرض سجل هذا الفحص أدناه',
        'core-url-monitor-actions.check': 'افحص',
        'core-url-monitor-actions.check_help': 'شغّله الآن',
        'core-url-monitor-actions.checked_now':
          '{{label}}: أجاب بـ {{status, number}} خلال {{ms, number}} ms.',
        'core-url-monitor-actions.check_failed': '{{label}}: {{detail}}.',
        'core-url-monitor-actions.pause': 'أوقف مؤقتًا',
        'core-url-monitor-actions.pause_help': 'أوقف الفحص مع الاحتفاظ بالسجل',
        'core-url-monitor-actions.resume': 'استأنف',
        'core-url-monitor-actions.resume_help': 'ابدأ الفحص مجددًا',
        'core-url-monitor-actions.edit': 'حرّر',
        'core-url-monitor-actions.edit_help': 'غيّر كيفية فحصه',
        'core-url-monitor-actions.remove': 'أزل',
        'core-url-monitor-actions.remove_help': 'توقف عن مراقبته واحذف سجله',
        'core-url-monitor-actions.remove_confirmation':
          'هل تتوقف عن مراقبة {{label}}؟ سيُحذف سجله أيضًا.',
        'core-url-monitor-actions.remove_title': 'أوقف المراقبة',
      }),
      zh: Object.freeze({
        'core-url-monitor-actions.history': '历史记录',
        'core-url-monitor-actions.history_help': '在下方显示此检查的历史记录',
        'core-url-monitor-actions.check': '检查',
        'core-url-monitor-actions.check_help': '立即运行',
        'core-url-monitor-actions.checked_now':
          '{{label}}：在 {{ms, number}} 毫秒内返回 {{status, number}}。',
        'core-url-monitor-actions.check_failed': '{{label}}：{{detail}}。',
        'core-url-monitor-actions.pause': '暂停',
        'core-url-monitor-actions.pause_help': '停止检查，但保留历史记录',
        'core-url-monitor-actions.resume': '恢复',
        'core-url-monitor-actions.resume_help': '重新开始检查',
        'core-url-monitor-actions.edit': '编辑',
        'core-url-monitor-actions.edit_help': '更改检查方式',
        'core-url-monitor-actions.remove': '移除',
        'core-url-monitor-actions.remove_help': '停止监视并删除其历史记录',
        'core-url-monitor-actions.remove_confirmation':
          '停止监视 {{label}}？其历史记录也会被删除。',
        'core-url-monitor-actions.remove_title': '停止监视',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const fallback =
      this.manifest.catalogs.en[`core-url-monitor-actions.${key}`];
    return i18n.t(`core-url-monitor-actions.${key}`, values, fallback);
  },
};
