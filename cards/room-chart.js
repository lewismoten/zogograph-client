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
      en: Object.freeze({
        'core-room-chart.chart_disk_free': 'Disk free',
        'core-room-chart.chart_inodes_free_right': 'Inodes free (right)',
        'core-room-chart.room_note_bytes':
          'Disk space and inodes. Either can run out on its own — a disk with free gigabytes and no inodes still refuses to create a file.',
        'core-room-chart.chart_disk_free_percent': 'Disk free %',
        'core-room-chart.chart_inodes_free_percent': 'Inodes free %',
        'core-room-chart.room_note_percent':
          'How close to the edge each one is. Both on one scale, so they can be read against each other.',
        'core-room-chart.chart_disk_change': 'Disk change',
        'core-room-chart.chart_inode_change_right': 'Inode change (right)',
        'core-room-chart.room_note_delta':
          'What changed between one reading and the next. Below the line is space being consumed, above it is space coming back — small, steady losses are what a leak looks like, and one deep spike is a file being written.',
        'core-room-chart.bucket_day': 'day',
        'core-room-chart.bucket_hour': 'hour',
        'core-room-chart.bucket_hours': '{{count, number}} hours',
        'core-room-chart.chart_disk_change_per': 'Disk change per {{period}}',
        'core-room-chart.chart_inode_change_per':
          'Inode change per {{period}} (right)',
        'core-room-chart.room_note_bucket':
          'Totalled per {{period}} over this range, because a reading a minute is more points than there are pixels to draw them in.',
        'core-room-chart.room_note_largest_change':
          'Largest single change: {{size}}.',
      }),
      es: Object.freeze({
        'core-room-chart.chart_disk_free': 'Disco libre',
        'core-room-chart.chart_inodes_free_right': 'Inodos libres (derecha)',
        'core-room-chart.room_note_bytes':
          'Espacio en disco e inodos. Cualquiera puede agotarse por sí solo: un disco con gigabytes libres y sin inodos aún rechaza crear un archivo.',
        'core-room-chart.chart_disk_free_percent': 'Disco libre %',
        'core-room-chart.chart_inodes_free_percent': 'Inodos libres %',
        'core-room-chart.room_note_percent':
          'Qué tan cerca del límite está cada uno. Ambos están en una escala, por lo que se pueden leer entre sí.',
        'core-room-chart.chart_disk_change': 'Cambio de disco',
        'core-room-chart.chart_inode_change_right':
          'Cambio de inodos (derecha)',
        'core-room-chart.room_note_delta':
          'Lo que cambió entre una lectura y la siguiente. Debajo de la línea se consume espacio; por encima vuelve espacio. Las pérdidas pequeñas y constantes parecen una fuga, y un pico profundo es un archivo que se escribe.',
        'core-room-chart.bucket_day': 'día',
        'core-room-chart.bucket_hour': 'hora',
        'core-room-chart.bucket_hours': '{{count, number}} horas',
        'core-room-chart.chart_disk_change_per':
          'Cambio de disco por {{period}}',
        'core-room-chart.chart_inode_change_per':
          'Cambio de inodos por {{period}} (derecha)',
        'core-room-chart.room_note_bucket':
          'Totalizado por {{period}} en este intervalo, porque una lectura por minuto son más puntos de los que hay píxeles para dibujarlos.',
        'core-room-chart.room_note_largest_change':
          'Cambio individual más grande: {{size}}.',
      }),
      hi: Object.freeze({
        'core-room-chart.chart_disk_free': 'खाली डिस्क',
        'core-room-chart.chart_inodes_free_right': 'खाली इनोड (दायाँ)',
        'core-room-chart.room_note_bytes':
          'डिस्क स्थान और इनोड। दोनों में से कोई भी अपने आप समाप्त हो सकता है — खाली गीगाबाइट लेकिन इनोड न होने वाला डिस्क भी फ़ाइल बनाने से मना कर देता है।',
        'core-room-chart.chart_disk_free_percent': 'खाली डिस्क %',
        'core-room-chart.chart_inodes_free_percent': 'खाली इनोड %',
        'core-room-chart.room_note_percent':
          'हर एक सीमा के कितना करीब है। दोनों एक ही पैमाने पर हैं, इसलिए उन्हें एक-दूसरे के विरुद्ध पढ़ा जा सकता है।',
        'core-room-chart.chart_disk_change': 'डिस्क बदलाव',
        'core-room-chart.chart_inode_change_right': 'इनोड बदलाव (दायाँ)',
        'core-room-chart.room_note_delta':
          'एक रीडिंग और अगली के बीच क्या बदला। रेखा के नीचे स्थान खर्च हो रहा है, ऊपर स्थान वापस आ रहा है — छोटे, लगातार नुकसान रिसाव जैसे दिखते हैं और एक गहरा उछाल फ़ाइल लिखे जाने जैसा है।',
        'core-room-chart.bucket_day': 'दिन',
        'core-room-chart.bucket_hour': 'घंटा',
        'core-room-chart.bucket_hours': '{{count, number}} घंटे',
        'core-room-chart.chart_disk_change_per': 'प्रति {{period}} डिस्क बदलाव',
        'core-room-chart.chart_inode_change_per':
          'प्रति {{period}} इनोड बदलाव (दायाँ)',
        'core-room-chart.room_note_bucket':
          'इस सीमा में प्रति {{period}} कुल किया गया, क्योंकि प्रति मिनट एक रीडिंग में उन्हें खींचने के लिए पिक्सेल से अधिक बिंदु होते हैं।',
        'core-room-chart.room_note_largest_change':
          'सबसे बड़ा एकल बदलाव: {{size}}।',
      }),
      ar: Object.freeze({
        'core-room-chart.chart_disk_free': 'المساحة الحرة',
        'core-room-chart.chart_inodes_free_right': 'عُقد حرة (يمين)',
        'core-room-chart.room_note_bytes':
          'مساحة القرص والعُقد. يمكن أن ينفد أي منهما وحده — فالقرص الذي به غيغابايتات فارغة ولا عُقد فيه يرفض إنشاء ملف.',
        'core-room-chart.chart_disk_free_percent': 'المساحة الحرة %',
        'core-room-chart.chart_inodes_free_percent': 'العُقد الحرة %',
        'core-room-chart.room_note_percent':
          'مدى قرب كل منهما من الحافة. كلاهما على مقياس واحد، لذا يمكن قراءتهما معًا.',
        'core-room-chart.chart_disk_change': 'تغير القرص',
        'core-room-chart.chart_inode_change_right': 'تغير العُقد (يمين)',
        'core-room-chart.room_note_delta':
          'ما تغير بين قراءة وأخرى. أسفل الخط مساحة تُستهلك، وفوقه مساحة تعود — الخسائر الصغيرة الثابتة تشبه تسرّبًا، والارتفاع العميق هو ملف يُكتب.',
        'core-room-chart.bucket_day': 'يوم',
        'core-room-chart.bucket_hour': 'ساعة',
        'core-room-chart.bucket_hours': '{{count, number}} ساعات',
        'core-room-chart.chart_disk_change_per': 'تغير القرص لكل {{period}}',
        'core-room-chart.chart_inode_change_per':
          'تغير العُقد لكل {{period}} (يمين)',
        'core-room-chart.room_note_bucket':
          'جُمعت لكل {{period}} ضمن هذا النطاق، لأن قراءة كل دقيقة تعني نقاطًا أكثر من وحدات البكسل المتاحة لرسمها.',
        'core-room-chart.room_note_largest_change':
          'أكبر تغير منفرد: {{size}}.',
      }),
      zh: Object.freeze({
        'core-room-chart.chart_disk_free': '磁盘可用空间',
        'core-room-chart.chart_inodes_free_right': '可用 inode（右轴）',
        'core-room-chart.room_note_bytes':
          '磁盘空间和 inode。两者任一都可能单独耗尽——即使磁盘仍有可用 GB，没有 inode 也会拒绝创建文件。',
        'core-room-chart.chart_disk_free_percent': '磁盘可用空间 %',
        'core-room-chart.chart_inodes_free_percent': '可用 inode %',
        'core-room-chart.room_note_percent':
          '每项离边界有多近。两者都在同一刻度上，因此可以相互比较。',
        'core-room-chart.chart_disk_change': '磁盘变化',
        'core-room-chart.chart_inode_change_right': 'inode 变化（右轴）',
        'core-room-chart.room_note_delta':
          '相邻两次读数之间的变化。线下表示空间正在被消耗，线上表示空间正在恢复——小而持续的损失像泄漏，一个很深的尖峰则像正在写入文件。',
        'core-room-chart.bucket_day': '天',
        'core-room-chart.bucket_hour': '小时',
        'core-room-chart.bucket_hours': '{{count, number}} 小时',
        'core-room-chart.chart_disk_change_per': '每 {{period}} 磁盘变化',
        'core-room-chart.chart_inode_change_per':
          '每 {{period}} inode 变化（右轴）',
        'core-room-chart.room_note_bucket':
          '在此范围内按每 {{period}} 汇总，因为每分钟一次读数的点数会多于可绘制它们的像素数。',
        'core-room-chart.room_note_largest_change': '最大单次变化：{{size}}。',
      }),
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
            label: ZogographI18n.t('core-room-chart.chart_disk_free'),
          },
          {
            key: 'inodes_free',
            label: ZogographI18n.t('core-room-chart.chart_inodes_free_right'),
            axis: 'right',
          },
        ],
        options: { axis: bytesAxis, rightAxis: countAxis },
        note: ZogographI18n.t(
          'core-room-chart.room_note_bytes',
          {},
          'Disk space and inodes. Either can run out on its own — a disk with free gigabytes and no inodes still refuses to create a file.',
        ),
      },
      percent: {
        series: [
          {
            key: 'disk_free_percent',
            label: ZogographI18n.t('core-room-chart.chart_disk_free_percent'),
          },
          {
            key: 'inodes_free_percent',
            label: ZogographI18n.t('core-room-chart.chart_inodes_free_percent'),
          },
        ],
        options: { axis: percentAxis, zeroBased: true, max: 100 },
        note: ZogographI18n.t(
          'core-room-chart.room_note_percent',
          {},
          'How close to the edge each one is. Both on one scale, so they can be read against each other.',
        ),
      },
      delta: {
        series: [
          {
            key: 'disk_delta',
            label: ZogographI18n.t('core-room-chart.chart_disk_change'),
          },
          {
            key: 'inodes_delta',
            label: ZogographI18n.t('core-room-chart.chart_inode_change_right'),
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
          'core-room-chart.room_note_delta',
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
          ? ZogographI18n.t('core-room-chart.bucket_day', {}, 'day')
          : bucket === 3600
            ? ZogographI18n.t('core-room-chart.bucket_hour', {}, 'hour')
            : ZogographI18n.t(
                'core-room-chart.bucket_hours',
                { count: Math.round(bucket / 3600) },
                '{{count, number}} hours',
              );

      shape.series = shape.series.map((entry) => ({
        ...entry,
        label:
          entry.key === 'disk_delta'
            ? ZogographI18n.t(
                'core-room-chart.chart_disk_change_per',
                { period: per },
                'Disk change per {{period}}',
              )
            : ZogographI18n.t(
                'core-room-chart.chart_inode_change_per',
                { period: per },
                'Inode change per {{period}} (right)',
              ),
      }));
      note += ` ${ZogographI18n.t(
        'core-room-chart.room_note_bucket',
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
          'core-room-chart.room_note_largest_change',
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
