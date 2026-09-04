/* Core card package: account CPU and memory over time. */
const CpuChartCard = {
  manifest: Object.freeze({
    id: 'core-cpu-chart',
    locales: ['core-cpu-chart.'],
    owner: 'core-cpu',
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
      en: Object.freeze({
        'core-cpu-chart.title': 'CPU and memory',
        'core-cpu-chart.help':
          'Share of what this account may use, since the previous reading.',
        'core-cpu-chart.ready': 'CPU chart',
        'core-cpu-chart.cpu_percent': 'CPU %',
        'core-cpu-chart.memory_used_right': 'Memory used (right)',
        'core-cpu-chart.memory_percent': 'Memory %',
        'core-cpu-chart.host_allows_above': ' See what the host allows, above.',
        'core-cpu-chart.cpu_note_absolute':
          'Share of what this account may use, since the previous reading. Memory is bytes consumed on the right, out of {{total}} available to this account.{{hostNote}}',
        'core-cpu-chart.cpu_note_relative':
          'Share of what this account may use, since the previous reading.{{hostNote}}',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-cpu/locales/es/chart.json',
      hi: 'packages/core-cpu/locales/hi/chart.json',
      ar: 'packages/core-cpu/locales/ar/chart.json',
      zh: 'packages/core-cpu/locales/zh/chart.json',
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-cpu-chart.${key}`,
      {},
      this.manifest.catalogs.en[`core-cpu-chart.${key}`],
    );
  },
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
        label: i18n.t('core-cpu-chart.cpu_percent', {}, 'CPU %'),
      },
      absolute
        ? {
            key: 'mem_used_bytes',
            label: i18n.t(
              'core-cpu-chart.memory_used_right',
              {},
              'Memory used (right)',
            ),
            axis: 'right',
          }
        : {
            key: 'mem_used_percent',
            label: i18n.t('core-cpu-chart.memory_percent', {}, 'Memory %'),
          },
    ];
    drawLineChart(canvas, prepared, series, {
      outages: outageBands(uptime),
      axis: percentAxis,
      rightAxis: absolute ? bytesAxis : undefined,
      zeroBased: true,
      max: 100,
    });
    const latest = [...prepared]
      .reverse()
      .find((row) => row.mem_total_bytes != null);
    const hostNote = limitsAvailable
      ? i18n.t(
          'core-cpu-chart.host_allows_above',
          {},
          ' See what the host allows, above.',
        )
      : '';
    noteTarget.textContent =
      absolute && latest
        ? i18n.t(
            'core-cpu-chart.cpu_note_absolute',
            { total: humanBytes(latest.mem_total_bytes), hostNote },
            'Share of what this account may use, since the previous reading. Memory is bytes consumed on the right, out of {{total}} available to this account.{{hostNote}}',
          )
        : i18n.t(
            'core-cpu-chart.cpu_note_relative',
            { hostNote },
            'Share of what this account may use, since the previous reading.{{hostNote}}',
          );
  },
};
Zogograph.cards.register(CpuChartCard);
