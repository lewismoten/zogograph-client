/* Core card package: page shell for the host filesystem layout. */
window.ZogographHostLayoutShellCard = {
  manifest: Object.freeze({
    id: 'core-host-layout-shell',
    locales: ['core-host-layout-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-host-layout-shell.title': 'Filesystem layout',
        'core-host-layout-shell.help':
          "Every mount the kernel reports, grouped by filesystem. Two paths on the same device are the same disk however different they look, which is why a bind mount can make one directory appear to be two — and why summing mount points reports the same space twice. The skeleton's own mounts used to be the second half of this page and are now on {{cagefs}}, which is where somebody looking for them goes.",
      }),
      es: Object.freeze({
        'core-host-layout-shell.title': 'Disposición de sistemas de archivos',
        'core-host-layout-shell.help':
          'Cada montaje que informa el kernel, agrupado por sistema de archivos. Dos rutas del mismo dispositivo son el mismo disco aunque parezcan distintas; por eso un montaje bind puede hacer que un directorio parezca dos y sumar puntos de montaje informa el mismo espacio dos veces. Los montajes del esqueleto ahora están en {{cagefs}}, donde se buscan.',
      }),
      hi: Object.freeze({
        'core-host-layout-shell.title': 'फ़ाइलसिस्टम विन्यास',
        'core-host-layout-shell.help':
          'कर्नेल द्वारा रिपोर्ट किया हर माउंट, फ़ाइलसिस्टम के अनुसार समूहित। एक ही डिवाइस के दो पथ एक ही डिस्क हैं; bind mount से एक डायरेकरी दो दिख सकती है और माउंट पॉइंट जोड़ने पर वही जगह दो बार दिखती है। स्केलेटन के माउंट अब {{cagefs}} में हैं।',
      }),
      ar: Object.freeze({
        'core-host-layout-shell.title': 'تخطيط نظام الملفات',
        'core-host-layout-shell.help':
          'كل نقاط التركيب التي يبلّغ عنها النواة، مجمعة حسب نظام الملفات. مساران على الجهاز نفسه هما القرص نفسه؛ وقد يجعل bind mount دليلًا واحدًا يبدو كأنه اثنان، وجمع نقاط التركيب يحسب المساحة ذاتها مرتين. نقاط تركيب الهيكل موجودة الآن في {{cagefs}}.',
      }),
      zh: Object.freeze({
        'core-host-layout-shell.title': '文件系统布局',
        'core-host-layout-shell.help':
          '内核报告的每个挂载点，按文件系统分组。同一设备上的两条路径是同一磁盘；绑定挂载会使一个目录看起来像两个，汇总挂载点会将同一空间计算两次。骨架自身的挂载现在位于 {{cagefs}}。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-host-layout-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-host-layout-shell.${key}`],
    );
  },
  render() {},
};
