/* Core card package: the current process list and its optional stop controls. */
window.ZogographProcessListCard = {
  manifest: Object.freeze({
    id: 'core-process-list',
    locales: ['core-process-list.'],
    helpers: [
      'target',
      'stopNote',
      'renderTable',
      'namedThing',
      'userCell',
      'processCpuCell',
      'humanBytes',
      'processAge',
      'stopButtons',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-process-list.title': 'What is using the machine',
        'core-process-list.summary':
          'Running right now, with processes of the same name added together. ‘Running for’ is the oldest of the group — a long-lived pool whose newest member is seconds old is restarting as fast as it dies. Arguments that look like passwords are hidden.',
        'core-process-list.stop_help':
          'For when the name is not specific enough to aim with — twelve {{webalizer}} processes where one of them is chewing through one particular log. This is {{pkill}}, with the two differences that matter: the pattern is matched here rather than handed to a shell, and it shows you what it would hit before it hits anything.',
        'core-process-list.process': 'Process',
        'core-process-list.how_many': 'How many',
        'core-process-list.user': 'User',
        'core-process-list.cpu': 'CPU',
        'core-process-list.memory': 'Memory',
        'core-process-list.of_ram': 'Of RAM',
        'core-process-list.running_for': 'Running for',
        'core-process-list.biggest_pid': 'Biggest PID',
        'core-process-list.pid': 'PID',
        'core-process-list.command': 'Command',
        'core-process-list.stop': 'Stop',
      }),
      es: Object.freeze({
        'core-process-list.title': 'Qué está usando la máquina',
        'core-process-list.summary':
          'Lo que se está ejecutando ahora, con procesos del mismo nombre sumados. «En ejecución desde» es el más antiguo del grupo: un grupo duradero cuyo miembro más reciente tiene segundos de vida se reinicia tan rápido como muere. Los argumentos que parecen contraseñas se ocultan.',
        'core-process-list.stop_help':
          'Para cuando el nombre no es lo bastante específico: doce procesos de {{webalizer}} y uno de ellos consume un registro concreto. Esto es {{pkill}}, con dos diferencias importantes: el patrón se compara aquí en lugar de pasarse a un shell, y muestra qué afectaría antes de afectar nada.',
        'core-process-list.process': 'Proceso',
        'core-process-list.how_many': 'Cantidad',
        'core-process-list.user': 'Usuario',
        'core-process-list.cpu': 'CPU',
        'core-process-list.memory': 'Memoria',
        'core-process-list.of_ram': 'De RAM',
        'core-process-list.running_for': 'En ejecución',
        'core-process-list.biggest_pid': 'PID principal',
        'core-process-list.pid': 'PID',
        'core-process-list.command': 'Comando',
        'core-process-list.stop': 'Detener',
      }),
      hi: Object.freeze({
        'core-process-list.title': 'मशीन का उपयोग क्या कर रहा है',
        'core-process-list.summary':
          'इस समय चल रहे प्रोसेस, जिनमें समान नाम वाले प्रोसेस जोड़े गए हैं। ‘चल रहा है’ समूह का सबसे पुराना प्रोसेस है—जिस लंबे समय से चल रहे पूल का सबसे नया सदस्य कुछ सेकंड पुराना है, वह जितनी तेजी से मरता है उतनी तेजी से रीस्टार्ट हो रहा है। पासवर्ड जैसे दिखने वाले आर्ग्युमेंट छिपाए जाते हैं।',
        'core-process-list.stop_help':
          'जब नाम निशाना लगाने के लिए पर्याप्त विशिष्ट न हो—{{webalizer}} के बारह प्रोसेस और उनमें से एक किसी खास लॉग को खा रहा हो। यह {{pkill}} है, पर दो अहम अंतर हैं: पैटर्न को शेल को देने के बजाय यहीं मिलाया जाता है, और कुछ करने से पहले यह दिखाता है कि किस पर असर होगा।',
        'core-process-list.process': 'प्रक्रिया',
        'core-process-list.how_many': 'कितनी',
        'core-process-list.user': 'उपयोगकर्ता',
        'core-process-list.cpu': 'CPU',
        'core-process-list.memory': 'मेमोरी',
        'core-process-list.of_ram': 'RAM का',
        'core-process-list.running_for': 'चल रहा है',
        'core-process-list.biggest_pid': 'सबसे बड़ा PID',
        'core-process-list.pid': 'PID',
        'core-process-list.command': 'कमांड',
        'core-process-list.stop': 'रोकें',
      }),
      ar: Object.freeze({
        'core-process-list.title': 'ما الذي يستخدم الجهاز',
        'core-process-list.summary':
          'العمليات التي تعمل الآن مع جمع العمليات ذات الاسم نفسه. «قيد التشغيل منذ» هي أقدم عملية في المجموعة؛ فالمجموعة طويلة العمر التي لا يتجاوز عمر أحدث عضو فيها ثوانيًا تُعاد تشغيلها بالسرعة التي تموت بها. تُخفى الوسائط التي تبدو كلمات مرور.',
        'core-process-list.stop_help':
          'عندما لا يكون الاسم محددًا بما يكفي للاستهداف — اثنتا عشرة عملية {{webalizer}} وإحداها تعمل على سجل معين. هذا هو {{pkill}} مع فارقين مهمين: يُطابق النمط هنا بدل تمريره إلى shell، ويعرض ما سيصيبه قبل أن يصيب أي شيء.',
        'core-process-list.process': 'العملية',
        'core-process-list.how_many': 'العدد',
        'core-process-list.user': 'المستخدم',
        'core-process-list.cpu': 'CPU',
        'core-process-list.memory': 'الذاكرة',
        'core-process-list.of_ram': 'من الذاكرة',
        'core-process-list.running_for': 'وقت التشغيل',
        'core-process-list.biggest_pid': 'أكبر PID',
        'core-process-list.pid': 'PID',
        'core-process-list.command': 'الأمر',
        'core-process-list.stop': 'إيقاف',
      }),
      zh: Object.freeze({
        'core-process-list.title': '什么在使用这台机器',
        'core-process-list.summary':
          '当前正在运行的进程，同名进程会合并计算。“已运行”取组中最早的进程：若一个长期存在的池中最新成员只有几秒钟大，它正以死亡的速度重启。看似密码的参数会被隐藏。',
        'core-process-list.stop_help':
          '当名称不足以精确定位时，例如有十二个 {{webalizer}} 进程，其中一个正在处理某个特定日志。这是 {{pkill}}，但有两个重要区别：模式在此处匹配，而不会交给 shell；它会在执行前显示将要命中的内容。',
        'core-process-list.process': '进程',
        'core-process-list.how_many': '数量',
        'core-process-list.user': '用户',
        'core-process-list.cpu': 'CPU',
        'core-process-list.memory': '内存',
        'core-process-list.of_ram': '占内存',
        'core-process-list.running_for': '运行时长',
        'core-process-list.biggest_pid': '最大 PID',
        'core-process-list.pid': 'PID',
        'core-process-list.command': '命令',
        'core-process-list.stop': '停止',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-process-list.${key}`,
      {},
      this.manifest.catalogs.en[`core-process-list.${key}`],
    );
  },

  render(data, helpers) {
    const {
      target,
      stopNote,
      renderTable,
      namedThing,
      userCell,
      processCpuCell,
      humanBytes,
      processAge,
      stopButtons,
      i18n,
    } = helpers;
    if (!target) return;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    const grouped = Boolean(data.grouped);
    const columns = [
      {
        label: t('core-process-list.process', {}, 'Process'),
        render: (row) => namedThing('process', row.name),
      },
      {
        label: t('core-process-list.how_many', {}, 'How many'),
        className: 'size',
        render: (row) => (row.procs > 1 ? row.procs : ''),
      },
      {
        label: t('core-process-list.user', {}, 'User'),
        render: (row) => userCell(row.user),
      },
      {
        label: t('core-process-list.cpu', {}, 'CPU'),
        className: 'size',
        render: (row) => processCpuCell(row.cpu_percent, data.cores),
      },
      {
        label: t('core-process-list.memory', {}, 'Memory'),
        className: 'size',
        render: (row) => humanBytes(row.rss_bytes),
      },
      {
        label: t('core-process-list.of_ram', {}, 'Of RAM'),
        className: 'size',
        render: (row) => `${row.mem_percent.toFixed(1)}%`,
      },
      {
        label: t('core-process-list.running_for', {}, 'Running for'),
        className: 'size',
        render: (row) => processAge(row),
      },
      {
        label: t(
          grouped ? 'core-process-list.biggest_pid' : 'core-process-list.pid',
          {},
          grouped ? 'Biggest PID' : 'PID',
        ),
        className: 'size',
        render: (row) => row.pid,
      },
      {
        label: t('core-process-list.command', {}, 'Command'),
        className: 'cmd',
        render: (row) => row.command,
      },
    ];
    if (data.can_stop) {
      columns.push({
        label: t('core-process-list.stop', {}, 'Stop'),
        className: 'stop-cell',
        render: (row) => stopButtons(row, grouped),
      });
    }
    renderTable(target.id, data.rows || [], columns);
    if (stopNote)
      stopNote.textContent = data.can_stop ? '' : data.stop_reason || '';
  },
};
