/* Core card package: whether assistant advice may cover host-only commands. */
window.ZogographAiHostCommandVisibilityCard = {
  manifest: Object.freeze({
    id: 'core-ai-host-command-visibility',
    locales: ['core-ai-host-command-visibility.'],
    helpers: ['fieldRow', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-host-command-visibility.label':
          'Let it see what only root can run',
        'core-ai-host-command-visibility.help':
          'The host collector already records which programs exist and which this account can reach. With this on, the assistant can see the difference and may suggest asking your host to run one and send back the output — which is an ordinary support request and often the fastest route to an answer. It is told to suggest reading only, never changes to a shared server. Off by default because it changes the kind of advice you get.',
      }),
      es: Object.freeze({
        'core-ai-host-command-visibility.label':
          'Permitirle ver lo que solo root puede ejecutar',
        'core-ai-host-command-visibility.help':
          'El colector del host ya registra qué programas existen y a cuáles llega esta cuenta. Al activarlo, el asistente puede sugerir pedir al host que ejecute uno y envíe la salida; solo aconseja lecturas.',
      }),
      hi: Object.freeze({
        'core-ai-host-command-visibility.label':
          'इसे वह देखने दें जो केवल root चला सकता है',
        'core-ai-host-command-visibility.help':
          'होस्ट कलेक्टर पहले ही बताता है कि कौन से प्रोग्राम हैं और यह खाता किन तक पहुँच सकता है। इसे चालू करने पर सहायक होस्ट से कोई कमांड चलाकर आउटपुट भेजने को कह सकता है; यह केवल पढ़ने का सुझाव देता है।',
      }),
      ar: Object.freeze({
        'core-ai-host-command-visibility.label': 'دعه يرى ما لا يشغله إلا root',
        'core-ai-host-command-visibility.help':
          'يجمع مضيف النظام البرامج الموجودة وما يصل إليه هذا الحساب. عند تشغيله قد يقترح المساعد أن تطلب من المضيف تشغيل أمر وإرسال الناتج؛ وهو يقترح القراءة فقط.',
      }),
      zh: Object.freeze({
        'core-ai-host-command-visibility.label':
          '让它看到只有 root 能运行的内容',
        'core-ai-host-command-visibility.help':
          '主机收集器已经记录哪些程序存在以及此账户可以访问哪些程序。开启后，助手可建议请主机运行某项命令并返回输出；它只会建议读取操作。',
      }),
    }),
  }),
  render(data, helpers) {
    const input = document.createElement('input');
    input.type = 'checkbox';
    input.id = 'aiHostCommands';
    input.checked = data.host_commands === true;
    return helpers.fieldRow(
      helpers.i18n.t(
        'core-ai-host-command-visibility.label',
        {},
        'Let it see what only root can run',
      ),
      input,
      helpers.i18n.t(
        'core-ai-host-command-visibility.help',
        {},
        'The host collector already records which programs exist and which this account can reach. With this on, the assistant can see the difference and may suggest asking your host to run one and send back the output — which is an ordinary support request and often the fastest route to an answer. It is told to suggest reading only, never changes to a shared server. Off by default because it changes the kind of advice you get.',
      ),
    );
  },
};
