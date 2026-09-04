/* Core card package: collector cadence and coverage summaries. */
const UptimeTracksCard = {
  manifest: Object.freeze({
    id: 'core-uptime-tracks',
    locales: ['core-uptime-tracks.'],
    owner: 'core-uptime',
    helpers: ['target', 'humanSpan', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-uptime-tracks.unreadable':
          'its database cannot be read from this account, so nothing can be said about whether it is running',
        'core-uptime-tracks.nothing_recorded':
          'nothing recorded in this window',
        'core-uptime-tracks.readings': '{{count, number}} readings',
        'core-uptime-tracks.every': 'about every {{duration}}',
        'core-uptime-tracks.last': 'last one {{duration}} ago',
        'core-uptime-tracks.last_late':
          'last one {{duration}} ago — later than usual',
        'core-uptime-tracks.gaps': '{{count, number}} gaps in this window',
        'core-uptime-tracks.requests_answered':
          '{{count, number}} requests answered',
        'core-uptime-tracks.summary': ' — {{values}}.',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-uptime/locales/es/uptime-tracks.json',
      hi: 'packages/core-uptime/locales/hi/uptime-tracks.json',
      ar: 'packages/core-uptime/locales/ar/uptime-tracks.json',
      zh: 'packages/core-uptime/locales/zh/uptime-tracks.json',
    }),
  }),

  render({ tracks, to }, helpers) {
    const { target, humanSpan, i18n } = helpers;
    if (!target) return;
    if (!tracks.length) {
      target.replaceChildren();
      return;
    }
    const t = (key, values = {}) =>
      i18n.t(
        `core-uptime-tracks.${key}`,
        values,
        this.manifest.catalogs.en[`core-uptime-tracks.${key}`],
      );
    const rows = tracks.map((track) => {
      const said = [];
      if (track.unreadable) said.push(t('unreadable'));
      else if (!track.readings) said.push(t('nothing_recorded'));
      else {
        said.push(t('readings', { count: track.readings }));
        if (track.interval)
          said.push(t('every', { duration: humanSpan(track.interval) }));
        const age = to - track.last;
        const late = track.interval && age > track.interval * 2.5;
        said.push(t(late ? 'last_late' : 'last', { duration: humanSpan(age) }));
        const holes = (track.gaps || []).length;
        if (holes) said.push(t('gaps', { count: holes }));
        if ((track.marks || []).length)
          said.push(t('requests_answered', { count: track.marks.length }));
      }
      const line = document.createElement('div');
      line.className = 'track-line';
      const name = document.createElement('strong');
      name.textContent = track.label;
      line.appendChild(name);
      const text = document.createElement('span');
      const stale = track.interval && to - track.last > track.interval * 2.5;
      text.className =
        stale || track.unreadable || !track.readings ? 'band-warn' : 'muted';
      text.textContent = t('summary', { values: said.join(', ') });
      line.appendChild(text);
      const why = document.createElement('div');
      why.className = 'muted small';
      why.textContent = track.what;
      line.appendChild(why);
      return line;
    });
    target.replaceChildren(...rows);
  },
};
Zogograph.cards.register(UptimeTracksCard);
