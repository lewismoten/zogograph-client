/* Core card package: retention-row clearing dialog copy. */
window.ZogographStoragePurgeCard = {
  manifest: Object.freeze({
    id: 'core-storage-purge',
    locales: ['core-storage-purge.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-purge.button_title':
          'Delete {{label}} older than a given age',
        'core-storage-purge.title': 'Clear old records',
        'core-storage-purge.age_in_days': 'Age in days',
        'core-storage-purge.invalid_days': 'That is not a number of days.',
        'core-storage-purge.zero_help': '0 deletes all {{label}}.{{protected}}',
        'core-storage-purge.protected_help':
          ' Readings near an alert are always kept.',
        'core-storage-purge.confirm':
          'Delete every one of {{label}}?\n\n{{why}}\n\nThis cannot be undone.',
        'core-storage-purge.removed':
          '{{count}} removed from {{label}}.{{protected}}',
        'core-storage-purge.protected':
          ' {{count}} kept because they sit beside an alert.',
      }),
      es: Object.freeze({
        'core-storage-purge.button_title':
          'Eliminar {{label}} anterior a una edad determinada',
        'core-storage-purge.title': 'Borrar registros antiguos',
        'core-storage-purge.age_in_days': 'Antigüedad en días',
        'core-storage-purge.invalid_days': 'No es una cantidad de días.',
        'core-storage-purge.zero_help':
          '0 elimina todos los {{label}}.{{protected}}',
        'core-storage-purge.protected_help':
          ' Las lecturas cercanas a una alerta siempre se conservan.',
        'core-storage-purge.confirm':
          '¿Eliminar todos los {{label}}?\n\n{{why}}\n\nEsto no se puede deshacer.',
        'core-storage-purge.removed':
          'Se eliminaron {{count}} de {{label}}.{{protected}}',
        'core-storage-purge.protected':
          ' Se conservaron {{count}} porque están junto a una alerta.',
      }),
      hi: Object.freeze({
        'core-storage-purge.button_title':
          'दिए गए समय से पुराने {{label}} हटाएँ',
        'core-storage-purge.title': 'पुराने रिकॉर्ड साफ़ करें',
        'core-storage-purge.age_in_days': 'दिनों में आयु',
        'core-storage-purge.invalid_days': 'यह दिनों की संख्या नहीं है।',
        'core-storage-purge.zero_help':
          '0 सभी {{label}} हटाता है।{{protected}}',
        'core-storage-purge.protected_help':
          ' अलर्ट के पास की रीडिंग हमेशा रखी जाती हैं।',
        'core-storage-purge.confirm':
          'हर {{label}} हटाएँ?\n\n{{why}}\n\nइसे पूर्ववत नहीं किया जा सकता।',
        'core-storage-purge.removed':
          '{{label}} से {{count}} हटाए गए।{{protected}}',
        'core-storage-purge.protected':
          ' {{count}} रखे गए क्योंकि वे अलर्ट के पास हैं।',
      }),
      ar: Object.freeze({
        'core-storage-purge.button_title': 'احذف {{label}} الأقدم من عمر محدد',
        'core-storage-purge.title': 'امسح السجلات القديمة',
        'core-storage-purge.age_in_days': 'العمر بالأيام',
        'core-storage-purge.invalid_days': 'هذه ليست عددًا من الأيام.',
        'core-storage-purge.zero_help': '0 يحذف كل {{label}}.{{protected}}',
        'core-storage-purge.protected_help':
          ' تُحتفظ دائمًا بالقراءات القريبة من تنبيه.',
        'core-storage-purge.confirm':
          'هل تريد حذف كل {{label}}؟\n\n{{why}}\n\nلا يمكن التراجع عن ذلك.',
        'core-storage-purge.removed':
          'أُزيل {{count}} من {{label}}.{{protected}}',
        'core-storage-purge.protected':
          ' احتُفظ بـ {{count}} لأنها بجانب تنبيه.',
      }),
      zh: Object.freeze({
        'core-storage-purge.button_title': '删除早于指定期限的 {{label}}',
        'core-storage-purge.title': '清除旧记录',
        'core-storage-purge.age_in_days': '天数',
        'core-storage-purge.invalid_days': '这不是有效的天数。',
        'core-storage-purge.zero_help': '0 会删除所有 {{label}}。{{protected}}',
        'core-storage-purge.protected_help': ' 警报附近的读数始终会保留。',
        'core-storage-purge.confirm':
          '删除所有 {{label}}？\n\n{{why}}\n\n此操作无法撤销。',
        'core-storage-purge.removed':
          '已从 {{label}} 中删除 {{count}} 项。{{protected}}',
        'core-storage-purge.protected':
          ' 已保留 {{count}} 项，因为它们位于警报附近。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const fallback = this.manifest.catalogs.en[`core-storage-purge.${key}`];
    return i18n.t(`core-storage-purge.${key}`, values, fallback);
  },
};
