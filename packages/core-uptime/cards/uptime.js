/* Core card package: the compact uptime reading that appears on the dashboard. */
const UptimeCard = {
  manifest: Object.freeze({
    id: 'core-uptime',
    locales: ['core-uptime.'],
    owner: 'core-uptime',
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
        'core-uptime.title': 'Uptime and outages',
        'core-uptime.gaps_help':
          'A hole in the readings is not the same as the machine being off, and the charts distinguish them — {{restart_shading}} where the boot time moved, so it really did restart, and {{gap_shading}} where the boot time was unchanged either side and the collector simply was not running. Uptime counts only the first kind; a cron job that stops should not look like a server that keeps falling over.',
        'core-uptime.restart_help':
          'A {{restart_line}} marks each restart the host knows about from {{last}}. Those need not leave a hole at all — a reboot the collector resumes after within a minute has nothing to shade — and they include restarts from before boot times were being recorded, which the readings alone cannot show.',
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
        'core-uptime.machine': 'The machine',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-uptime/locales/es/uptime.json',
      hi: 'packages/core-uptime/locales/hi/uptime.json',
      ar: 'packages/core-uptime/locales/ar/uptime.json',
      zh: 'packages/core-uptime/locales/zh/uptime.json',
    }),
  }),
  text(key, valuesOrHelpers = {}, maybeHelpers = null) {
    const helpers = maybeHelpers || valuesOrHelpers;
    const values = maybeHelpers ? valuesOrHelpers : {};
    return helpers.i18n.t(
      `core-uptime.${key}`,
      values,
      this.manifest.catalogs.en[`core-uptime.${key}`],
    );
  },
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
Zogograph.cards.register(UptimeCard);
