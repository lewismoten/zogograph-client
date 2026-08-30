/* Core card package: browser-localized calendar labels stay inside this view. */
window.ZogographCalendarGridCard = {
  manifest: Object.freeze({
    id: 'core-calendar-grid',
    locales: ['core-calendar-grid.'],
    helpers: ['target', 'openDay', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-calendar-grid.events_on_day':
          '{{count, number}} events on {{date}}',
      }),
      es: Object.freeze({
        'core-calendar-grid.events_on_day':
          '{{count, number}} eventos el {{date}}',
      }),
      hi: Object.freeze({
        'core-calendar-grid.events_on_day':
          '{{date}} को {{count, number}} कार्यक्रम',
      }),
      ar: Object.freeze({
        'core-calendar-grid.events_on_day':
          '{{count, number}} أحداث في {{date}}',
      }),
      zh: Object.freeze({
        'core-calendar-grid.events_on_day':
          '{{date}} 有 {{count, number}} 个事项',
      }),
    }),
  }),

  render(data, helpers) {
    const { target, openDay, i18n } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (!target) return;
    const fragment = document.createDocumentFragment();
    const start = new Date(data.from * 1000);
    start.setDate(1);
    const months = data.back_months + data.forward_months + 1;
    const today = new Date();
    for (let index = 0; index < months; index++) {
      const month = new Date(start.getFullYear(), start.getMonth() + index, 1);
      const box = document.createElement('div');
      box.className = 'month';
      const heading = document.createElement('h4');
      heading.textContent = month.toLocaleDateString(undefined, {
        month: 'long',
        year: 'numeric',
      });
      box.appendChild(heading);
      const table = document.createElement('table');
      table.className = 'month-grid';
      const head = table.createTHead().insertRow();
      const sample = new Date(2026, 1, 1);
      for (let dayOffset = 0; dayOffset < 7; dayOffset++) {
        const th = document.createElement('th');
        const day = new Date(
          sample.getFullYear(),
          sample.getMonth(),
          sample.getDate() + dayOffset,
        );
        th.textContent = day.toLocaleDateString(undefined, {
          weekday: 'narrow',
        });
        head.appendChild(th);
      }
      const body = table.createTBody();
      const first = new Date(month.getFullYear(), month.getMonth(), 1);
      const lead = first.getDay();
      const days = new Date(
        month.getFullYear(),
        month.getMonth() + 1,
        0,
      ).getDate();
      let row = body.insertRow();
      for (let index = 0; index < lead; index++) {
        row.insertCell().className = 'empty';
      }
      for (let day = 1; day <= days; day++) {
        if (row.cells.length === 7) row = body.insertRow();
        const cell = row.insertCell();
        const date = new Date(month.getFullYear(), month.getMonth(), day);
        const key =
          `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-` +
          String(day).padStart(2, '0');
        const entries = data.days[key] || [];
        cell.textContent = String(day);
        if (date.toDateString() === today.toDateString()) {
          cell.classList.add('today');
        }
        if (entries.length) {
          const rank = { error: 0, warn: 1, info: 2, done: 3, past: 4 };
          const worst = entries.reduce(
            (current, entry) =>
              rank[entry.level] < rank[current] ? entry.level : current,
            'past',
          );
          cell.classList.add('has-entries', `level-${worst}`);
          cell.tabIndex = 0;
          cell.setAttribute('role', 'button');
          cell.title = entries.map((entry) => entry.title).join('\n');
          cell.setAttribute(
            'aria-label',
            t(
              'core-calendar-grid.events_on_day',
              {
                count: entries.length,
                date: date.toLocaleDateString(undefined, {
                  day: 'numeric',
                  month: 'long',
                  year: 'numeric',
                }),
              },
              '{{count, number}} events on {{date}}',
            ),
          );
          const open = () => openDay(key, entries);
          cell.addEventListener('click', open);
          cell.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
              event.preventDefault();
              open();
            }
          });
        }
      }
      while (row.cells.length < 7) row.insertCell().className = 'empty';
      box.appendChild(table);
      fragment.appendChild(box);
    }
    const monthsWrap = document.createElement('div');
    monthsWrap.className = 'months';
    monthsWrap.appendChild(fragment);
    target.replaceChildren(monthsWrap);
  },
};
