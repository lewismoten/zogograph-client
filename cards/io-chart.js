/* Core card package: account and device disk I/O. */
window.ZogographIoChart = {
  manifest: Object.freeze({
    id: 'core-io-chart',
    locales: ['core-io-chart.'],
    helpers: [
      'canvas',
      'noteTarget',
      'uptime',
      'drawLineChart',
      'outageBands',
      'rateAxis',
      'percentAxis',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({ 'core-io-chart.ready': 'I/O chart' }),
      es: Object.freeze({ 'core-io-chart.ready': 'Gráfico de E/S' }),
      hi: Object.freeze({ 'core-io-chart.ready': 'I/O चार्ट' }),
      ar: Object.freeze({ 'core-io-chart.ready': 'مخطط الإدخال/الإخراج' }),
      zh: Object.freeze({ 'core-io-chart.ready': 'I/O 图表' }),
    }),
  }),
  render(rows, helpers) {
    const {
      canvas,
      noteTarget,
      uptime,
      drawLineChart,
      outageBands,
      rateAxis,
      percentAxis,
      i18n,
    } = helpers;
    if (!canvas || !noteTarget) return;
    const ZogographI18n = i18n;
    const has = (key) => rows.some((r) => r[key] != null);

    const series = [];
    if (has('own_read_rate'))
      series.push({
        key: 'own_read_rate',
        label: ZogographI18n.t(
          'core-io-chart.account_read',
          {},
          'This account, read',
        ),
      });
    if (has('own_write_rate'))
      series.push({
        key: 'own_write_rate',
        label: ZogographI18n.t(
          'core-io-chart.account_written',
          {},
          'This account, written',
        ),
      });
    if (has('io_read_rate'))
      series.push({
        key: 'io_read_rate',
        label: ZogographI18n.t(
          'core-io-chart.io_device_read',
          {},
          'Device read',
        ),
      });
    if (has('io_write_rate'))
      series.push({
        key: 'io_write_rate',
        label: ZogographI18n.t(
          'core-io-chart.io_device_written',
          {},
          'Device written',
        ),
      });
    if (has('io_util_percent')) {
      series.push({
        key: 'io_util_percent',
        label: ZogographI18n.t(
          'core-io-chart.device_busy_right',
          {},
          'Device busy % (right)',
        ),
        axis: 'right',
      });
    }

    drawLineChart(canvas, rows, series, {
      outages: outageBands(uptime),
      axis: rateAxis,
      rightAxis: percentAxis,
      zeroBased: true,
      empty: series.length
        ? ZogographI18n.t(
            'core-io-chart.nothing_collected_range',
            {},
            'Nothing collected for this range yet',
          )
        : ZogographI18n.t(
            'core-io-chart.io_empty_unreadable',
            {},
            'No disk I/O readings — neither /proc/diskstats nor /proc/<pid>/io could be read',
          ),
    });

    const latest = [...rows]
      .reverse()
      .find((r) => r.io_util_percent != null || r.own_read_rate != null);
    const parts = [
      ZogographI18n.t(
        'core-io-chart.io_bytes_to_disk',
        {},
        'Bytes actually reaching a disk, not bytes served from cache.',
      ),
    ];

    /*
     * Which halves are here and which are not, said plainly.
     *
     * On a caged account the device lines are simply absent, and a chart with two
     * lines where the text describes four leaves the reader wondering whether
     * something is broken. Nothing is: /proc/diskstats is filtered out, and the
     * host collector has it.
     */
    if (!has('io_read_rate')) {
      parts.push(
        ZogographI18n.t(
          'core-io-chart.io_device_missing',
          {},
          'The device lines are missing because /proc/diskstats is not readable from this account — see the host panel, which reads it as root.',
        ),
      );
    }
    if (!has('own_read_rate')) {
      parts.push(
        ZogographI18n.t(
          'core-io-chart.io_account_missing',
          {},
          'This account’s own traffic is missing too: /proc/<pid>/io could not be read for any process here.',
        ),
      );
    }
    // Only worth saying when those lines are actually on the chart. Explaining
    // what the device lines cover, immediately after saying there are none, reads
    // as a contradiction.
    if (has('io_read_rate') && has('own_read_rate')) {
      parts.push(
        ZogographI18n.t(
          'core-io-chart.io_scope',
          {},
          'The device lines cover every account on the machine; only the “this account” lines are yours.',
        ),
      );
    }

    if (latest && latest.io_util_percent != null) {
      parts.push(
        ZogographI18n.t(
          'core-io-chart.io_busy',
          { percent: Number(latest.io_util_percent).toFixed(0) },
          'The device was busy {{percent}}% of the last interval.',
        ),
      );
    }
    if (latest && latest.io_pressure_full != null) {
      /*
       * Pressure, where the kernel offers it, because utilisation alone is a poor
       * proxy for harm: a disk can sit at 100% pushing background writeback and
       * bother nobody. This measures the opposite thing - time spent *waiting*.
       */
      parts.push(
        ZogographI18n.t(
          'core-io-chart.io_stalled',
          {
            some: Number(latest.io_pressure_some).toFixed(1),
            full: Number(latest.io_pressure_full).toFixed(1),
          },
          'Tasks were stalled waiting on the disk {{some}}% of the time, and nothing at all could run for {{full}}%.',
        ),
      );
    }
    noteTarget.textContent = parts.join(' ');
  },
};
