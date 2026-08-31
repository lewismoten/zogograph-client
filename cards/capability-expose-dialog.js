window.ZogographCapabilityExposeDialogCard = {
  manifest: Object.freeze({
    id: 'core-capability-expose-dialog',
    locales: ['core-capability-expose-dialog.'],
    helpers: ['copyButtonLabel', 'queue', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-capability-expose-dialog.intro':
          '{{name}} is installed at {{path}} and is not visible inside this account. Run these as root on the host. They write a CageFS config fragment and rebuild the skeleton — which changes what every caged account can run, so nothing here does it for you.',
        'core-capability-expose-dialog.ini_warning':
          'These files are INI. A bare path without the [section] header makes cagefsctl refuse to run at all until the file is removed.',
        'core-capability-expose-dialog.queue_check':
          'Ask the collector to check afterwards',
        'core-capability-expose-dialog.queue_check_help':
          'Queues a capability sweep so the next host run confirms whether it worked.',
      }),
      es: Object.freeze({
        'core-capability-expose-dialog.intro':
          '{{name}} está instalado en {{path}} y no es visible dentro de esta cuenta. Ejecute esto como root en el host. Escribe un fragmento de configuración de CageFS y reconstruye el esqueleto, lo que cambia lo que puede ejecutar cada cuenta enjaulada, así que aquí no se ejecuta nada por usted.',
        'core-capability-expose-dialog.ini_warning':
          'Estos archivos son INI. Una ruta sin el encabezado [section] hace que cagefsctl se niegue a ejecutarse hasta que se elimine el archivo.',
        'core-capability-expose-dialog.queue_check':
          'Pedir al recopilador que lo compruebe después',
        'core-capability-expose-dialog.queue_check_help':
          'Encola una comprobación de capacidades para que la siguiente ejecución del host confirme si funcionó.',
      }),
      hi: Object.freeze({
        'core-capability-expose-dialog.intro':
          '{{name}} {{path}} पर स्थापित है और इस खाते के अंदर दिखाई नहीं देता। इन्हें होस्ट पर root के रूप में चलाएँ। ये CageFS कॉन्फ़िगरेशन फ्रैगमेंट लिखते और स्केलेटन बनाते हैं—जिससे हर केज्ड खाता क्या चला सकता है बदलता है, इसलिए यहाँ कुछ आपके लिए नहीं चलाया जाता।',
        'core-capability-expose-dialog.ini_warning':
          'ये फ़ाइलें INI हैं। [section] हेडर के बिना केवल पथ होने पर cagefsctl तब तक चलने से मना करता है जब तक फ़ाइल हटा न दी जाए।',
        'core-capability-expose-dialog.queue_check':
          'कलेक्टर से बाद में जाँचने को कहें',
        'core-capability-expose-dialog.queue_check_help':
          'क्षमता जाँच को कतार में रखता है ताकि अगला होस्ट रन पुष्टि करे कि यह काम किया।',
      }),
      ar: Object.freeze({
        'core-capability-expose-dialog.intro':
          '{{name}} مُثبّت في {{path}} ولا يظهر داخل هذا الحساب. شغّل هذه الأوامر كمستخدم root على المضيف. تكتب جزء إعداد CageFS وتعيد بناء الهيكل، ما يغيّر ما يمكن لكل حساب محبوس تشغيله، لذلك لا يُنفّذ هنا شيء نيابةً عنك.',
        'core-capability-expose-dialog.ini_warning':
          'هذه الملفات INI. المسار المجرد بلا ترويسة [section] يجعل cagefsctl يرفض العمل تمامًا حتى تُزال الملف.',
        'core-capability-expose-dialog.queue_check':
          'اطلب من الجامع التحقق لاحقًا',
        'core-capability-expose-dialog.queue_check_help':
          'يضع فحص القدرات في الطابور ليؤكد تشغيل المضيف التالي نجاحه.',
      }),
      zh: Object.freeze({
        'core-capability-expose-dialog.intro':
          '{{name}} 已安装在 {{path}}，但在此账户内不可见。请在主机上以 root 身份运行这些命令。它们会写入 CageFS 配置片段并重建骨架，这会改变每个受限账户可运行的内容，因此这里不会替您执行它们。',
        'core-capability-expose-dialog.ini_warning':
          '这些文件是 INI 文件。没有 [section] 标头的裸路径会使 cagefsctl 完全拒绝运行，直到移除此文件。',
        'core-capability-expose-dialog.queue_check': '让采集器随后检查',
        'core-capability-expose-dialog.queue_check_help':
          '将能力扫描排入队列，以便下次主机运行确认是否生效。',
      }),
    }),
  }),
  render(row, helpers) {
    const frag = document.createDocumentFragment();
    const intro = document.createElement('p');
    intro.className = 'muted small';
    intro.textContent = helpers.i18n.t(
      'core-capability-expose-dialog.intro',
      { name: row.name, path: row.host_path },
      '{{name}} is installed at {{path}} and is not visible inside this account.',
    );
    frag.appendChild(intro);
    const pre = document.createElement('pre');
    pre.className = 'commands';
    pre.textContent = row.expose.commands.join('\n');
    frag.appendChild(pre);
    const buttons = document.createElement('p');
    const copy = document.createElement('button');
    copy.textContent = helpers.copyButtonLabel('copy');
    copy.addEventListener('click', async () => {
      try {
        await navigator.clipboard.writeText(row.expose.commands.join('\n'));
        copy.textContent = helpers.copyButtonLabel('copied');
        setTimeout(() => {
          copy.textContent = helpers.copyButtonLabel('copy');
        }, 1500);
      } catch {
        copy.textContent = helpers.copyButtonLabel('select_to_copy');
        const range = document.createRange();
        range.selectNodeContents(pre);
        const selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);
      }
    });
    buttons.appendChild(copy);
    const queue = document.createElement('button');
    queue.className = 'link';
    queue.style.marginLeft = '10px';
    queue.textContent = helpers.i18n.t(
      'core-capability-expose-dialog.queue_check',
      {},
      'Ask the collector to check afterwards',
    );
    queue.title = helpers.i18n.t(
      'core-capability-expose-dialog.queue_check_help',
      {},
      'Queues a capability sweep so the next host run confirms whether it worked.',
    );
    queue.addEventListener('click', () => helpers.queue(queue, row));
    buttons.appendChild(queue);
    frag.appendChild(buttons);
    const note = document.createElement('p');
    note.className = 'muted small';
    note.textContent = helpers.i18n.t(
      'core-capability-expose-dialog.ini_warning',
      {},
      'These files are INI. A bare path without the [section] header makes cagefsctl refuse to run at all until the file is removed.',
    );
    frag.appendChild(note);
    return frag;
  },
};
