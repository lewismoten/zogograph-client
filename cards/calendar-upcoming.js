/* Core card package: the shell supplies date, section, and navigation helpers. */
window.ZogographCalendarUpcomingCard = {
  manifest: Object.freeze({
    id: 'core-calendar-upcoming',
    locales: ['core-calendar-upcoming.'],
    helpers: ['target', 'note', 'humanTime', 'section', 'showView', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-calendar-upcoming.title': 'What is coming up',
        'core-calendar-upcoming.add_date': 'Add a date',
        'core-calendar-upcoming.help':
          'Three months back and twelve forward. Domain expiries and the projected full-disk date are worked out here; renewals, invoices and anything else only you know about are the ones you add. Behind us: the critical alerts that fired, so ‘what changed around then’ has an answer.',
        'core-calendar-upcoming.empty': 'Nothing dated is coming up.',
        'core-calendar-upcoming.today': 'today',
        'core-calendar-upcoming.tomorrow': 'tomorrow',
        'core-calendar-upcoming.in_days': 'in {{count, number}} days',
        'core-calendar-upcoming.next': 'Next',
        'core-calendar-upcoming.open': 'Open',
        'core-calendar-upcoming.see': 'See',
      }),
      es: Object.freeze({
        'core-calendar-upcoming.title': 'Qué viene',
        'core-calendar-upcoming.add_date': 'Añadir una fecha',
        'core-calendar-upcoming.help':
          'Tres meses atrás y doce adelante. Aquí se calculan los vencimientos de dominios y la fecha prevista de disco lleno; las renovaciones, facturas y todo lo demás que solo usted sabe son lo que añade. Detrás quedan las alertas críticas que se activaron, de modo que «qué cambió entonces» tiene respuesta.',
        'core-calendar-upcoming.empty': 'No hay nada con fecha próxima.',
        'core-calendar-upcoming.today': 'hoy',
        'core-calendar-upcoming.tomorrow': 'mañana',
        'core-calendar-upcoming.in_days': 'dentro de {{count, number}} días',
        'core-calendar-upcoming.next': 'Siguiente',
        'core-calendar-upcoming.open': 'Abrir',
        'core-calendar-upcoming.see': 'Ver',
      }),
      hi: Object.freeze({
        'core-calendar-upcoming.title': 'आगे क्या है',
        'core-calendar-upcoming.add_date': 'तारीख जोड़ें',
        'core-calendar-upcoming.help':
          'तीन महीने पीछे और बारह आगे। डोमेन समाप्ति और अनुमानित डिस्क-भरने की तारीख यहाँ निकाली जाती है; नवीनीकरण, इनवॉइस और बाकी चीज़ें जिन्हें केवल आप जानते हैं, आप जोड़ते हैं। पीछे वे गंभीर अलर्ट हैं जो आए थे, इसलिए ‘उस समय क्या बदला’ का उत्तर मिलता है।',
        'core-calendar-upcoming.empty':
          'तारीख वाला कोई कार्यक्रम आने वाला नहीं है।',
        'core-calendar-upcoming.today': 'आज',
        'core-calendar-upcoming.tomorrow': 'कल',
        'core-calendar-upcoming.in_days': '{{count, number}} दिन में',
        'core-calendar-upcoming.next': 'अगला',
        'core-calendar-upcoming.open': 'खोलें',
        'core-calendar-upcoming.see': 'देखें',
      }),
      ar: Object.freeze({
        'core-calendar-upcoming.title': 'ما القادم',
        'core-calendar-upcoming.add_date': 'أضف تاريخًا',
        'core-calendar-upcoming.help':
          'ثلاثة أشهر إلى الخلف واثنا عشر إلى الأمام. تُحسب هنا تواريخ انتهاء النطاقات وتاريخ امتلاء القرص المتوقع؛ أما التجديدات والفواتير وكل ما لا يعرفه سواك فتضيفه أنت. وخلفنا التنبيهات الحرجة التي أطلقت، لذا يوجد جواب لسؤال «ما الذي تغير آنذاك؟».',
        'core-calendar-upcoming.empty': 'لا يوجد شيء مؤرخ قادم.',
        'core-calendar-upcoming.today': 'اليوم',
        'core-calendar-upcoming.tomorrow': 'غدًا',
        'core-calendar-upcoming.in_days': 'خلال {{count, number}} أيام',
        'core-calendar-upcoming.next': 'التالي',
        'core-calendar-upcoming.open': 'فتح',
        'core-calendar-upcoming.see': 'عرض',
      }),
      zh: Object.freeze({
        'core-calendar-upcoming.title': '即将发生什么',
        'core-calendar-upcoming.add_date': '添加日期',
        'core-calendar-upcoming.help':
          '向前十二个月、向后回看三个月。域名到期日和预计磁盘满载日会在此计算；续订、发票和只有您知道的其他事项由您添加。后方显示已触发的关键警报，因此“当时发生了什么变化”也有答案。',
        'core-calendar-upcoming.empty': '没有即将到来的已定日期事项。',
        'core-calendar-upcoming.today': '今天',
        'core-calendar-upcoming.tomorrow': '明天',
        'core-calendar-upcoming.in_days': '{{count, number}} 天后',
        'core-calendar-upcoming.next': '下一步',
        'core-calendar-upcoming.open': '打开',
        'core-calendar-upcoming.see': '查看',
      }),
    }),
  }),

  text(key, helpers) {
    return helpers.i18n.t(
      `core-calendar-upcoming.${key}`,
      {},
      this.manifest.catalogs.en[`core-calendar-upcoming.${key}`],
    );
  },

  render(data, helpers) {
    const { target, note, humanTime, section, showView, i18n } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (!target) return;
    if (!data.upcoming?.length) {
      target.replaceChildren(
        note(
          t('core-calendar-upcoming.empty', {}, 'Nothing dated is coming up.'),
        ),
      );
      return;
    }
    const list = document.createElement('div');
    list.className = 'upcoming';
    for (const entry of data.upcoming.slice(0, 8)) {
      const days = Math.round((entry.at - data.now) / 86400);
      const row = document.createElement('div');
      row.className = `upcoming-row level-${entry.level}`;
      const when = document.createElement('span');
      when.className = 'upcoming-when';
      when.textContent =
        days === 0
          ? t('core-calendar-upcoming.today', {}, 'today')
          : days === 1
            ? t('core-calendar-upcoming.tomorrow', {}, 'tomorrow')
            : t(
                'core-calendar-upcoming.in_days',
                { count: days },
                'in {{count, number}} days',
              );
      row.appendChild(when);
      const what = document.createElement('span');
      what.textContent = entry.title;
      if (entry.priority) what.className = 'upcoming-priority';
      row.appendChild(what);
      const on = document.createElement('span');
      on.className = 'muted small';
      on.textContent = humanTime(entry.at).split(',')[0];
      row.appendChild(on);
      if (entry.link) {
        const go = document.createElement('a');
        go.href = entry.link;
        go.target = '_blank';
        go.rel = 'noopener noreferrer';
        go.className = 'link';
        go.textContent = t('core-calendar-upcoming.open', {}, 'Open');
        row.appendChild(go);
      }
      if (entry.view) {
        const go = document.createElement('button');
        go.className = 'link';
        go.textContent =
          entry.viewLabel || t('core-calendar-upcoming.see', {}, 'See');
        go.addEventListener('click', () => showView(entry.view));
        row.appendChild(go);
      }
      list.appendChild(row);
    }
    target.replaceChildren(
      section(t('core-calendar-upcoming.next', {}, 'Next'), list),
    );
  },
};
