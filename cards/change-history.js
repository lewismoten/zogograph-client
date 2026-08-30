/* Core card package: changes recorded over the selected observation window. */
window.ZogographChangeHistoryCard = {
  manifest: Object.freeze({
    id: 'core-change-history',
    locales: ['core-change-history.'],
    helpers: ['target', 'note', 'renderTable', 'whenSpan', 'ago', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-change-history.empty_checked':
          'Nothing has changed in this range. Last checked {{when}}.',
        'core-change-history.empty_unchecked':
          'Nothing has been checked yet. Press Check now, or open What the host allows.',
        'core-change-history.noticed': 'Noticed',
        'core-change-history.what': 'What',
        'core-change-history.was': 'Was',
        'core-change-history.became': 'Became',
        'core-change-history.by_fingerprint': 'by fingerprint',
      }),
      es: Object.freeze({
        'core-change-history.empty_checked':
          'No ha cambiado nada en este intervalo. Última comprobación {{when}}.',
        'core-change-history.empty_unchecked':
          'Aún no se ha comprobado nada. Pulse Comprobar ahora o abra Lo que permite el host.',
        'core-change-history.noticed': 'Detectado',
        'core-change-history.what': 'Qué',
        'core-change-history.was': 'Era',
        'core-change-history.became': 'Pasó a ser',
        'core-change-history.by_fingerprint': 'por huella digital',
      }),
      hi: Object.freeze({
        'core-change-history.empty_checked':
          'इस अवधि में कुछ नहीं बदला। आखिरी जाँच {{when}}।',
        'core-change-history.empty_unchecked':
          'अभी कुछ जाँचा नहीं गया है। अभी जाँचें दबाएँ या होस्ट क्या अनुमति देता है खोलें।',
        'core-change-history.noticed': 'देखा गया',
        'core-change-history.what': 'क्या',
        'core-change-history.was': 'था',
        'core-change-history.became': 'बन गया',
        'core-change-history.by_fingerprint': 'फिंगरप्रिंट द्वारा',
      }),
      ar: Object.freeze({
        'core-change-history.empty_checked':
          'لم يتغير شيء في هذه المدة. آخر فحص {{when}}.',
        'core-change-history.empty_unchecked':
          'لم يُفحص شيء بعد. اضغط افحص الآن أو افتح ما يسمح به المضيف.',
        'core-change-history.noticed': 'تمت ملاحظته',
        'core-change-history.what': 'ما هو',
        'core-change-history.was': 'كان',
        'core-change-history.became': 'أصبح',
        'core-change-history.by_fingerprint': 'بالبصمة',
      }),
      zh: Object.freeze({
        'core-change-history.empty_checked':
          '此时间范围内没有任何变化。上次检查 {{when}}。',
        'core-change-history.empty_unchecked':
          '尚未进行检查。请按“立即检查”，或打开“主机允许的内容”。',
        'core-change-history.noticed': '发现时间',
        'core-change-history.what': '项目',
        'core-change-history.was': '原来是',
        'core-change-history.became': '变为',
        'core-change-history.by_fingerprint': '按指纹识别',
      }),
    }),
  }),

  render(data, helpers) {
    const { target, note, renderTable, whenSpan, ago, i18n } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (!target) return;
    if (!data.events?.length) {
      target.replaceChildren(
        note(
          data.last_checked
            ? t(
                'core-change-history.empty_checked',
                { when: ago(data.last_checked) },
                'Nothing has changed in this range. Last checked {{when}}.',
              )
            : t(
                'core-change-history.empty_unchecked',
                {},
                'Nothing has been checked yet. Press Check now, or open What the host allows.',
              ),
        ),
      );
      return;
    }
    const holder = document.createElement('div');
    holder.id = 'changedTable';
    target.replaceChildren(holder);
    renderTable(
      'changedTable',
      data.events,
      [
        {
          label: t('core-change-history.noticed', {}, 'Noticed'),
          render: (row) => whenSpan(row.at),
        },
        {
          label: t('core-change-history.what', {}, 'What'),
          className: 'name',
          render: (row) => {
            const span = document.createElement('span');
            span.textContent = row.label;
            span.title =
              row.kind === 'software'
                ? t('installed.system_software_tip')
                : t('installed.watched_program_tip', {
                    group: row.group ? ` (${row.group})` : '',
                  });
            return span;
          },
        },
        {
          label: t('core-change-history.was', {}, 'Was'),
          className: 'cmd',
          render: (row) => row.from ?? '—',
        },
        {
          label: t('core-change-history.became', {}, 'Became'),
          className: 'cmd',
          render: (row) => row.to,
        },
        {
          label: '',
          render: (row) => {
            if (row.state_from && row.state_from !== row.state_to) {
              const span = document.createElement('span');
              span.className =
                row.state_to === 'absent' ? 'band-crit' : 'band-ok';
              span.textContent = `${row.state_from} → ${row.state_to}`;
              return span;
            }
            if (row.by_fingerprint) {
              const span = document.createElement('span');
              span.className = 'muted small';
              span.textContent = t(
                'core-change-history.by_fingerprint',
                {},
                'by fingerprint',
              );
              span.title = t('installed.fingerprint_change_tip');
              return span;
            }
            return '';
          },
        },
      ],
      null,
      null,
      (row) => `${row.at}|${row.name}`,
    );
  },
};
