/* Core card package: the compact uptime reading that appears on the dashboard. */
window.ZogographUptimeCard = {
  manifest: Object.freeze({
    id: 'core-uptime',
    locales: ['core-uptime.'],
    helpers: [
      'setCard',
      'summary',
      'humanDuration',
      'humanTime',
      'trimPercent',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-uptime.no_boot_time': 'no boot time recorded yet',
        'core-uptime.since': 'since {{time}}',
        'core-uptime.measured_window_up':
          'Up {{percent}}% of the measured window',
        'core-uptime.window_recorded':
          '{{percent}}% of it was actually recorded',
        'core-uptime.no_downtime': 'no downtime detected',
        'core-uptime.restarts_window':
          '{{count, number}} restarts in this window',
        'core-uptime.down_at_most': 'at most {{duration}} down',
        'core-uptime.not_recorded': '{{duration}} not recorded',
        'core-uptime.no_readings_window': 'No readings in this window.',
      }),
      es: Object.freeze({
        'core-uptime.no_boot_time': 'aún no se registró la hora de arranque',
        'core-uptime.since': 'desde {{time}}',
        'core-uptime.measured_window_up':
          'Activo el {{percent}}% de la ventana medida',
        'core-uptime.window_recorded': 'se registró realmente el {{percent}}%',
        'core-uptime.no_downtime': 'no se detectó tiempo de inactividad',
        'core-uptime.restarts_window':
          '{{count, number}} reinicios en esta ventana',
        'core-uptime.down_at_most': 'caído como máximo {{duration}}',
        'core-uptime.not_recorded': '{{duration}} sin registrar',
        'core-uptime.no_readings_window': 'No hay lecturas en esta ventana.',
      }),
      hi: Object.freeze({
        'core-uptime.no_boot_time': 'अभी कोई बूट समय दर्ज नहीं है',
        'core-uptime.since': '{{time}} से',
        'core-uptime.measured_window_up': 'मापी गई अवधि का {{percent}}% चालू',
        'core-uptime.window_recorded': 'इसका {{percent}}% वास्तव में दर्ज हुआ',
        'core-uptime.no_downtime': 'कोई डाउनटाइम नहीं मिला',
        'core-uptime.restarts_window':
          'इस अवधि में {{count, number}} रीस्टार्ट',
        'core-uptime.down_at_most': 'अधिकतम {{duration}} बंद',
        'core-uptime.not_recorded': '{{duration}} दर्ज नहीं',
        'core-uptime.no_readings_window': 'इस अवधि में कोई रीडिंग नहीं है।',
      }),
      ar: Object.freeze({
        'core-uptime.no_boot_time': 'لم يُسجل وقت إقلاع بعد',
        'core-uptime.since': 'منذ {{time}}',
        'core-uptime.measured_window_up':
          'كان يعمل خلال {{percent}}% من النافذة المقاسة',
        'core-uptime.window_recorded': 'سُجل فعلياً {{percent}}% منها',
        'core-uptime.no_downtime': 'لم يُكتشف وقت توقف',
        'core-uptime.restarts_window':
          '{{count, number}} عمليات إعادة تشغيل في هذه النافذة',
        'core-uptime.down_at_most': 'متوقف لمدة {{duration}} على الأكثر',
        'core-uptime.not_recorded': '{{duration}} غير مسجلة',
        'core-uptime.no_readings_window': 'لا توجد قراءات في هذه النافذة.',
      }),
      zh: Object.freeze({
        'core-uptime.no_boot_time': '尚未记录启动时间',
        'core-uptime.since': '自 {{time}} 起',
        'core-uptime.measured_window_up': '在测量窗口内运行了 {{percent}}%',
        'core-uptime.window_recorded': '其中实际记录了 {{percent}}%',
        'core-uptime.no_downtime': '未检测到停机',
        'core-uptime.restarts_window': '此窗口内重启 {{count, number}} 次',
        'core-uptime.down_at_most': '最多停机 {{duration}}',
        'core-uptime.not_recorded': '有 {{duration}} 未记录',
        'core-uptime.no_readings_window': '此窗口中没有读数。',
      }),
    }),
  }),
  render(input, helpers) {
    const { upFor, bootAt, analysis = {} } = input;
    const { setCard, summary, humanDuration, humanTime, trimPercent, i18n } =
      helpers;
    if (upFor == null) {
      setCard(
        '—',
        i18n.t('core-uptime.no_boot_time', {}, 'no boot time recorded yet'),
      );
    } else {
      setCard(
        humanDuration(upFor),
        i18n.t(
          'core-uptime.since',
          { time: humanTime(bootAt) },
          'since {{time}}',
        ),
        upFor < 3600 ? 'warn' : '',
      );
    }
    if (!summary) return;
    const t = (key, values, fallback) =>
      i18n.t(`core-uptime.${key}`, values, fallback);
    const parts = [];
    if (analysis.uptime_percent != null)
      parts.push(
        t(
          'measured_window_up',
          { percent: trimPercent(analysis.uptime_percent) },
          'Up {{percent}}% of the measured window',
        ),
      );
    if (analysis.coverage_percent != null && analysis.coverage_percent < 100)
      parts.push(
        t(
          'window_recorded',
          { percent: trimPercent(analysis.coverage_percent) },
          '{{percent}}% of it was actually recorded',
        ),
      );
    if (
      analysis.uptime_percent === 100 &&
      analysis.unmonitored > 0 &&
      !analysis.boots
    )
      parts.push(t('no_downtime', {}, 'no downtime detected'));
    if (analysis.boots)
      parts.push(
        t(
          'restarts_window',
          { count: analysis.boots },
          '{{count, number}} restarts in this window',
        ),
      );
    if (analysis.downtime)
      parts.push(
        t(
          'down_at_most',
          { duration: humanDuration(analysis.downtime) },
          'at most {{duration}} down',
        ),
      );
    if (analysis.unmonitored)
      parts.push(
        t(
          'not_recorded',
          { duration: humanDuration(analysis.unmonitored) },
          '{{duration}} not recorded',
        ),
      );
    summary.textContent = parts.length
      ? parts.join(' · ') + '.'
      : t('no_readings_window', {}, 'No readings in this window.');
  },
};
