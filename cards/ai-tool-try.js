/* Core card package: an operator-facing form for trying one AI tool. */
window.ZogographAiToolTryCard = {
  manifest: Object.freeze({
    id: 'core-ai-tool-try',
    locales: ['core-ai-tool-try.'],
    helpers: ['body', 'fieldRow', 'note', 'tryFields', 'runAiTry', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-tool-try.empty': 'There are no tools.',
        'core-ai-tool-try.cannot_run': ' (cannot be run here)',
        'core-ai-tool-try.tool': 'Tool',
        'core-ai-tool-try.catalogue_count':
          '{{count, number}} in the catalogue.',
        'core-ai-tool-try.run': 'Run it',
        'core-ai-tool-try.run_costs': 'Run it (this costs)',
        'core-ai-tool-try.picture_warning':
          'This one really generates a picture, which really costs whatever one costs and counts against the hourly limit. It is not a dry run.',
        'core-ai-tool-try.browser_warning':
          'This one does its work in this window, so it runs here for real — it will draw, or change the range, or ask for a screenshot, exactly as it would mid-conversation.',
        'core-ai-tool-try.browser_permission':
          ' It asks your permission first, and so does this.',
        'core-ai-tool-try.unknown_tool':
          'This page does not know how to try {{tool}}.',
        'core-ai-tool-try.browser_shown': 'What you would have seen',
        'core-ai-tool-try.arguments': 'What it was given',
        'core-ai-tool-try.browser_result': 'What the model gets back',
        'core-ai-tool-try.browser_help':
          'This ran in this window, which is where it runs in a conversation too. The only difference is that the result is shown here instead of being handed back to the model.',
        'core-ai-tool-try.browser_timing': '{{ms}} ms, in this window',
        'core-ai-tool-try.refused':
          'Refused before it ran: {{reason}}\n\n{{note}}',
        'core-ai-tool-try.server_timing': '{{ms}} ms, {{size}} back',
        'core-ai-tool-try.generated_picture': 'Generated picture',
        'core-ai-tool-try.generated_picture_alt': 'A generated picture',
        'core-ai-tool-try.picture_note':
          'Style: {{style}}. This is the image the tool just made.',
        'core-ai-tool-try.not_reported': 'not reported',
        'core-ai-tool-try.result': 'What came back',
        'core-ai-tool-try.result_help':
          'This is exactly what the model would receive, in the same shape. If an answer looked wrong, this is what it was based on. The line numbers are drawn rather than written, so copying this gives you the JSON and nothing else.',
      }),
      es: Object.freeze({
        'core-ai-tool-try.empty': 'No hay herramientas.',
        'core-ai-tool-try.cannot_run': ' (no se puede ejecutar aquí)',
        'core-ai-tool-try.tool': 'Herramienta',
        'core-ai-tool-try.catalogue_count': '{{count, number}} en el catálogo.',
        'core-ai-tool-try.run': 'Ejecutar',
        'core-ai-tool-try.run_costs': 'Ejecutar (cuesta dinero)',
        'core-ai-tool-try.picture_warning':
          'Esta herramienta genera una imagen real y cuenta contra el límite por hora.',
        'core-ai-tool-try.browser_warning':
          'Esta herramienta actúa realmente en esta ventana, igual que en una conversación.',
        'core-ai-tool-try.browser_permission':
          ' También pide su permiso primero.',
        'core-ai-tool-try.unknown_tool':
          'Esta página no sabe cómo probar {{tool}}.',
        'core-ai-tool-try.browser_shown': 'Lo que habría visto',
        'core-ai-tool-try.arguments': 'Lo que se le dio',
        'core-ai-tool-try.browser_result': 'Lo que recibe el modelo',
        'core-ai-tool-try.browser_help':
          'Esto se ejecutó en esta ventana, que es donde también se ejecuta en una conversación. La única diferencia es que el resultado se muestra aquí en vez de entregarse al modelo.',
        'core-ai-tool-try.browser_timing': '{{ms}} ms, en esta ventana',
        'core-ai-tool-try.refused':
          'Se rechazó antes de ejecutarse: {{reason}}\n\n{{note}}',
        'core-ai-tool-try.server_timing': '{{ms}} ms, {{size}} de vuelta',
        'core-ai-tool-try.generated_picture': 'Imagen generada',
        'core-ai-tool-try.generated_picture_alt': 'Una imagen generada',
        'core-ai-tool-try.picture_note':
          'Estilo: {{style}}. Esta es la imagen que acaba de crear la herramienta.',
        'core-ai-tool-try.not_reported': 'no informado',
        'core-ai-tool-try.result': 'Lo que volvió',
        'core-ai-tool-try.result_help':
          'Esto es exactamente lo que recibiría el modelo, con la misma forma. Si una respuesta parecía incorrecta, esto es en lo que se basó. Los números de línea se dibujan en lugar de escribirse, por lo que al copiar obtiene el JSON y nada más.',
      }),
      hi: Object.freeze({
        'core-ai-tool-try.empty': 'कोई टूल नहीं हैं।',
        'core-ai-tool-try.cannot_run': ' (यहाँ चल नहीं सकता)',
        'core-ai-tool-try.tool': 'टूल',
        'core-ai-tool-try.catalogue_count': 'कैटलॉग में {{count, number}}।',
        'core-ai-tool-try.run': 'चलाएँ',
        'core-ai-tool-try.run_costs': 'चलाएँ (इसकी लागत है)',
        'core-ai-tool-try.picture_warning':
          'यह सच में चित्र बनाता है और प्रति घंटे की सीमा में गिना जाता है।',
        'core-ai-tool-try.browser_warning':
          'यह टूल इसी विंडो में वास्तविक रूप से काम करता है।',
        'core-ai-tool-try.browser_permission': ' यह पहले अनुमति भी माँगता है।',
        'core-ai-tool-try.unknown_tool':
          'यह पृष्ठ नहीं जानता कि {{tool}} को कैसे आज़माया जाए।',
        'core-ai-tool-try.browser_shown': 'जो आप देखते',
        'core-ai-tool-try.arguments': 'जिसे इसे दिया गया',
        'core-ai-tool-try.browser_result': 'जो मॉडल को वापस मिलता है',
        'core-ai-tool-try.browser_help':
          'यह इसी विंडो में चला, जहाँ यह बातचीत में भी चलता है। अंतर केवल इतना है कि परिणाम यहाँ दिखता है, मॉडल को वापस नहीं दिया जाता।',
        'core-ai-tool-try.browser_timing': '{{ms}} ms, इस विंडो में',
        'core-ai-tool-try.refused':
          'चलने से पहले अस्वीकार किया गया: {{reason}}\n\n{{note}}',
        'core-ai-tool-try.server_timing': '{{ms}} ms, {{size}} वापस',
        'core-ai-tool-try.generated_picture': 'बनाया गया चित्र',
        'core-ai-tool-try.generated_picture_alt': 'बनाया गया चित्र',
        'core-ai-tool-try.picture_note':
          'शैली: {{style}}। यह वह चित्र है जो टूल ने अभी बनाया है।',
        'core-ai-tool-try.not_reported': 'रिपोर्ट नहीं किया गया',
        'core-ai-tool-try.result': 'जो वापस आया',
        'core-ai-tool-try.result_help':
          'यह ठीक वही है जो मॉडल को उसी रूप में मिलेगा। यदि कोई उत्तर गलत लगा, तो यह उसका आधार है। पंक्ति संख्याएँ लिखी नहीं बल्कि बनाई जाती हैं, इसलिए इसे कॉपी करने पर केवल JSON मिलता है।',
      }),
      ar: Object.freeze({
        'core-ai-tool-try.empty': 'لا توجد أدوات.',
        'core-ai-tool-try.cannot_run': ' (لا يمكن تشغيلها هنا)',
        'core-ai-tool-try.tool': 'الأداة',
        'core-ai-tool-try.catalogue_count': '{{count, number}} في الفهرس.',
        'core-ai-tool-try.run': 'شغّلها',
        'core-ai-tool-try.run_costs': 'شغّلها (تكلّف مالًا)',
        'core-ai-tool-try.picture_warning':
          'هذه الأداة تنشئ صورة فعلًا وتُحتسب ضمن الحد لكل ساعة.',
        'core-ai-tool-try.browser_warning':
          'تعمل هذه الأداة فعليًا في هذه النافذة.',
        'core-ai-tool-try.browser_permission': ' وتطلب إذنك أولًا.',
        'core-ai-tool-try.unknown_tool':
          'لا تعرف هذه الصفحة كيفية تجربة {{tool}}.',
        'core-ai-tool-try.browser_shown': 'ما كنت ستراه',
        'core-ai-tool-try.arguments': 'ما أُعطي له',
        'core-ai-tool-try.browser_result': 'ما يعود إلى النموذج',
        'core-ai-tool-try.browser_help':
          'شُغّل هذا في هذه النافذة، وهي المكان الذي يُشغّل فيه في المحادثة أيضًا. الفرق الوحيد هو أن النتيجة تُعرض هنا بدل أن تُسلّم إلى النموذج.',
        'core-ai-tool-try.browser_timing': '{{ms}} مللي ثانية، في هذه النافذة',
        'core-ai-tool-try.refused': 'رُفض قبل أن يعمل: {{reason}}\n\n{{note}}',
        'core-ai-tool-try.server_timing': '{{ms}} مللي ثانية، {{size}} عادت',
        'core-ai-tool-try.generated_picture': 'صورة مولدة',
        'core-ai-tool-try.generated_picture_alt': 'صورة مُنشأة',
        'core-ai-tool-try.picture_note':
          'الأسلوب: {{style}}. هذه هي الصورة التي أنشأتها الأداة للتو.',
        'core-ai-tool-try.not_reported': 'لم يُبلّغ عنه',
        'core-ai-tool-try.result': 'ما عاد',
        'core-ai-tool-try.result_help':
          'هذا بالضبط ما سيتلقاه النموذج، بالشكل نفسه. إذا بدا جواب خاطئًا، فهذا ما استند إليه. تُرسم أرقام السطور ولا تُكتب، لذلك يمنحك النسخ JSON فقط ولا شيء غيره.',
      }),
      zh: Object.freeze({
        'core-ai-tool-try.empty': '没有工具。',
        'core-ai-tool-try.cannot_run': '（无法在这里运行）',
        'core-ai-tool-try.tool': '工具',
        'core-ai-tool-try.catalogue_count': '目录中有 {{count, number}} 个。',
        'core-ai-tool-try.run': '运行',
        'core-ai-tool-try.run_costs': '运行（会产生费用）',
        'core-ai-tool-try.picture_warning':
          '这会真正生成图像，并计入每小时限额。',
        'core-ai-tool-try.browser_warning': '此工具会在当前窗口中实际运行。',
        'core-ai-tool-try.browser_permission': ' 它也会先请求您的许可。',
        'core-ai-tool-try.unknown_tool': '此页面不知道如何试用 {{tool}}。',
        'core-ai-tool-try.browser_shown': '您本会看到的内容',
        'core-ai-tool-try.arguments': '提供给它的内容',
        'core-ai-tool-try.browser_result': '模型收到的返回内容',
        'core-ai-tool-try.browser_help':
          '这在此窗口中运行，而它在对话中也是在这里运行。唯一的区别是结果显示在这里，而不是交给模型。',
        'core-ai-tool-try.browser_timing': '{{ms}} 毫秒，在此窗口中',
        'core-ai-tool-try.refused': '运行前被拒绝：{{reason}}\n\n{{note}}',
        'core-ai-tool-try.server_timing': '{{ms}} 毫秒，返回 {{size}}',
        'core-ai-tool-try.generated_picture': '生成的图片',
        'core-ai-tool-try.generated_picture_alt': '一张生成的图片',
        'core-ai-tool-try.picture_note':
          '样式：{{style}}。这是该工具刚刚生成的图片。',
        'core-ai-tool-try.not_reported': '未报告',
        'core-ai-tool-try.result': '返回的内容',
        'core-ai-tool-try.result_help':
          '这正是模型会以相同形状收到的内容。如果答案看起来有误，这就是其依据。行号是绘制的而不是写入的，因此复制时只会得到 JSON。',
      }),
    }),
  }),
  render(input, helpers) {
    const { body, fieldRow, note, tryFields, runAiTry, i18n } = helpers;
    const t = (key, vars, fallback) => i18n.t(key, vars, fallback);
    if (!input.tools.length) {
      body.replaceChildren(
        note(t('core-ai-tool-try.empty', {}, 'There are no tools.')),
      );
      return;
    }
    const pick = document.createElement('select');
    pick.id = 'aiTryTool';
    const byGroup = new Map();
    for (const tool of input.tools) {
      if (!byGroup.has(tool.group)) byGroup.set(tool.group, []);
      byGroup.get(tool.group).push(tool);
    }
    for (const [group, list] of byGroup) {
      const holder = document.createElement('optgroup');
      holder.label = group;
      for (const tool of list) {
        const option = document.createElement('option');
        option.value = tool.name;
        option.textContent =
          tool.name +
          (tool.why_not
            ? t('core-ai-tool-try.cannot_run', {}, ' (cannot be run here)')
            : '');
        option.selected = input.state.tool === tool.name;
        holder.appendChild(option);
      }
      pick.appendChild(holder);
    }
    const what = document.createElement('p');
    what.className = 'muted small';
    what.id = 'aiTryWhat';
    const fields = document.createElement('div');
    fields.className = 'settings';
    fields.id = 'aiTryFields';
    const actions = document.createElement('div');
    actions.className = 'inspect-actions';
    const go = document.createElement('button');
    go.type = 'button';
    go.id = 'aiTryRun';
    const said = document.createElement('span');
    said.className = 'muted small';
    said.id = 'aiTrySaid';
    actions.append(go, said);
    const out = document.createElement('div');
    out.id = 'aiTryResult';
    if (input.state.shown) out.replaceChildren(...input.state.shown);
    body.replaceChildren(
      fieldRow(
        t('core-ai-tool-try.tool', {}, 'Tool'),
        pick,
        t(
          'core-ai-tool-try.catalogue_count',
          { count: input.tools.length },
          '{{count, number}} in the catalogue.',
        ),
      ),
      what,
      fields,
      actions,
      out,
    );
    const sync = () => {
      const tool = input.tools.find((item) => item.name === pick.value);
      input.state.tool = pick.value;
      what.replaceChildren();
      if (tool) {
        const summary = document.createElement('div');
        summary.textContent = tool.description.split('\n\n')[0];
        what.appendChild(summary);
        if (tool.why_not) what.appendChild(note(tool.why_not));
        else if (tool.group === 'pictures')
          what.appendChild(
            note(
              t(
                'core-ai-tool-try.picture_warning',
                {},
                'This one really generates a picture, which really costs whatever one costs and counts against the hourly limit. It is not a dry run.',
              ),
            ),
          );
        else if (tool.where === 'browser')
          what.appendChild(
            note(
              t(
                'core-ai-tool-try.browser_warning',
                {},
                'This one does its work in this window, so it runs here for real — it will draw, or change the range, or ask for a screenshot, exactly as it would mid-conversation.',
              ) +
                (tool.permission
                  ? t(
                      'core-ai-tool-try.browser_permission',
                      {},
                      ' It asks your permission first, and so does this.',
                    )
                  : ''),
            ),
          );
      }
      fields.replaceChildren(...tryFields(tool));
      go.disabled = Boolean(tool?.why_not);
      go.textContent =
        tool?.group === 'pictures'
          ? t('core-ai-tool-try.run_costs', {}, 'Run it (this costs)')
          : t('core-ai-tool-try.run', {}, 'Run it');
    };
    pick.addEventListener('change', sync);
    go.addEventListener('click', () =>
      runAiTry(input.tools.find((tool) => tool.name === pick.value)),
    );
    sync();
  },
};
