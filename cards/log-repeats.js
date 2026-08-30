window.ZogographLogRepeatsCard = {
  manifest: Object.freeze({
    id: 'core-log-repeats',
    locales: ['core-log-repeats.'],
    helpers: [
      'target',
      'note',
      'humanCount',
      'count',
      'renderTable',
      'whenSpan',
      'logMarks',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-log-repeats.summary':
          '{{lines}} recent lines across every log fall into {{shapes}}.',
        'core-log-repeats.per_minute': '{{rate}}/min',
        'core-log-repeats.grouped_as': 'Grouped as: {{shape}}',
      }),
      es: Object.freeze({
        'core-log-repeats.summary':
          '{{lines}} líneas recientes de todos los registros se agrupan en {{shapes}}.',
        'core-log-repeats.per_minute': '{{rate}}/min',
        'core-log-repeats.grouped_as': 'Agrupado como: {{shape}}',
      }),
      hi: Object.freeze({
        'core-log-repeats.summary':
          'सभी लॉग की {{lines}} हाल की पंक्तियाँ {{shapes}} में आती हैं।',
        'core-log-repeats.per_minute': '{{rate}}/मिनट',
        'core-log-repeats.grouped_as': 'इस रूप में समूहीकृत: {{shape}}',
      }),
      ar: Object.freeze({
        'core-log-repeats.summary':
          'تقع {{lines}} أسطر حديثة من كل السجلات ضمن {{shapes}}.',
        'core-log-repeats.per_minute': '{{rate}}/دقيقة',
        'core-log-repeats.grouped_as': 'مجمّع بوصفه: {{shape}}',
      }),
      zh: Object.freeze({
        'core-log-repeats.summary':
          '所有日志中最近的 {{lines}} 行归入 {{shapes}}。',
        'core-log-repeats.per_minute': '{{rate}}/分钟',
        'core-log-repeats.grouped_as': '分组为：{{shape}}',
      }),
    }),
  }),
  render(data, helpers) {
    const repeats = data.repeats;
    if (!repeats?.groups.length) {
      helpers.target.replaceChildren(
        helpers.note(
          helpers.i18n.t('log.nothing_to_group', {}, 'Nothing to group yet.'),
        ),
      );
      return;
    }
    const wrap = document.createDocumentFragment();
    wrap.appendChild(
      helpers.note(
        helpers.i18n.t(
          'core-log-repeats.summary',
          {
            lines: helpers.humanCount(repeats.lines),
            shapes: helpers.count(repeats.distinct, 'distinct shape'),
          },
          '{{lines}} recent lines across every log fall into {{shapes}}.',
        ),
      ),
    );
    const holder = document.createElement('div');
    holder.id = 'repeatTable';
    wrap.appendChild(holder);
    helpers.target.replaceChildren(wrap);
    helpers.renderTable(
      'repeatTable',
      repeats.groups,
      [
        {
          label: helpers.i18n.t('log.repeat_times', {}, 'Times'),
          className: 'size',
          render: (row) => helpers.humanCount(row.count),
        },
        {
          label: helpers.i18n.t('log.repeat_rate', {}, 'Rate'),
          className: 'size',
          render: (row) => {
            if (row.per_minute == null) return '—';
            const span = document.createElement('span');
            span.textContent = helpers.i18n.t(
              'core-log-repeats.per_minute',
              { rate: row.per_minute },
              '{{rate}}/min',
            );
            if (row.per_minute >= 60) span.className = 'grew';
            return span;
          },
        },
        {
          label: helpers.i18n.t('log.repeat_in', {}, 'In'),
          render: (row) => (row.files || []).join(', '),
        },
        {
          label: helpers.i18n.t('log.repeat_last_one', {}, 'Last one'),
          render: (row) => (row.last_at ? helpers.whenSpan(row.last_at) : '—'),
        },
        {
          label: helpers.i18n.t('log.repeat_what_it_says', {}, 'What it says'),
          className: 'cmd',
          render: (row) => {
            const cell = document.createDocumentFragment();
            if (row.kind) {
              const [icon, meaning] = helpers.logMarks[row.kind] || ['▫️', ''];
              const mark = document.createElement('span');
              mark.textContent = `${icon} `;
              mark.title = meaning;
              cell.appendChild(mark);
            }
            if (row.where) {
              const who = document.createElement('span');
              who.className = 'muted';
              who.textContent = `${row.where}: `;
              cell.appendChild(who);
            }
            const message = document.createElement('span');
            message.className = 'repeat-line';
            message.textContent = row.message || row.example;
            message.title = `${row.example}\n\n${helpers.i18n.t(
              'core-log-repeats.grouped_as',
              { shape: row.shape },
              'Grouped as: {{shape}}',
            )}`;
            cell.appendChild(message);
            return cell;
          },
        },
      ],
      null,
      null,
      (row) => row.shape,
    );
  },
};
