/* Core card package: the shell supplies all rendering and formatting helpers. */
window.ZogographHostDisksCard = {
  manifest: Object.freeze({
    id: 'core-host-disks',
    locales: ['core-host-disks.'],
    helpers: ['target', 'renderTable', 'humanBytes', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-host-disks.title': 'Disks',
        'core-host-disks.help':
          'Throughput and how close each device is to saturated. LVM and RAID volumes are shown but not added into any total: a write to {{dm0}} is remapped onto the disk underneath and advances both counters, so summing them reports twice the traffic that happened.',
        'core-host-disks.old_collector':
          'The reading was written by an older build of the collector that did not record disk counters. Upload host-cron.php and lib/, then let it run twice.',
        'core-host-disks.unreadable':
          'The host collector ran but could not read /proc/diskstats, even as root.',
        'core-host-disks.needs_readings':
          'Needs two host readings before a rate can be worked out — there {{readings, number}} so far.',
        'core-host-disks.empty':
          'No devices were recorded in the latest reading.',
      }),
      es: Object.freeze({
        'core-host-disks.title': 'Discos',
        'core-host-disks.help':
          'Rendimiento y cercanía a la saturación de cada dispositivo. Los volúmenes LVM y RAID se muestran pero no se suman: una escritura en {{dm0}} se reasigna al disco inferior y avanza ambos contadores, por lo que sumarlos informa el doble de tráfico.',
        'core-host-disks.old_collector':
          'La lectura la escribió una versión antigua del recopilador que no registraba contadores de disco. Cargue host-cron.php y lib/, y déjelo ejecutarse dos veces.',
        'core-host-disks.unreadable':
          'El recopilador del host se ejecutó, pero no pudo leer /proc/diskstats, ni siquiera como root.',
        'core-host-disks.needs_readings':
          'Se necesitan dos lecturas del host para calcular una tasa; hay {{readings, number}} hasta ahora.',
        'core-host-disks.empty':
          'No se registraron dispositivos en la lectura más reciente.',
      }),
      hi: Object.freeze({
        'core-host-disks.title': 'डिस्क',
        'core-host-disks.help':
          'थ्रूपुट और हर डिवाइस के संतृप्त होने की निकटता। LVM और RAID वॉल्यूम दिखते हैं पर कुल में नहीं जोड़े जाते: {{dm0}} पर लिखना नीचे की डिस्क पर पुनर्निर्देशित होता है और दोनों काउंटर बढ़ते हैं।',
        'core-host-disks.old_collector':
          'रीडिंग कलेक्टर के पुराने बिल्ड से लिखी गई थी, जिसमें डिस्क काउंटर दर्ज नहीं होते थे। host-cron.php और lib/ अपलोड करें, फिर इसे दो बार चलने दें।',
        'core-host-disks.unreadable':
          'होस्ट कलेक्टर चला, लेकिन root के रूप में भी /proc/diskstats नहीं पढ़ सका।',
        'core-host-disks.needs_readings':
          'दर निकालने से पहले दो होस्ट रीडिंग चाहिए — अभी {{readings, number}} हैं।',
        'core-host-disks.empty': 'नवीनतम रीडिंग में कोई डिवाइस दर्ज नहीं हुआ।',
      }),
      ar: Object.freeze({
        'core-host-disks.title': 'الأقراص',
        'core-host-disks.help':
          'معدل النقل ومدى قرب كل جهاز من التشبع. تظهر وحدات LVM وRAID لكنها لا تدخل في المجموع: الكتابة إلى {{dm0}} يعاد تعيينها إلى القرص أسفله فتزيد العدادين، وجمعهما يبلّغ عن ضعف الحركة.',
        'core-host-disks.old_collector':
          'كُتبت القراءة بواسطة إصدار أقدم من الجامع لا يسجل عدادات القرص. ارفع host-cron.php وlib/، ثم اتركه يعمل مرتين.',
        'core-host-disks.unreadable':
          'عمل جامع المضيف لكنه لم يستطع قراءة /proc/diskstats حتى بصفة root.',
        'core-host-disks.needs_readings':
          'يلزم قراءتان للمضيف قبل احتساب المعدل — يوجد {{readings, number}} حتى الآن.',
        'core-host-disks.empty': 'لم تُسجَّل أجهزة في أحدث قراءة.',
      }),
      zh: Object.freeze({
        'core-host-disks.title': '磁盘',
        'core-host-disks.help':
          '每个设备的吞吐量及接近饱和的程度。LVM 和 RAID 卷会显示但不计入总计：写入 {{dm0}} 会重新映射到下层磁盘并推进两个计数器，因此相加会报告两倍的流量。',
        'core-host-disks.old_collector':
          '该读数由较旧的采集器版本写入，该版本未记录磁盘计数器。请上传 host-cron.php 和 lib/，然后让它运行两次。',
        'core-host-disks.unreadable':
          '主机采集器已运行，但即使以 root 身份也无法读取 /proc/diskstats。',
        'core-host-disks.needs_readings':
          '需要两次主机读数才能计算速率——目前有 {{readings, number}} 次。',
        'core-host-disks.empty': '最新读数中没有记录设备。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-host-disks.${key}`,
      {},
      this.manifest.catalogs.en[`core-host-disks.${key}`],
    );
  },

  render(input, helpers) {
    const { rows, sample, sampleCount } = input;
    const { target, renderTable, humanBytes, i18n } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (!target) return;

    if (!rows.length) {
      let message;
      if (sample && sample.diskstats_available == null) {
        message = t(
          'core-host-disks.old_collector',
          {},
          'The reading was written by an older build of the collector that did not record disk counters. Upload host-cron.php and lib/, then let it run twice.',
        );
      } else if (sample && Number(sample.diskstats_available) === 0) {
        message = t(
          'core-host-disks.unreadable',
          {},
          'The host collector ran but could not read /proc/diskstats, even as root.',
        );
      } else if (sampleCount < 2) {
        message = t(
          'core-host-disks.needs_readings',
          { readings: sampleCount },
          'Needs two host readings before a rate can be worked out — there {{readings, number}} so far.',
        );
      } else {
        message = t(
          'core-host-disks.empty',
          {},
          'No devices were recorded in the latest reading.',
        );
      }
      target.replaceChildren(
        Object.assign(document.createElement('p'), {
          className: 'muted',
          textContent: message,
        }),
      );
      return;
    }

    const rate = (value) =>
      value == null
        ? '—'
        : t(
            'host.io_rate_per_second',
            { amount: humanBytes(value) },
            '{{amount}}/s',
          );
    renderTable(target.id, rows, [
      {
        label: t('host.io_device', {}, 'Device'),
        render: (row) => {
          if (!row.stacked) return row.name;
          const box = document.createElement('span');
          box.appendChild(document.createTextNode(row.name));
          const note = document.createElement('span');
          note.className = 'muted small';
          note.textContent = t('host.io_stacked', {}, ' · stacked');
          note.title = t(
            'host.io_stacked_tip',
            {},
            'A volume mapped onto another device. Its traffic is already counted there, so it is not added into any total.',
          );
          box.appendChild(note);
          return box;
        },
      },
      {
        label: t('host.io_read', {}, 'Read'),
        className: 'size',
        render: (row) => rate(row.read_rate),
      },
      {
        label: t('host.io_written', {}, 'Written'),
        className: 'size',
        render: (row) => rate(row.write_rate),
      },
      {
        label: t('host.io_busy', {}, 'Busy'),
        className: 'size',
        render: (row) => {
          if (row.util_percent == null) return '—';
          const span = document.createElement('span');
          span.textContent = `${Number(row.util_percent).toFixed(0)}%`;
          if (row.util_percent >= 90) span.className = 'grew';
          return span;
        },
      },
      {
        label: t('host.io_queue', {}, 'Queue'),
        className: 'size',
        render: (row) =>
          row.queue_depth == null ? '—' : Number(row.queue_depth).toFixed(2),
      },
      {
        label: t('host.io_in_flight', {}, 'In flight'),
        className: 'size',
        render: (row) => row.in_flight,
      },
    ]);
  },
};
