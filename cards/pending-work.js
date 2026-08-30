/* Core card package: outstanding work waiting for the host collector. */
window.ZogographPendingWorkCard = {
  manifest: Object.freeze({
    id: 'core-pending-work',
    locales: ['core-pending-work.'],
    helpers: ['target', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-pending-work.waiting': 'Waiting on root for {{label}}',
      }),
      es: Object.freeze({
        'core-pending-work.waiting': 'Esperando a root para {{label}}',
      }),
      hi: Object.freeze({
        'core-pending-work.waiting': '{{label}} के लिए root की प्रतीक्षा है',
      }),
      ar: Object.freeze({
        'core-pending-work.waiting': 'بانتظار root من أجل {{label}}',
      }),
      zh: Object.freeze({
        'core-pending-work.waiting': '正在等待 root 完成 {{label}}',
      }),
    }),
  }),

  render(rows, helpers) {
    const { target, i18n } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (!target) return;
    const fragment = document.createDocumentFragment();
    for (const row of rows) {
      const item = document.createElement('div');
      item.className = 'pending-item';
      item.dataset.task = row.task;
      const head = document.createElement('div');
      head.className = 'pending-head';
      const what = document.createElement('strong');
      what.textContent = t(
        'core-pending-work.waiting',
        { label: row.label },
        'Waiting on root for {{label}}',
      );
      head.appendChild(what);
      const when = document.createElement('span');
      when.className = 'pending-when muted small';
      head.appendChild(when);
      item.appendChild(head);
      const track = document.createElement('div');
      track.className = 'pending-track';
      const fill = document.createElement('div');
      fill.className = 'pending-fill';
      track.appendChild(fill);
      item.appendChild(track);
      fragment.appendChild(item);
    }
    target.replaceChildren(fragment);
  },
};
