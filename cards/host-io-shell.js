/* Core card package: page shell for host-wide disk activity. */
window.ZogographHostIoShellCard = {
  manifest: Object.freeze({
    id: 'core-host-io-shell',
    locales: ['core-host-io-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-host-io-shell.title': 'How hard the disks are working',
        'core-host-io-shell.help':
          "Throughput and saturation per device, read by root — so this is every account on the server together. A busy line here says the disk is working, never that this account is the reason; the account's own traffic is on {{dashboard}}, and {{processes}} has its own page.",
      }),
      es: Object.freeze({
        'core-host-io-shell.title': 'Cuánto están trabajando los discos',
        'core-host-io-shell.help':
          'Rendimiento y saturación por dispositivo, leídos por root; por tanto incluye todas las cuentas del servidor. Una línea ocupada indica que el disco trabaja, no que esta cuenta sea la causa; el tráfico de la cuenta está en {{dashboard}} y {{processes}} tiene su propia página.',
      }),
      hi: Object.freeze({
        'core-host-io-shell.title': 'डिस्क कितनी मेहनत कर रही हैं',
        'core-host-io-shell.help':
          'प्रति डिवाइस थ्रूपुट और संतृप्ति, root द्वारा पढ़ी गई — इसलिए सर्वर के सभी खाते शामिल हैं। व्यस्त लाइन का अर्थ केवल डिस्क काम कर रही है; इस खाते का ट्रैफ़िक {{dashboard}} पर है और {{processes}} का अपना पृष्ठ है।',
      }),
      ar: Object.freeze({
        'core-host-io-shell.title': 'مدى انشغال الأقراص',
        'core-host-io-shell.help':
          'معدل النقل والتشبع لكل جهاز، يقرؤه root، لذا يشمل كل حسابات الخادم. السطر المشغول هنا يعني أن القرص يعمل لا أن هذا الحساب هو السبب؛ حركة الحساب في {{dashboard}} و{{processes}} لها صفحتها الخاصة.',
      }),
      zh: Object.freeze({
        'core-host-io-shell.title': '磁盘工作负载',
        'core-host-io-shell.help':
          '由 root 读取的每个设备的吞吐量和饱和度，因此包含服务器上的所有账户。此处忙碌的线表示磁盘正在工作，并不表示由此账户造成；账户自身流量在 {{dashboard}}，{{processes}} 有单独页面。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-host-io-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-host-io-shell.${key}`],
    );
  },
  render() {},
};
