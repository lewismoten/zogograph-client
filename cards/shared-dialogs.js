/* Core card package: titles and contextual labels shared by the dashboard dialogs. */
window.ZogographSharedDialogsCard = {
  manifest: Object.freeze({
    id: 'core-shared-dialogs',
    locales: ['core-shared-dialogs.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-shared-dialogs.please_confirm': 'Please confirm',
        'core-shared-dialogs.details': 'Details',
        'core-shared-dialogs.page_help': 'Page help',
        'core-shared-dialogs.this_page': 'This page',
        'core-shared-dialogs.view_help': '{{view}} help',
        'core-shared-dialogs.are_you_sure': 'Are you sure?',
      }),
      es: Object.freeze({
        'core-shared-dialogs.please_confirm': 'Confirme, por favor',
        'core-shared-dialogs.details': 'Detalles',
        'core-shared-dialogs.page_help': 'Ayuda de la página',
        'core-shared-dialogs.this_page': 'Esta página',
        'core-shared-dialogs.view_help': 'Ayuda de {{view}}',
        'core-shared-dialogs.are_you_sure': '¿Está seguro?',
      }),
      hi: Object.freeze({
        'core-shared-dialogs.please_confirm': 'कृपया पुष्टि करें',
        'core-shared-dialogs.details': 'विवरण',
        'core-shared-dialogs.page_help': 'पृष्ठ सहायता',
        'core-shared-dialogs.this_page': 'यह पृष्ठ',
        'core-shared-dialogs.view_help': '{{view}} सहायता',
        'core-shared-dialogs.are_you_sure': 'क्या आप सुनिश्चित हैं?',
      }),
      ar: Object.freeze({
        'core-shared-dialogs.please_confirm': 'يرجى التأكيد',
        'core-shared-dialogs.details': 'التفاصيل',
        'core-shared-dialogs.page_help': 'مساعدة الصفحة',
        'core-shared-dialogs.this_page': 'هذه الصفحة',
        'core-shared-dialogs.view_help': 'مساعدة {{view}}',
        'core-shared-dialogs.are_you_sure': 'هل أنت متأكد؟',
      }),
      zh: Object.freeze({
        'core-shared-dialogs.please_confirm': '请确认',
        'core-shared-dialogs.details': '详细信息',
        'core-shared-dialogs.page_help': '页面帮助',
        'core-shared-dialogs.this_page': '此页面',
        'core-shared-dialogs.view_help': '{{view}} 帮助',
        'core-shared-dialogs.are_you_sure': '确定吗？',
      }),
    }),
  }),
  render(input, helpers) {
    return helpers.i18n.t(
      `core-shared-dialogs.${input.key}`,
      input.values,
      input.fallback,
    );
  },
};
