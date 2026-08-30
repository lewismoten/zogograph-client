/* Core card package: the newest AI summary shown on the overview. */
window.ZogographLatestReportCard = {
  manifest: Object.freeze({
    id: 'core-latest-report',
    locales: ['core-latest-report.'],
    helpers: [
      'target',
      'whenLabel',
      'whenTitle',
      'renderMarkdown',
      'openAll',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-latest-report.latest': 'Latest summary from {{assistant}}',
        'core-latest-report.all': 'All summaries',
      }),
      es: Object.freeze({
        'core-latest-report.latest': 'Último resumen de {{assistant}}',
        'core-latest-report.all': 'Todos los resúmenes',
      }),
      hi: Object.freeze({
        'core-latest-report.latest': '{{assistant}} का नवीनतम सारांश',
        'core-latest-report.all': 'सभी सारांश',
      }),
      ar: Object.freeze({
        'core-latest-report.latest': 'أحدث ملخص من {{assistant}}',
        'core-latest-report.all': 'كل الملخصات',
      }),
      zh: Object.freeze({
        'core-latest-report.latest': '{{assistant}} 的最新摘要',
        'core-latest-report.all': '所有摘要',
      }),
    }),
  }),

  render(data, helpers) {
    const { target, whenLabel, whenTitle, renderMarkdown, openAll, i18n } =
      helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (!target) return;
    const { report, assistant } = data;
    if (!report) {
      target.hidden = true;
      return;
    }
    target.hidden = false;
    const head = document.createElement('div');
    head.className = 'report-head';
    const title = document.createElement('strong');
    title.textContent = t(
      'core-latest-report.latest',
      { assistant },
      'Latest summary from {{assistant}}',
    );
    head.appendChild(title);
    const when = document.createElement('span');
    when.className = 'muted small when';
    when.textContent = whenLabel(report.at);
    when.title = whenTitle(report.at);
    head.appendChild(when);
    const all = document.createElement('button');
    all.className = 'link small';
    all.textContent = t('core-latest-report.all', {}, 'All summaries');
    all.addEventListener('click', openAll);
    head.appendChild(all);
    const text = document.createElement('div');
    text.className = 'report-body';
    text.appendChild(renderMarkdown(report.body));
    target.replaceChildren(head, text);
  },
};
