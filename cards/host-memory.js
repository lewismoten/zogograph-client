/* Core card package: only declared dependencies are received from the shell. */
window.ZogographHostMemoryCard = {
  manifest: Object.freeze({
    id: 'core-host-memory',
    locales: ['core-host-memory.'],
    helpers: [
      'setCard',
      'note',
      'humanBytes',
      'humanCount',
      'count',
      'num',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-host-memory.summary_counts':
          '{{processes}} processes {{users}} {{threads}}.',
        'core-host-memory.summary_counts_missing':
          'This reading did not record how many processes or threads were running.',
        'core-host-memory.summary_rss_over':
          'Their resident sizes add up to {{resident}}, more than the {{used}} actually in use — a page shared by ten processes is counted ten times, so that total is an overcount and the difference is not missing memory.',
        'core-host-memory.summary_rss':
          'Their resident sizes add up to {{resident}}. Resident size counts shared pages once per process, so it is an upper bound rather than a total.',
        'core-host-memory.summary_pss':
          'Proportional set size — private memory in full plus a share of whatever is shared — comes to {{amount}} across {{processes}} processes{{suffix}}.',
        'core-host-memory.summary_pss_largest': ' biggest',
        'core-host-memory.of_total': 'of {{total}}',
        'core-host-memory.monitoring_footprint': 'Monitoring footprint',
        'core-host-memory.collector_processes':
          '{{count, number}} collector-tree processes',
        'core-host-memory.available': 'Available',
        'core-host-memory.available_help':
          "the kernel's estimate of what is left",
        'core-host-memory.cache_buffers': 'Cache and buffers',
        'core-host-memory.cache_help': 'reclaimable — not a problem',
        'core-host-memory.kernel_slab': 'Kernel slab',
        'core-host-memory.unreclaimable': '{{amount}} of it unreclaimable',
        'core-host-memory.shared_tmpfs': 'Kernel shared memory / tmpfs',
        'core-host-memory.in_dev_shm': '{{amount}} in /dev/shm',
        'core-host-memory.swap': 'Swap',
        'core-host-memory.allocated': '{{percent}}% allocated',
        'core-host-memory.other_kernel_help':
          'not represented by the categories above',
        'core-host-memory.in_use': 'Memory in use',
        'core-host-memory.other_kernel': 'Other kernel/system memory',
      }),
      es: Object.freeze({
        'core-host-memory.summary_counts':
          '{{processes}} procesos {{users}} {{threads}}.',
        'core-host-memory.summary_counts_missing':
          'Esta lectura no registró cuántos procesos o hilos estaban en ejecución.',
        'core-host-memory.summary_rss_over':
          'Sus tamaños residentes suman {{resident}}, más que los {{used}} realmente en uso; una página compartida por diez procesos se cuenta diez veces, por lo que el total es excesivo y la diferencia no es memoria ausente.',
        'core-host-memory.summary_rss':
          'Sus tamaños residentes suman {{resident}}. El tamaño residente cuenta las páginas compartidas una vez por proceso, por lo que es un límite superior y no un total.',
        'core-host-memory.summary_pss':
          'El tamaño proporcional de conjunto —memoria privada completa más una parte de la compartida— suma {{amount}} en {{processes}} procesos{{suffix}}.',
        'core-host-memory.summary_pss_largest': ' más grandes',
        'core-host-memory.of_total': 'de {{total}}',
        'core-host-memory.monitoring_footprint': 'Huella de monitorización',
        'core-host-memory.collector_processes':
          '{{count, number}} procesos del árbol de recopilación',
        'core-host-memory.available': 'Disponible',
        'core-host-memory.available_help':
          'la estimación del kernel de lo que queda',
        'core-host-memory.cache_buffers': 'Caché y búferes',
        'core-host-memory.cache_help': 'recuperable — no es un problema',
        'core-host-memory.kernel_slab': 'Slab del kernel',
        'core-host-memory.unreclaimable': '{{amount}} no recuperable',
        'core-host-memory.shared_tmpfs':
          'Memoria compartida del kernel / tmpfs',
        'core-host-memory.in_dev_shm': '{{amount}} en /dev/shm',
        'core-host-memory.swap': 'Intercambio',
        'core-host-memory.allocated': '{{percent}}% asignado',
        'core-host-memory.other_kernel_help':
          'no representada por las categorías anteriores',
        'core-host-memory.in_use': 'Memoria en uso',
        'core-host-memory.other_kernel': 'Otra memoria de kernel/sistema',
      }),
      hi: Object.freeze({
        'core-host-memory.summary_counts':
          '{{processes}} प्रोसेस {{users}} {{threads}}।',
        'core-host-memory.summary_counts_missing':
          'इस रीडिंग ने चल रहे प्रोसेस या थ्रेड की संख्या दर्ज नहीं की।',
        'core-host-memory.summary_rss_over':
          'उनके रेज़िडेंट आकार {{resident}} हैं, जो वास्तविक उपयोग के {{used}} से अधिक हैं; दस प्रोसेस की साझा पेज दस बार गिनी जाती है।',
        'core-host-memory.summary_rss':
          'उनके रेज़िडेंट आकार {{resident}} हैं। रेज़िडेंट आकार साझा पेज को हर प्रोसेस के लिए गिनता है, इसलिए यह ऊपरी सीमा है।',
        'core-host-memory.summary_pss':
          'आनुपातिक सेट आकार {{amount}} है, {{processes}} प्रोसेसों में{{suffix}}।',
        'core-host-memory.summary_pss_largest': ' सबसे बड़े',
        'core-host-memory.of_total': '{{total}} में से',
        'core-host-memory.monitoring_footprint': 'निगरानी पदचिह्न',
        'core-host-memory.collector_processes':
          'कलेक्टर-ट्री की {{count, number}} प्रक्रियाएं',
        'core-host-memory.available': 'उपलब्ध',
        'core-host-memory.available_help': 'कर्नेल का अनुमान कि कितना बचा है',
        'core-host-memory.cache_buffers': 'कैश और बफ़र',
        'core-host-memory.cache_help': 'पुनर्प्राप्त करने योग्य — समस्या नहीं',
        'core-host-memory.kernel_slab': 'कर्नेल स्लैब',
        'core-host-memory.unreclaimable':
          'इसमें से {{amount}} पुनर्प्राप्त नहीं किया जा सकता',
        'core-host-memory.shared_tmpfs': 'कर्नेल साझा मेमोरी / tmpfs',
        'core-host-memory.in_dev_shm': '/dev/shm में {{amount}}',
        'core-host-memory.swap': 'स्वैप',
        'core-host-memory.allocated': '{{percent}}% आवंटित',
        'core-host-memory.other_kernel_help':
          'ऊपर की श्रेणियों में प्रदर्शित नहीं',
        'core-host-memory.in_use': 'उपयोग में मेमोरी',
        'core-host-memory.other_kernel': 'अन्य कर्नेल/सिस्टम मेमोरी',
      }),
      ar: Object.freeze({
        'core-host-memory.summary_counts':
          '{{processes}} عملية {{users}} {{threads}}.',
        'core-host-memory.summary_counts_missing':
          'لم تسجل هذه القراءة عدد العمليات أو الخيوط العاملة.',
        'core-host-memory.summary_rss_over':
          'مجموع الأحجام المقيمة {{resident}}، أكثر من {{used}} المستخدم فعلياً؛ الصفحة المشتركة بين عشر عمليات تُحسب عشر مرات.',
        'core-host-memory.summary_rss':
          'مجموع الأحجام المقيمة {{resident}}. يُحسب الحجم المقيم للصفحات المشتركة مرة لكل عملية، لذا فهو حد أعلى لا إجمالي.',
        'core-host-memory.summary_pss':
          'حجم المجموعة التناسبي هو {{amount}} عبر {{processes}} عملية{{suffix}}.',
        'core-host-memory.summary_pss_largest': ' الأكبر',
        'core-host-memory.of_total': 'من {{total}}',
        'core-host-memory.monitoring_footprint': 'بصمة المراقبة',
        'core-host-memory.collector_processes':
          '{{count, number}} عملية لشجرة الجامع',
        'core-host-memory.available': 'متاح',
        'core-host-memory.available_help': 'تقدير النواة لما تبقى',
        'core-host-memory.cache_buffers': 'الذاكرة المخبأة والمخازن',
        'core-host-memory.cache_help': 'قابل للاسترداد — ليس مشكلة',
        'core-host-memory.kernel_slab': 'slab النواة',
        'core-host-memory.unreclaimable': '{{amount}} منها غير قابل للاسترداد',
        'core-host-memory.shared_tmpfs': 'ذاكرة النواة المشتركة / tmpfs',
        'core-host-memory.in_dev_shm': '{{amount}} في /dev/shm',
        'core-host-memory.swap': 'التبديل',
        'core-host-memory.allocated': '{{percent}}% مخصص',
        'core-host-memory.other_kernel_help': 'غير ممثلة بالفئات أعلاه',
        'core-host-memory.in_use': 'الذاكرة المستخدمة',
        'core-host-memory.other_kernel': 'ذاكرة نواة/نظام أخرى',
      }),
      zh: Object.freeze({
        'core-host-memory.summary_counts':
          '{{processes}} 个进程 {{users}} {{threads}}。',
        'core-host-memory.summary_counts_missing':
          '此读数未记录正在运行的进程或线程数量。',
        'core-host-memory.summary_rss_over':
          '它们的常驻大小合计为 {{resident}}，超过实际使用的 {{used}}；十个进程共享的一页会被计数十次。',
        'core-host-memory.summary_rss':
          '它们的常驻大小合计为 {{resident}}。常驻大小会为每个进程计数一次共享页，因此它是上限而非总计。',
        'core-host-memory.summary_pss':
          '按比例集合大小为 {{amount}}，分布在 {{processes}} 个进程中{{suffix}}。',
        'core-host-memory.summary_pss_largest': ' 个最大的',
        'core-host-memory.of_total': '共 {{total}}',
        'core-host-memory.monitoring_footprint': '监控占用量',
        'core-host-memory.collector_processes':
          '{{count, number}} 个采集器树进程',
        'core-host-memory.available': '可用',
        'core-host-memory.available_help': '内核对剩余内存的估计',
        'core-host-memory.cache_buffers': '缓存和缓冲区',
        'core-host-memory.cache_help': '可回收 — 不成问题',
        'core-host-memory.kernel_slab': '内核 slab',
        'core-host-memory.unreclaimable': '其中 {{amount}} 不可回收',
        'core-host-memory.shared_tmpfs': '内核共享内存 / tmpfs',
        'core-host-memory.in_dev_shm': '/dev/shm 中有 {{amount}}',
        'core-host-memory.swap': '交换空间',
        'core-host-memory.allocated': '已分配 {{percent}}%',
        'core-host-memory.other_kernel_help': '未包含在上述类别中',
        'core-host-memory.in_use': '已用内存',
        'core-host-memory.other_kernel': '其他内核/系统内存',
      }),
    }),
  }),
  render(input, helpers) {
    const { sample, swap, swapUsed, swapPercent, users = [] } = input;
    const { setCard, note, humanBytes, humanCount, count, num, i18n } = helpers;
    const t = (key, values, fallback) =>
      i18n.t(`core-host-memory.${key}`, values, fallback);
    setCard(
      t('in_use', {}, 'Memory in use'),
      humanBytes(sample.used_bytes),
      t('of_total', { total: humanBytes(sample.mem_total) }, 'of {{total}}'),
    );
    if (
      sample.monitoring_pss_bytes != null ||
      sample.monitoring_rss_bytes != null
    ) {
      const measured =
        sample.monitoring_pss_bytes != null
          ? humanBytes(sample.monitoring_pss_bytes) + ' PSS'
          : humanBytes(sample.monitoring_rss_bytes) + ' RSS';
      const processes = Number(sample.monitoring_processes || 0);
      setCard(
        t('monitoring_footprint', {}, 'Monitoring footprint'),
        measured,
        t(
          'collector_processes',
          { count: processes },
          '{{count, number}} collector-tree processes',
        ),
      );
    }
    setCard(
      t('available', {}, 'Available'),
      humanBytes(sample.mem_available),
      t('available_help', {}, "the kernel's estimate of what is left"),
    );
    setCard(
      t('cache_buffers', {}, 'Cache and buffers'),
      humanBytes(sample.cache_bytes),
      t('cache_help', {}, 'reclaimable — not a problem'),
    );
    setCard(
      t('kernel_slab', {}, 'Kernel slab'),
      humanBytes(sample.slab),
      t(
        'unreclaimable',
        { amount: humanBytes(sample.sunreclaim) },
        '{{amount}} of it unreclaimable',
      ),
    );
    setCard(
      t('shared_tmpfs', {}, 'Kernel shared memory / tmpfs'),
      humanBytes(sample.shmem),
      sample.devshm_used == null
        ? ''
        : t(
            'in_dev_shm',
            { amount: humanBytes(sample.devshm_used) },
            '{{amount}} in /dev/shm',
          ),
    );
    setCard(
      t('swap', {}, 'Swap'),
      `${humanBytes(swapUsed)} / ${humanBytes(sample.swap_total)}`,
      swapPercent == null
        ? ''
        : t('allocated', { percent: swapPercent }, '{{percent}}% allocated'),
      swap.state === 'pressure' ? 'warn' : '',
      'pair',
    );
    setCard(
      t('other_kernel', {}, 'Other kernel/system memory'),
      humanBytes(sample.unaccounted),
      t('other_kernel_help', {}, 'not represented by the categories above'),
    );
    if (!note) return;
    const parts = [];
    if (sample.proc_count != null || sample.thread_count != null) {
      parts.push(
        t(
          'summary_counts',
          {
            processes:
              sample.proc_count == null ? '' : humanCount(sample.proc_count),
            users: users.length ? count(users.length, 'user') : '',
            threads:
              sample.thread_count == null
                ? ''
                : humanCount(sample.thread_count),
          },
          '{{processes}} processes {{users}} {{threads}}.',
        )
          .replace(/\s+/g, ' ')
          .trim(),
      );
    } else {
      parts.push(
        t(
          'summary_counts_missing',
          {},
          'This reading did not record how many processes or threads were running.',
        ),
      );
    }
    const rssSum = num(sample.rss_sum_bytes);
    const inUse = num(sample.used_bytes);
    if (rssSum > 0)
      parts.push(
        rssSum > inUse
          ? t(
              'summary_rss_over',
              { resident: humanBytes(rssSum), used: humanBytes(inUse) },
              'Their resident sizes add up to {{resident}}, more than the {{used}} actually in use — a page shared by ten processes is counted ten times, so that total is an overcount and the difference is not missing memory.',
            )
          : t(
              'summary_rss',
              { resident: humanBytes(rssSum) },
              'Their resident sizes add up to {{resident}}. Resident size counts shared pages once per process, so it is an upper bound rather than a total.',
            ),
      );
    if (num(sample.pss_procs) > 0)
      parts.push(
        t(
          'summary_pss',
          {
            amount: humanBytes(sample.pss_sum_bytes),
            processes: sample.pss_procs,
            suffix:
              num(sample.pss_procs) < num(sample.proc_count)
                ? t('summary_pss_largest', {}, ' biggest')
                : '',
          },
          'Proportional set size — private memory in full plus a share of whatever is shared — comes to {{amount}} across {{processes}} processes{{suffix}}.',
        ),
      );
    note.textContent = parts.join(' ');
  },
};
