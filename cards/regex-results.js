/* Core card package: regex tester match navigation and capture selection. */
window.ZogographRegexResultsCard = {
  manifest: Object.freeze({
    id: 'core-regex-results',
    locales: ['core-regex-results.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-regex-results.previous': '‹ Previous',
        'core-regex-results.next': 'Next ›',
        'core-regex-results.match_position':
          'Match {{current, number}} of {{total, number}}',
        'core-regex-results.keep_group': 'Keep this one',
        'core-regex-results.record_group':
          'Record group {{group, number}} on every run',
        'core-regex-results.group_recorded':
          'This check will record group {{group, number}}.',
        'core-regex-results.whole_match': 'whole match',
        'core-regex-results.group_absent': '— did not take part in this match',
        'core-regex-results.group': 'Group',
        'core-regex-results.took': 'Took',
        'core-regex-results.group_absence_help':
          'A group that did not take part is not the same as one that matched nothing — an optional group is simply absent, and the check would record nothing on that run.',
      }),
      es: Object.freeze({
        'core-regex-results.previous': '‹ Anterior',
        'core-regex-results.next': 'Siguiente ›',
        'core-regex-results.match_position':
          'Coincidencia {{current, number}} de {{total, number}}',
        'core-regex-results.keep_group': 'Conservar este',
        'core-regex-results.record_group':
          'Registrar el grupo {{group, number}} en cada ejecución',
        'core-regex-results.group_recorded':
          'Esta comprobación registrará el grupo {{group, number}}.',
        'core-regex-results.whole_match': 'coincidencia completa',
        'core-regex-results.group_absent':
          '— no participó en esta coincidencia',
        'core-regex-results.group': 'Grupo',
        'core-regex-results.took': 'Tomó',
        'core-regex-results.group_absence_help':
          'Un grupo que no participó no es lo mismo que uno que no coincidió con nada: un grupo opcional simplemente está ausente, y la comprobación no registraría nada en esa ejecución.',
      }),
      hi: Object.freeze({
        'core-regex-results.previous': '‹ पिछला',
        'core-regex-results.next': 'अगला ›',
        'core-regex-results.match_position':
          'मिलान {{current, number}} / {{total, number}}',
        'core-regex-results.keep_group': 'इसे रखें',
        'core-regex-results.record_group':
          'हर रन में समूह {{group, number}} रिकॉर्ड करें',
        'core-regex-results.group_recorded':
          'यह जाँच समूह {{group, number}} रिकॉर्ड करेगी।',
        'core-regex-results.whole_match': 'पूरा मिलान',
        'core-regex-results.group_absent': '— इस मिलान में शामिल नहीं था',
        'core-regex-results.group': 'समूह',
        'core-regex-results.took': 'लिया',
        'core-regex-results.group_absence_help':
          'जिस समूह ने भाग नहीं लिया वह उस समूह जैसा नहीं है जो किसी चीज़ से मेल नहीं खाया — वैकल्पिक समूह बस अनुपस्थित होता है और जांच उस रन पर कुछ भी रिकॉर्ड नहीं करेगी।',
      }),
      ar: Object.freeze({
        'core-regex-results.previous': '‹ السابق',
        'core-regex-results.next': 'التالي ›',
        'core-regex-results.match_position':
          'المطابقة {{current, number}} من {{total, number}}',
        'core-regex-results.keep_group': 'احتفظ بهذا',
        'core-regex-results.record_group':
          'سجّل المجموعة {{group, number}} في كل تشغيل',
        'core-regex-results.group_recorded':
          'سيسجل هذا الفحص المجموعة {{group, number}}.',
        'core-regex-results.whole_match': 'المطابقة كاملة',
        'core-regex-results.group_absent': '— لم يشارك في هذه المطابقة',
        'core-regex-results.group': 'المجموعة',
        'core-regex-results.took': 'التقط',
        'core-regex-results.group_absence_help':
          'المجموعة التي لم تشارك ليست كالمجموعة التي لم تطابق شيئًا — فالمجموعة الاختيارية تكون غائبة ببساطة، ولن يسجل الفحص شيئًا في ذلك التشغيل.',
      }),
      zh: Object.freeze({
        'core-regex-results.previous': '‹ 上一个',
        'core-regex-results.next': '下一个 ›',
        'core-regex-results.match_position':
          '匹配 {{current, number}} / {{total, number}}',
        'core-regex-results.keep_group': '保留此项',
        'core-regex-results.record_group':
          '每次运行都记录第 {{group, number}} 组',
        'core-regex-results.group_recorded':
          '此检查将记录第 {{group, number}} 组。',
        'core-regex-results.whole_match': '完整匹配',
        'core-regex-results.group_absent': '— 未参与此匹配',
        'core-regex-results.group': '分组',
        'core-regex-results.took': '捕获内容',
        'core-regex-results.group_absence_help':
          '未参与的分组与没有匹配任何内容的分组不同：可选分组只是不存在，因此该次检查不会记录任何内容。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-regex-results.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
