window.ZogographActionLogTableCard = {
  manifest: Object.freeze({
    id: 'core-action-log-table',
    locales: ['core-action-log-table.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-action-log-table.when': 'When',
        'core-action-log-table.what': 'What',
        'core-action-log-table.target': 'Target',
        'core-action-log-table.outcome': 'Outcome',
        'core-action-log-table.freed': 'Freed',
        'core-action-log-table.actor': 'Actor',
        'core-action-log-table.from': 'From',
      }),
      es: Object.freeze({
        'core-action-log-table.when': 'Cuándo',
        'core-action-log-table.what': 'Qué',
        'core-action-log-table.target': 'Destino',
        'core-action-log-table.outcome': 'Resultado',
        'core-action-log-table.freed': 'Liberado',
        'core-action-log-table.actor': 'Actor',
        'core-action-log-table.from': 'Desde',
      }),
      hi: Object.freeze({
        'core-action-log-table.when': 'कब',
        'core-action-log-table.what': 'क्या',
        'core-action-log-table.target': 'लक्ष्य',
        'core-action-log-table.outcome': 'परिणाम',
        'core-action-log-table.freed': 'खाली किया',
        'core-action-log-table.actor': 'कर्ता',
        'core-action-log-table.from': 'से',
      }),
      ar: Object.freeze({
        'core-action-log-table.when': 'متى',
        'core-action-log-table.what': 'ما',
        'core-action-log-table.target': 'الهدف',
        'core-action-log-table.outcome': 'النتيجة',
        'core-action-log-table.freed': 'المحرر',
        'core-action-log-table.actor': 'الفاعل',
        'core-action-log-table.from': 'من',
      }),
      zh: Object.freeze({
        'core-action-log-table.when': '时间',
        'core-action-log-table.what': '内容',
        'core-action-log-table.target': '目标',
        'core-action-log-table.outcome': '结果',
        'core-action-log-table.freed': '已释放',
        'core-action-log-table.actor': '执行者',
        'core-action-log-table.from': '来自',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-action-log-table.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
