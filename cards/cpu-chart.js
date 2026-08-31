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
      en: Object.freeze({
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
      es: Object.freeze({
        'core-cpu-chart.ready': 'Gráfico de CPU',
        'core-cpu-chart.cpu_percent': 'CPU %',
        'core-cpu-chart.memory_used_right': 'Memoria usada (derecha)',
        'core-cpu-chart.memory_percent': 'Memoria %',
        'core-cpu-chart.host_allows_above':
          ' Consulte arriba lo que permite el host.',
        'core-cpu-chart.cpu_note_absolute':
          'Parte de lo que esta cuenta puede usar desde la lectura anterior. La memoria son bytes consumidos a la derecha, de {{total}} disponibles para esta cuenta.{{hostNote}}',
        'core-cpu-chart.cpu_note_relative':
          'Parte de lo que esta cuenta puede usar desde la lectura anterior.{{hostNote}}',
      }),
      hi: Object.freeze({
        'core-cpu-chart.ready': 'CPU चार्ट',
        'core-cpu-chart.cpu_percent': 'CPU %',
        'core-cpu-chart.memory_used_right': 'उपयोग की गई मेमोरी (दायाँ)',
        'core-cpu-chart.memory_percent': 'मेमोरी %',
        'core-cpu-chart.host_allows_above':
          ' ऊपर देखें कि होस्ट क्या अनुमति देता है।',
        'core-cpu-chart.cpu_note_absolute':
          'पिछली रीडिंग के बाद से यह खाता जितना उपयोग कर सकता है उसका हिस्सा। दाईं ओर की मेमोरी इस खाते के लिए उपलब्ध {{total}} में से उपयोग किए गए बाइट हैं।{{hostNote}}',
        'core-cpu-chart.cpu_note_relative':
          'पिछली रीडिंग के बाद से यह खाता जितना उपयोग कर सकता है उसका हिस्सा।{{hostNote}}',
      }),
      ar: Object.freeze({
        'core-cpu-chart.ready': 'مخطط المعالج',
        'core-cpu-chart.cpu_percent': 'المعالج %',
        'core-cpu-chart.memory_used_right': 'الذاكرة المستخدمة (يمين)',
        'core-cpu-chart.memory_percent': 'الذاكرة %',
        'core-cpu-chart.host_allows_above': ' راجع ما يسمح به المضيف أعلاه.',
        'core-cpu-chart.cpu_note_absolute':
          'حصة مما يمكن لهذا الحساب استخدامه منذ القراءة السابقة. الذاكرة هي البايتات المستهلكة على اليمين من {{total}} المتاحة لهذا الحساب.{{hostNote}}',
        'core-cpu-chart.cpu_note_relative':
          'حصة مما يمكن لهذا الحساب استخدامه منذ القراءة السابقة.{{hostNote}}',
      }),
      zh: Object.freeze({
        'core-cpu-chart.ready': 'CPU 图表',
        'core-cpu-chart.cpu_percent': 'CPU %',
        'core-cpu-chart.memory_used_right': '已用内存（右轴）',
        'core-cpu-chart.memory_percent': '内存 %',
        'core-cpu-chart.host_allows_above': ' 请参阅上方主机允许的用量。',
        'core-cpu-chart.cpu_note_absolute':
          '自上次读数以来，此帐户可用份额。右轴内存为此帐户可用 {{total}} 中已消耗的字节数。{{hostNote}}',
        'core-cpu-chart.cpu_note_relative':
          '自上次读数以来，此帐户可用份额。{{hostNote}}',
      }),
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
