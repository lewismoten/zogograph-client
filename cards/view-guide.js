/* Core card package: the unauthenticated access guide shown above a view. */
window.ZogographViewGuideCard = {
  manifest: Object.freeze({
    id: 'core-view-guide',
    locales: ['core-view-guide.'],
    helpers: ['target', 'heading', 'onSignIn', 'onHelp', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-view-guide.access_page': 'Access and page guide',
        'core-view-guide.sign_in_help':
          'Sign in to see protected measurements and controls. This guide remains available so the page is never an unexplained empty state.',
        'core-view-guide.read_help': 'Read page help',
        'core-view-guide.sign_in': 'Sign in',
        'core-view-guide.vitals_purpose':
          'Shows the current health of this account and how its capacity has changed over time.',
        'core-view-guide.vitals_useful':
          'Use it to decide whether a problem is disk space, inodes, CPU, memory, load, or a trend that needs a closer look.',
        'core-view-guide.storage_purpose':
          'Explains where filesystem space is being used, what changed, and what can safely be investigated next.',
        'core-view-guide.storage_useful':
          'Use it when capacity is shrinking, a scan or log is growing, or a path needs to be traced before any cleanup.',
      }),
      es: Object.freeze({
        'core-view-guide.access_page': 'Guía de acceso y página',
        'core-view-guide.sign_in_help':
          'Inicie sesión para ver mediciones y controles protegidos. Esta guía sigue disponible para que la página nunca sea un estado vacío sin explicación.',
        'core-view-guide.read_help': 'Leer la ayuda de la página',
        'core-view-guide.sign_in': 'Iniciar sesión',
        'core-view-guide.vitals_purpose':
          'Muestra el estado actual de esta cuenta y cómo ha cambiado su capacidad con el tiempo.',
        'core-view-guide.vitals_useful':
          'Úsela para decidir si un problema es de espacio en disco, inodos, CPU, memoria, carga o una tendencia que necesita revisión.',
        'core-view-guide.storage_purpose':
          'Explica dónde se utiliza el espacio del sistema de archivos, qué cambió y qué se puede investigar con seguridad a continuación.',
        'core-view-guide.storage_useful':
          'Úsela cuando la capacidad se reduzca, un análisis o registro crezca, o sea necesario rastrear una ruta antes de limpiar nada.',
      }),
      hi: Object.freeze({
        'core-view-guide.access_page': 'पहुँच और पृष्ठ मार्गदर्शिका',
        'core-view-guide.sign_in_help':
          'सुरक्षित माप और नियंत्रण देखने के लिए साइन इन करें। यह गाइड उपलब्ध रहता है ताकि पृष्ठ कभी बिना व्याख्या की खाली स्थिति न हो।',
        'core-view-guide.read_help': 'पृष्ठ सहायता पढ़ें',
        'core-view-guide.sign_in': 'साइन इन करें',
        'core-view-guide.vitals_purpose':
          'इस खाते की वर्तमान स्थिति और समय के साथ इसकी क्षमता कैसे बदली है, यह दिखाता है।',
        'core-view-guide.vitals_useful':
          'इससे तय करें कि समस्या डिस्क स्थान, इनोड, CPU, मेमोरी, लोड या ऐसी प्रवृत्ति की है जिसे करीब से देखने की जरूरत है।',
        'core-view-guide.storage_purpose':
          'बताता है कि फ़ाइल सिस्टम स्थान कहाँ उपयोग हो रहा है, क्या बदला है और आगे क्या सुरक्षित रूप से जाँचा जा सकता है।',
        'core-view-guide.storage_useful':
          'इसे तब उपयोग करें जब क्षमता घट रही हो, कोई स्कैन या लॉग बढ़ रहा हो, या सफाई से पहले किसी पथ का पता लगाना हो।',
      }),
      ar: Object.freeze({
        'core-view-guide.access_page': 'دليل الوصول والصفحة',
        'core-view-guide.sign_in_help':
          'سجّل الدخول لرؤية القياسات والضوابط المحمية. يظل هذا الدليل متاحًا كي لا تكون الصفحة حالة فارغة بلا تفسير.',
        'core-view-guide.read_help': 'اقرأ مساعدة الصفحة',
        'core-view-guide.sign_in': 'تسجيل الدخول',
        'core-view-guide.vitals_purpose':
          'يعرض الحالة الحالية لهذا الحساب وكيف تغيّرت سعته بمرور الوقت.',
        'core-view-guide.vitals_useful':
          'استخدمه لتحديد ما إذا كانت المشكلة مساحة القرص أو inodes أو CPU أو الذاكرة أو الحمل أو اتجاهًا يحتاج إلى فحص أدق.',
        'core-view-guide.storage_purpose':
          'يشرح أين تُستخدم مساحة نظام الملفات، وما الذي تغيّر، وما الذي يمكن فحصه بأمان بعد ذلك.',
        'core-view-guide.storage_useful':
          'استخدمه عندما تتقلص السعة أو يكبر فحص أو سجل أو يلزم تتبع مسار قبل أي تنظيف.',
      }),
      zh: Object.freeze({
        'core-view-guide.access_page': '访问和页面指南',
        'core-view-guide.sign_in_help':
          '请登录以查看受保护的测量值和控件。此指南仍可用，因此页面不会成为毫无解释的空白状态。',
        'core-view-guide.read_help': '阅读页面帮助',
        'core-view-guide.sign_in': '登录',
        'core-view-guide.vitals_purpose':
          '显示此帐户的当前健康状况及其容量如何随时间变化。',
        'core-view-guide.vitals_useful':
          '用它判断问题是否出在磁盘空间、inode、CPU、内存、负载，或某个需要进一步查看的趋势。',
        'core-view-guide.storage_purpose':
          '说明文件系统空间的使用位置、发生了什么变化，以及下一步可安全调查的内容。',
        'core-view-guide.storage_useful':
          '当容量缩减、扫描或日志增长，或需要在清理前追踪路径时使用它。',
      }),
    }),
  }),
  render(profile, helpers) {
    const guide = document.createElement('section');
    guide.className = 'view-guide';
    guide.setAttribute(
      'aria-label',
      helpers.i18n.t(
        'core-view-guide.access_page',
        {},
        'Access and page guide',
      ),
    );

    const heading = document.createElement('h2');
    heading.textContent = helpers.heading;
    const purpose = document.createElement('p');
    purpose.textContent = profile.purpose;
    const access = document.createElement('p');
    access.className = 'view-guide-auth';
    access.textContent = helpers.i18n.t(
      'core-view-guide.sign_in_help',
      {},
      'Sign in to see protected measurements and controls. This guide remains available so the page is never an unexplained empty state.',
    );
    const actions = document.createElement('div');
    actions.className = 'view-guide-actions';
    const signIn = document.createElement('button');
    signIn.type = 'button';
    signIn.className = 'primary';
    signIn.textContent = helpers.i18n.t(
      'core-view-guide.sign_in',
      {},
      'Sign in',
    );
    signIn.addEventListener('click', helpers.onSignIn);
    const help = document.createElement('button');
    help.type = 'button';
    help.className = 'link';
    help.textContent = helpers.i18n.t(
      'core-view-guide.read_help',
      {},
      'Read page help',
    );
    help.addEventListener('click', helpers.onHelp);
    actions.append(signIn, help);
    guide.append(heading, purpose, access, actions);
    helpers.target.prepend(guide);
  },
};
