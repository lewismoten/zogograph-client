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
        'core-process-list.by': 'By',
        'core-process-list.group_by_name': 'Group by name',
        'core-process-list.stop_title': 'Stop by command line',
        'core-process-list.show_matches': 'Show matches',
        'core-process-list.pattern_placeholder':
          'webalizer/bin/english.*ssl_log\\.bkup',
        'core-process-list.stop_unavailable':
          'This one cannot be stopped from here.',
        'core-process-list.stop_subject_many':
          'all {{count}} processes named “{{name}}”',
        'core-process-list.stop_subject_one': '“{{name}}” (pid {{pid}})',
        'core-process-list.stop_group_stopped':
          '{{stopped}} of {{total}} stopped',
        'core-process-list.stop_group_remaining': '{{count}} still running',
        'core-process-list.stop_group_refused': '{{count}} refused',
        'core-process-list.stop_group_failed': '{{count}} failed',
        'core-process-list.stop_stopped': 'Stopped.',
        'core-process-list.stop_signalled': 'Signalled.',
        'core-process-list.stop_force': 'Force',
        'core-process-list.stop_ask': 'Ask',
        'core-process-list.stop_force_many_help':
          'SIGKILL — all {{count}} are killed outright, with no chance to finish writing anything',
        'core-process-list.stop_force_one_help':
          'SIGKILL — it is killed outright, with no chance to finish writing anything',
        'core-process-list.stop_ask_many_help':
          'SIGTERM — each is asked to exit and can tidy up first',
        'core-process-list.stop_ask_one_help':
          'SIGTERM — it is asked to exit and can tidy up first',
        'core-process-list.stop_killing': 'Killing…',
        'core-process-list.stop_asking': 'Asking…',
        'core-process-list.stop_force_waiting':
          '{{name}} is still in the process list after SIGKILL. That normally means it is stuck in the kernel — waiting on a disk or a network mount — where no signal can reach it until whatever it is waiting for returns.',
        'core-process-list.stop_ask_waiting':
          '{{name}} was asked to stop and has not. It is entitled to ignore SIGTERM; Force sends a signal it cannot ignore.',
        'core-process-list.stop_force_confirm':
          'Force {{what}} to stop?\n\nSIGKILL cannot be caught or ignored. Anything part-way through being written stays part-way written.',
        'core-process-list.stop_ask_confirm':
          'Ask {{what}} to stop?\n\nSIGTERM lets it close its files and exit cleanly. It can also ignore this.',
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
        'core-process-list.by': 'Por',
        'core-process-list.group_by_name': 'Agrupar por nombre',
        'core-process-list.stop_title': 'Detener por línea de comandos',
        'core-process-list.show_matches': 'Mostrar coincidencias',
        'core-process-list.pattern_placeholder':
          'webalizer/bin/english.*ssl_log\\.bkup',
        'core-process-list.stop_unavailable': 'No se puede detener desde aquí.',
        'core-process-list.stop_subject_many':
          'los {{count}} procesos llamados “{{name}}”',
        'core-process-list.stop_subject_one': '“{{name}}” (pid {{pid}})',
        'core-process-list.stop_group_stopped':
          '{{stopped}} de {{total}} detenidos',
        'core-process-list.stop_group_remaining': '{{count}} aún en ejecución',
        'core-process-list.stop_group_refused': '{{count}} rechazaron',
        'core-process-list.stop_group_failed': '{{count}} fallaron',
        'core-process-list.stop_stopped': 'Detenido.',
        'core-process-list.stop_signalled': 'Señal enviada.',
        'core-process-list.stop_force': 'Forzar',
        'core-process-list.stop_ask': 'Pedir',
        'core-process-list.stop_force_many_help':
          'SIGKILL: los {{count}} se eliminan de inmediato, sin oportunidad de terminar de escribir nada',
        'core-process-list.stop_force_one_help':
          'SIGKILL: se elimina de inmediato, sin oportunidad de terminar de escribir nada',
        'core-process-list.stop_ask_many_help':
          'SIGTERM: se les pide salir y pueden ordenar todo antes',
        'core-process-list.stop_ask_one_help':
          'SIGTERM: se le pide salir y puede ordenar todo antes',
        'core-process-list.stop_killing': 'Eliminando…',
        'core-process-list.stop_asking': 'Pidiendo…',
        'core-process-list.stop_force_waiting':
          '{{name}} sigue en la lista de procesos después de SIGKILL. Normalmente significa que está atascado en el kernel, esperando un disco o un montaje de red, donde ninguna señal puede alcanzarlo hasta que regrese aquello que espera.',
        'core-process-list.stop_ask_waiting':
          'Se pidió a {{name}} que se detuviera y no lo ha hecho. Tiene derecho a ignorar SIGTERM; Forzar envía una señal que no puede ignorar.',
        'core-process-list.stop_force_confirm':
          '¿Forzar la detención de {{what}}?\n\nSIGKILL no se puede capturar ni ignorar. Todo lo que se esté escribiendo queda a medio escribir.',
        'core-process-list.stop_ask_confirm':
          '¿Pedir a {{what}} que se detenga?\n\nSIGTERM le permite cerrar sus archivos y salir limpiamente. También puede ignorarlo.',
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
        'core-process-list.by': 'के अनुसार',
        'core-process-list.group_by_name': 'नाम के अनुसार समूहित करें',
        'core-process-list.stop_title': 'कमांड लाइन से रोकें',
        'core-process-list.show_matches': 'मिलान दिखाएँ',
        'core-process-list.pattern_placeholder':
          'webalizer/bin/english.*ssl_log\\.bkup',
        'core-process-list.stop_unavailable': 'इसे यहाँ से रोका नहीं जा सकता।',
        'core-process-list.stop_subject_many':
          '“{{name}}” नाम की सभी {{count}} प्रक्रियाएँ',
        'core-process-list.stop_subject_one': '“{{name}}” (pid {{pid}})',
        'core-process-list.stop_group_stopped':
          '{{total}} में से {{stopped}} रुके',
        'core-process-list.stop_group_remaining': '{{count}} अभी भी चल रहे हैं',
        'core-process-list.stop_group_refused': '{{count}} ने मना किया',
        'core-process-list.stop_group_failed': '{{count}} विफल हुए',
        'core-process-list.stop_stopped': 'रोक दिया गया।',
        'core-process-list.stop_signalled': 'सिग्नल भेजा गया।',
        'core-process-list.stop_force': 'बलपूर्वक',
        'core-process-list.stop_ask': 'कहें',
        'core-process-list.stop_force_many_help':
          'SIGKILL — सभी {{count}} को तुरंत समाप्त कर दिया जाता है, लिखना पूरा करने का कोई अवसर नहीं',
        'core-process-list.stop_force_one_help':
          'SIGKILL — इसे तुरंत समाप्त कर दिया जाता है, लिखना पूरा करने का कोई अवसर नहीं',
        'core-process-list.stop_ask_many_help':
          'SIGTERM — हर एक को बाहर निकलने के लिए कहा जाता है और पहले साफ़ कर सकता है',
        'core-process-list.stop_ask_one_help':
          'SIGTERM — इसे बाहर निकलने के लिए कहा जाता है और पहले साफ़ कर सकता है',
        'core-process-list.stop_killing': 'समाप्त किया जा रहा है…',
        'core-process-list.stop_asking': 'कहा जा रहा है…',
        'core-process-list.stop_force_waiting':
          'SIGKILL के बाद भी {{name}} प्रक्रिया सूची में है। इसका सामान्य अर्थ है कि वह कर्नेल में अटका है — डिस्क या नेटवर्क माउंट की प्रतीक्षा कर रहा है — जहाँ कोई सिग्नल तब तक नहीं पहुँच सकता जब तक वह चीज़ वापस न आ जाए जिसकी वह प्रतीक्षा कर रहा है।',
        'core-process-list.stop_ask_waiting':
          '{{name}} को रुकने के लिए कहा गया था और वह नहीं रुका। उसे SIGTERM को अनदेखा करने का अधिकार है; बलपूर्वक ऐसा सिग्नल भेजता है जिसे वह अनदेखा नहीं कर सकता।',
        'core-process-list.stop_force_confirm':
          'क्या {{what}} को बलपूर्वक रोकें?\n\nSIGKILL पकड़ा या अनदेखा नहीं किया जा सकता। जो भी लिखने के बीच में है वह बीच में ही रह जाएगा।',
        'core-process-list.stop_ask_confirm':
          'क्या {{what}} को रुकने के लिए कहें?\n\nSIGTERM उसे फ़ाइलें बंद कर साफ़ रूप से बाहर निकलने देता है। वह इसे अनदेखा भी कर सकता है।',
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
        'core-process-list.by': 'حسب',
        'core-process-list.group_by_name': 'تجميع حسب الاسم',
        'core-process-list.stop_title': 'الإيقاف بسطر الأوامر',
        'core-process-list.show_matches': 'إظهار المطابقات',
        'core-process-list.pattern_placeholder':
          'webalizer/bin/english.*ssl_log\\.bkup',
        'core-process-list.stop_unavailable': 'لا يمكن إيقاف هذه من هنا.',
        'core-process-list.stop_subject_many':
          'كل العمليات {{count}} المسماة “{{name}}”',
        'core-process-list.stop_subject_one': '“{{name}}” (pid {{pid}})',
        'core-process-list.stop_group_stopped': 'توقف {{stopped}} من {{total}}',
        'core-process-list.stop_group_remaining':
          'لا يزال {{count}} قيد التشغيل',
        'core-process-list.stop_group_refused': 'رفض {{count}}',
        'core-process-list.stop_group_failed': 'فشل {{count}}',
        'core-process-list.stop_stopped': 'توقفت.',
        'core-process-list.stop_signalled': 'أُرسلت الإشارة.',
        'core-process-list.stop_force': 'إجبار',
        'core-process-list.stop_ask': 'طلب',
        'core-process-list.stop_force_many_help':
          'SIGKILL — تُقتل {{count}} كلها فورًا، بلا فرصة لإكمال كتابة أي شيء',
        'core-process-list.stop_force_one_help':
          'SIGKILL — تُقتل فورًا، بلا فرصة لإكمال كتابة أي شيء',
        'core-process-list.stop_ask_many_help':
          'SIGTERM — يُطلب من كل منها الخروج ويمكنها ترتيب الأمور أولًا',
        'core-process-list.stop_ask_one_help':
          'SIGTERM — يُطلب منها الخروج ويمكنها ترتيب الأمور أولًا',
        'core-process-list.stop_killing': 'جارٍ الإنهاء…',
        'core-process-list.stop_asking': 'جارٍ الطلب…',
        'core-process-list.stop_force_waiting':
          'لا تزال {{name}} في قائمة العمليات بعد SIGKILL. يعني ذلك عادة أنها عالقة في النواة، تنتظر قرصًا أو تحميل شبكة، حيث لا تصل أي إشارة إليها حتى يعود ما تنتظره.',
        'core-process-list.stop_ask_waiting':
          'طُلب من {{name}} التوقف ولم يتوقف. يحق له تجاهل SIGTERM؛ يرسل إجبار إشارة لا يمكنه تجاهلها.',
        'core-process-list.stop_force_confirm':
          'هل تريد إجبار {{what}} على التوقف؟\n\nلا يمكن التقاط SIGKILL أو تجاهله. يبقى أي شيء في منتصف الكتابة مكتوبًا جزئيًا.',
        'core-process-list.stop_ask_confirm':
          'هل تطلب من {{what}} التوقف؟\n\nيتيح SIGTERM له إغلاق ملفاته والخروج بشكل سليم. ويمكنه أيضًا تجاهله.',
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
        'core-process-list.by': '按',
        'core-process-list.group_by_name': '按名称分组',
        'core-process-list.stop_title': '按命令行停止',
        'core-process-list.show_matches': '显示匹配项',
        'core-process-list.pattern_placeholder':
          'webalizer/bin/english.*ssl_log\\.bkup',
        'core-process-list.stop_unavailable': '无法在此处停止它。',
        'core-process-list.stop_subject_many':
          '名为“{{name}}”的全部 {{count}} 个进程',
        'core-process-list.stop_subject_one': '“{{name}}”(pid {{pid}})',
        'core-process-list.stop_group_stopped':
          '{{total}} 个中有 {{stopped}} 个已停止',
        'core-process-list.stop_group_remaining': '{{count}} 个仍在运行',
        'core-process-list.stop_group_refused': '{{count}} 个拒绝',
        'core-process-list.stop_group_failed': '{{count}} 个失败',
        'core-process-list.stop_stopped': '已停止。',
        'core-process-list.stop_signalled': '已发送信号。',
        'core-process-list.stop_force': '强制',
        'core-process-list.stop_ask': '请求',
        'core-process-list.stop_force_many_help':
          'SIGKILL — 会立即终止全部 {{count}} 个，无法完成任何正在写入的内容',
        'core-process-list.stop_force_one_help':
          'SIGKILL — 会立即终止它，无法完成任何正在写入的内容',
        'core-process-list.stop_ask_many_help':
          'SIGTERM — 会请求每个进程退出，并可先完成清理',
        'core-process-list.stop_ask_one_help':
          'SIGTERM — 会请求它退出，并可先完成清理',
        'core-process-list.stop_killing': '正在终止…',
        'core-process-list.stop_asking': '正在请求…',
        'core-process-list.stop_force_waiting':
          'SIGKILL 后，{{name}} 仍在进程列表中。这通常表示它卡在内核中，正在等待磁盘或网络挂载；在其等待的内容恢复前，任何信号都无法触及它。',
        'core-process-list.stop_ask_waiting':
          '已请求 {{name}} 停止，但它尚未停止。它有权忽略 SIGTERM；强制会发送它无法忽略的信号。',
        'core-process-list.stop_force_confirm':
          '要强制 {{what}} 停止吗？\n\nSIGKILL 无法被捕获或忽略。任何正在写入的内容都会停留在未完成状态。',
        'core-process-list.stop_ask_confirm':
          '要请求 {{what}} 停止吗？\n\nSIGTERM 允许它关闭文件并正常退出。它也可以忽略此请求。',
      }),
    }),
  }),
  text(key, helpers, values = {}) {
    return helpers.i18n.t(
      `core-process-list.${key}`,
      values,
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
