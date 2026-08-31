/* Core card package: account CPU ceilings and CloudLinux throttling evidence. */
window.ZogographCpuLimitsCard = {
  manifest: Object.freeze({
    id: 'core-cpu-limits',
    locales: ['core-cpu-limits.'],
    helpers: [
      'renderLimits',
      'noteTarget',
      'lveWrap',
      'renderTable',
      'humanCount',
      'drawLineChart',
      'countAxis',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-cpu-limits.title': 'CloudLinux limits',
        'core-cpu-limits.page_title': 'Ceilings and throttling',
        'core-cpu-limits.lve_title': 'Where the ceiling was actually hit',
        'core-cpu-limits.help':
          'The page for ‘the CPU is pinned and the server is idle’, which is not a contradiction and not a broken reading. On shared hosting this account is given a slice of the machine and told, through a percentage of its own allowance, how much of that slice it is using. Both numbers are correct and they measure different things; nothing useful comes of comparing them as a ratio, so this page puts them side by side instead.',
        'core-cpu-limits.lve_help':
          'CloudLinux records a fault every time an account asks for more processor than its LVE allows and is held back. This is the difference between ‘we are near the limit’ and ‘we are being throttled right now’, and it is the only figure here that says so directly — a throttled account’s own CPU percentage sits at its ceiling and looks calm.',
        'core-cpu-limits.faults':
          "{{faults, number}} CPU fault(s) recorded at the last reading across {{accounts, number}} account(s). A fault is an account asking for more processor than its LVE allows and being held back — this is being throttled now, not being near a limit. A throttled account's own percentage sits calmly at its ceiling, which is why the fault count is the figure to watch and not the percentage.",
        'core-cpu-limits.throttled':
          'This account is at {{account, number}}% of its own allowance while the machine is {{host, number}}% busy. That is not a contradiction: the two percentages have different denominators. It is the ordinary shape of hitting a limit, and it is felt as a slow site even though the server has plenty of processor left.',
        'core-cpu-limits.comparison':
          'This account is using {{account, number}}% of what it is allowed; the machine as a whole is {{host, number}}% busy. Neither number is a share of the other, and dividing them produces nothing meaningful.',
        'core-cpu-limits.missing_side':
          'Both halves of this comparison need a reading — one from this account and one from the host collector running as root.',
        'core-cpu-limits.account': 'Account',
        'core-cpu-limits.cpu_faults': 'CPU faults',
        'core-cpu-limits.memory_faults': 'Memory faults',
        'core-cpu-limits.process_faults': 'Process faults',
        'core-cpu-limits.cpu_in_use': 'CPU in use',
        'core-cpu-limits.no_lve_readings': 'No LVE readings in this range',
      }),
      es: Object.freeze({
        'core-cpu-limits.title': 'Límites de CloudLinux',
        'core-cpu-limits.page_title': 'Límites y estrangulamiento',
        'core-cpu-limits.help':
          'La página para «la CPU está al límite y el servidor está inactivo», que no es una contradicción ni una lectura rota. En alojamiento compartido a esta cuenta se le da una parte de la máquina y se le indica, mediante un porcentaje de su propia asignación, cuánto de esa parte está usando. Ambos números son correctos y miden cosas distintas; compararles como una proporción no aporta nada, por eso esta página los muestra juntos.',
        'core-cpu-limits.lve_help':
          'CloudLinux registra un fallo cada vez que una cuenta pide más procesador del que permite su LVE y se la contiene. Esa es la diferencia entre «estamos cerca del límite» y «nos están limitando ahora mismo», y es la única cifra aquí que lo dice directamente: el porcentaje de CPU de una cuenta limitada se queda en su techo y parece tranquilo.',
        'core-cpu-limits.lve_title': 'Dónde se alcanzó realmente el límite',
        'core-cpu-limits.faults':
          'Se registraron {{faults, number}} fallos de CPU en la última lectura de {{accounts, number}} cuenta(s). Un fallo indica que una cuenta pide más procesador del que permite su LVE y está siendo retenida: se está limitando ahora, no solo acercándose a un límite. El porcentaje de una cuenta limitada puede permanecer tranquilo en su tope; por eso debe vigilarse el número de fallos y no el porcentaje.',
        'core-cpu-limits.throttled':
          'Esta cuenta usa el {{account, number}}% de su propia asignación mientras la máquina está ocupada al {{host, number}}%. No hay contradicción: los porcentajes tienen denominadores distintos. Es la forma habitual de alcanzar un límite y se percibe como un sitio lento aunque al servidor le quede procesador.',
        'core-cpu-limits.comparison':
          'Esta cuenta usa el {{account, number}}% de lo que tiene permitido; la máquina completa está ocupada al {{host, number}}%. Ninguno de los valores es una parte del otro y dividirlos no produce nada significativo.',
        'core-cpu-limits.missing_side':
          'Ambas partes de esta comparación necesitan una lectura: una de esta cuenta y otra del recolector del host que se ejecuta como root.',
        'core-cpu-limits.account': 'Cuenta',
        'core-cpu-limits.cpu_faults': 'Fallos de CPU',
        'core-cpu-limits.memory_faults': 'Fallos de memoria',
        'core-cpu-limits.process_faults': 'Fallos de proceso',
        'core-cpu-limits.cpu_in_use': 'CPU en uso',
        'core-cpu-limits.no_lve_readings':
          'No hay lecturas LVE en este intervalo',
      }),
      hi: Object.freeze({
        'core-cpu-limits.title': 'CloudLinux सीमाएँ',
        'core-cpu-limits.page_title': 'सीमाएँ और थ्रॉटलिंग',
        'core-cpu-limits.help':
          '‘CPU सीमा पर है और सर्वर निष्क्रिय है’ वाला पृष्ठ—यह न विरोधाभास है, न टूटी रीडिंग। साझा होस्टिंग में इस खाते को मशीन का एक हिस्सा दिया जाता है और उसके अपने आवंटन के प्रतिशत से बताया जाता है कि वह उस हिस्से का कितना उपयोग कर रहा है। दोनों संख्या सही हैं और अलग चीज़ें मापती हैं; अनुपात के रूप में तुलना उपयोगी नहीं, इसलिए यह पृष्ठ उन्हें साथ रखता है।',
        'core-cpu-limits.lve_help':
          'CloudLinux हर बार fault दर्ज करता है जब कोई खाता अपनी LVE अनुमति से अधिक प्रोसेसर माँगता है और रोका जाता है। यही ‘हम सीमा के पास हैं’ और ‘हम अभी थ्रॉटल हो रहे हैं’ का अंतर है, और यहाँ यही एक आंकड़ा इसे सीधे कहता है—थ्रॉटल खाते का अपना CPU प्रतिशत सीमा पर बैठकर शांत दिखता है।',
        'core-cpu-limits.lve_title': 'सीमा वास्तव में कहाँ लगी',
        'core-cpu-limits.faults':
          'अंतिम रीडिंग में {{accounts, number}} खाते में {{faults, number}} CPU दोष दर्ज हुए। दोष का अर्थ है कि कोई खाता अपने LVE की अनुमति से अधिक प्रोसेसर मांग रहा है और उसे रोका जा रहा है — यह अभी थ्रॉटलिंग है, केवल सीमा के पास होना नहीं। थ्रॉटल खाते का प्रतिशत अपनी सीमा पर शांत दिख सकता है, इसलिए प्रतिशत के बजाय दोष संख्या देखें।',
        'core-cpu-limits.throttled':
          'यह खाता अपनी अनुमति का {{account, number}}% उपयोग कर रहा है, जबकि मशीन {{host, number}}% व्यस्त है। इसमें कोई विरोधाभास नहीं है: दोनों प्रतिशतों के हर अलग हैं। सीमा तक पहुँचने का यह सामान्य रूप है और सर्वर में प्रोसेसर बचा होने पर भी साइट धीमी लग सकती है।',
        'core-cpu-limits.comparison':
          'यह खाता अपनी अनुमति का {{account, number}}% उपयोग कर रहा है; पूरी मशीन {{host, number}}% व्यस्त है। कोई भी संख्या दूसरी का हिस्सा नहीं है, इसलिए उन्हें विभाजित करना अर्थपूर्ण नहीं है।',
        'core-cpu-limits.missing_side':
          'इस तुलना के दोनों भागों के लिए रीडिंग चाहिए — एक इस खाते से और एक root के रूप में चल रहे होस्ट कलेक्टर से।',
        'core-cpu-limits.account': 'खाता',
        'core-cpu-limits.cpu_faults': 'CPU त्रुटियाँ',
        'core-cpu-limits.memory_faults': 'मेमोरी त्रुटियाँ',
        'core-cpu-limits.process_faults': 'प्रक्रिया त्रुटियाँ',
        'core-cpu-limits.cpu_in_use': 'प्रयुक्त CPU',
        'core-cpu-limits.no_lve_readings': 'इस अवधि में कोई LVE रीडिंग नहीं',
      }),
      ar: Object.freeze({
        'core-cpu-limits.title': 'حدود CloudLinux',
        'core-cpu-limits.page_title': 'السقوف والخنق',
        'core-cpu-limits.help':
          'صفحة «المعالج مثبت عند الحد والخادم خامل»، وهي ليست تناقضًا ولا قراءة معطلة. في الاستضافة المشتركة يُمنح هذا الحساب شريحة من الآلة ويُخبر، من خلال نسبة من حصته، بكم يستخدم من تلك الشريحة. الرقمان صحيحان ويقيسان شيئين مختلفين؛ لا يفيد مقارنتهما كنسبة، لذا تضعهما هذه الصفحة جنبًا إلى جنب.',
        'core-cpu-limits.lve_help':
          'يسجل CloudLinux عطلًا كلما طلب حساب معالجًا أكثر مما تسمح به LVE الخاصة به فتم كبحه. هذا هو الفرق بين «نحن قريبون من الحد» و«يُخنق أداؤنا الآن»، وهو الرقم الوحيد هنا الذي يقول ذلك مباشرة — نسبة CPU للحساب المخنوق تقف عند سقفها وتبدو هادئة.',
        'core-cpu-limits.lve_title': 'الموضع الذي بلغ فيه السقف فعليًا',
        'core-cpu-limits.faults':
          'سُجلت {{faults, number}} أعطال لوحدة المعالجة في آخر قراءة عبر {{accounts, number}} حسابًا. يعني العطل أن حسابًا يطلب معالجًا أكثر مما يسمح به حد LVE الخاص به ويجري تقييده الآن؛ فهذه حالة خنق فعلية وليست مجرد اقتراب من الحد. قد تبدو نسبة الحساب المخنوق ثابتة عند سقفها، لذا راقب عدد الأعطال لا النسبة المئوية.',
        'core-cpu-limits.throttled':
          'يستخدم هذا الحساب {{account, number}}٪ من سماحيته، بينما انشغال الجهاز كله {{host, number}}٪. لا يوجد تناقض لأن النسبتين لهما مقامان مختلفان. هذا هو الشكل المعتاد لبلوغ حد، وقد يبدو الموقع بطيئًا رغم بقاء قدرة معالج في الخادم.',
        'core-cpu-limits.comparison':
          'يستخدم هذا الحساب {{account, number}}٪ مما يسمح له به؛ وانشغال الجهاز كله {{host, number}}٪. لا يمثل أي من الرقمين حصة من الآخر، ولا معنى مفيدًا لقسمتهما.',
        'core-cpu-limits.missing_side':
          'يحتاج طرفا هذه المقارنة إلى قراءة: واحدة من هذا الحساب وأخرى من جامع المضيف الذي يعمل بصلاحية root.',
        'core-cpu-limits.account': 'الحساب',
        'core-cpu-limits.cpu_faults': 'أخطاء المعالج',
        'core-cpu-limits.memory_faults': 'أخطاء الذاكرة',
        'core-cpu-limits.process_faults': 'أخطاء العمليات',
        'core-cpu-limits.cpu_in_use': 'المعالج المستخدم',
        'core-cpu-limits.no_lve_readings': 'لا توجد قراءات LVE في هذا النطاق',
      }),
      zh: Object.freeze({
        'core-cpu-limits.title': 'CloudLinux 限制',
        'core-cpu-limits.page_title': '上限与限速',
        'core-cpu-limits.help':
          '此页面解释“CPU 已顶住但服务器很空闲”的情况；这既不是矛盾，也不是错误读数。在共享主机上，此账户获得机器的一部分，并通过其自身配额的百分比了解自己使用了这部分中的多少。两个数值都正确，只是衡量不同的事情；将其作为比例比较没有意义，因此本页将它们并列显示。',
        'core-cpu-limits.lve_help':
          '每当账户请求的处理器资源超过其 LVE 允许范围而被限制时，CloudLinux 都会记录一次故障。这正是“接近上限”和“此刻正被限速”之间的区别，也是这里唯一直接说明这一点的数据——被限速账户自身的 CPU 百分比停在上限上，看起来却很平静。',
        'core-cpu-limits.lve_title': '实际上撞到上限的位置',
        'core-cpu-limits.faults':
          '最近一次读数中，{{accounts, number}} 个帐户共记录了 {{faults, number}} 次 CPU 故障。故障表示帐户请求的处理器超过其 LVE 允许值，正在被限制；这表示当前已受节流，而不仅是接近限制。受节流帐户自身的百分比可能平静地停在上限，因此应关注故障次数而不是百分比。',
        'core-cpu-limits.throttled':
          '此帐户正在使用其自身配额的 {{account, number}}%，而整台机器忙碌度为 {{host, number}}%。这并不矛盾：两个百分比的分母不同。这是达到限制时的常见表现；即使服务器仍有处理器余量，站点也会感觉缓慢。',
        'core-cpu-limits.comparison':
          '此帐户使用了允许量的 {{account, number}}%；整台机器忙碌度为 {{host, number}}%。两个数值都不是另一个的份额，将它们相除没有实际意义。',
        'core-cpu-limits.missing_side':
          '这项比较的两部分都需要读数：一个来自此帐户，另一个来自以 root 身份运行的主机收集器。',
        'core-cpu-limits.account': '帐户',
        'core-cpu-limits.cpu_faults': 'CPU 故障',
        'core-cpu-limits.memory_faults': '内存故障',
        'core-cpu-limits.process_faults': '进程故障',
        'core-cpu-limits.cpu_in_use': '正在使用的 CPU',
        'core-cpu-limits.no_lve_readings': '此范围内没有 LVE 读数',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-cpu-limits.${key}`,
      {},
      this.manifest.catalogs.en[`core-cpu-limits.${key}`],
    );
  },
  render(data, helpers) {
    const {
      renderLimits,
      noteTarget,
      lveWrap,
      renderTable,
      humanCount,
      drawLineChart,
      countAxis,
      i18n,
    } = helpers;
    if (!noteTarget || !lveWrap) return;
    renderLimits(data.limits, 'cpuLimitsTable', 'cpuLimitsWhen');
    const mine =
      data.vitals?.cpu_percent == null ? null : Number(data.vitals.cpu_percent);
    const host =
      data.sample?.cpu_percent == null ? null : Number(data.sample.cpu_percent);
    const rows = data.lve ?? [];
    const faults = rows.reduce(
      (sum, row) => sum + (Number(row.faults_cpu) || 0),
      0,
    );
    const t = (key, values, fallback) =>
      i18n.t(`core-cpu-limits.${key}`, values, fallback);
    noteTarget.textContent =
      faults > 0
        ? t(
            'faults',
            { faults, accounts: rows.length },
            "{{faults, number}} CPU fault(s) recorded at the last reading across {{accounts, number}} account(s). A fault is an account asking for more processor than its LVE allows and being held back — this is being throttled now, not being near a limit. A throttled account's own percentage sits calmly at its ceiling, which is why the fault count is the figure to watch and not the percentage.",
          )
        : mine != null && host != null && mine >= 85 && host < 50
          ? t(
              'throttled',
              { account: Math.round(mine), host: Math.round(host) },
              'This account is at {{account, number}}% of its own allowance while the machine is {{host, number}}% busy. That is not a contradiction: the two percentages have different denominators. It is the ordinary shape of hitting a limit, and it is felt as a slow site even though the server has plenty of processor left.',
            )
          : mine != null && host != null
            ? t(
                'comparison',
                { account: Math.round(mine), host: Math.round(host) },
                'This account is using {{account, number}}% of what it is allowed; the machine as a whole is {{host, number}}% busy. Neither number is a share of the other, and dividing them produces nothing meaningful.',
              )
            : t(
                'missing_side',
                {},
                'Both halves of this comparison need a reading — one from this account and one from the host collector running as root.',
              );
    lveWrap.hidden = rows.length === 0;
    if (!rows.length) return;
    renderTable('cpuLve', rows, [
      { label: t('account', {}, 'Account'), render: (row) => row.user },
      {
        label: t('cpu_faults', {}, 'CPU faults'),
        className: 'size',
        render: (row) => humanCount(row.faults_cpu),
      },
      {
        label: t('memory_faults', {}, 'Memory faults'),
        className: 'size',
        render: (row) => humanCount(row.faults_mem),
      },
      {
        label: t('process_faults', {}, 'Process faults'),
        className: 'size',
        render: (row) => humanCount(row.faults_procs),
      },
      {
        label: t('cpu_in_use', {}, 'CPU in use'),
        className: 'size',
        render: (row) =>
          row.cpu_percent == null
            ? '—'
            : `${Number(row.cpu_percent).toFixed(0)}%`,
      },
    ]);
    drawLineChart(
      document.querySelector('#cpuLveChart'),
      data.lve_series ?? [],
      [
        { key: 'faults_cpu', label: t('cpu_faults', {}, 'CPU faults') },
        { key: 'faults_mem', label: t('memory_faults', {}, 'Memory faults') },
      ],
      {
        axis: countAxis,
        zeroBased: true,
        empty: t('no_lve_readings', {}, 'No LVE readings in this range'),
      },
    );
  },
};
