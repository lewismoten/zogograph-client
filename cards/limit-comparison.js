/* Core card package: compare one account's limits with the host's facts. */
window.ZogographLimitComparisonCard = {
  manifest: Object.freeze({
    id: 'core-limit-comparison',
    locales: ['core-limit-comparison.'],
    helpers: ['body', 'when', 'humanBytes', 'humanTime', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-limit-comparison.unavailable': 'No comparison available.',
        'core-limit-comparison.host_reading': 'host reading {{time}}',
        'core-limit-comparison.not_measured': 'not measured',
        'core-limit-comparison.account_sees': 'This account sees',
        'core-limit-comparison.machine_has': 'The machine has',
      }),
      es: Object.freeze({
        'core-limit-comparison.unavailable': 'No hay comparación disponible.',
        'core-limit-comparison.host_reading': 'lectura del host {{time}}',
        'core-limit-comparison.not_measured': 'sin medir',
        'core-limit-comparison.account_sees': 'Esta cuenta ve',
        'core-limit-comparison.machine_has': 'La máquina tiene',
      }),
      hi: Object.freeze({
        'core-limit-comparison.unavailable': 'कोई तुलना उपलब्ध नहीं है।',
        'core-limit-comparison.host_reading': 'होस्ट रीडिंग {{time}}',
        'core-limit-comparison.not_measured': 'मापा नहीं गया',
        'core-limit-comparison.account_sees': 'यह खाता देखता है',
        'core-limit-comparison.machine_has': 'मशीन में है',
      }),
      ar: Object.freeze({
        'core-limit-comparison.unavailable': 'لا توجد مقارنة متاحة.',
        'core-limit-comparison.host_reading': 'قراءة المضيف {{time}}',
        'core-limit-comparison.not_measured': 'غير مقاس',
        'core-limit-comparison.account_sees': 'يرى هذا الحساب',
        'core-limit-comparison.machine_has': 'لدى الآلة',
      }),
      zh: Object.freeze({
        'core-limit-comparison.unavailable': '没有可用的比较。',
        'core-limit-comparison.host_reading': '主机读数 {{time}}',
        'core-limit-comparison.not_measured': '未测量',
        'core-limit-comparison.account_sees': '此帐户看到',
        'core-limit-comparison.machine_has': '机器具有',
      }),
    }),
  }),
  render({ limits }, helpers) {
    const { body, when, humanBytes, humanTime, i18n } = helpers;
    const t = (key, values = {}, fallback = key) =>
      i18n.t(`core-limit-comparison.${key}`, values, fallback);
    if (!limits || !limits.available) {
      if (when) when.textContent = '';
      body.replaceChildren(
        Object.assign(document.createElement('p'), {
          className: 'muted',
          textContent:
            limits?.reason || t('unavailable', {}, 'No comparison available.'),
        }),
      );
      return;
    }
    if (when)
      when.textContent = limits.host_at
        ? t(
            'host_reading',
            { time: humanTime(limits.host_at) },
            'host reading {{time}}',
          )
        : '';
    const format = (value, unit) =>
      value == null
        ? t('not_measured', {}, 'not measured')
        : unit === 'bytes'
          ? humanBytes(value)
          : unit === 'percent'
            ? `${Number(value).toFixed(0)}%`
            : String(
                Number(value) % 1 === 0 ? value : Number(value).toFixed(2),
              );
    const account = t('account_sees', {}, 'This account sees');
    body.replaceChildren(
      ...limits.rows.map((row) => {
        const div = document.createElement('div');
        div.className = 'limit-row';
        const name = document.createElement('div');
        name.className = 'limit-name';
        name.textContent = row.name;
        div.appendChild(name);
        for (const [side, value] of [
          [account, row.account],
          [t('machine_has', {}, 'The machine has'), row.host],
        ]) {
          const pair = document.createElement('div');
          pair.className = 'limit-pair';
          const heading = document.createElement('div');
          heading.className = 'muted';
          heading.textContent = side;
          const figure = document.createElement('div');
          figure.textContent = format(value, row.unit);
          if (row.verdict === 'capped' && side === account)
            figure.className = 'band-warn';
          pair.append(heading, figure);
          div.appendChild(pair);
        }
        const note = document.createElement('div');
        note.className = 'limit-note';
        note.textContent = row.note;
        div.appendChild(note);
        return div;
      }),
    );
    return body;
  },
};
