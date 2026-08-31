/* Core card package: host-wide memory ownership by account. */
window.ZogographHostMemoryUsersCard = {
  manifest: Object.freeze({
    id: 'core-host-memory-users',
    locales: ['core-host-memory-users.'],
    helpers: [
      'target',
      'renderTable',
      'userCell',
      'rankedCell',
      'humanBytes',
      'hostPss',
      'topTwo',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-host-memory-users.title': 'Memory by user',
        'core-host-memory-users.help':
          "The column CageFS hides. {{pss}} counts a process's private memory {{full}} and divides each shared page among the processes using it — so one copy of libc shared by a hundred workers contributes a hundredth to each, while everything that process alone holds counts whole. That makes it the effective physical memory attributable to a process, and the one figure you can add up. {{resident}} counts every shared page once per process, so it always totals more than the machine has. The {{kernel_shared}} card above measures something else again — tmpfs and System V segments — and is not a subtotal of this column: a tmpfs page some process maps is counted in both, and one that nothing maps is counted only there.",
        'core-host-memory-users.user': 'User',
        'core-host-memory-users.processes': 'Processes',
        'core-host-memory-users.threads': 'Threads',
        'core-host-memory-users.resident': 'Resident',
        'core-host-memory-users.proportional_pss': 'Proportional (PSS)',
        'core-host-memory-users.swapped': 'Swapped',
      }),
      es: Object.freeze({
        'core-host-memory-users.title': 'Memoria por usuario',
        'core-host-memory-users.help':
          'La columna que CageFS oculta. {{pss}} cuenta la memoria privada de un proceso {{full}} y divide cada página compartida entre los procesos que la usan: una copia de libc compartida por cien trabajadores aporta una centésima a cada uno, mientras que todo lo que solo posee ese proceso cuenta completo. Así representa la memoria física efectiva atribuible a un proceso, y la única cifra que se puede sumar. {{resident}} cuenta cada página compartida una vez por proceso, por lo que siempre suma más de lo que tiene la máquina. La tarjeta de {{kernel_shared}} de arriba mide otra cosa — segmentos tmpfs y System V — y no es un subtotal de esta columna: una página tmpfs que algún proceso mapea cuenta en ambas, y una que nadie mapea cuenta solo allí.',
        'core-host-memory-users.user': 'Usuario',
        'core-host-memory-users.processes': 'Procesos',
        'core-host-memory-users.threads': 'Hilos',
        'core-host-memory-users.resident': 'Residente',
        'core-host-memory-users.proportional_pss': 'Proporcional (PSS)',
        'core-host-memory-users.swapped': 'En intercambio',
      }),
      hi: Object.freeze({
        'core-host-memory-users.title': 'उपयोगकर्ता के अनुसार मेमोरी',
        'core-host-memory-users.help':
          'वह कॉलम जिसे CageFS छिपाता है। {{pss}} किसी प्रक्रिया की निजी मेमोरी को {{full}} गिनता है और हर साझा पृष्ठ को उसे इस्तेमाल करने वाली प्रक्रियाओं में बांटता है — इसलिए सौ वर्करों द्वारा साझा libc की एक प्रति हर एक में सौवां हिस्सा जोड़ती है, जबकि केवल उस प्रक्रिया के पास मौजूद सब कुछ पूरा गिना जाता है। इससे किसी प्रक्रिया को दी जा सकने वाली प्रभावी भौतिक मेमोरी मिलती है, और यही एक आंकड़ा है जिसे जोड़ा जा सकता है। {{resident}} हर साझा पृष्ठ को प्रत्येक प्रक्रिया के लिए एक बार गिनता है, इसलिए इसका कुल हमेशा मशीन की मेमोरी से अधिक होता है। ऊपर का {{kernel_shared}} कार्ड कुछ और मापता है — tmpfs और System V सेगमेंट — और यह इस कॉलम का उप-योग नहीं है: किसी प्रक्रिया द्वारा मैप किया गया tmpfs पृष्ठ दोनों में गिना जाता है और जिसे कुछ भी मैप नहीं करता वह केवल वहां गिना जाता है।',
        'core-host-memory-users.user': 'उपयोगकर्ता',
        'core-host-memory-users.processes': 'प्रोसेस',
        'core-host-memory-users.threads': 'थ्रेड',
        'core-host-memory-users.resident': 'रेज़िडेंट',
        'core-host-memory-users.proportional_pss': 'आनुपातिक (PSS)',
        'core-host-memory-users.swapped': 'स्वैप्ड',
      }),
      ar: Object.freeze({
        'core-host-memory-users.title': 'الذاكرة حسب المستخدم',
        'core-host-memory-users.help':
          'العمود الذي يخفيه CageFS. يحسب {{pss}} ذاكرة العملية الخاصة {{full}} ويقسم كل صفحة مشتركة بين العمليات التي تستخدمها — لذا فإن نسخة واحدة من libc مشتركة بين مئة عامل تسهم بجزء من مئة لكل واحد، بينما يُحسب كامل ما تحتفظ به تلك العملية وحدها. وهذا يجعلها الذاكرة الفعلية المنسوبة إلى العملية، والرقم الوحيد الذي يمكن جمعه. يحسب {{resident}} كل صفحة مشتركة مرة واحدة لكل عملية، لذلك يكون مجموعه دائمًا أكبر مما لدى الجهاز. تقيس بطاقة {{kernel_shared}} أعلاه شيئًا آخر — مقاطع tmpfs وSystem V — وليست مجموعًا فرعيًا لهذا العمود: صفحة tmpfs يربطها بعض العمليات تُحسب في كليهما، والصفحة التي لا يربطها شيء تُحسب هناك فقط.',
        'core-host-memory-users.user': 'المستخدم',
        'core-host-memory-users.processes': 'العمليات',
        'core-host-memory-users.threads': 'الخيوط',
        'core-host-memory-users.resident': 'المقيم',
        'core-host-memory-users.proportional_pss': 'التناسبي (PSS)',
        'core-host-memory-users.swapped': 'المبدّل',
      }),
      zh: Object.freeze({
        'core-host-memory-users.title': '按用户划分的内存',
        'core-host-memory-users.help':
          '这是 CageFS 隐藏的那一列。{{pss}} 会将进程的私有内存{{full}}计算，并将每个共享页面分摊给使用它的进程——因此，一份由一百个工作进程共享的 libc 会向每个进程贡献百分之一，而只有该进程持有的内容会完整计算。这使它成为可归因于一个进程的有效物理内存，也是唯一可以相加的数值。{{resident}} 会为每个进程计算一次每个共享页面，因此其总和始终高于机器拥有的内存。上方的 {{kernel_shared}} 卡片测量的是另一类内容——tmpfs 和 System V 段——不是此列的小计：某个进程映射的 tmpfs 页面会同时计入两边，而没有任何进程映射的页面只计入那里。',
        'core-host-memory-users.user': '用户',
        'core-host-memory-users.processes': '进程',
        'core-host-memory-users.threads': '线程',
        'core-host-memory-users.resident': '常驻',
        'core-host-memory-users.proportional_pss': '比例集大小 (PSS)',
        'core-host-memory-users.swapped': '已交换',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-host-memory-users.${key}`,
      {},
      this.manifest.catalogs.en[`core-host-memory-users.${key}`],
    );
  },
  render({ rows = [] }, helpers) {
    const {
      target,
      renderTable,
      userCell,
      rankedCell,
      humanBytes,
      hostPss,
      topTwo,
      i18n,
    } = helpers;
    const rank = topTwo(rows, ['rss_bytes', 'pss_bytes', 'swap_bytes']);
    const t = (key, fallback) =>
      i18n.t(`core-host-memory-users.${key}`, {}, fallback);
    renderTable(target.id, rows, [
      { label: t('user', 'User'), render: (row) => userCell(row.user) },
      {
        label: t('processes', 'Processes'),
        className: 'size',
        render: (row) => row.procs,
      },
      {
        label: t('threads', 'Threads'),
        className: 'size',
        render: (row) => row.threads,
      },
      {
        label: t('resident', 'Resident'),
        className: 'size',
        render: (row) =>
          rankedCell(humanBytes(row.rss_bytes), rank.rss_bytes, row),
      },
      {
        label: t('proportional_pss', 'Proportional (PSS)'),
        className: 'size',
        render: (row) =>
          rankedCell(hostPss(row.pss_bytes), rank.pss_bytes, row),
      },
      {
        label: t('swapped', 'Swapped'),
        className: 'size',
        render: (row) =>
          rankedCell(humanBytes(row.swap_bytes), rank.swap_bytes, row),
      },
    ]);
  },
};
