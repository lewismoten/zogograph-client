/* Core card package: collector cadence and coverage summaries. */
window.ZogographUptimeTracksCard = {
  manifest: Object.freeze({
    id: 'core-uptime-tracks',
    locales: ['core-uptime-tracks.'],
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
      es: Object.freeze({
        'core-uptime-tracks.unreadable':
          'esta cuenta no puede leer su base de datos, por lo que no se puede saber si está en ejecución',
        'core-uptime-tracks.nothing_recorded':
          'no hay nada registrado en esta ventana',
        'core-uptime-tracks.readings': '{{count, number}} lecturas',
        'core-uptime-tracks.every': 'aproximadamente cada {{duration}}',
        'core-uptime-tracks.last': 'la última hace {{duration}}',
        'core-uptime-tracks.last_late':
          'la última hace {{duration}} — más tarde de lo habitual',
        'core-uptime-tracks.gaps': '{{count, number}} brechas en esta ventana',
        'core-uptime-tracks.requests_answered':
          '{{count, number}} solicitudes respondidas',
        'core-uptime-tracks.summary': ' — {{values}}.',
      }),
      hi: Object.freeze({
        'core-uptime-tracks.unreadable':
          'इस खाते से इसका डेटाबेस नहीं पढ़ा जा सकता, इसलिए यह चल रहा है या नहीं इसके बारे में कुछ नहीं कहा जा सकता',
        'core-uptime-tracks.nothing_recorded':
          'इस विंडो में कुछ भी दर्ज नहीं है',
        'core-uptime-tracks.readings': '{{count, number}} रीडिंग',
        'core-uptime-tracks.every': 'लगभग हर {{duration}}',
        'core-uptime-tracks.last': 'आखिरी {{duration}} पहले',
        'core-uptime-tracks.last_late':
          'आखिरी {{duration}} पहले — सामान्य से देर से',
        'core-uptime-tracks.gaps': 'इस विंडो में {{count, number}} अंतर',
        'core-uptime-tracks.requests_answered':
          '{{count, number}} अनुरोधों का उत्तर दिया गया',
        'core-uptime-tracks.summary': ' — {{values}}।',
      }),
      ar: Object.freeze({
        'core-uptime-tracks.unreadable':
          'لا يستطيع هذا الحساب قراءة قاعدة بياناته، لذا لا يمكن قول شيء عن كونه يعمل',
        'core-uptime-tracks.nothing_recorded': 'لا شيء مسجل في هذه النافذة',
        'core-uptime-tracks.readings': '{{count, number}} قراءات',
        'core-uptime-tracks.every': 'كل {{duration}} تقريبًا',
        'core-uptime-tracks.last': 'آخر واحدة منذ {{duration}}',
        'core-uptime-tracks.last_late':
          'آخر واحدة منذ {{duration}} — متأخرة عن المعتاد',
        'core-uptime-tracks.gaps': '{{count, number}} فجوات في هذه النافذة',
        'core-uptime-tracks.requests_answered':
          '{{count, number}} طلبات أُجيب عنها',
        'core-uptime-tracks.summary': ' — {{values}}.',
      }),
      zh: Object.freeze({
        'core-uptime-tracks.unreadable':
          '此账户无法读取其数据库，因此无法判断它是否正在运行',
        'core-uptime-tracks.nothing_recorded': '此窗口中没有记录内容',
        'core-uptime-tracks.readings': '{{count, number}} 次读数',
        'core-uptime-tracks.every': '大约每 {{duration}} 一次',
        'core-uptime-tracks.last': '上一次在 {{duration}} 前',
        'core-uptime-tracks.last_late': '上一次在 {{duration}} 前——比平时晚',
        'core-uptime-tracks.gaps': '此窗口中有 {{count, number}} 个间隔',
        'core-uptime-tracks.requests_answered':
          '已响应 {{count, number}} 个请求',
        'core-uptime-tracks.summary': ' — {{values}}。',
      }),
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

