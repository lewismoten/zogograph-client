/* Core card package: live countdowns for collector-backed work. */
window.ZogographPendingStatusCard = {
  manifest: Object.freeze({
    id: 'core-pending-status',
    locales: ['core-pending-status.'],
    helpers: ['humanDuration', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-pending-status.schedule_unknown':
          "The collector's schedule is not known yet, so there is no countdown to give.",
        'core-pending-status.collector_overdue':
          'The collector last ran {{age}} ago and runs every {{interval}} — this will not arrive until it runs again.',
        'core-pending-status.expected_in': 'Expected in {{duration}}',
        'core-pending-status.due_now': 'Due now',
        'core-pending-status.past_due':
          '{{duration}} past due — the collector may not be running',
      }),
      es: Object.freeze({
        'core-pending-status.schedule_unknown':
          'Aún no se conoce el horario del recopilador, por lo que no hay cuenta atrás que mostrar.',
        'core-pending-status.collector_overdue':
          'El recopilador se ejecutó por última vez hace {{age}} y se ejecuta cada {{interval}}; esto no llegará hasta que vuelva a ejecutarse.',
        'core-pending-status.expected_in': 'Previsto en {{duration}}',
        'core-pending-status.due_now': 'Vence ahora',
        'core-pending-status.past_due':
          'Vencido hace {{duration}}; es posible que el recopilador no esté ejecutándose',
      }),
      hi: Object.freeze({
        'core-pending-status.schedule_unknown':
          'कलेक्टर का शेड्यूल अभी ज्ञात नहीं है, इसलिए दिखाने के लिए कोई उलटी गिनती नहीं है।',
        'core-pending-status.collector_overdue':
          'कलेक्टर आखिरी बार {{age}} पहले चला था और हर {{interval}} में चलता है — यह उसके दोबारा चलने तक नहीं आएगा।',
        'core-pending-status.expected_in': '{{duration}} में अपेक्षित',
        'core-pending-status.due_now': 'अब देय',
        'core-pending-status.past_due':
          '{{duration}} से विलंबित — कलेक्टर शायद चल नहीं रहा है',
      }),
      ar: Object.freeze({
        'core-pending-status.schedule_unknown':
          'جدول المُجمّع غير معروف بعد، لذلك لا يوجد عد تنازلي يمكن عرضه.',
        'core-pending-status.collector_overdue':
          'آخر تشغيل للمُجمّع كان قبل {{age}} ويعمل كل {{interval}} — لن يصل هذا حتى يعمل مجددًا.',
        'core-pending-status.expected_in': 'متوقع خلال {{duration}}',
        'core-pending-status.due_now': 'مستحق الآن',
        'core-pending-status.past_due':
          'متأخر {{duration}} — قد لا يكون المُجمّع قيد التشغيل',
      }),
      zh: Object.freeze({
        'core-pending-status.schedule_unknown':
          '尚不知道收集器的计划，因此没有可显示的倒计时。',
        'core-pending-status.collector_overdue':
          '收集器上次运行于 {{age}} 前，通常每 {{interval}} 运行一次——它再次运行前不会到达。',
        'core-pending-status.expected_in': '预计在 {{duration}} 后',
        'core-pending-status.due_now': '现在到期',
        'core-pending-status.past_due':
          '已逾期 {{duration}}——收集器可能没有运行',
      }),
    }),
  }),

  render({ rows, clock, now }, helpers) {
    const { humanDuration, i18n } = helpers;
    const t = (key, values = {}) =>
      i18n.t(
        `core-pending-status.${key}`,
        values,
        this.manifest.catalogs.en[`core-pending-status.${key}`],
      );
    let animate = false;
    for (const item of document.querySelectorAll('.pending-item')) {
      const row = rows.find((entry) => entry.task === item.dataset.task);
      if (!row) continue;
      const when = item.querySelector('.pending-when');
      const fill = item.querySelector('.pending-fill');
      if (!clock.lastAt || !clock.interval) {
        when.textContent = t('schedule_unknown');
        fill.style.width = '0%';
        continue;
      }
      const overdue = now - clock.lastAt - clock.interval;
      if (overdue > clock.interval) {
        fill.classList.add('late');
        fill.style.width = '100%';
        when.textContent = t('collector_overdue', {
          age: humanDuration(now - clock.lastAt),
          interval: humanDuration(clock.interval),
        });
        animate = true;
        continue;
      }
      let due = clock.lastAt + clock.interval;
      while (due < row.requested_at) due += clock.interval;
      const total = Math.max(1, due - row.requested_at);
      const left = due - now;
      if (left > 0) {
        animate = true;
        fill.classList.remove('late');
        fill.style.width = `${Math.max(0, Math.min(100, (1 - left / total) * 100))}%`;
        when.textContent = t('expected_in', {
          duration: humanDuration(left),
        });
      } else {
        fill.classList.add('late');
        fill.style.width = '100%';
        when.textContent =
          -left < clock.interval
            ? t('due_now')
            : t('past_due', { duration: humanDuration(-left) });
        animate = true;
      }
    }
    return animate;
  },
};
