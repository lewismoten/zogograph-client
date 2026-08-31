/* Core card package: offender-list size change descriptions. */
window.ZogographStorageGrowthStatusCard = {
  manifest: Object.freeze({
    id: 'core-storage-growth-status',
    locales: ['core-storage-growth-status.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-growth-status.none':
          'No measurable change since the previous scan.',
        'core-storage-growth-status.grew':
          'Grew by {{bytes}} since the previous scan — this is taking space.',
        'core-storage-growth-status.shrank':
          'Shrank by {{bytes}} since the previous scan — this gave space back.',
      }),
      es: Object.freeze({
        'core-storage-growth-status.none':
          'No hay cambios medibles desde el análisis anterior.',
        'core-storage-growth-status.grew':
          'Creció {{bytes}} desde el análisis anterior; está ocupando espacio.',
        'core-storage-growth-status.shrank':
          'Se redujo {{bytes}} desde el análisis anterior; devolvió espacio.',
      }),
      hi: Object.freeze({
        'core-storage-growth-status.none':
          'पिछले स्कैन के बाद कोई मापने योग्य बदलाव नहीं है।',
        'core-storage-growth-status.grew':
          'पिछले स्कैन के बाद {{bytes}} बढ़ा; यह जगह ले रहा है।',
        'core-storage-growth-status.shrank':
          'पिछले स्कैन के बाद {{bytes}} घटा; इससे जगह वापस मिली।',
      }),
      ar: Object.freeze({
        'core-storage-growth-status.none':
          'لا يوجد تغيير قابل للقياس منذ الفحص السابق.',
        'core-storage-growth-status.grew':
          'زاد بمقدار {{bytes}} منذ الفحص السابق؛ وهذا يستهلك مساحة.',
        'core-storage-growth-status.shrank':
          'انكمش بمقدار {{bytes}} منذ الفحص السابق؛ وهذا أعاد مساحة.',
      }),
      zh: Object.freeze({
        'core-storage-growth-status.none': '自上次扫描以来没有可测量的变化。',
        'core-storage-growth-status.grew':
          '自上次扫描以来增长了 {{bytes}}；这正在占用空间。',
        'core-storage-growth-status.shrank':
          '自上次扫描以来缩小了 {{bytes}}；这释放了空间。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    return i18n.t(
      `core-storage-growth-status.${key}`,
      values,
      this.manifest.catalogs.en[`core-storage-growth-status.${key}`],
    );
  },
};
