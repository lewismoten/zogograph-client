/* Core card package: account CPU and memory over time. */
window.ZogographCpuChart = {
  manifest: Object.freeze({
    id: 'core-cpu-chart',
    locales: ['core-cpu-chart.'],
    helpers: [
      'canvas',
      'noteTarget',
      'units',
      'uptime',
      'limitsAvailable',
      'drawLineChart',
      'outageBands',
      'percentAxis',
      'bytesAxis',
      'humanBytes',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({ 'core-cpu-chart.ready': 'CPU chart' }),
      es: Object.freeze({ 'core-cpu-chart.ready': 'Gráfico de CPU' }),
      hi: Object.freeze({ 'core-cpu-chart.ready': 'CPU चार्ट' }),
      ar: Object.freeze({ 'core-cpu-chart.ready': 'مخطط المعالج' }),
      zh: Object.freeze({ 'core-cpu-chart.ready': 'CPU 图表' }),
    }),
  }),
  render(rows, helpers) {
    const {
      canvas,
      noteTarget,
      units,
      uptime,
      limitsAvailable,
      drawLineChart,
      outageBands,
      percentAxis,
      bytesAxis,
      humanBytes,
      i18n,
    } = helpers;
    if (!canvas || !noteTarget) return;
    const ZogographI18n = i18n;
    const absolute = units === 'bytes';

    const prepared = absolute
      ? rows.map((row) => ({
          ...row,
          mem_used_bytes:
            row.mem_total_bytes == null || row.mem_available_bytes == null
              ? null
              : Number(row.mem_total_bytes) - Number(row.mem_available_bytes),
        }))
      : rows;

    const series = [
      {
        key: 'cpu_percent',
        label: ZogographI18n.t('core-cpu-chart.cpu_percent', {}, 'CPU %'),
      },
    ];
    series.push(
      absolute
        ? {
            key: 'mem_used_bytes',
            label: ZogographI18n.t(
              'core-cpu-chart.memory_used_right',
              {},
              'Memory used (right)',
            ),
            axis: 'right',
          }
        : {
            key: 'mem_used_percent',
            label: ZogographI18n.t(
              'core-cpu-chart.memory_percent',
              {},
              'Memory %',
            ),
          },
    );

    drawLineChart(canvas, prepared, series, {
      outages: outageBands(uptime),
      axis: percentAxis,
      rightAxis: absolute ? bytesAxis : undefined,
      zeroBased: true,
      // Caps the percentage axis. Harmless on the byte axis beside it, which is
      // already orders of magnitude past a hundred.
      max: 100,
    });

    // The last reading that actually had a memory figure, so the note can say how
    // much of the machine that is. A host with no /proc/meminfo has none at all.
    const latest = [...prepared]
      .reverse()
      .find((row) => row.mem_total_bytes != null);

    /*
     * "Share of the machine" was the wrong words, and they mattered.
     *
     * Inside a cage this is the share of what the account is *allowed*, not of
     * the server, and a chart at 95% next to an idle machine has been the single
     * most misleading thing on this page. The limits panel carries the
     * comparison; the note only has to stop claiming the wrong denominator.
     */
    const cap = limitsAvailable
      ? ZogographI18n.t(
          'core-cpu-chart.host_allows_above',
          {},
          ' See what the host allows, above.',
        )
      : '';
    noteTarget.textContent =
      absolute && latest
        ? ZogographI18n.t(
            'core-cpu-chart.cpu_note_absolute',
            { total: humanBytes(latest.mem_total_bytes), hostNote: cap },
            'Share of what this account may use, since the previous reading. Memory is bytes consumed on the right, out of {{total}} available to this account.{{hostNote}}',
          )
        : ZogographI18n.t(
            'core-cpu-chart.cpu_note_relative',
            { hostNote: cap },
            'Share of what this account may use, since the previous reading.{{hostNote}}',
          );
  },
};
