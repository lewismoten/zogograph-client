/* Core card package: the shell supplies formatting and the scoped watch action. */
window.ZogographHostDaemonsCard = {
  manifest: Object.freeze({
    id: 'core-host-daemons',
    locales: ['core-host-daemons.'],
    helpers: [
      'target',
      'renderTable',
      'humanDuration',
      'whenSpan',
      'watch',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-host-daemons.title': 'Are the services alive?',
        'core-host-daemons.help':
          'The one question the rest of this page cannot answer. A daemon that has died uses no memory, no CPU and no disk, so it vanishes from every chart above rather than standing out in one. {{not_installed}} is shown apart from {{stopped}} on purpose — a service this machine never ran is not a fault, and colouring it red would make the panel permanently alarming and therefore ignored.',
        'core-host-daemons.empty': 'No service states recorded yet.',
        'core-host-daemons.running': 'running',
        'core-host-daemons.stopped': 'STOPPED',
        'core-host-daemons.not_installed': 'not installed',
        'core-host-daemons.cannot_tell': 'cannot tell',
        'core-host-daemons.not_watched': 'not watched',
        'core-host-daemons.waiting': 'waiting for a reading',
        'core-host-daemons.stopped_lower': 'stopped',
        'core-host-daemons.stopped_all_readings_tip':
          'Stopped for every reading on record, so this was almost certainly disabled deliberately rather than having failed. It does not raise alerts.',
        'core-host-daemons.not_watched_tip':
          'Switched off here, so the collector is not recording it. Nothing is known about whether it is running.',
        'core-host-daemons.waiting_tip':
          'Switched on, but the collector has not run since. It reports on its own schedule.',
        'core-host-daemons.not_installed_tip':
          'Neither a systemd unit nor a running process by any of the names this service is known by. Almost certainly not installed on this machine.',
        'core-host-daemons.cannot_tell_tip':
          'systemd could not be asked and no matching process is running. Stopped and never-installed look identical from a process list.',
        'core-host-daemons.stopped_tip':
          'This was running earlier and is not now.',
        'core-host-daemons.service': 'Service',
        'core-host-daemons.state': 'State',
        'core-host-daemons.for': 'For',
        'core-host-daemons.since_systemd': 'Since systemd started it.',
        'core-host-daemons.since_state':
          'Since this state was first seen here.',
        'core-host-daemons.at_least_this_long':
          'At least this long — it has been this way for every reading on record.',
        'core-host-daemons.restarts': 'Restarts',
        'core-host-daemons.processes': 'Processes',
        'core-host-daemons.active_units': '{{active}}/{{total}} units',
        'core-host-daemons.last_seen_down': 'Last seen down',
        'core-host-daemons.not_in_days': 'not in {{days, number}} days',
        'core-host-daemons.stop_watching': 'Stop watching',
        'core-host-daemons.watch': 'Watch',
        'core-host-daemons.stop_watching_tip':
          'Stop recording this service. Its history is kept, but nothing more is added and it will not raise alerts.',
        'core-host-daemons.watch_tip':
          "Start recording this service again from the collector's next run.",
        'core-host-daemons.stop_watching_notice':
          '{{service}} will stop being recorded at the next host reading.',
        'core-host-daemons.watch_notice':
          '{{service}} will be recorded again at the next host reading.',
      }),
      es: Object.freeze({
        'core-host-daemons.title': '¿Están activos los servicios?',
        'core-host-daemons.help':
          'La única pregunta que el resto de esta página no puede responder. Un demonio que ha muerto no usa memoria, CPU ni disco, así que desaparece de todos los gráficos en vez de destacar en uno. {{not_installed}} se muestra separado de {{stopped}} deliberadamente: un servicio que esta máquina nunca ejecutó no es un fallo, y colorearlo en rojo haría que el panel estuviera siempre alarmante y se ignorara.',
        'core-host-daemons.empty':
          'Aún no se registraron estados de servicios.',
        'core-host-daemons.running': 'en ejecución',
        'core-host-daemons.stopped': 'DETENIDO',
        'core-host-daemons.not_installed': 'no instalado',
        'core-host-daemons.cannot_tell': 'no se puede determinar',
        'core-host-daemons.not_watched': 'sin vigilancia',
        'core-host-daemons.waiting': 'esperando una lectura',
        'core-host-daemons.stopped_lower': 'detenido',
        'core-host-daemons.stopped_all_readings_tip':
          'Estuvo detenido en todas las lecturas registradas, así que casi con seguridad se desactivó deliberadamente y no falló. No genera alertas.',
        'core-host-daemons.not_watched_tip':
          'Se desactivó aquí, por lo que el recopilador no lo registra. No se sabe si está en ejecución.',
        'core-host-daemons.waiting_tip':
          'Está activado, pero el recopilador aún no se ha ejecutado. Informa según su propio horario.',
        'core-host-daemons.not_installed_tip':
          'No hay ni una unidad systemd ni un proceso en ejecución con ninguno de los nombres conocidos de este servicio. Casi con seguridad no está instalado en esta máquina.',
        'core-host-daemons.cannot_tell_tip':
          'No se pudo consultar systemd y no hay ningún proceso coincidente. Detenido y nunca instalado se ven igual desde una lista de procesos.',
        'core-host-daemons.stopped_tip':
          'Estaba en ejecución antes y ahora no lo está.',
        'core-host-daemons.service': 'Servicio',
        'core-host-daemons.state': 'Estado',
        'core-host-daemons.for': 'Durante',
        'core-host-daemons.since_systemd': 'Desde que systemd lo inició.',
        'core-host-daemons.since_state':
          'Desde que este estado se vio aquí por primera vez.',
        'core-host-daemons.at_least_this_long':
          'Al menos este tiempo: ha estado así en todas las lecturas registradas.',
        'core-host-daemons.restarts': 'Reinicios',
        'core-host-daemons.processes': 'Procesos',
        'core-host-daemons.active_units': '{{active}}/{{total}} unidades',
        'core-host-daemons.last_seen_down': 'Visto caído por última vez',
        'core-host-daemons.not_in_days': 'no en {{days, number}} días',
        'core-host-daemons.stop_watching': 'Dejar de vigilar',
        'core-host-daemons.watch': 'Vigilar',
        'core-host-daemons.stop_watching_tip':
          'Deja de registrar este servicio. Se conserva su historial, pero no se añadirá nada más ni generará alertas.',
        'core-host-daemons.watch_tip':
          'Vuelve a registrar este servicio a partir de la próxima ejecución del recopilador.',
        'core-host-daemons.stop_watching_notice':
          '{{service}} dejará de registrarse en la próxima lectura del host.',
        'core-host-daemons.watch_notice':
          '{{service}} volverá a registrarse en la próxima lectura del host.',
      }),
      hi: Object.freeze({
        'core-host-daemons.title': 'क्या सेवाएँ चालू हैं?',
        'core-host-daemons.help':
          'यह वह एक प्रश्न है जिसका उत्तर इस पेज का बाकी हिस्सा नहीं दे सकता। बंद हो चुका डेमन मेमोरी, CPU या डिस्क उपयोग नहीं करता, इसलिए वह किसी एक चार्ट में उभरने के बजाय ऊपर के सभी चार्टों से गायब हो जाता है। {{not_installed}} को जानबूझकर {{stopped}} से अलग दिखाया गया है: जो सेवा इस मशीन पर कभी चली ही नहीं वह त्रुटि नहीं है, और उसे लाल दिखाने से पैनल हमेशा चेतावनी जैसा लगेगा और अनदेखा होगा।',
        'core-host-daemons.empty': 'अभी तक कोई सेवा स्थिति दर्ज नहीं हुई।',
        'core-host-daemons.running': 'चल रही है',
        'core-host-daemons.stopped': 'रुकी हुई',
        'core-host-daemons.not_installed': 'स्थापित नहीं',
        'core-host-daemons.cannot_tell': 'पता नहीं चल सकता',
        'core-host-daemons.not_watched': 'निगरानी में नहीं',
        'core-host-daemons.waiting': 'रीडिंग की प्रतीक्षा में',
        'core-host-daemons.stopped_lower': 'रुकी हुई',
        'core-host-daemons.stopped_all_readings_tip':
          'रिकॉर्ड की हर रीडिंग में यह रुकी हुई थी, इसलिए लगभग निश्चित रूप से इसे जानबूझकर बंद किया गया था, यह विफल नहीं हुई। यह चेतावनियाँ नहीं उठाती।',
        'core-host-daemons.not_watched_tip':
          'इसे यहाँ बंद किया गया है, इसलिए कलेक्टर इसे रिकॉर्ड नहीं कर रहा। यह चल रही है या नहीं, इसकी जानकारी नहीं है।',
        'core-host-daemons.waiting_tip':
          'यह चालू है, लेकिन तब से कलेक्टर नहीं चला है। यह अपने समय-सारणी पर रिपोर्ट करता है।',
        'core-host-daemons.not_installed_tip':
          'इस सेवा के किसी भी ज्ञात नाम से न systemd यूनिट है, न चल रही प्रक्रिया। यह लगभग निश्चित रूप से इस मशीन पर स्थापित नहीं है।',
        'core-host-daemons.cannot_tell_tip':
          'systemd से पूछा नहीं जा सका और कोई मिलती हुई प्रक्रिया नहीं चल रही है। प्रक्रिया सूची में रुकी हुई और कभी स्थापित न हुई सेवा एक जैसी दिखती हैं।',
        'core-host-daemons.stopped_tip':
          'यह पहले चल रही थी और अब नहीं चल रही है।',
        'core-host-daemons.service': 'सेवा',
        'core-host-daemons.state': 'स्थिति',
        'core-host-daemons.for': 'अवधि',
        'core-host-daemons.since_systemd': 'जब से systemd ने इसे शुरू किया।',
        'core-host-daemons.since_state': 'जब से यह स्थिति यहाँ पहली बार दिखी।',
        'core-host-daemons.at_least_this_long':
          'कम से कम इतनी अवधि — रिकॉर्ड की हर रीडिंग में यह ऐसी ही रही है।',
        'core-host-daemons.restarts': 'पुनः आरंभ',
        'core-host-daemons.processes': 'प्रक्रियाएँ',
        'core-host-daemons.active_units': '{{active}}/{{total}} इकाइयाँ',
        'core-host-daemons.last_seen_down': 'अंतिम बार बंद देखा गया',
        'core-host-daemons.not_in_days': '{{days, number}} दिनों में नहीं',
        'core-host-daemons.stop_watching': 'निगरानी बंद करें',
        'core-host-daemons.watch': 'निगरानी करें',
        'core-host-daemons.stop_watching_tip':
          'इस सेवा को रिकॉर्ड करना बंद करें। इसका इतिहास रखा जाता है, लेकिन आगे कुछ नहीं जोड़ा जाएगा और यह चेतावनियाँ नहीं उठाएगी।',
        'core-host-daemons.watch_tip':
          'कलेक्टर के अगले रन से इस सेवा को फिर रिकॉर्ड करना शुरू करें।',
        'core-host-daemons.stop_watching_notice':
          '{{service}} को अगली होस्ट रीडिंग पर रिकॉर्ड करना बंद कर दिया जाएगा।',
        'core-host-daemons.watch_notice':
          '{{service}} को अगली होस्ट रीडिंग पर फिर रिकॉर्ड किया जाएगा।',
      }),
      ar: Object.freeze({
        'core-host-daemons.title': 'هل الخدمات تعمل؟',
        'core-host-daemons.help':
          'هذا هو السؤال الوحيد الذي لا تجيب عنه بقية الصفحة. الخادم الذي توقف لا يستخدم ذاكرة أو معالجًا أو قرصًا، لذا يختفي من كل المخططات أعلاه بدل أن يبرز في واحد منها. يُعرض {{not_installed}} منفصلًا عن {{stopped}} عمدًا: فالخدمة التي لم يشغّلها هذا الجهاز قط ليست عطلًا، وتلوينها بالأحمر سيجعل اللوحة مقلقة دائمًا ثم تُهمَل.',
        'core-host-daemons.empty': 'لم تُسجَّل حالات الخدمات بعد.',
        'core-host-daemons.running': 'قيد التشغيل',
        'core-host-daemons.stopped': 'متوقف',
        'core-host-daemons.not_installed': 'غير مثبّت',
        'core-host-daemons.cannot_tell': 'لا يمكن التحديد',
        'core-host-daemons.not_watched': 'غير مراقَب',
        'core-host-daemons.waiting': 'بانتظار قراءة',
        'core-host-daemons.stopped_lower': 'متوقف',
        'core-host-daemons.stopped_all_readings_tip':
          'كان متوقفًا في كل قراءة مسجلة، لذا من شبه المؤكد أنه عُطّل عمدًا بدل أن يكون قد فشل. لا يطلق تنبيهات.',
        'core-host-daemons.not_watched_tip':
          'أُوقف هنا، لذا لا يسجله الجامع. لا شيء معروف عن كونه قيد التشغيل.',
        'core-host-daemons.waiting_tip':
          'هو مفعّل، لكن الجامع لم يعمل منذ ذلك الحين. يبلّغ وفق جدوله الخاص.',
        'core-host-daemons.not_installed_tip':
          'لا توجد وحدة systemd ولا عملية تعمل بأي اسم من الأسماء المعروفة لهذه الخدمة. على الأرجح ليست مثبّتة على هذا الجهاز.',
        'core-host-daemons.cannot_tell_tip':
          'تعذر سؤال systemd ولا تعمل عملية مطابقة. المتوقف وغير المثبّت مطلقًا يبدوان متماثلين في قائمة العمليات.',
        'core-host-daemons.stopped_tip':
          'كانت تعمل في وقت سابق وليست كذلك الآن.',
        'core-host-daemons.service': 'الخدمة',
        'core-host-daemons.state': 'الحالة',
        'core-host-daemons.for': 'المدة',
        'core-host-daemons.since_systemd': 'منذ أن شغّلها systemd.',
        'core-host-daemons.since_state': 'منذ ظهور هذه الحالة هنا أول مرة.',
        'core-host-daemons.at_least_this_long':
          'لهذه المدة على الأقل — كانت هكذا في كل قراءة مسجلة.',
        'core-host-daemons.restarts': 'إعادات التشغيل',
        'core-host-daemons.processes': 'العمليات',
        'core-host-daemons.active_units': '{{active}}/{{total}} وحدة',
        'core-host-daemons.last_seen_down': 'آخر ظهور كمتوقفة',
        'core-host-daemons.not_in_days': 'ليس في {{days, number}} أيام',
        'core-host-daemons.stop_watching': 'أوقف المراقبة',
        'core-host-daemons.watch': 'راقب',
        'core-host-daemons.stop_watching_tip':
          'أوقف تسجيل هذه الخدمة. يُحتفظ بسجلها، لكن لن يُضاف شيء آخر ولن تطلق تنبيهات.',
        'core-host-daemons.watch_tip':
          'ابدأ تسجيل هذه الخدمة مجددًا من التشغيل التالي للجامع.',
        'core-host-daemons.stop_watching_notice':
          'سيتوقف تسجيل {{service}} عند قراءة المضيف التالية.',
        'core-host-daemons.watch_notice':
          'سيُسجَّل {{service}} مجددًا عند قراءة المضيف التالية.',
      }),
      zh: Object.freeze({
        'core-host-daemons.title': '服务是否正常运行？',
        'core-host-daemons.help':
          '这是本页其他部分无法回答的唯一问题。已停止的守护进程不使用内存、CPU 或磁盘，因此会从上方所有图表中消失，而不是在某一张图上突出显示。{{not_installed}} 会刻意与 {{stopped}} 分开显示：这台机器从未运行的服务并非故障；将其标红会使面板永远处于警报状态，反而被忽略。',
        'core-host-daemons.empty': '尚未记录服务状态。',
        'core-host-daemons.running': '正在运行',
        'core-host-daemons.stopped': '已停止',
        'core-host-daemons.not_installed': '未安装',
        'core-host-daemons.cannot_tell': '无法确定',
        'core-host-daemons.not_watched': '未监控',
        'core-host-daemons.waiting': '等待读数',
        'core-host-daemons.stopped_lower': '已停止',
        'core-host-daemons.stopped_all_readings_tip':
          '在所有已记录的读数中它都处于停止状态，因此几乎可以肯定是被有意禁用，而不是发生了故障。它不会触发警报。',
        'core-host-daemons.not_watched_tip':
          '已在这里关闭，因此采集器不会记录它。无法得知它是否正在运行。',
        'core-host-daemons.waiting_tip':
          '已启用，但采集器此后尚未运行。它会按自己的计划报告。',
        'core-host-daemons.not_installed_tip':
          '既没有 systemd 单元，也没有以此服务任何已知名称运行的进程。它几乎可以肯定未安装在此机器上。',
        'core-host-daemons.cannot_tell_tip':
          '无法查询 systemd，也没有匹配的运行进程。从进程列表看，已停止和从未安装是一样的。',
        'core-host-daemons.stopped_tip': '它之前正在运行，现在已经不在运行。',
        'core-host-daemons.service': '服务',
        'core-host-daemons.state': '状态',
        'core-host-daemons.for': '持续时间',
        'core-host-daemons.since_systemd': '自 systemd 启动它以来。',
        'core-host-daemons.since_state': '自这里首次看到此状态以来。',
        'core-host-daemons.at_least_this_long':
          '至少这么久——它在每次已记录的读数中都是这样。',
        'core-host-daemons.restarts': '重启次数',
        'core-host-daemons.processes': '进程',
        'core-host-daemons.active_units': '{{active}}/{{total}} 个单元',
        'core-host-daemons.last_seen_down': '上次看到停止',
        'core-host-daemons.not_in_days': '在 {{days, number}} 天内未出现',
        'core-host-daemons.stop_watching': '停止监控',
        'core-host-daemons.watch': '监控',
        'core-host-daemons.stop_watching_tip':
          '停止记录此服务。其历史会保留，但不会再添加内容，也不会触发警报。',
        'core-host-daemons.watch_tip': '从采集器下一次运行起再次记录此服务。',
        'core-host-daemons.stop_watching_notice':
          '{{service}} 将在下一次主机读数时停止记录。',
        'core-host-daemons.watch_notice':
          '{{service}} 将在下一次主机读数时再次记录。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-host-daemons.${key}`,
      {},
      this.manifest.catalogs.en[`core-host-daemons.${key}`],
    );
  },

  render(daemons, helpers) {
    const { target, renderTable, humanDuration, whenSpan, watch, i18n } =
      helpers;
    const t = (key, values, fallback) => {
      const packageKey = key.startsWith('host.daemon_')
        ? `core-host-daemons.${key.slice('host.daemon_'.length)}`
        : key;
      return i18n.t(packageKey, values, fallback);
    };
    if (!target) return;
    if (!daemons || !daemons.available || !daemons.rows?.length) {
      target.replaceChildren(
        Object.assign(document.createElement('p'), {
          className: 'muted',
          textContent:
            daemons?.reason ||
            t('core-host-daemons.empty', {}, 'No service states recorded yet.'),
        }),
      );
      return;
    }
    const states = {
      running: {
        text: t('core-host-daemons.running', {}, 'running'),
        className: 'state-up',
      },
      stopped: {
        text: t('core-host-daemons.stopped', {}, 'STOPPED'),
        className: 'state-down',
      },
      absent: {
        text: t('core-host-daemons.not_installed', {}, 'not installed'),
        className: 'state-absent',
      },
      unknown: {
        text: t('core-host-daemons.cannot_tell', {}, 'cannot tell'),
        className: 'state-absent',
      },
      off: {
        text: t('core-host-daemons.not_watched', {}, 'not watched'),
        className: 'state-absent',
      },
      pending: {
        text: t('core-host-daemons.waiting', {}, 'waiting for a reading'),
        className: 'state-absent',
      },
    };
    const now = Date.now() / 1000;
    renderTable(target.id, daemons.rows, [
      {
        label: t('host.daemon_service', {}, 'Service'),
        className: 'name',
        render: (row) => row.label,
      },
      {
        label: t('host.daemon_state', {}, 'State'),
        render: (row) => {
          const span = document.createElement('span');
          if (row.state === 'stopped' && !row.since_known) {
            span.className = 'state-absent';
            span.textContent = t('host.daemon_stopped_lower', {}, 'stopped');
            span.title = t(
              'host.daemon_stopped_all_readings_tip',
              {},
              'Stopped for every reading on record, so this was almost certainly disabled deliberately rather than having failed. It does not raise alerts.',
            );
            return span;
          }
          const shown = states[row.state] ?? states.unknown;
          span.className = shown.className;
          span.textContent = shown.text;
          const tips = {
            off: [
              'host.daemon_not_watched_tip',
              'Switched off here, so the collector is not recording it. Nothing is known about whether it is running.',
            ],
            pending: [
              'host.daemon_waiting_tip',
              'Switched on, but the collector has not run since. It reports on its own schedule.',
            ],
            absent: [
              'host.daemon_not_installed_tip',
              'Neither a systemd unit nor a running process by any of the names this service is known by. Almost certainly not installed on this machine.',
            ],
            unknown: [
              'host.daemon_cannot_tell_tip',
              'systemd could not be asked and no matching process is running. Stopped and never-installed look identical from a process list.',
            ],
            stopped: [
              'host.daemon_stopped_tip',
              'This was running earlier and is not now.',
            ],
          };
          if (tips[row.state]) {
            span.title = t(tips[row.state][0], {}, tips[row.state][1]);
          }
          return span;
        },
      },
      {
        label: t('host.daemon_for', {}, 'For'),
        className: 'size',
        render: (row) => {
          if (['absent', 'unknown', 'off', 'pending'].includes(row.state))
            return '—';
          const at = row.started_at ?? row.since;
          if (!at) return '—';
          const span = document.createElement('span');
          span.textContent = humanDuration(now - at);
          span.title = row.started_at
            ? t('host.daemon_since_systemd', {}, 'Since systemd started it.')
            : row.since_known
              ? t(
                  'host.daemon_since_state',
                  {},
                  'Since this state was first seen here.',
                )
              : t(
                  'host.daemon_at_least_this_long',
                  {},
                  'At least this long — it has been this way for every reading on record.',
                );
          if (!row.started_at && !row.since_known) span.className = 'muted';
          return span;
        },
      },
      {
        label: t('host.daemon_restarts', {}, 'Restarts'),
        className: 'size',
        render: (row) => (row.restarts == null ? '—' : String(row.restarts)),
      },
      {
        label: t('host.daemon_processes', {}, 'Processes'),
        className: 'size',
        render: (row) =>
          row.units_total > 1
            ? t(
                'host.daemon_active_units',
                { active: row.units_active, total: row.units_total },
                '{{active}}/{{total}} units',
              )
            : row.procs
              ? String(row.procs)
              : '—',
      },
      {
        label: t('host.daemon_last_seen_down', {}, 'Last seen down'),
        render: (row) => {
          if (row.last_down_at) return whenSpan(row.last_down_at);
          const span = document.createElement('span');
          span.className = 'muted';
          span.textContent = ['absent', 'off', 'pending'].includes(row.state)
            ? '—'
            : t(
                'host.daemon_not_in_days',
                { days: daemons.history_days },
                'not in {{days, number}} days',
              );
          return span;
        },
      },
      {
        label: '',
        render: (row) => {
          const button = document.createElement('button');
          button.className = row.watched ? 'link' : 'link muted';
          button.textContent = row.watched
            ? t('host.daemon_stop_watching', {}, 'Stop watching')
            : t('host.daemon_watch', {}, 'Watch');
          button.title = row.watched
            ? t(
                'host.daemon_stop_watching_tip',
                {},
                'Stop recording this service. Its history is kept, but nothing more is added and it will not raise alerts.',
              )
            : t(
                'host.daemon_watch_tip',
                {},
                "Start recording this service again from the collector's next run.",
              );
          button.addEventListener('click', () => watch(row, button));
          return button;
        },
      },
    ]);
  },
};
