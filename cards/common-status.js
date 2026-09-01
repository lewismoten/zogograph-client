/* Core package: reusable signed-out, loading, and refresh-status messages. */
window.ZogographCommonStatusCard = {
  manifest: Object.freeze({
    id: 'core-common-status',
    locales: ['core-common-status.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-common-status.sign_in_to_see': 'Sign in to see this.',
        'core-common-status.loading': 'Loading…',
        'core-common-status.refresh_failed': 'Could not refresh',
        'core-common-status.ready': 'Ready',
      }),
      es: Object.freeze({
        'core-common-status.sign_in_to_see': 'Inicie sesión para ver esto.',
        'core-common-status.loading': 'Cargando…',
        'core-common-status.refresh_failed': 'No se pudo actualizar',
        'core-common-status.ready': 'Listo',
      }),
      hi: Object.freeze({
        'core-common-status.sign_in_to_see': 'इसे देखने के लिए साइन इन करें।',
        'core-common-status.loading': 'लोड हो रहा है…',
        'core-common-status.refresh_failed': 'रीफ़्रेश नहीं हो सका',
        'core-common-status.ready': 'तैयार',
      }),
      ar: Object.freeze({
        'core-common-status.sign_in_to_see': 'سجّل الدخول لرؤية هذا.',
        'core-common-status.loading': 'جارٍ التحميل…',
        'core-common-status.refresh_failed': 'تعذر التحديث',
        'core-common-status.ready': 'جاهز',
      }),
      zh: Object.freeze({
        'core-common-status.sign_in_to_see': '登录后可查看此内容。',
        'core-common-status.loading': '正在加载…',
        'core-common-status.refresh_failed': '无法刷新',
        'core-common-status.ready': '就绪',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-common-status.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
