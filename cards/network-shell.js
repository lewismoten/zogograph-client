/* Core card package: static guidance and controls for the Network view. */
window.ZogographNetworkShellCard = {
  manifest: Object.freeze({
    id: 'core-network-shell',
    locales: ['core-network-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-network-shell.intro':
          'Every other page here measures something being used up — disk, memory, CPU — and none of them can say why. A machine pinned at 100% because it is answering four thousand DNS queries a second looks exactly like a machine pinned at 100% for any other reason, and the difference is only visible on the wire.',
        'core-network-shell.sign_in': 'Sign in to see the network.',
        'core-network-shell.network': 'Network',
        'core-network-shell.interfaces': 'Interfaces',
        'core-network-shell.ports_public_only': 'Reachable from outside only',
        'core-network-shell.nothing_recorded': 'Nothing recorded.',
      }),
      es: Object.freeze({
        'core-network-shell.intro':
          'Todas las demás páginas miden algo que se está agotando — disco, memoria, CPU — y ninguna puede decir por qué. Una máquina al 100 % porque responde cuatro mil consultas DNS por segundo parece exactamente igual que una máquina al 100 % por cualquier otra razón; la diferencia solo se ve en la red.',
        'core-network-shell.sign_in': 'Inicie sesión para ver la red.',
        'core-network-shell.network': 'Red',
        'core-network-shell.interfaces': 'Interfaces',
        'core-network-shell.ports_public_only': 'Solo accesibles desde fuera',
        'core-network-shell.nothing_recorded': 'No hay nada registrado.',
      }),
      hi: Object.freeze({
        'core-network-shell.intro':
          'यहाँ बाकी सभी पेज किसी घटती चीज़—डिस्क, मेमोरी, CPU—को मापते हैं, पर कोई नहीं बता सकता कि क्यों। प्रति सेकंड चार हज़ार DNS प्रश्नों का उत्तर देने के कारण 100% पर अटकी मशीन किसी अन्य कारण से 100% पर अटकी मशीन जैसी ही दिखती है; अंतर केवल नेटवर्क पर दिखता है।',
        'core-network-shell.sign_in': 'नेटवर्क देखने के लिए साइन इन करें।',
        'core-network-shell.network': 'नेटवर्क',
        'core-network-shell.interfaces': 'इंटरफ़ेस',
        'core-network-shell.ports_public_only': 'केवल बाहर से पहुँच योग्य',
        'core-network-shell.nothing_recorded': 'कुछ भी दर्ज नहीं है।',
      }),
      ar: Object.freeze({
        'core-network-shell.intro':
          'تقيس كل الصفحات الأخرى هنا شيئًا يُستهلك — القرص أو الذاكرة أو المعالج — ولا تستطيع أي منها أن تقول لماذا. الجهاز المثبت عند 100٪ لأنه يجيب عن أربعة آلاف استعلام DNS في الثانية يبدو تمامًا كجهاز مثبت عند 100٪ لأي سبب آخر؛ لا يظهر الفرق إلا على الشبكة.',
        'core-network-shell.sign_in': 'سجّل الدخول لرؤية الشبكة.',
        'core-network-shell.network': 'الشبكة',
        'core-network-shell.interfaces': 'الواجهات',
        'core-network-shell.ports_public_only':
          'يمكن الوصول إليها من الخارج فقط',
        'core-network-shell.nothing_recorded': 'لم يُسجل شيء.',
      }),
      zh: Object.freeze({
        'core-network-shell.intro':
          '这里的其他页面都在测量某种正在消耗的资源——磁盘、内存、CPU——但没有一个能说明原因。因每秒响应四千个 DNS 查询而占满 100% 的机器，看起来和因任何其他原因占满 100% 的机器完全一样；区别只能在线路上看到。',
        'core-network-shell.sign_in': '登录后可查看网络。',
        'core-network-shell.network': '网络',
        'core-network-shell.interfaces': '接口',
        'core-network-shell.ports_public_only': '仅可从外部访问',
        'core-network-shell.nothing_recorded': '没有已记录的内容。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-network-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-network-shell.${key}`],
    );
  },
  render() {},
};
