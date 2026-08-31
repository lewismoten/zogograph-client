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
      en: Object.freeze({
        'core-io-chart.ready': 'I/O chart',
        'core-io-chart.account_read': 'This account, read',
        'core-io-chart.account_written': 'This account, written',
        'core-io-chart.io_device_read': 'Device read',
        'core-io-chart.io_device_written': 'Device written',
        'core-io-chart.device_busy_right': 'Device busy % (right)',
        'core-io-chart.nothing_collected_range':
          'Nothing collected for this range yet',
        'core-io-chart.io_empty_unreadable':
          'No disk I/O readings — neither /proc/diskstats nor /proc/<pid>/io could be read',
        'core-io-chart.io_bytes_to_disk':
          'Bytes actually reaching a disk, not bytes served from cache.',
        'core-io-chart.io_device_missing':
          'The device lines are missing because /proc/diskstats is not readable from this account — see the host panel, which reads it as root.',
        'core-io-chart.io_account_missing':
          'This account’s own traffic is missing too: /proc/<pid>/io could not be read for any process here.',
        'core-io-chart.io_scope':
          'The device lines cover every account on the machine; only the “this account” lines are yours.',
        'core-io-chart.io_busy':
          'The device was busy {{percent}}% of the last interval.',
        'core-io-chart.io_stalled':
          'Tasks were stalled waiting on the disk {{some}}% of the time, and nothing at all could run for {{full}}%.',
      }),
      es: Object.freeze({
        'core-io-chart.ready': 'Gráfico de E/S',
        'core-io-chart.account_read': 'Esta cuenta, leído',
        'core-io-chart.account_written': 'Esta cuenta, escrito',
        'core-io-chart.io_device_read': 'Lectura del dispositivo',
        'core-io-chart.io_device_written': 'Escritura del dispositivo',
        'core-io-chart.device_busy_right': 'Dispositivo ocupado % (derecha)',
        'core-io-chart.nothing_collected_range':
          'Aún no se ha recopilado nada para este intervalo',
        'core-io-chart.io_empty_unreadable':
          'No hay lecturas de E/S de disco: no se pudo leer /proc/diskstats ni /proc/<pid>/io',
        'core-io-chart.io_bytes_to_disk':
          'Bytes que llegan realmente al disco, no bytes servidos desde caché.',
        'core-io-chart.io_device_missing':
          'Faltan las líneas del dispositivo porque /proc/diskstats no se puede leer desde esta cuenta; consulte el panel del host, que lo lee como root.',
        'core-io-chart.io_account_missing':
          'También falta el tráfico propio de esta cuenta: no se pudo leer /proc/<pid>/io para ningún proceso.',
        'core-io-chart.io_scope':
          'Las líneas del dispositivo cubren todas las cuentas de la máquina; solo las líneas de «esta cuenta» son suyas.',
        'core-io-chart.io_busy':
          'El dispositivo estuvo ocupado el {{percent}}% del último intervalo.',
        'core-io-chart.io_stalled':
          'Las tareas estuvieron detenidas esperando al disco el {{some}}% del tiempo, y no pudo ejecutarse nada durante el {{full}}%.',
      }),
      hi: Object.freeze({
        'core-io-chart.ready': 'I/O चार्ट',
        'core-io-chart.account_read': 'यह खाता, पढ़ा',
        'core-io-chart.account_written': 'यह खाता, लिखा',
        'core-io-chart.io_device_read': 'डिवाइस पढ़ना',
        'core-io-chart.io_device_written': 'डिवाइस लिखना',
        'core-io-chart.device_busy_right': 'डिवाइस व्यस्त % (दायाँ)',
        'core-io-chart.nothing_collected_range':
          'इस दायरे के लिए अभी कुछ नहीं जुटाया गया',
        'core-io-chart.io_empty_unreadable':
          'कोई डिस्क I/O रीडिंग नहीं — /proc/diskstats और /proc/<pid>/io दोनों पढ़े नहीं जा सके',
        'core-io-chart.io_bytes_to_disk':
          'वास्तव में डिस्क तक पहुँचने वाले बाइट, कैश से दिए गए बाइट नहीं।',
        'core-io-chart.io_device_missing':
          'डिवाइस लाइनें गायब हैं क्योंकि इस खाते से /proc/diskstats पढ़ा नहीं जा सकता — होस्ट पैनल देखें, जो इसे root के रूप में पढ़ता है।',
        'core-io-chart.io_account_missing':
          'इस खाते का अपना ट्रैफ़िक भी गायब है: यहाँ किसी प्रक्रिया के लिए /proc/<pid>/io पढ़ा नहीं जा सका।',
        'core-io-chart.io_scope':
          'डिवाइस लाइनें मशीन के हर खाते को कवर करती हैं; केवल “यह खाता” लाइनें आपकी हैं।',
        'core-io-chart.io_busy':
          'पिछले अंतराल में डिवाइस {{percent}}% व्यस्त था।',
        'core-io-chart.io_stalled':
          'कार्य {{some}}% समय डिस्क की प्रतीक्षा में रुके रहे, और {{full}}% समय कुछ भी नहीं चल सका।',
      }),
      ar: Object.freeze({
        'core-io-chart.ready': 'مخطط الإدخال/الإخراج',
        'core-io-chart.account_read': 'هذا الحساب، قراءة',
        'core-io-chart.account_written': 'هذا الحساب، كتابة',
        'core-io-chart.io_device_read': 'قراءة الجهاز',
        'core-io-chart.io_device_written': 'كتابة الجهاز',
        'core-io-chart.device_busy_right': 'انشغال الجهاز % (يمين)',
        'core-io-chart.nothing_collected_range': 'لم يُجمع شيء لهذا النطاق بعد',
        'core-io-chart.io_empty_unreadable':
          'لا توجد قراءات إدخال/إخراج للقرص — لم يمكن قراءة /proc/diskstats أو /proc/<pid>/io',
        'core-io-chart.io_bytes_to_disk':
          'بايتات تصل فعلاً إلى القرص، لا بايتات مخدومة من الذاكرة المؤقتة.',
        'core-io-chart.io_device_missing':
          'أسطر الجهاز مفقودة لأن /proc/diskstats غير قابل للقراءة من هذا الحساب — راجع لوحة المضيف التي تقرؤه كجذر.',
        'core-io-chart.io_account_missing':
          'حركة هذا الحساب نفسها مفقودة أيضاً: تعذر قراءة /proc/<pid>/io لأي عملية هنا.',
        'core-io-chart.io_scope':
          'أسطر الجهاز تغطي كل حساب على الآلة؛ أسطر «هذا الحساب» وحدها تخصك.',
        'core-io-chart.io_busy':
          'كان الجهاز مشغولاً {{percent}}% من الفترة الأخيرة.',
        'core-io-chart.io_stalled':
          'توقفت المهام بانتظار القرص {{some}}% من الوقت، ولم يكن شيء قادراً على التشغيل لمدة {{full}}%.',
      }),
      zh: Object.freeze({
        'core-io-chart.ready': 'I/O 图表',
        'core-io-chart.account_read': '此帐户，读取',
        'core-io-chart.account_written': '此帐户，写入',
        'core-io-chart.io_device_read': '设备读取',
        'core-io-chart.io_device_written': '设备写入',
        'core-io-chart.device_busy_right': '设备忙碌 %（右轴）',
        'core-io-chart.nothing_collected_range': '此范围尚未收集任何数据',
        'core-io-chart.io_empty_unreadable':
          '没有磁盘 I/O 读数：无法读取 /proc/diskstats 或 /proc/<pid>/io',
        'core-io-chart.io_bytes_to_disk':
          '实际到达磁盘的字节，而非从缓存提供的字节。',
        'core-io-chart.io_device_missing':
          '设备线缺失，因为此帐户无法读取 /proc/diskstats；请查看以 root 身份读取它的主机面板。',
        'core-io-chart.io_account_missing':
          '此帐户自身的流量也缺失：此处所有进程均无法读取 /proc/<pid>/io。',
        'core-io-chart.io_scope':
          '设备线涵盖机器上的每个帐户；只有“此帐户”线属于您。',
        'core-io-chart.io_busy': '上一个间隔内设备忙碌了 {{percent}}%。',
        'core-io-chart.io_stalled':
          '任务有 {{some}}% 的时间在等待磁盘而停滞，且有 {{full}}% 的时间完全无法运行。',
      }),
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
