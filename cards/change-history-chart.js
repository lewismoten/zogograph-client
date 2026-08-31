/* Core card package: change-history chart labels and empty states. */
window.ZogographChangeHistoryChartCard = {
  manifest: Object.freeze({
    id: 'core-change-history-chart',
    locales: ['core-change-history-chart.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-change-history-chart.nothing_changed_range':
          'Nothing changed in this range.',
        'core-change-history-chart.nothing_changed_last_checked':
          'Nothing has changed in this range. Last checked {{when}}.',
        'core-change-history-chart.nothing_checked_yet':
          'Nothing has been checked yet. Press Check now, or open What the host allows.',
        'core-change-history-chart.changed_summary':
          '{{changes}} across {{things}}.',
        'core-change-history-chart.changed_hidden':
          '{{count}} not shown — use the filter.',
        'core-change-history-chart.changed_chart_legend':
          'Blue is the system software, green a program this tool watches, and a hollow ring means the change was noticed by fingerprint rather than by version.',
      }),
      es: Object.freeze({
        'core-change-history-chart.nothing_changed_range':
          'No cambió nada en este intervalo.',
        'core-change-history-chart.nothing_changed_last_checked':
          'No ha cambiado nada en este intervalo. Última comprobación: {{when}}.',
        'core-change-history-chart.nothing_checked_yet':
          'Aún no se ha comprobado nada. Pulse Comprobar ahora o abra Qué permite el host.',
        'core-change-history-chart.changed_summary':
          '{{changes}} en {{things}}.',
        'core-change-history-chart.changed_hidden':
          '{{count}} no se muestran; use el filtro.',
        'core-change-history-chart.changed_chart_legend':
          'El azul es el software del sistema, el verde es un programa que vigila esta herramienta y un anillo hueco indica que el cambio se detectó por huella digital en vez de por versión.',
      }),
      hi: Object.freeze({
        'core-change-history-chart.nothing_changed_range':
          'इस सीमा में कुछ भी नहीं बदला।',
        'core-change-history-chart.nothing_changed_last_checked':
          'इस सीमा में कुछ भी नहीं बदला। अंतिम जांच {{when}}।',
        'core-change-history-chart.nothing_checked_yet':
          'अभी तक कुछ भी नहीं जांचा गया है। अभी जांचें दबाएं या देखें कि होस्ट क्या अनुमति देता है।',
        'core-change-history-chart.changed_summary':
          '{{changes}} बदलाव, {{things}} में।',
        'core-change-history-chart.changed_hidden':
          '{{count}} नहीं दिखाए गए हैं — फ़िल्टर का उपयोग करें।',
        'core-change-history-chart.changed_chart_legend':
          'नीला सिस्टम सॉफ़्टवेयर है, हरा वह प्रोग्राम है जिस पर यह टूल नज़र रखता है, और खोखला छल्ला बताता है कि बदलाव संस्करण के बजाय फ़िंगरप्रिंट से पता चला।',
      }),
      ar: Object.freeze({
        'core-change-history-chart.nothing_changed_range':
          'لم يتغير شيء في هذه المدة.',
        'core-change-history-chart.nothing_changed_last_checked':
          'لم يتغير شيء في هذه المدة. آخر فحص {{when}}.',
        'core-change-history-chart.nothing_checked_yet':
          'لم يُفحص شيء بعد. اضغط افحص الآن، أو افتح ما الذي يسمح به المضيف.',
        'core-change-history-chart.changed_summary':
          '{{changes}} من التغييرات عبر {{things}}.',
        'core-change-history-chart.changed_hidden':
          'لا يظهر {{count}} منها — استخدم عامل التصفية.',
        'core-change-history-chart.changed_chart_legend':
          'الأزرق هو برمجيات النظام، والأخضر برنامج تراقبه هذه الأداة، وتعني الحلقة المجوفة أن التغيير لوحظ بالبصمة بدلاً من الإصدار.',
      }),
      zh: Object.freeze({
        'core-change-history-chart.nothing_changed_range':
          '此范围内没有任何变化。',
        'core-change-history-chart.nothing_changed_last_checked':
          '此范围内没有任何变化。上次检查时间：{{when}}。',
        'core-change-history-chart.nothing_checked_yet':
          '尚未检查任何内容。请按“立即检查”，或打开“主机允许的内容”。',
        'core-change-history-chart.changed_summary':
          '{{changes}} 项变更，涉及 {{things}} 项内容。',
        'core-change-history-chart.changed_hidden':
          '有 {{count}} 项未显示——请使用筛选器。',
        'core-change-history-chart.changed_chart_legend':
          '蓝色表示系统软件，绿色表示此工具监测的程序；空心圆环表示通过指纹而非版本发现了变更。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    return i18n.t(
      `core-change-history-chart.${key}`,
      values,
      this.manifest.catalogs.en[`core-change-history-chart.${key}`],
    );
  },
};
