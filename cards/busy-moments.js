/* Core card package: recorded moments when the machine was busy. */
window.ZogographBusyMomentsCard = {
  manifest: Object.freeze({
    id: 'core-busy-moments',
    locales: ['core-busy-moments.'],
    helpers: [
      'holder',
      'busyOpen',
      'humanTime',
      'humanCount',
      'humanBytes',
      'processAge',
      'severityIcon',
      'showView',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-busy-moments.title': 'When it was last busy',
        'core-busy-moments.panel_help':
          'Recorded automatically whenever a reading crosses the CPU or memory threshold — so an overnight spike still has an answer in the morning. The collector and the {{ps}} it runs are left out: a record of a busy moment listing the thing that looks at processes is noise by construction.',
        'core-busy-moments.allowance_help':
          "{{allowance}} Inside CageFS {{proc_stat}} is virtualised to the account's own CPU allowance, so one PHP request working hard can cross 70% while the server as a whole is idle. Raise {{threshold}} if these are too frequent to be interesting; {{whole_machine}} is where the server's own load lives.",
        'core-busy-moments.empty':
          'Nothing recorded — the machine has not been busy enough to bother.',
        'core-busy-moments.alert_help':
          'An alert fired within two minutes of this — open the alerts page',
        'core-busy-moments.table_what': 'What',
        'core-busy-moments.level': 'high {{reason}}',
        'core-busy-moments.level_with_trigger': 'high {{reason}} {{value}}%',
        'core-busy-moments.top_more': '{{name}} and {{count}} other',
        'core-busy-moments.top_none': 'nothing measurable',
        'core-busy-moments.cpu_explained':
          'The reading was {{value}}%. These were the busiest processes at that moment — ps reports a lifetime average per core, so they will not add up to it.',
        'core-busy-moments.cpu_burst':
          'The reading was {{value}}%, but nothing in the list was using measurable CPU: whatever caused it had already finished when the process list was taken. A burst rather than steady load.',
        'core-busy-moments.row_processes': '{{name}} ({{count}} processes)',
        'core-busy-moments.cpu': 'CPU',
        'core-busy-moments.memory': 'Memory',
        'core-busy-moments.running_for': 'Running for',
      }),
      es: Object.freeze({
        'core-busy-moments.title': 'Cuándo estuvo ocupado por última vez',
        'core-busy-moments.panel_help':
          'Se registra automáticamente cuando una lectura supera el umbral de CPU o memoria, de modo que un pico nocturno aún tiene una respuesta por la mañana. Se omiten el recopilador y el {{ps}} que ejecuta: un registro de actividad que enumera la herramienta que mira los procesos es ruido por construcción.',
        'core-busy-moments.allowance_help':
          '{{allowance}} Dentro de CageFS, {{proc_stat}} está virtualizado según la asignación de CPU de esta cuenta, por lo que una solicitud PHP trabajando mucho puede superar el 70 % mientras el servidor completo está inactivo. Aumente {{threshold}} si son demasiado frecuentes para resultar interesantes; {{whole_machine}} es donde vive la carga propia del servidor.',
        'core-busy-moments.empty':
          'No hay nada registrado: la máquina no ha estado lo bastante ocupada como para importarlo.',
        'core-busy-moments.alert_help':
          'Se activó una alerta dentro de los dos minutos de esto; abra la página de alertas',
        'core-busy-moments.table_what': 'Qué',
        'core-busy-moments.level': '{{reason}} alta',
        'core-busy-moments.level_with_trigger': '{{reason}} alta {{value}}%',
        'core-busy-moments.top_more': '{{name}} y {{count}} más',
        'core-busy-moments.top_none': 'nada medible',
        'core-busy-moments.cpu_explained':
          'La lectura fue {{value}} %. Estos eran los procesos más ocupados en ese momento; ps informa un promedio de vida por núcleo, por lo que no sumarán esa cifra.',
        'core-busy-moments.cpu_burst':
          'La lectura fue {{value}} %, pero nada de la lista usaba CPU medible: lo que la causó ya había terminado al tomar la lista de procesos. Una ráfaga, no carga constante.',
        'core-busy-moments.row_processes': '{{name}} ({{count}} procesos)',
        'core-busy-moments.cpu': 'CPU',
        'core-busy-moments.memory': 'Memoria',
        'core-busy-moments.running_for': 'En ejecución desde',
      }),
      hi: Object.freeze({
        'core-busy-moments.title': 'यह आखिरी बार कब व्यस्त था',
        'core-busy-moments.panel_help':
          'जब कोई रीडिंग CPU या मेमोरी सीमा को पार करती है तो यह अपने-आप रिकॉर्ड होता है, इसलिए रात का स्पाइक सुबह भी उत्तर देता है। कलेक्टर और उसके चलाए {{ps}} को छोड़ दिया जाता है: व्यस्त समय का वह रिकॉर्ड जो प्रोसेस देखने वाले उपकरण को ही सूचीबद्ध करे, अपने स्वभाव से शोर है।',
        'core-busy-moments.allowance_help':
          '{{allowance}} CageFS के अंदर {{proc_stat}} इस खाते की अपनी CPU अनुमति के लिए वर्चुअलाइज्ड है, इसलिए कड़ी मेहनत करता एक PHP अनुरोध 70% पार कर सकता है जबकि पूरा सर्वर निष्क्रिय हो। यदि ये दिलचस्प होने के लिए बहुत बार आते हैं तो {{threshold}} बढ़ाएं; {{whole_machine}} वह जगह है जहां सर्वर का अपना लोड दिखता है।',
        'core-busy-moments.empty':
          'कुछ रिकॉर्ड नहीं हुआ — मशीन इतनी व्यस्त नहीं थी कि ध्यान दिया जाए।',
        'core-busy-moments.alert_help':
          'इसके दो मिनट के भीतर एक अलर्ट चला — अलर्ट पृष्ठ खोलें',
        'core-busy-moments.table_what': 'क्या',
        'core-busy-moments.level': 'उच्च {{reason}}',
        'core-busy-moments.level_with_trigger': 'उच्च {{reason}} {{value}}%',
        'core-busy-moments.top_more': '{{name}} और {{count}} अन्य',
        'core-busy-moments.top_none': 'कुछ मापने योग्य नहीं',
        'core-busy-moments.cpu_explained':
          'रीडिंग {{value}}% थी। उस समय ये सबसे व्यस्त प्रक्रियाएँ थीं — ps प्रति कोर जीवनकाल औसत बताता है, इसलिए ये उसमें नहीं जुड़ेंगी।',
        'core-busy-moments.cpu_burst':
          'रीडिंग {{value}}% थी, लेकिन सूची में कुछ भी मापने योग्य CPU उपयोग नहीं कर रहा था: प्रक्रिया सूची लेते समय कारण पहले ही समाप्त हो चुका था। स्थिर लोड के बजाय एक बर्स्ट।',
        'core-busy-moments.row_processes': '{{name}} ({{count}} प्रक्रियाएँ)',
        'core-busy-moments.cpu': 'CPU',
        'core-busy-moments.memory': 'मेमोरी',
        'core-busy-moments.running_for': 'से चल रहा है',
      }),
      ar: Object.freeze({
        'core-busy-moments.title': 'متى كان مشغولًا آخر مرة',
        'core-busy-moments.panel_help':
          'يُسجَّل تلقائيًا كلما تجاوزت قراءة عتبة المعالج أو الذاكرة، لذلك تبقى للذروة الليلية إجابة في الصباح. يُستبعد الجامع و{{ps}} الذي يشغله: فسجل لحظة انشغال يسرد الأداة التي تنظر إلى العمليات هو ضجيج بحكم تصميمه.',
        'core-busy-moments.allowance_help':
          '{{allowance}} داخل CageFS، يجري تمثيل {{proc_stat}} افتراضيًا وفق سماح CPU الخاص بهذا الحساب، لذلك قد يتجاوز طلب PHP يعمل بكثافة 70٪ بينما يكون الخادم كله خاملًا. ارفع {{threshold}} إذا كانت هذه الحالات متكررة أكثر من اللازم؛ {{whole_machine}} هو موضع حمل الخادم نفسه.',
        'core-busy-moments.empty':
          'لا شيء مسجل — لم يكن الجهاز مشغولًا بما يكفي للاهتمام.',
        'core-busy-moments.alert_help':
          'أُطلق تنبيه خلال دقيقتين من هذا — افتح صفحة التنبيهات',
        'core-busy-moments.table_what': 'ما',
        'core-busy-moments.level': '{{reason}} مرتفع',
        'core-busy-moments.level_with_trigger': '{{reason}} مرتفع {{value}}٪',
        'core-busy-moments.top_more': '{{name}} و{{count}} أخرى',
        'core-busy-moments.top_none': 'لا شيء قابل للقياس',
        'core-busy-moments.cpu_explained':
          'كانت القراءة {{value}}٪. كانت هذه أكثر العمليات انشغالًا في تلك اللحظة — يبلغ ps عن متوسط طوال العمر لكل نواة، لذا لن تجمع إليها.',
        'core-busy-moments.cpu_burst':
          'كانت القراءة {{value}}٪، لكن لا شيء في القائمة كان يستخدم CPU قابلًا للقياس: كان ما تسبب فيها قد انتهى عند أخذ قائمة العمليات. دفعة لا حمل ثابت.',
        'core-busy-moments.row_processes': '{{name}} ({{count}} عمليات)',
        'core-busy-moments.cpu': 'CPU',
        'core-busy-moments.memory': 'الذاكرة',
        'core-busy-moments.running_for': 'يعمل منذ',
      }),
      zh: Object.freeze({
        'core-busy-moments.title': '上次繁忙是什么时候',
        'core-busy-moments.panel_help':
          '每当读数超过 CPU 或内存阈值时都会自动记录，因此夜间峰值在早晨仍有答案。采集器及其运行的 {{ps}} 会被排除：一份繁忙时刻的记录若把查看进程的工具本身列出来，按定义就是噪声。',
        'core-busy-moments.allowance_help':
          '{{allowance}} 在 CageFS 内，{{proc_stat}} 会按此帐户自己的 CPU 配额虚拟化，因此单个高负载 PHP 请求可以超过 70%，而整个服务器仍处于空闲状态。如果这些记录太频繁而不再有意义，请提高 {{threshold}}；{{whole_machine}} 显示服务器自身的负载。',
        'core-busy-moments.empty': '没有记录——机器没有忙到值得记录的程度。',
        'core-busy-moments.alert_help':
          '此事件两分钟内触发了警报——打开警报页面',
        'core-busy-moments.table_what': '内容',
        'core-busy-moments.level': '{{reason}} 偏高',
        'core-busy-moments.level_with_trigger': '{{reason}} 偏高 {{value}}%',
        'core-busy-moments.top_more': '{{name}} 及其他 {{count}} 个',
        'core-busy-moments.top_none': '没有可测量的内容',
        'core-busy-moments.cpu_explained':
          '读数为 {{value}}%。这些是当时最忙的进程——ps 按每个核心报告生命周期平均值，因此它们不会相加等于该读数。',
        'core-busy-moments.cpu_burst':
          '读数为 {{value}}%，但列表中没有内容使用可测量的 CPU：导致该读数的内容在获取进程列表时已结束。这是突发而非持续负载。',
        'core-busy-moments.row_processes': '{{name}}（{{count}} 个进程）',
        'core-busy-moments.cpu': 'CPU',
        'core-busy-moments.memory': '内存',
        'core-busy-moments.running_for': '已运行',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-busy-moments.${key}`,
      {},
      this.manifest.catalogs.en[`core-busy-moments.${key}`],
    );
  },
  render(moments, helpers) {
    const {
      holder,
      busyOpen,
      humanTime,
      humanCount,
      humanBytes,
      processAge,
      severityIcon,
      showView,
      i18n,
    } = helpers;
    if (!holder) return;
    const t = (key, values, fallback) =>
      i18n.t(`core-busy-moments.${key}`, values, fallback);
    if (!moments.length) {
      holder.replaceChildren(
        Object.assign(document.createElement('p'), {
          className: 'muted',
          textContent: t(
            'empty',
            {},
            'Nothing recorded — the machine has not been busy enough to bother.',
          ),
        }),
      );
      return;
    }
    holder.replaceChildren(
      ...moments.map((moment) => {
        const section = document.createElement('details');
        section.className = 'busy-moment';
        section.open = busyOpen.has(moment.taken_at);
        section.addEventListener('toggle', () =>
          section.open
            ? busyOpen.add(moment.taken_at)
            : busyOpen.delete(moment.taken_at),
        );
        const summary = document.createElement('summary');
        const when = document.createElement('span');
        when.className = 'busy-when';
        when.textContent = humanTime(moment.taken_at);
        summary.appendChild(when);
        const first = moment.rows[0] || {};
        const trigger = Number(first.trigger_percent ?? 0);
        const level = document.createElement('span');
        level.className = 'busy-level';
        level.textContent = t(
          trigger ? 'level_with_trigger' : 'level',
          { reason: moment.reason, value: trigger.toFixed(0) },
          trigger
            ? `high ${moment.reason} ${trigger.toFixed(0)}%`
            : `high ${moment.reason}`,
        );
        summary.appendChild(level);
        const top = document.createElement('span');
        top.className = 'busy-top muted';
        top.textContent = first.name
          ? moment.rows.length > 1
            ? t(
                'top_more',
                { name: first.name, count: humanCount(moment.rows.length - 1) },
                `${first.name} and ${humanCount(moment.rows.length - 1)} other`,
              )
            : first.name
          : t('top_none', {}, 'nothing measurable');
        summary.appendChild(top);
        for (const alert of moment.alerts || []) {
          const link = document.createElement('button');
          link.className = 'link busy-alert';
          link.textContent = `${severityIcon(alert.severity)} ${alert.subject}`;
          link.title = t(
            'alert_help',
            {},
            'An alert fired within two minutes of this — open the alerts page',
          );
          link.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            showView('alerts');
          });
          summary.appendChild(link);
        }
        section.appendChild(summary);
        if (moment.reason === 'cpu' && first.trigger_percent != null) {
          const busiest = Number(first.cpu_percent ?? 0);
          const note = document.createElement('p');
          note.className = 'muted small';
          note.textContent = t(
            busiest >= 2 ? 'cpu_explained' : 'cpu_burst',
            { value: trigger.toFixed(0) },
            busiest >= 2
              ? `The reading was ${trigger.toFixed(0)}%. These were the busiest processes at that moment — ps reports a lifetime average per core, so they will not add up to it.`
              : `The reading was ${trigger.toFixed(0)}%, but nothing in the list was using measurable CPU: whatever caused it had already finished when the process list was taken. A burst rather than steady load.`,
          );
          section.appendChild(note);
        }
        const table = document.createElement('table');
        const head = table.createTHead().insertRow();
        for (const [key, fallback, cls] of [
          ['busy_table_what', 'What', ''],
          ['cpu', 'CPU', 'size'],
          ['memory', 'Memory', 'size'],
          ['running_for', 'Running for', 'size'],
        ]) {
          const th = document.createElement('th');
          th.textContent =
            key === 'busy_table_what'
              ? t('table_what', {}, fallback)
              : t(key, {}, fallback);
          if (cls) th.className = cls;
          head.appendChild(th);
        }
        const body = table.createTBody();
        for (const row of moment.rows) {
          const tr = body.insertRow();
          tr.insertCell().textContent =
            row.procs > 1
              ? t(
                  'row_processes',
                  { name: row.name, count: humanCount(row.procs) },
                  `${row.name} (${humanCount(row.procs)} processes)`,
                )
              : row.name;
          const cpu = tr.insertCell();
          cpu.className = 'size';
          cpu.textContent = `${Number(row.cpu_percent).toFixed(1)}%`;
          const rss = tr.insertCell();
          rss.className = 'size';
          rss.textContent = humanBytes(row.rss_bytes);
          const age = tr.insertCell();
          age.className = 'size muted';
          age.textContent = processAge(row);
        }
        section.appendChild(table);
        return section;
      }),
    );
  },
};
