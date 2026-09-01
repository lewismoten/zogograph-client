/* Core card package: shared interactive table controls and empty states. */
window.ZogographTableCommonCard = {
  manifest: Object.freeze({
    id: 'core-table-common',
    locales: ['core-table-common.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-table-common.nothing_to_show': 'Nothing to show.',
        'core-table-common.download_rows':
          'Download {{count, number}} rows as {{format}}',
        'core-table-common.sort_by': 'Sort by {{label}}',
        'core-table-common.rows_total': '{{count, number}} rows',
        'core-table-common.rows_shown':
          'Showing {{shown, number}} of {{total, number}} rows',
        'core-table-common.show_more': 'Show {{count, number}} more',
        'core-table-common.size': 'Size',
        'core-table-common.change': 'Change',
        'core-table-common.name': 'Name',
        'core-table-common.value': 'Value',
        'core-table-common.header': 'Header',
        'core-table-common.says': 'Says',
        'core-table-common.domain': 'Domain',
        'core-table-common.means': 'Means',
      }),
      es: Object.freeze({
        'core-table-common.nothing_to_show': 'No hay nada que mostrar.',
        'core-table-common.download_rows':
          'Descargar {{count, number}} filas como {{format}}',
        'core-table-common.sort_by': 'Ordenar por {{label}}',
        'core-table-common.rows_total': '{{count, number}} filas',
        'core-table-common.rows_shown':
          'Se muestran {{shown, number}} de {{total, number}} filas',
        'core-table-common.show_more': 'Mostrar {{count, number}} más',
        'core-table-common.size': 'Tamaño',
        'core-table-common.change': 'Cambio',
        'core-table-common.name': 'Nombre',
        'core-table-common.value': 'Valor',
        'core-table-common.header': 'Cabecera',
        'core-table-common.says': 'Dice',
        'core-table-common.domain': 'Dominio',
        'core-table-common.means': 'Significado',
      }),
      hi: Object.freeze({
        'core-table-common.nothing_to_show': 'दिखाने के लिए कुछ नहीं है।',
        'core-table-common.download_rows':
          '{{count, number}} पंक्तियाँ {{format}} के रूप में डाउनलोड करें',
        'core-table-common.sort_by': '{{label}} के अनुसार क्रमित करें',
        'core-table-common.rows_total': '{{count, number}} पंक्तियाँ',
        'core-table-common.rows_shown':
          '{{total, number}} में से {{shown, number}} पंक्तियाँ दिखाई जा रही हैं',
        'core-table-common.show_more': '{{count, number}} और दिखाएँ',
        'core-table-common.size': 'आकार',
        'core-table-common.change': 'बदलाव',
        'core-table-common.name': 'नाम',
        'core-table-common.value': 'मान',
        'core-table-common.header': 'हेडर',
        'core-table-common.says': 'कहता है',
        'core-table-common.domain': 'डोमेन',
        'core-table-common.means': 'अर्थ',
      }),
      ar: Object.freeze({
        'core-table-common.nothing_to_show': 'لا يوجد ما يمكن عرضه.',
        'core-table-common.download_rows':
          'نزّل {{count, number}} صفًا بصيغة {{format}}',
        'core-table-common.sort_by': 'فرز حسب {{label}}',
        'core-table-common.rows_total': '{{count, number}} صفًا',
        'core-table-common.rows_shown':
          'يتم عرض {{shown, number}} من {{total, number}} صفًا',
        'core-table-common.show_more': 'اعرض {{count, number}} إضافية',
        'core-table-common.size': 'الحجم',
        'core-table-common.change': 'التغيير',
        'core-table-common.name': 'الاسم',
        'core-table-common.value': 'القيمة',
        'core-table-common.header': 'الترويسة',
        'core-table-common.says': 'يقول',
        'core-table-common.domain': 'النطاق',
        'core-table-common.means': 'يعني',
      }),
      zh: Object.freeze({
        'core-table-common.nothing_to_show': '没有可显示的内容。',
        'core-table-common.download_rows':
          '将 {{count, number}} 行下载为 {{format}}',
        'core-table-common.sort_by': '按 {{label}} 排序',
        'core-table-common.rows_total': '{{count, number}} 行',
        'core-table-common.rows_shown':
          '显示 {{total, number}} 行中的 {{shown, number}} 行',
        'core-table-common.show_more': '再显示 {{count, number}} 行',
        'core-table-common.size': '大小',
        'core-table-common.change': '变化',
        'core-table-common.name': '名称',
        'core-table-common.value': '值',
        'core-table-common.header': '标头',
        'core-table-common.says': '内容',
        'core-table-common.domain': '域名',
        'core-table-common.means': '含义',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-table-common.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
