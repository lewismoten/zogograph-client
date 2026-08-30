/* Core card package: the shell supplies all rendering and formatting helpers. */
window.ZogographHostIoProcessesCard = {
  manifest: Object.freeze({
    id: 'core-host-io-processes',
    locales: ['core-host-io-processes.'],
    helpers: ['target', 'renderTable', 'humanBytes', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-host-io-processes.empty':
          'No per-process disk I/O recorded yet. It needs the host collector, running as root.',
        'core-host-io-processes.process': 'Process',
        'core-host-io-processes.process_meta': ' · pid {{pid}} · {{user}}',
        'core-host-io-processes.reading': 'Reading',
        'core-host-io-processes.writing': 'Writing',
        'core-host-io-processes.since_started': 'Since it started',
        'core-host-io-processes.rate_is': 'Rate is',
        'core-host-io-processes.rate_now': 'now (over {{seconds, number}}s)',
        'core-host-io-processes.rate_now_tip':
          'Measured between the last two host readings.',
        'core-host-io-processes.rate_lifetime': 'lifetime average',
        'core-host-io-processes.rate_lifetime_tip':
          'This process has only been seen once, so its total I/O is spread over how long it has been running. Accurate for a job that just started, much less so for a long-running daemon.',
      }),
      es: Object.freeze({
        'core-host-io-processes.empty':
          'Aún no se ha registrado E/S de disco por proceso. Necesita el recopilador del host ejecutándose como root.',
        'core-host-io-processes.process': 'Proceso',
        'core-host-io-processes.process_meta': ' · PID {{pid}} · {{user}}',
        'core-host-io-processes.reading': 'Lectura',
        'core-host-io-processes.writing': 'Escritura',
        'core-host-io-processes.since_started': 'Desde que se inició',
        'core-host-io-processes.rate_is': 'La tasa es',
        'core-host-io-processes.rate_now':
          'ahora (durante {{seconds, number}} s)',
        'core-host-io-processes.rate_now_tip':
          'Medido entre las dos últimas lecturas del host.',
        'core-host-io-processes.rate_lifetime': 'promedio de toda la vida',
        'core-host-io-processes.rate_lifetime_tip':
          'Este proceso solo se ha visto una vez, por lo que su E/S total se distribuye a lo largo de su tiempo de ejecución. Es preciso para un trabajo que acaba de empezar, mucho menos para un demonio de larga duración.',
      }),
      hi: Object.freeze({
        'core-host-io-processes.empty':
          'अभी तक प्रति-प्रक्रिया डिस्क I/O दर्ज नहीं है। इसके लिए root के रूप में चल रहे होस्ट कलेक्टर की आवश्यकता है।',
        'core-host-io-processes.process': 'प्रक्रिया',
        'core-host-io-processes.process_meta': ' · PID {{pid}} · {{user}}',
        'core-host-io-processes.reading': 'पढ़ रहा है',
        'core-host-io-processes.writing': 'लिख रहा है',
        'core-host-io-processes.since_started': 'शुरू होने से',
        'core-host-io-processes.rate_is': 'दर है',
        'core-host-io-processes.rate_now': 'अभी ({{seconds, number}}से. में)',
        'core-host-io-processes.rate_now_tip':
          'पिछली दो होस्ट रीडिंग के बीच मापा गया।',
        'core-host-io-processes.rate_lifetime': 'जीवनकाल औसत',
        'core-host-io-processes.rate_lifetime_tip':
          'यह प्रक्रिया केवल एक बार देखी गई है, इसलिए इसका कुल I/O इसके चलने की अवधि में फैला है। अभी शुरू हुए काम के लिए सटीक, लंबे समय से चल रहे डेमन के लिए बहुत कम सटीक।',
      }),
      ar: Object.freeze({
        'core-host-io-processes.empty':
          'لم تُسجَّل مدخلات/مخرجات القرص لكل عملية بعد. يتطلب ذلك جامع المضيف وهو يعمل بصفة root.',
        'core-host-io-processes.process': 'العملية',
        'core-host-io-processes.process_meta':
          ' · معرّف العملية {{pid}} · {{user}}',
        'core-host-io-processes.reading': 'قراءة',
        'core-host-io-processes.writing': 'كتابة',
        'core-host-io-processes.since_started': 'منذ بدء التشغيل',
        'core-host-io-processes.rate_is': 'المعدل هو',
        'core-host-io-processes.rate_now': 'الآن (على {{seconds, number}} ث)',
        'core-host-io-processes.rate_now_tip': 'قيس بين آخر قراءتين للمضيف.',
        'core-host-io-processes.rate_lifetime': 'متوسط العمر',
        'core-host-io-processes.rate_lifetime_tip':
          'شوهدت هذه العملية مرة واحدة فقط، لذا موزع إجمالي مدخلاتها/مخرجاتها على مدة تشغيلها. دقيق لعمل بدأ للتو، وأقل دقة بكثير لخادم طويل التشغيل.',
      }),
      zh: Object.freeze({
        'core-host-io-processes.empty':
          '尚未记录每个进程的磁盘 I/O。它需要以 root 身份运行的主机采集器。',
        'core-host-io-processes.process': '进程',
        'core-host-io-processes.process_meta': ' · 进程 ID {{pid}} · {{user}}',
        'core-host-io-processes.reading': '读取',
        'core-host-io-processes.writing': '写入',
        'core-host-io-processes.since_started': '自启动以来',
        'core-host-io-processes.rate_is': '速率为',
        'core-host-io-processes.rate_now': '当前（{{seconds, number}} 秒内）',
        'core-host-io-processes.rate_now_tip': '在最近两次主机读数之间测得。',
        'core-host-io-processes.rate_lifetime': '生命周期平均值',
        'core-host-io-processes.rate_lifetime_tip':
          '此进程仅出现过一次，因此其总 I/O 按运行时长分摊。对于刚启动的作业很准确，对于长期运行的守护进程则准确性低得多。',
      }),
    }),
  }),

  render(io, helpers) {
    const { target, renderTable, humanBytes, i18n } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (!target) return;
    if (!io || !io.available || !io.rows?.length) {
      target.replaceChildren(
        Object.assign(document.createElement('p'), {
          className: 'muted',
          textContent:
            io?.reason ||
            t(
              'core-host-io-processes.empty',
              {},
              'No per-process disk I/O recorded yet. It needs the host collector, running as root.',
            ),
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
    renderTable(
      target.id,
      io.rows,
      [
        {
          label: t('core-host-io-processes.process', {}, 'Process'),
          className: 'name',
          render: (row) => {
            const box = document.createElement('span');
            box.appendChild(document.createTextNode(row.name));
            const note = document.createElement('span');
            note.className = 'muted small';
            note.textContent = t(
              'core-host-io-processes.process_meta',
              { pid: row.pid, user: row.user },
              ' · pid {{pid}} · {{user}}',
            );
            box.appendChild(note);
            box.title = row.command;
            return box;
          },
        },
        {
          label: t('core-host-io-processes.reading', {}, 'Reading'),
          className: 'size',
          render: (row) => rate(row.read_rate),
        },
        {
          label: t('core-host-io-processes.writing', {}, 'Writing'),
          className: 'size',
          render: (row) => rate(row.write_rate),
        },
        {
          label: t(
            'core-host-io-processes.since_started',
            {},
            'Since it started',
          ),
          className: 'size',
          render: (row) => humanBytes(row.read_bytes + row.write_bytes),
        },
        {
          label: t('core-host-io-processes.rate_is', {}, 'Rate is'),
          render: (row) => {
            const span = document.createElement('span');
            if (row.basis === 'current') {
              span.textContent = t(
                'core-host-io-processes.rate_now',
                { seconds: io.seconds },
                'now (over {{seconds, number}}s)',
              );
              span.title = t(
                'core-host-io-processes.rate_now_tip',
                {},
                'Measured between the last two host readings.',
              );
            } else if (row.basis === 'average') {
              span.textContent = t(
                'core-host-io-processes.rate_lifetime',
                {},
                'lifetime average',
              );
              span.className = 'muted';
              span.title = t(
                'core-host-io-processes.rate_lifetime_tip',
                {},
                'This process has only been seen once, so its total I/O is spread over how long it has been running. Accurate for a job that just started, much less so for a long-running daemon.',
              );
            } else {
              span.textContent = '—';
              span.className = 'muted';
            }
            return span;
          },
        },
      ],
      null,
      null,
      (row) => String(row.pid),
    );
  },
};
