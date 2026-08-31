/* Core card package: saved terminal sessions and their opening table. */
window.ZogographTerminalSessionsCard = {
  manifest: Object.freeze({
    id: 'core-terminal-sessions',
    locales: ['core-terminal-sessions.'],
    helpers: ['target', 'note', 'renderTable', 'whenSpan', 'open', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-terminal-sessions.title': 'Terminal sessions',
        'core-terminal-sessions.sign_in': 'Sign in to see terminal sessions.',
        'core-terminal-sessions.grouping_help':
          'Commands that were run close together, grouped the way the work actually happened — an hour’s gap starts a new one, because somebody who ran three commands, went to lunch and came back was doing two different things.',
        'core-terminal-sessions.topic_help':
          'Give one a topic and it becomes something you can come back to. A list of timestamps is a list nobody reads; ‘the disk filling on Tuesday’ is a thing you look for six weeks later.',
        'core-terminal-sessions.output_help':
          'Opening a session shows what was run. What each command {{printed}} is behind its own switch, because those are two different questions with two different answers: what you typed is safe on any screen, and what came back may be a config file with a password in it.',
        'core-terminal-sessions.empty':
          'Nothing yet. A session starts the first time a command is run and stays open until an hour goes by without one.',
        'core-terminal-sessions.topic': 'Topic',
        'core-terminal-sessions.no_topic': 'no topic yet',
        'core-terminal-sessions.started': 'Started',
        'core-terminal-sessions.last_command': 'Last command',
        'core-terminal-sessions.commands': 'Commands',
        'core-terminal-sessions.who': 'Who',
        'core-terminal-sessions.name_title': 'Name session',
        'core-terminal-sessions.name_label': 'What was this session about?',
        'core-terminal-sessions.name_help':
          'Name the path, service, or symptom specifically.',
        'core-terminal-sessions.named': 'Named.',
      }),
      es: Object.freeze({
        'core-terminal-sessions.title': 'Sesiones de terminal',
        'core-terminal-sessions.sign_in':
          'Inicie sesión para ver las sesiones de terminal.',
        'core-terminal-sessions.grouping_help':
          'Comandos ejecutados juntos, agrupados como realmente ocurrió el trabajo: una pausa de una hora inicia otra sesión, porque alguien que ejecutó tres comandos, fue a comer y regresó estaba haciendo dos cosas diferentes.',
        'core-terminal-sessions.topic_help':
          'Déle un tema a una y se convertirá en algo a lo que puede volver. Una lista de marcas de tiempo es una lista que nadie lee; «el disco llenándose el martes» es algo que busca seis semanas después.',
        'core-terminal-sessions.output_help':
          'Abrir una sesión muestra qué se ejecutó. Lo que cada comando {{printed}} está detrás de su propio interruptor porque son dos preguntas distintas con dos respuestas distintas: lo que escribió es seguro en cualquier pantalla, y lo que volvió puede ser un archivo de configuración con una contraseña.',
        'core-terminal-sessions.empty':
          'Todavía no hay nada. Una sesión empieza al ejecutar el primer comando y permanece abierta hasta que pasa una hora sin otro.',
        'core-terminal-sessions.topic': 'Tema',
        'core-terminal-sessions.no_topic': 'sin tema aún',
        'core-terminal-sessions.started': 'Inicio',
        'core-terminal-sessions.last_command': 'Último comando',
        'core-terminal-sessions.commands': 'Comandos',
        'core-terminal-sessions.who': 'Quién',
        'core-terminal-sessions.name_title': 'Nombrar sesión',
        'core-terminal-sessions.name_label': '¿De qué trató esta sesión?',
        'core-terminal-sessions.name_help':
          'Nombre específicamente la ruta, el servicio o el síntoma.',
        'core-terminal-sessions.named': 'Nombrada.',
      }),
      hi: Object.freeze({
        'core-terminal-sessions.title': 'टर्मिनल सत्र',
        'core-terminal-sessions.sign_in':
          'टर्मिनल सत्र देखने के लिए साइन इन करें।',
        'core-terminal-sessions.grouping_help':
          'साथ चलाए गए कमांड, काम वास्तव में जैसे हुआ वैसे समूहित—एक घंटे का अंतर नया सत्र शुरू करता है, क्योंकि जिसने तीन कमांड चलाए, लंच पर गया और वापस आया वह दो अलग काम कर रहा था।',
        'core-terminal-sessions.topic_help':
          'किसी को विषय दें तो वह ऐसी चीज़ बन जाती है जिस पर वापस आ सकते हैं। टाइमस्टैंप की सूची कोई नहीं पढ़ता; ‘मंगलवार को डिस्क भर रही थी’ ऐसी चीज़ है जिसे छह हफ्ते बाद खोजते हैं।',
        'core-terminal-sessions.output_help':
          'सत्र खोलने पर दिखता है क्या चलाया गया। हर कमांड ने क्या {{printed}} उसके अलग स्विच के पीछे है, क्योंकि वे दो अलग सवाल और दो अलग जवाब हैं: जो आपने टाइप किया वह किसी भी स्क्रीन पर सुरक्षित है, और जो वापस आया वह पासवर्ड वाली कॉन्फ़िग फ़ाइल हो सकती है।',
        'core-terminal-sessions.empty':
          'अभी कुछ नहीं है। पहला कमांड चलने पर सत्र शुरू होता है और एक घंटे तक कोई कमांड न होने पर बंद होता है।',
        'core-terminal-sessions.topic': 'विषय',
        'core-terminal-sessions.no_topic': 'अभी कोई विषय नहीं',
        'core-terminal-sessions.started': 'शुरू हुआ',
        'core-terminal-sessions.last_command': 'अंतिम कमांड',
        'core-terminal-sessions.commands': 'कमांड',
        'core-terminal-sessions.who': 'कौन',
        'core-terminal-sessions.name_title': 'सत्र का नाम दें',
        'core-terminal-sessions.name_label': 'यह सत्र किस बारे में था?',
        'core-terminal-sessions.name_help':
          'पथ, सेवा या लक्षण का नाम विशेष रूप से दें।',
        'core-terminal-sessions.named': 'नाम दिया गया।',
      }),
      ar: Object.freeze({
        'core-terminal-sessions.title': 'جلسات الطرفية',
        'core-terminal-sessions.sign_in': 'سجّل الدخول لرؤية جلسات الطرفية.',
        'core-terminal-sessions.grouping_help':
          'الأوامر التي شُغلت متقاربة، مجمعة بالطريقة التي حدث بها العمل فعلًا — فجوة ساعة تبدأ واحدة جديدة، لأن من شغّل ثلاثة أوامر وذهب إلى الغداء وعاد كان يفعل شيئين مختلفين.',
        'core-terminal-sessions.topic_help':
          'أعط واحدة موضوعًا فتصبح شيئًا يمكنك الرجوع إليه. قائمة الطوابع الزمنية قائمة لا يقرؤها أحد؛ «القرص يمتلئ يوم الثلاثاء» شيء تبحث عنه بعد ستة أسابيع.',
        'core-terminal-sessions.output_help':
          'فتح جلسة يعرض ما شُغّل. ما {{printed}} كل أمر خلف مفتاحه الخاص، لأنهما سؤالان مختلفان بإجابتين مختلفتين: ما كتبته آمن على أي شاشة، وما عاد قد يكون ملف إعداد به كلمة مرور.',
        'core-terminal-sessions.empty':
          'لا شيء بعد. تبدأ الجلسة عند تشغيل أول أمر وتبقى مفتوحة حتى تمر ساعة بلا أمر آخر.',
        'core-terminal-sessions.topic': 'الموضوع',
        'core-terminal-sessions.no_topic': 'لا موضوع بعد',
        'core-terminal-sessions.started': 'بدأت',
        'core-terminal-sessions.last_command': 'آخر أمر',
        'core-terminal-sessions.commands': 'الأوامر',
        'core-terminal-sessions.who': 'من',
        'core-terminal-sessions.name_title': 'تسمية الجلسة',
        'core-terminal-sessions.name_label': 'عن ماذا كانت هذه الجلسة؟',
        'core-terminal-sessions.name_help':
          'سمِّ المسار أو الخدمة أو العَرَض تحديدًا.',
        'core-terminal-sessions.named': 'تمت التسمية.',
      }),
      zh: Object.freeze({
        'core-terminal-sessions.title': '终端会话',
        'core-terminal-sessions.sign_in': '登录后可查看终端会话。',
        'core-terminal-sessions.grouping_help':
          '按实际工作方式将相近时间运行的命令分组——间隔一小时会开始一个新会话，因为运行三条命令、去吃午饭再回来的人是在做两件不同的事。',
        'core-terminal-sessions.topic_help':
          '给一个会话添加主题，它就会成为可以回看的内容。时间戳列表没人会读；“周二磁盘填满”才是六周后会去查找的内容。',
        'core-terminal-sessions.output_help':
          '打开会话会显示运行了什么。每条命令{{printed}}的内容放在单独的开关后，因为这是两个不同的问题，有两个不同的答案：您输入的内容在任何屏幕上都安全，而返回的内容可能是含有密码的配置文件。',
        'core-terminal-sessions.empty':
          '尚无内容。首次运行命令时会开始会话，一个小时没有命令后会话关闭。',
        'core-terminal-sessions.topic': '主题',
        'core-terminal-sessions.no_topic': '尚无主题',
        'core-terminal-sessions.started': '开始时间',
        'core-terminal-sessions.last_command': '最后命令',
        'core-terminal-sessions.commands': '命令',
        'core-terminal-sessions.who': '执行者',
        'core-terminal-sessions.name_title': '命名会话',
        'core-terminal-sessions.name_label': '此会话是关于什么的？',
        'core-terminal-sessions.name_help': '请具体命名路径、服务或症状。',
        'core-terminal-sessions.named': '已命名。',
      }),
    }),
  }),

  text(key, helpers) {
    return helpers.i18n.t(
      `core-terminal-sessions.${key}`,
      {},
      this.manifest.catalogs.en[`core-terminal-sessions.${key}`],
    );
  },

  render(rows, helpers) {
    const { target, note, renderTable, whenSpan, open, i18n } = helpers;
    if (rows?.kind === 'name')
      return i18n.t(`core-terminal-sessions.${rows.key}`, {}, rows.fallback);
    if (!target) return;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (!rows.length) {
      target.replaceChildren(
        note(
          t(
            'core-terminal-sessions.empty',
            {},
            'Nothing yet. A session starts the first time a command is run and stays open until an hour goes by without one.',
          ),
        ),
      );
      return;
    }
    const holder = document.createElement('div');
    holder.id = 'sessionsTable';
    target.replaceChildren(holder);
    renderTable(
      holder.id,
      rows,
      [
        {
          label: t('core-terminal-sessions.topic', {}, 'Topic'),
          className: 'name',
          render: (row) => {
            if (row.topic) return row.topic;
            const said = document.createElement('span');
            said.className = 'muted';
            said.textContent = t(
              'core-terminal-sessions.no_topic',
              {},
              'no topic yet',
            );
            return said;
          },
        },
        {
          label: t('core-terminal-sessions.started', {}, 'Started'),
          render: (row) => whenSpan(row.started_at),
        },
        {
          label: t('core-terminal-sessions.last_command', {}, 'Last command'),
          render: (row) => whenSpan(row.last_at),
        },
        {
          label: t('core-terminal-sessions.commands', {}, 'Commands'),
          className: 'size',
          render: (row) => row.commands,
        },
        {
          label: t('core-terminal-sessions.who', {}, 'Who'),
          render: (row) => row.who || '',
        },
      ],
      open,
      null,
      (row) => row.id,
    );
  },
};
