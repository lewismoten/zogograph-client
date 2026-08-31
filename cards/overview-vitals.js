/* Core card package: only declared dependencies are received from the shell. */
window.ZogographOverviewVitalsCards = {
  manifest: Object.freeze({
    id: 'core-overview-vitals',
    locales: ['core-overview-vitals.'],
    helpers: [
      'setCard',
      'humanBytes',
      'humanCount',
      'humanDuration',
      'projectedDate',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-overview-vitals.up_for': 'Up for',
        'core-overview-vitals.projected_full': 'Projected full',
        'core-overview-vitals.nothing_collected': 'nothing collected yet',
        'core-overview-vitals.free_of': '{{percent}}% free of {{total}}',
        'core-overview-vitals.not_available': 'n/a',
        'core-overview-vitals.not_reported': 'not reported',
        'core-overview-vitals.percent_free': '{{percent}}% free',
        'core-overview-vitals.no_proc_stat': 'no /proc/stat here',
        'core-overview-vitals.since_previous_reading':
          'since the previous reading',
        'core-overview-vitals.available': '{{amount}} available',
        'core-overview-vitals.no_proc_meminfo': 'no /proc/meminfo here',
        'core-overview-vitals.percent_of_cores':
          '{{percent}}% of {{cores}} cores · ',
        'core-overview-vitals.load_at':
          '{{five}} / {{fifteen}} at 5 and 15 min',
        'core-overview-vitals.no_proc_net_dev': 'no /proc/net/dev here',
        'core-overview-vitals.network_in_out': 'in · {{outgoing}}/s out',
        'core-overview-vitals.not_enough_history': 'not enough history yet',
        'core-overview-vitals.one_off': 'one-off',
        'core-overview-vitals.went_at_once':
          '{{amount}} went at once, not a trend',
        'core-overview-vitals.steady': 'steady',
        'core-overview-vitals.free_space_steady': 'free space is not falling',
        'core-overview-vitals.losing_rate':
          'losing {{rate}}/h, measured over {{window}}',
      }),
      es: Object.freeze({
        'core-overview-vitals.up_for': 'Activo desde hace',
        'core-overview-vitals.projected_full': 'Lleno previsto',
        'core-overview-vitals.nothing_collected':
          'todavía no se ha recogido nada',
        'core-overview-vitals.free_of': '{{percent}}% libre de {{total}}',
        'core-overview-vitals.not_available': 'n/d',
        'core-overview-vitals.not_reported': 'sin informar',
        'core-overview-vitals.percent_free': '{{percent}}% libre',
        'core-overview-vitals.no_proc_stat': 'no hay /proc/stat aquí',
        'core-overview-vitals.since_previous_reading':
          'desde la lectura anterior',
        'core-overview-vitals.available': '{{amount}} disponible',
        'core-overview-vitals.no_proc_meminfo': 'no hay /proc/meminfo aquí',
        'core-overview-vitals.percent_of_cores':
          '{{percent}}% de {{cores}} núcleos · ',
        'core-overview-vitals.load_at':
          '{{five}} / {{fifteen}} a los 5 y 15 min',
        'core-overview-vitals.no_proc_net_dev': 'no hay /proc/net/dev aquí',
        'core-overview-vitals.network_in_out':
          'entrada · {{outgoing}}/s salida',
        'core-overview-vitals.not_enough_history':
          'aún no hay suficiente historial',
        'core-overview-vitals.one_off': 'ocasional',
        'core-overview-vitals.went_at_once':
          '{{amount}} se fue de una vez, no es una tendencia',
        'core-overview-vitals.steady': 'estable',
        'core-overview-vitals.free_space_steady':
          'el espacio libre no está disminuyendo',
        'core-overview-vitals.losing_rate':
          'pierde {{rate}}/h, medido durante {{window}}',
      }),
      hi: Object.freeze({
        'core-overview-vitals.up_for': 'चल रहा है',
        'core-overview-vitals.projected_full': 'अनुमानित भराव',
        'core-overview-vitals.nothing_collected': 'अभी तक कुछ एकत्र नहीं हुआ',
        'core-overview-vitals.free_of': '{{total}} में से {{percent}}% खाली',
        'core-overview-vitals.not_available': 'उपलब्ध नहीं',
        'core-overview-vitals.not_reported': 'रिपोर्ट नहीं किया गया',
        'core-overview-vitals.percent_free': '{{percent}}% खाली',
        'core-overview-vitals.no_proc_stat': 'यहाँ /proc/stat नहीं है',
        'core-overview-vitals.since_previous_reading': 'पिछली रीडिंग के बाद से',
        'core-overview-vitals.available': '{{amount}} उपलब्ध',
        'core-overview-vitals.no_proc_meminfo': 'यहाँ /proc/meminfo नहीं है',
        'core-overview-vitals.percent_of_cores':
          '{{cores}} कोर का {{percent}}% · ',
        'core-overview-vitals.load_at':
          '5 और 15 मिनट पर {{five}} / {{fifteen}}',
        'core-overview-vitals.no_proc_net_dev': 'यहाँ /proc/net/dev नहीं है',
        'core-overview-vitals.network_in_out': 'अंदर · {{outgoing}}/s बाहर',
        'core-overview-vitals.not_enough_history':
          'अभी पर्याप्त इतिहास नहीं है',
        'core-overview-vitals.one_off': 'एक बार का',
        'core-overview-vitals.went_at_once':
          '{{amount}} एक साथ गया, प्रवृत्ति नहीं',
        'core-overview-vitals.steady': 'स्थिर',
        'core-overview-vitals.free_space_steady': 'खाली स्थान कम नहीं हो रहा',
        'core-overview-vitals.losing_rate':
          '{{rate}}/घंटा कम हो रहा है, {{window}} में मापा गया',
      }),
      ar: Object.freeze({
        'core-overview-vitals.up_for': 'قيد التشغيل منذ',
        'core-overview-vitals.projected_full': 'الامتلاء المتوقع',
        'core-overview-vitals.nothing_collected': 'لم تُجمع بيانات بعد',
        'core-overview-vitals.free_of': '{{percent}}% متاح من {{total}}',
        'core-overview-vitals.not_available': 'غير متاح',
        'core-overview-vitals.not_reported': 'لم يُبلّغ عنه',
        'core-overview-vitals.percent_free': '{{percent}}% متاح',
        'core-overview-vitals.no_proc_stat': 'لا يوجد /proc/stat هنا',
        'core-overview-vitals.since_previous_reading': 'منذ القراءة السابقة',
        'core-overview-vitals.available': '{{amount}} متاح',
        'core-overview-vitals.no_proc_meminfo': 'لا يوجد /proc/meminfo هنا',
        'core-overview-vitals.percent_of_cores':
          '{{percent}}% من {{cores}} نوى · ',
        'core-overview-vitals.load_at':
          '{{five}} / {{fifteen}} عند 5 و15 دقيقة',
        'core-overview-vitals.no_proc_net_dev': 'لا يوجد /proc/net/dev هنا',
        'core-overview-vitals.network_in_out': 'داخل · {{outgoing}}/ث خارج',
        'core-overview-vitals.not_enough_history':
          'لا توجد بيانات تاريخية كافية بعد',
        'core-overview-vitals.one_off': 'مرة واحدة',
        'core-overview-vitals.went_at_once':
          'اختفى {{amount}} دفعة واحدة، وليس اتجاهًا',
        'core-overview-vitals.steady': 'مستقر',
        'core-overview-vitals.free_space_steady': 'المساحة المتاحة لا تنخفض',
        'core-overview-vitals.losing_rate':
          'يفقد {{rate}}/ساعة، مقاسًا على {{window}}',
      }),
      zh: Object.freeze({
        'core-overview-vitals.up_for': '已运行',
        'core-overview-vitals.projected_full': '预计填满',
        'core-overview-vitals.nothing_collected': '尚未收集到数据',
        'core-overview-vitals.free_of': '{{total}} 中有 {{percent}}% 可用',
        'core-overview-vitals.not_available': '不适用',
        'core-overview-vitals.not_reported': '未报告',
        'core-overview-vitals.percent_free': '{{percent}}% 可用',
        'core-overview-vitals.no_proc_stat': '此处没有 /proc/stat',
        'core-overview-vitals.since_previous_reading': '自上次读数以来',
        'core-overview-vitals.available': '{{amount}} 可用',
        'core-overview-vitals.no_proc_meminfo': '此处没有 /proc/meminfo',
        'core-overview-vitals.percent_of_cores':
          '{{cores}} 个核心的 {{percent}}% · ',
        'core-overview-vitals.load_at':
          '5 和 15 分钟时为 {{five}} / {{fifteen}}',
        'core-overview-vitals.no_proc_net_dev': '此处没有 /proc/net/dev',
        'core-overview-vitals.network_in_out': '入站 · 出站 {{outgoing}}/秒',
        'core-overview-vitals.not_enough_history': '历史数据不足',
        'core-overview-vitals.one_off': '一次性',
        'core-overview-vitals.went_at_once': '{{amount}} 一次性消失，并非趋势',
        'core-overview-vitals.steady': '稳定',
        'core-overview-vitals.free_space_steady': '可用空间没有下降',
        'core-overview-vitals.losing_rate':
          '每小时减少 {{rate}}，统计窗口为 {{window}}',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-overview-vitals.${key}`,
      {},
      this.manifest.catalogs.en[`core-overview-vitals.${key}`],
    );
  },
  render(overview, helpers) {
    const {
      setCard,
      humanBytes,
      humanCount,
      humanDuration,
      projectedDate,
      i18n,
    } = helpers;
    const t = (key, values, fallback) =>
      i18n.t(`core-overview-vitals.${key}`, values, fallback);
    const v = overview.vitals;
    if (!v) {
      setCard(
        'cardDisk',
        '—',
        t('nothing_collected', {}, 'nothing collected yet'),
      );
      return;
    }
    const free = v.disk_total_bytes
      ? (v.disk_free_bytes / v.disk_total_bytes) * 100
      : null;
    setCard(
      'cardDisk',
      humanBytes(v.disk_free_bytes),
      free === null
        ? ''
        : t(
            'free_of',
            { percent: free.toFixed(1), total: humanBytes(v.disk_total_bytes) },
            `${free.toFixed(1)}% free of ${humanBytes(v.disk_total_bytes)}`,
          ),
      free === null ? '' : free < 5 ? 'crit' : free < 12 ? 'warn' : '',
    );
    const trend = overview.trend?.disk;
    if (!trend)
      setCard(
        'cardTrend',
        '—',
        t('not_enough_history', {}, 'not enough history yet'),
      );
    else if (trend.spike)
      setCard(
        'cardTrend',
        t('one_off', {}, 'one-off'),
        t(
          'went_at_once',
          { amount: humanBytes(-trend.overall * (trend.span_seconds || 1)) },
          `${humanBytes(-trend.overall * (trend.span_seconds || 1))} went at once, not a trend`,
        ),
      );
    else if (!trend.sustained || trend.seconds_left == null)
      setCard(
        'cardTrend',
        t('steady', {}, 'steady'),
        t('free_space_steady', {}, 'free space is not falling'),
      );
    else
      setCard(
        'cardTrend',
        projectedDate(trend.seconds_left),
        t(
          'losing_rate',
          {
            rate: humanBytes(-trend.rate * 3600),
            window: humanDuration(trend.window_seconds || 86400),
          },
          `losing ${humanBytes(-trend.rate * 3600)}/h, measured over ${humanDuration(trend.window_seconds || 86400)}`,
        ),
        trend.seconds_left < 86400
          ? 'crit'
          : trend.seconds_left < 604800
            ? 'warn'
            : '',
      );
    const inode = v.inodes_total
      ? (v.inodes_free / v.inodes_total) * 100
      : null;
    setCard(
      'cardInodes',
      v.inodes_free == null
        ? t('not_available', {}, 'n/a')
        : humanCount(v.inodes_free),
      inode === null
        ? t('not_reported', {}, 'not reported')
        : t(
            'percent_free',
            { percent: inode.toFixed(1) },
            `${inode.toFixed(1)}% free`,
          ),
      inode === null ? '' : inode < 5 ? 'crit' : inode < 12 ? 'warn' : '',
    );
    setCard(
      'cardCpu',
      v.cpu_percent == null
        ? t('not_available', {}, 'n/a')
        : `${Number(v.cpu_percent).toFixed(0)}%`,
      v.cpu_percent == null
        ? t('no_proc_stat', {}, 'no /proc/stat here')
        : t('since_previous_reading', {}, 'since the previous reading'),
      v.cpu_percent > 90 ? 'crit' : v.cpu_percent > 70 ? 'warn' : '',
    );
    setCard(
      'cardMem',
      v.mem_used_percent == null
        ? t('not_available', {}, 'n/a')
        : `${Number(v.mem_used_percent).toFixed(0)}%`,
      v.mem_total_bytes
        ? t(
            'available',
            { amount: humanBytes(v.mem_available_bytes) },
            `${humanBytes(v.mem_available_bytes)} available`,
          )
        : t('no_proc_meminfo', {}, 'no /proc/meminfo here'),
      v.mem_used_percent > 92 ? 'crit' : v.mem_used_percent > 80 ? 'warn' : '',
    );
    const cores = v.cpu_cores ? Number(v.cpu_cores) : null;
    const perCore = v.load1 != null && cores ? Number(v.load1) / cores : null;
    setCard(
      'cardLoad',
      v.load1 == null
        ? t('not_available', {}, 'n/a')
        : Number(v.load1).toFixed(2),
      v.load1 == null
        ? ''
        : (cores
            ? t(
                'percent_of_cores',
                { percent: (perCore * 100).toFixed(0), cores },
                `${(perCore * 100).toFixed(0)}% of ${cores} cores · `,
              )
            : '') +
            t(
              'load_at',
              {
                five: Number(v.load5).toFixed(2),
                fifteen: Number(v.load15).toFixed(2),
              },
              `${Number(v.load5).toFixed(2)} / ${Number(v.load15).toFixed(2)} at 5 and 15 min`,
            ),
      perCore == null ? '' : perCore > 1.5 ? 'crit' : perCore > 1 ? 'warn' : '',
    );
    const rx = v.net_rx_rate == null ? null : Number(v.net_rx_rate),
      tx = v.net_tx_rate == null ? null : Number(v.net_tx_rate);
    setCard(
      'cardNet',
      rx == null ? t('not_available', {}, 'n/a') : `${humanBytes(rx, 1)}/s`,
      rx == null
        ? t('no_proc_net_dev', {}, 'no /proc/net/dev here')
        : t(
            'network_in_out',
            { incoming: humanBytes(rx, 1), outgoing: humanBytes(tx, 1) },
            `in · ${humanBytes(tx, 1)}/s out`,
          ),
    );
  },
};
