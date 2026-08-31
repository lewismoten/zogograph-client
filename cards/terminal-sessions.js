/* Core card package: saved terminal sessions and their opening table. */
window.ZogographTerminalSessionsCard = {
  manifest: Object.freeze({
    id: 'core-terminal-sessions',
    locales: ['core-terminal-sessions.'],
    helpers: ['target', 'note', 'renderTable', 'whenSpan', 'open', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-terminal-sessions.title': 'Terminal sessions',
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
