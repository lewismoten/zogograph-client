window.ZogographCagefsExplainerCard = {
  manifest: Object.freeze({
    id: 'core-cagefs-explainer',
    locales: ['core-cagefs-explainer.'],
    helpers: ['target', 'note', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-cagefs-explainer.absent':
          "Nothing here says CageFS is installed. From inside a cage that is expected: the skeleton's mounts live in the host's namespace and cannot be seen from an account. Run the host collector as root and this fills in — or it confirms the machine genuinely does not use CageFS.",
        'core-cagefs-explainer.title': 'What CageFS is',
        'core-cagefs-explainer.private_view':
          'CageFS gives every account its own private view of the filesystem. Instead of the machine’s real /usr and /bin, an account sees a small curated copy built from a skeleton at /usr/share/cagefs-skeleton — enough to run a website and nothing else.',
        'core-cagefs-explainer.availability':
          'So a program the host has in /usr/sbin is not hidden from you by permissions. It is simply not present in the filesystem you are looking at. When the account says a program is not found, it is telling the truth about a world where that program does not exist — which is why “installed” and “available here” are different questions on this machine, and why this page answers both.',
        'core-cagefs-explainer.update':
          'Adding a program for accounts means listing its path in /etc/cagefs/conf.d and running cagefsctl --force-update as root. That changes what every caged account can run, so this tool reports the exact commands rather than running them for you.',
      }),
      es: Object.freeze({
        'core-cagefs-explainer.absent':
          'Nada aquí indica que CageFS esté instalado. Desde dentro de una jaula es lo esperado: los montajes del esqueleto viven en el espacio de nombres del host y no se pueden ver desde una cuenta. Ejecute el recopilador del host como root y esto se completará, o confirmará que la máquina realmente no usa CageFS.',
        'core-cagefs-explainer.title': 'Qué es CageFS',
        'core-cagefs-explainer.private_view':
          'CageFS da a cada cuenta su propia vista privada del sistema de archivos. En lugar de los /usr y /bin reales de la máquina, una cuenta ve una copia pequeña y seleccionada construida desde un esqueleto en /usr/share/cagefs-skeleton, suficiente para ejecutar un sitio web y nada más.',
        'core-cagefs-explainer.availability':
          'Por tanto, un programa que el host tiene en /usr/sbin no queda oculto por permisos. Simplemente no está presente en el sistema de archivos que está mirando. Cuando la cuenta dice que no se encuentra un programa, dice la verdad sobre un mundo donde ese programa no existe; por eso “instalado” y “disponible aquí” son preguntas distintas en esta máquina.',
        'core-cagefs-explainer.update':
          'Añadir un programa para cuentas implica incluir su ruta en /etc/cagefs/conf.d y ejecutar cagefsctl --force-update como root. Eso cambia lo que puede ejecutar cada cuenta enjaulada, por lo que esta herramienta informa los comandos exactos en lugar de ejecutarlos por usted.',
      }),
      hi: Object.freeze({
        'core-cagefs-explainer.absent':
          'यहाँ कुछ भी यह नहीं कहता कि CageFS स्थापित है। केज के अंदर से यह अपेक्षित है: स्केलेटन के माउंट होस्ट के नेमस्पेस में रहते हैं और खाते से दिखाई नहीं देते। होस्ट कलेक्टर को root के रूप में चलाएँ; तब यह भर जाएगा या पुष्टि करेगा कि मशीन सच में CageFS का उपयोग नहीं करती।',
        'core-cagefs-explainer.title': 'CageFS क्या है',
        'core-cagefs-explainer.private_view':
          'CageFS हर खाते को फाइलसिस्टम का अपना निजी दृश्य देता है। मशीन के वास्तविक /usr और /bin के बजाय खाता /usr/share/cagefs-skeleton के स्केलेटन से बनी छोटी चुनी हुई प्रति देखता है—वेबसाइट चलाने के लिए पर्याप्त और कुछ नहीं।',
        'core-cagefs-explainer.availability':
          'इसलिए होस्ट के /usr/sbin में मौजूद प्रोग्राम आपसे अनुमतियों के कारण छिपा नहीं है। वह बस उस फाइलसिस्टम में मौजूद नहीं है जिसे आप देख रहे हैं। जब खाता कहता है कि प्रोग्राम नहीं मिला, तो वह उस दुनिया के बारे में सच कह रहा है जहाँ वह प्रोग्राम मौजूद नहीं है; इसलिए इस मशीन पर “स्थापित” और “यहाँ उपलब्ध” अलग प्रश्न हैं।',
        'core-cagefs-explainer.update':
          'खातों के लिए प्रोग्राम जोड़ने का अर्थ उसका पथ /etc/cagefs/conf.d में लिखना और root के रूप में cagefsctl --force-update चलाना है। इससे हर केज्ड खाते द्वारा चलाए जा सकने वाले प्रोग्राम बदलते हैं, इसलिए यह उपकरण उन्हें चलाने के बजाय सटीक कमांड बताता है।',
      }),
      ar: Object.freeze({
        'core-cagefs-explainer.absent':
          'لا يوجد هنا ما يشير إلى أن CageFS مُثبّت. من داخل قفص، هذا متوقع: نقاط تركيب الهيكل موجودة في مساحة أسماء المضيف ولا يمكن رؤيتها من حساب. شغّل جامع المضيف كمستخدم root وسيكتمل هذا، أو سيؤكد أن الجهاز لا يستخدم CageFS فعلًا.',
        'core-cagefs-explainer.title': 'ما هو CageFS',
        'core-cagefs-explainer.private_view':
          'يمنح CageFS كل حساب عرضًا خاصًا به لنظام الملفات. بدلًا من /usr و/bin الحقيقيين للجهاز، يرى الحساب نسخة صغيرة منتقاة مبنية من هيكل في /usr/share/cagefs-skeleton، تكفي لتشغيل موقع ويب ولا شيء آخر.',
        'core-cagefs-explainer.availability':
          'لذلك لا يكون البرنامج الذي لدى المضيف في /usr/sbin مخفيًا عنك بسبب الأذونات؛ بل إنه غير موجود ببساطة في نظام الملفات الذي تنظر إليه. عندما يقول الحساب إن برنامجًا غير موجود، فهو يصف بصدق عالمًا لا يوجد فيه ذلك البرنامج، ولهذا فـ«مثبّت» و«متاح هنا» سؤالان مختلفان على هذا الجهاز.',
        'core-cagefs-explainer.update':
          'إضافة برنامج للحسابات تعني إدراج مساره في /etc/cagefs/conf.d وتشغيل cagefsctl --force-update كمستخدم root. يغيّر ذلك ما يمكن لكل حساب محبوس تشغيله، لذا تعرض هذه الأداة الأوامر الدقيقة بدلًا من تشغيلها نيابةً عنك.',
      }),
      zh: Object.freeze({
        'core-cagefs-explainer.absent':
          '这里没有任何内容表明已安装 CageFS。从笼子内部看这是正常的：骨架挂载位于主机命名空间中，账户无法看到。以 root 身份运行主机收集器后这里会显示信息，或者确认这台机器确实没有使用 CageFS。',
        'core-cagefs-explainer.title': '什么是 CageFS',
        'core-cagefs-explainer.private_view':
          'CageFS 为每个账户提供自己的私有文件系统视图。账户看到的不是机器真实的 /usr 和 /bin，而是由 /usr/share/cagefs-skeleton 中的骨架构建的一小份精选副本，足以运行网站而无其他内容。',
        'core-cagefs-explainer.availability':
          '因此，主机在 /usr/sbin 中拥有的程序并不是因为权限而对您隐藏；它根本不存在于您正在查看的文件系统中。当账户说找不到程序时，它是在如实描述一个该程序不存在的世界，这就是为什么“已安装”和“此处可用”在这台机器上是不同的问题。',
        'core-cagefs-explainer.update':
          '为账户添加程序意味着在 /etc/cagefs/conf.d 中列出其路径，并以 root 身份运行 cagefsctl --force-update。这会改变每个受限账户能够运行的内容，因此此工具报告准确命令，而不会替您执行它们。',
      }),
    }),
  }),
  render(data, helpers) {
    const cage = data.mounts?.cagefs;
    const installed = data.capabilities?.cagefs?.installed || cage?.present;
    if (!installed) {
      helpers.target.replaceChildren(
        helpers.note(
          helpers.i18n.t(
            'core-cagefs-explainer.absent',
            {},
            "Nothing here says CageFS is installed. From inside a cage that is expected: the skeleton's mounts live in the host's namespace and cannot be seen from an account. Run the host collector as root and this fills in — or it confirms the machine genuinely does not use CageFS.",
          ),
        ),
      );
      return;
    }
    const box = document.createElement('div');
    box.className = 'explainer';
    const head = document.createElement('h3');
    head.textContent = helpers.i18n.t(
      'core-cagefs-explainer.title',
      {},
      'What CageFS is',
    );
    box.appendChild(head);
    const paragraphs = [
      helpers.i18n.t(
        'core-cagefs-explainer.private_view',
        {},
        'CageFS gives every account its own private view of the filesystem.',
      ),
      helpers.i18n.t(
        'core-cagefs-explainer.availability',
        {},
        'A host program is not necessarily available from this account.',
      ),
      helpers.i18n.t(
        'core-cagefs-explainer.update',
        {},
        'Adding an account program changes what every caged account can run.',
      ),
    ];
    if (data.capabilities?.cagefs?.note)
      paragraphs.push(data.capabilities.cagefs.note);
    for (const text of paragraphs) {
      const paragraph = document.createElement('p');
      paragraph.textContent = text;
      box.appendChild(paragraph);
    }
    helpers.target.replaceChildren(box);
  },
};
