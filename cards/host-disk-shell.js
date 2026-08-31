/* Core card package: page shells driven by the shared host disk walk. */
window.ZogographHostDiskShellCard = {
  manifest: Object.freeze({
    id: 'core-host-disk-shell',
    locales: ['core-host-disk-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-host-disk-shell.filesystems_title':
          'Filesystems, and the space nothing accounts for',
        'core-host-disk-shell.deleted_title': 'Deleted, still holding space',
        'core-host-disk-shell.deleted_help':
          'A file that has been unlinked while a process still has it open keeps its blocks. {{df}} counts them; {{du}}, {{ls}}, {{find}} and the tree map cannot see them at all. This is the one kind of missing space no directory walk can ever find, and restarting the process listed is what frees it.',
        'core-host-disk-shell.directories_title':
          'Biggest directories on the machine',
      }),
      es: Object.freeze({
        'core-host-disk-shell.filesystems_title':
          'Sistemas de archivos y el espacio que no se explica',
        'core-host-disk-shell.deleted_title':
          'Eliminado, pero aún ocupa espacio',
        'core-host-disk-shell.deleted_help':
          'Un archivo desvinculado mientras un proceso aún lo tiene abierto conserva sus bloques. {{df}} los cuenta; {{du}}, {{ls}}, {{find}} y el mapa de árbol no pueden verlos en absoluto. Es el único tipo de espacio perdido que ningún recorrido de directorios puede encontrar, y reiniciar el proceso indicado es lo que lo libera.',
        'core-host-disk-shell.directories_title':
          'Directorios más grandes de la máquina',
      }),
      hi: Object.freeze({
        'core-host-disk-shell.filesystems_title':
          'फ़ाइल सिस्टम और वह स्थान जिसका कोई हिसाब नहीं',
        'core-host-disk-shell.deleted_title':
          'हटाया गया, फिर भी जगह घेर रहा है',
        'core-host-disk-shell.deleted_help':
          'जब किसी फ़ाइल को हटाया जाता है लेकिन कोई प्रोसेस उसे खुला रखता है, तो उसके ब्लॉक बने रहते हैं। {{df}} उन्हें गिनता है; {{du}}, {{ls}}, {{find}} और ट्री मैप उन्हें बिल्कुल नहीं देख सकते। यह गायब स्थान का एकमात्र प्रकार है जिसे कोई डायरेक्टरी स्कैन कभी नहीं ढूँढ सकता, और सूचीबद्ध प्रोसेस को रीस्टार्ट करने से ही वह मुक्त होता है।',
        'core-host-disk-shell.directories_title':
          'मशीन पर सबसे बड़ी निर्देशिकाएँ',
      }),
      ar: Object.freeze({
        'core-host-disk-shell.filesystems_title':
          'أنظمة الملفات والمساحة التي لا يفسرها شيء',
        'core-host-disk-shell.deleted_title': 'محذوف، لكنه ما زال يشغل مساحة',
        'core-host-disk-shell.deleted_help':
          'يحتفظ الملف الذي أُلغي ارتباطه بينما ما زالت عملية تفتحه بكتله. يحسبها {{df}}؛ ولا يستطيع {{du}} أو {{ls}} أو {{find}} أو خريطة الشجرة رؤيتها إطلاقًا. هذا هو النوع الوحيد من المساحة المفقودة الذي لا يمكن لأي مسح للمجلدات إيجاده، وإعادة تشغيل العملية المذكورة هي ما يحررها.',
        'core-host-disk-shell.directories_title': 'أكبر المجلدات على الجهاز',
      }),
      zh: Object.freeze({
        'core-host-disk-shell.filesystems_title':
          '文件系统，以及无从解释的空间',
        'core-host-disk-shell.deleted_title': '已删除，但仍占用空间',
        'core-host-disk-shell.deleted_help':
          '文件虽已取消链接，但只要进程仍将其打开，它的块就会保留。{{df}} 会统计它们；{{du}}、{{ls}}、{{find}} 和树状图完全看不到它们。这是目录扫描永远找不到的唯一一种丢失空间；重启列出的进程即可释放它。',
        'core-host-disk-shell.directories_title': '机器上最大的目录',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-host-disk-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-host-disk-shell.${key}`],
    );
  },
  render() {},
};
