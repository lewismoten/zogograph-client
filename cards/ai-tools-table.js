/* Core card package: AI tool table with availability and permission controls. */
window.ZogographAiToolsTableCard = {
  manifest: Object.freeze({
    id: 'core-ai-tools-table',
    locales: ['core-ai-tools-table.'],
    helpers: [
      'aiToolText',
      'renderCard',
      'availabilityCard',
      'permissionCard',
      'descriptionCard',
      'showListDialog',
      'heading',
      'help',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-tools-table.tool': 'Tool',
        'core-ai-tools-table.runs': 'Runs',
        'core-ai-tools-table.may_use': 'May use it',
        'core-ai-tools-table.ask_first': 'Ask me first',
        'core-ai-tools-table.where_server': 'here',
        'core-ai-tools-table.where_browser': 'in your browser',
        'core-ai-tools-table.where_acts': 'changes something',
        'core-ai-tools-table.where_server_title':
          'Answered from the databases on this server. Read-only.',
        'core-ai-tools-table.where_browser_title':
          'Only your browser can do it — moving you to a page, or capturing what you can see.',
        'core-ai-tools-table.where_acts_title':
          'This one acts rather than reads.',
        'core-ai-tools-table.title': 'What it may do',
        'core-ai-tools-table.help':
          'A tool switched off is not described to the model at all, rather than being described and then refused — one told about a tool it cannot use keeps trying it and explains its absence to you as a fault. Anything that asks permission asks every time, and a refusal is reported back to the model so that it works around it rather than retrying.',
      }),
      es: Object.freeze({
        'core-ai-tools-table.tool': 'Herramienta',
        'core-ai-tools-table.runs': 'Se ejecuta',
        'core-ai-tools-table.may_use': 'Puede usarla',
        'core-ai-tools-table.ask_first': 'Preguntarme primero',
        'core-ai-tools-table.where_server': 'aquí',
        'core-ai-tools-table.where_browser': 'en tu navegador',
        'core-ai-tools-table.where_acts': 'cambia algo',
        'core-ai-tools-table.where_server_title':
          'Se responde desde las bases de datos de este servidor. Solo lectura.',
        'core-ai-tools-table.where_browser_title':
          'Solo tu navegador puede hacerlo: llevarte a una página o capturar lo que ves.',
        'core-ai-tools-table.where_acts_title':
          'Esta herramienta actúa en vez de limitarse a leer.',
        'core-ai-tools-table.title': 'Qué puede hacer',
        'core-ai-tools-table.help':
          'Una herramienta desactivada no se describe al modelo en absoluto, en lugar de describirla y rechazarla: un modelo al que se le cuenta una herramienta que no puede usar sigue intentándolo y le explica su ausencia como un fallo. Cualquier cosa que pide permiso lo pide siempre, y una negativa se comunica al modelo para que la resuelva en lugar de reintentarlo.',
      }),
      hi: Object.freeze({
        'core-ai-tools-table.tool': 'टूल',
        'core-ai-tools-table.runs': 'चलता है',
        'core-ai-tools-table.may_use': 'इसे इस्तेमाल कर सकता है',
        'core-ai-tools-table.ask_first': 'पहले मुझसे पूछें',
        'core-ai-tools-table.where_server': 'यहीं',
        'core-ai-tools-table.where_browser': 'आपके ब्राउज़र में',
        'core-ai-tools-table.where_acts': 'कुछ बदलता है',
        'core-ai-tools-table.where_server_title':
          'इस सर्वर के डेटाबेस से उत्तर दिया जाता है। केवल पढ़ने के लिए।',
        'core-ai-tools-table.where_browser_title':
          'केवल आपका ब्राउज़र ही इसे कर सकता है — आपको किसी पेज पर ले जाना या जो आप देख सकते हैं उसे कैप्चर करना।',
        'core-ai-tools-table.where_acts_title':
          'यह पढ़ने के बजाय कार्य करता है।',
        'core-ai-tools-table.title': 'यह क्या कर सकता है',
        'core-ai-tools-table.help':
          'बंद किया टूल मॉडल को बताया ही नहीं जाता, उसे बताकर अस्वीकार करने के बजाय—जिसे ऐसे टूल के बारे में बताया जाए जिसका उपयोग वह नहीं कर सकता, वह उसे आज़माता रहता है और आपको उसकी अनुपस्थिति को दोष बताता है। जो अनुमति माँगता है वह हर बार माँगता है और अस्वीकृति मॉडल को वापस बताई जाती है ताकि वह फिर कोशिश करने के बजाय उसके आसपास काम करे।',
      }),
      ar: Object.freeze({
        'core-ai-tools-table.tool': 'الأداة',
        'core-ai-tools-table.runs': 'تعمل في',
        'core-ai-tools-table.may_use': 'يمكنه استخدامها',
        'core-ai-tools-table.ask_first': 'اسألني أولًا',
        'core-ai-tools-table.where_server': 'هنا',
        'core-ai-tools-table.where_browser': 'في متصفحك',
        'core-ai-tools-table.where_acts': 'يغيّر شيئًا',
        'core-ai-tools-table.where_server_title':
          'تأتي الإجابة من قواعد بيانات هذا الخادم. للقراءة فقط.',
        'core-ai-tools-table.where_browser_title':
          'لا يستطيع ذلك إلا متصفحك — بنقلك إلى صفحة أو بالتقاط ما يمكنك رؤيته.',
        'core-ai-tools-table.where_acts_title':
          'تعمل هذه الأداة بدلًا من القراءة.',
        'core-ai-tools-table.title': 'ما قد يفعله',
        'core-ai-tools-table.help':
          'لا توصف الأداة المعطلة للنموذج إطلاقًا بدل وصفها ثم رفضها — فالنموذج الذي يُخبر عن أداة لا يستطيع استخدامها يستمر في تجربتها ويفسر غيابها لك على أنه عطل. كل ما يطلب إذنًا يطلبه في كل مرة، ويُبلّغ الرفض إلى النموذج كي يعمل حوله بدل إعادة المحاولة.',
      }),
      zh: Object.freeze({
        'core-ai-tools-table.tool': '工具',
        'core-ai-tools-table.runs': '运行位置',
        'core-ai-tools-table.may_use': '可以使用',
        'core-ai-tools-table.ask_first': '先询问我',
        'core-ai-tools-table.where_server': '此处',
        'core-ai-tools-table.where_browser': '在您的浏览器中',
        'core-ai-tools-table.where_acts': '会更改内容',
        'core-ai-tools-table.where_server_title':
          '从此服务器上的数据库回答。只读。',
        'core-ai-tools-table.where_browser_title':
          '只有您的浏览器能完成它：跳转到页面或捕捉您可以看到的内容。',
        'core-ai-tools-table.where_acts_title':
          '此工具执行操作，而不只是读取。',
        'core-ai-tools-table.title': '它可以做什么',
        'core-ai-tools-table.help':
          '已关闭的工具完全不会向模型描述，而不是描述后再拒绝——被告知某个无法使用的工具的模型会不断尝试它，并把其不可用向您解释为故障。凡是需要权限的操作每次都会请求权限，拒绝会反馈给模型，让它绕开而非重试。',
      }),
    }),
  }),
  render(input, helpers) {
    const {
      aiToolText,
      renderCard,
      availabilityCard,
      permissionCard,
      descriptionCard,
      showListDialog,
      heading,
      help,
      i18n,
    } = helpers;
    const t = (key, vars, fallback) => i18n.t(key, vars, fallback);
    if (heading)
      heading.textContent = t(
        'core-ai-tools-table.title',
        {},
        'What it may do',
      );
    if (help)
      help.textContent = t(
        'core-ai-tools-table.help',
        {},
        'A tool switched off is not described to the model at all, rather than being described and then refused — one told about a tool it cannot use keeps trying it and explains its absence to you as a fault. Anything that asks permission asks every time, and a refusal is reported back to the model so that it works around it rather than retrying.',
      );
    const child = (card, id, data, extra = {}) =>
      renderCard(card, id, data, { i18n, ...extra });
    const table = document.createElement('table');
    table.className = 'retention';
    const head = table.createTHead().insertRow();
    for (const [key, fallback] of [
      ['tool', 'Tool'],
      ['runs', 'Runs'],
      ['may_use', 'May use it'],
      ['ask_first', 'Ask me first'],
      ['', ''],
    ]) {
      const th = document.createElement('th');
      th.textContent = key ? t(`core-ai-tools-table.${key}`, {}, fallback) : '';
      head.appendChild(th);
    }
    const locations = {
      server: [
        'where_server',
        'here',
        'where_server_title',
        'Answered from the databases on this server. Read-only.',
      ],
      browser: [
        'where_browser',
        'in your browser',
        'where_browser_title',
        'Only your browser can do it — moving you to a page, or capturing what you can see.',
      ],
      ask: [
        'where_acts',
        'changes something',
        'where_acts_title',
        'This one acts rather than reads.',
      ],
    };
    const tbody = table.createTBody();
    for (const tool of input.tools) {
      const row = tbody.insertRow();
      row.hidden = tool.group !== input.selected;
      const what = row.insertCell();
      what.className = 'name';
      what.appendChild(
        document.createTextNode(
          aiToolText(tool, 'summary', tool.summary).replace(/\.$/, ''),
        ),
      );
      const name = document.createElement('div');
      name.className = 'muted small';
      name.textContent = tool.name;
      what.appendChild(name);
      const where = row.insertCell();
      where.className = 'muted small';
      const location = locations[tool.where];
      where.textContent = location
        ? t(`core-ai-tools-table.${location[0]}`, {}, location[1])
        : tool.where;
      where.title = location
        ? t(`core-ai-tools-table.${location[2]}`, {}, location[3])
        : '';
      const on = row.insertCell();
      const enabled = document.createElement('input');
      enabled.type = 'checkbox';
      enabled.className = 'tool-on';
      enabled.dataset.tool = tool.name;
      enabled.checked = tool.enabled;
      enabled.disabled = !input.writable;
      on.appendChild(enabled);
      if (tool.enabled && tool.offered === false) {
        on.appendChild(
          child(availabilityCard, 'core-ai-tool-availability', {
            reason: tool.not_offered_because ?? '',
          }),
        );
      }
      const gate = row.insertCell();
      gate.appendChild(
        child(permissionCard, 'core-ai-tool-permission', {
          tool,
          writable: input.writable,
        }),
      );
      const detail = row.insertCell();
      detail.appendChild(
        child(
          descriptionCard,
          'core-ai-tool-description',
          { tool },
          { showListDialog },
        ),
      );
    }
    return table;
  },
};
