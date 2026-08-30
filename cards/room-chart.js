/* Core card package: disk and inode room over time. */
window.ZogographRoomChart = {
  manifest: Object.freeze({
    id: 'core-room-chart',
    locales: ['core-room-chart.'],
    helpers: [
      'canvas',
      'noteTarget',
      'mode',
      'range',
      'uptime',
      'delta',
      'deltaBucket',
      'bucketDeltas',
      'bytesAxis',
      'countAxis',
      'percentAxis',
      'drawLineChart',
      'outageBands',
      'humanBytes',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({ 'core-room-chart.ready': 'Room chart' }),
      es: Object.freeze({ 'core-room-chart.ready': 'Gráfico de espacio' }),
      hi: Object.freeze({ 'core-room-chart.ready': 'स्थान चार्ट' }),
      ar: Object.freeze({ 'core-room-chart.ready': 'مخطط المساحة' }),
      zh: Object.freeze({ 'core-room-chart.ready': '空间图表' }),
    }),
  }),
  render(rows, helpers) {
    const {
      canvas,
      noteTarget,
      mode,
      range,
      uptime,
      delta,
      deltaBucket,
      bucketDeltas,
      bytesAxis,
      countAxis,
      percentAxis,
      drawLineChart,
      outageBands,
      humanBytes,
      i18n,
    } = helpers;
    if (!canvas || !noteTarget) return;
    const ZogographI18n = i18n;

    /*
     * How long a bar of "change" covers.
     *
     * Per reading is what you want over an hour and useless over a month: at a
     * reading a minute, thirty days is forty thousand points drawn into eight
     * hundred pixels, and what reaches the screen is noise. Totalling the change
     * over a longer bucket keeps the same information at a scale that fits.
     *
     * Summed, not averaged. The total bytes lost in an hour is a real quantity
     * you can reason about; the mean of the steps inside it is a smaller number
     * that means nothing on its own. And an average would not help with spikes
     * anyway - the mean is precisely the statistic one outlier moves furthest.
     * Spikes are handled by trimming the axis and by the median-based trend.
     */
    const bucket = mode === 'delta' ? deltaBucket(range) : 0;

    const prepared = rows.map((row, i) => {
      const previous = rows[i - 1];
      const seconds = previous
        ? Number(row.taken_at) - Number(previous.taken_at)
        : 0;

      return {
        ...row,
        disk_free_percent: row.disk_total_bytes
          ? (Number(row.disk_free_bytes) / Number(row.disk_total_bytes)) * 100
          : null,
        inodes_free_percent: row.inodes_total
          ? (Number(row.inodes_free) / Number(row.inodes_total)) * 100
          : null,
        // The first reading has nothing to differ from, so it is a gap rather
        // than a zero - a drawn zero would read as "nothing changed".
        disk_delta: delta(
          previous?.disk_free_bytes,
          row.disk_free_bytes,
          seconds,
        ),
        inodes_delta: delta(previous?.inodes_free, row.inodes_free, seconds),
      };
    });

    const shapes = {
      bytes: {
        series: [
          {
            key: 'disk_free_bytes',
            label: ZogographI18n.t('storage.chart_disk_free'),
          },
          {
            key: 'inodes_free',
            label: ZogographI18n.t('storage.chart_inodes_free_right'),
            axis: 'right',
          },
        ],
        options: { axis: bytesAxis, rightAxis: countAxis },
        note: ZogographI18n.t(
          'storage.room_note_bytes',
          {},
          'Disk space and inodes. Either can run out on its own — a disk with free gigabytes and no inodes still refuses to create a file.',
        ),
      },
      percent: {
        series: [
          {
            key: 'disk_free_percent',
            label: ZogographI18n.t('storage.chart_disk_free_percent'),
          },
          {
            key: 'inodes_free_percent',
            label: ZogographI18n.t('storage.chart_inodes_free_percent'),
          },
        ],
        options: { axis: percentAxis, zeroBased: true, max: 100 },
        note: ZogographI18n.t(
          'storage.room_note_percent',
          {},
          'How close to the edge each one is. Both on one scale, so they can be read against each other.',
        ),
      },
      delta: {
        series: [
          {
            key: 'disk_delta',
            label: ZogographI18n.t('storage.chart_disk_change'),
          },
          {
            key: 'inodes_delta',
            label: ZogographI18n.t('storage.chart_inode_change_right'),
            axis: 'right',
          },
        ],
        options: {
          axis: bytesAxis,
          rightAxis: countAxis,
          zeroLine: true,
          trim: 0.02,
        },
        note: ZogographI18n.t(
          'storage.room_note_delta',
          {},
          'What changed between one reading and the next. Below the line is space being consumed, above it is space coming back — small, steady losses are what a leak looks like, and one deep spike is a file being written.',
        ),
      },
    };

    const bucketed = bucket > 0 ? bucketDeltas(prepared, bucket) : prepared;
    const shape = shapes[mode] ?? shapes.bytes;
    let note = shape.note;

    if (mode === 'delta' && bucket > 0) {
      const per =
        bucket === 86400
          ? ZogographI18n.t('storage.bucket_day', {}, 'day')
          : bucket === 3600
            ? ZogographI18n.t('storage.bucket_hour', {}, 'hour')
            : ZogographI18n.t(
                'storage.bucket_hours',
                { count: Math.round(bucket / 3600) },
                '{{count, number}} hours',
              );

      shape.series = shape.series.map((entry) => ({
        ...entry,
        label:
          entry.key === 'disk_delta'
            ? ZogographI18n.t(
                'storage.chart_disk_change_per',
                { period: per },
                'Disk change per {{period}}',
              )
            : ZogographI18n.t(
                'storage.chart_inode_change_per',
                { period: per },
                'Inode change per {{period}} (right)',
              ),
      }));
      note += ` ${ZogographI18n.t(
        'storage.room_note_bucket',
        { period: per },
        'Totalled per {{period}} over this range, because a reading a minute is more points than there are pixels to draw them in.',
      )}`;
    }

    if (mode === 'delta') {
      const biggest = bucketed.reduce((worst, row) => {
        const v = Number(row.disk_delta);
        return Number.isFinite(v) && Math.abs(v) > Math.abs(worst) ? v : worst;
      }, 0);
      if (biggest) {
        note += ` ${ZogographI18n.t(
          'storage.room_note_largest_change',
          { size: humanBytes(biggest) },
          'Largest single change: {{size}}.',
        )}`;
      }
    }

    noteTarget.textContent = note;
    drawLineChart(
      canvas,
      mode === 'delta' ? bucketed : prepared,
      shape.series,
      { ...shape.options, outages: outageBands(uptime) },
    );
  },
};
