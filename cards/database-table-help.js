/* Core card package: explanatory metadata for unlisted SQLite tables. */
window.ZogographDatabaseTableHelpCard = {
  manifest: Object.freeze({
    id: 'core-database-table-help',
    locales: ['core-database-table-help.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-database-table-help.unknown_table_what':
          'A {{collector}} SQLite table retained for this installation.',
        'core-database-table-help.host_collector_data': 'Host collector data',
        'core-database-table-help.forensic_views':
          'Database and forensic views',
        'core-database-table-help.dashboard_collector':
          'maintained by the dashboard collector',
        'core-database-table-help.table_metadata':
          'Used by: {{where}}. Collected by: {{collected}}. Inspect: sqlite3 <database> "SELECT COUNT(*) FROM {{name}};"',
      }),
      es: Object.freeze({
        'core-database-table-help.unknown_table_what':
          'Una tabla SQLite de {{collector}} conservada para esta instalación.',
        'core-database-table-help.host_collector_data':
          'Datos del recopilador del host',
        'core-database-table-help.forensic_views':
          'Base de datos y vistas forenses',
        'core-database-table-help.dashboard_collector':
          'mantenida por el recopilador del panel',
        'core-database-table-help.table_metadata':
          'Usado por: {{where}}. Recopilado por: {{collected}}. Inspeccione: sqlite3 <database> "SELECT COUNT(*) FROM {{name}};"',
      }),
      hi: Object.freeze({
        'core-database-table-help.unknown_table_what':
          'इस इंस्टॉलेशन के लिए रखा गया {{collector}} SQLite तालिका।',
        'core-database-table-help.host_collector_data': 'होस्ट कलेक्टर डेटा',
        'core-database-table-help.forensic_views': 'डेटाबेस और फोरेंसिक दृश्य',
        'core-database-table-help.dashboard_collector':
          'डैशबोर्ड कलेक्टर द्वारा रखा गया',
        'core-database-table-help.table_metadata':
          'इसका उपयोग: {{where}}। संग्रह: {{collected}}। निरीक्षण करें: sqlite3 <database> "SELECT COUNT(*) FROM {{name}};"',
      }),
      ar: Object.freeze({
        'core-database-table-help.unknown_table_what':
          'جدول SQLite تابع لـ {{collector}} محفوظ لهذا التثبيت.',
        'core-database-table-help.host_collector_data': 'بيانات جامع المضيف',
        'core-database-table-help.forensic_views':
          'قاعدة البيانات والعروض الجنائية',
        'core-database-table-help.dashboard_collector':
          'تديره أداة جمع لوحة المعلومات',
        'core-database-table-help.table_metadata':
          'يُستخدم بواسطة: {{where}}. جُمع بواسطة: {{collected}}. افحص: sqlite3 <database> "SELECT COUNT(*) FROM {{name}};"',
      }),
      zh: Object.freeze({
        'core-database-table-help.unknown_table_what':
          '为此安装保留的 {{collector}} SQLite 表。',
        'core-database-table-help.host_collector_data': '主机收集器数据',
        'core-database-table-help.forensic_views': '数据库和取证视图',
        'core-database-table-help.dashboard_collector': '由仪表板收集器维护',
        'core-database-table-help.table_metadata':
          '用途：{{where}}。收集方式：{{collected}}。检查：sqlite3 <database> "SELECT COUNT(*) FROM {{name}};"',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-database-table-help.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
