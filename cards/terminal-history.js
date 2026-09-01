/* Core card package: headings for the terminal command-history table. */
window.ZogographTerminalHistoryCard = {
  manifest: Object.freeze({
    id: 'core-terminal-history',
    locales: ['core-terminal-history.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-terminal-history.command': 'Command',
        'core-terminal-history.when': 'When',
        'core-terminal-history.output': 'Output',
        'core-terminal-history.took': 'Took',
        'core-terminal-history.how_it_ended': 'How it ended',
      }),
      es: Object.freeze({
        'core-terminal-history.command': 'Comando',
        'core-terminal-history.when': 'Cuándo',
        'core-terminal-history.output': 'Salida',
        'core-terminal-history.took': 'Duró',
        'core-terminal-history.how_it_ended': 'Cómo terminó',
      }),
      hi: Object.freeze({
        'core-terminal-history.command': 'कमांड',
        'core-terminal-history.when': 'कब',
        'core-terminal-history.output': 'आउटपुट',
        'core-terminal-history.took': 'लगा',
        'core-terminal-history.how_it_ended': 'कैसे समाप्त हुआ',
      }),
      ar: Object.freeze({
        'core-terminal-history.command': 'الأمر',
        'core-terminal-history.when': 'متى',
        'core-terminal-history.output': 'المخرجات',
        'core-terminal-history.took': 'استغرق',
        'core-terminal-history.how_it_ended': 'كيف انتهى',
      }),
      zh: Object.freeze({
        'core-terminal-history.command': '命令',
        'core-terminal-history.when': '时间',
        'core-terminal-history.output': '输出',
        'core-terminal-history.took': '耗时',
        'core-terminal-history.how_it_ended': '结束方式',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-terminal-history.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
