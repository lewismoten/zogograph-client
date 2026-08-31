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
        'core-host-disk-shell.filesystems': 'Filesystems',
        'core-host-disk-shell.mounted_at': 'Mounted at',
        'core-host-disk-shell.device': 'Device',
        'core-host-disk-shell.type': 'Type',
        'core-host-disk-shell.used': 'Used',
        'core-host-disk-shell.free': 'Free',
        'core-host-disk-shell.full': 'Full',
        'core-host-disk-shell.mount_points_more':
          '+{{count, number}} mount points',
        'core-host-disk-shell.mount_points_more_tip':
          'The same filesystem is mounted in these places too. Bind mounts, so the space is counted once.',
        'core-host-disk-shell.mount_also_at': '{{mount}} is also mounted at',
        'core-host-disk-shell.mount_points_help':
          'One filesystem, mounted in several places. The disk holds these bytes once — which is why this table shows one row rather than one per mount point.',
        'core-host-disk-shell.deleted_title': 'Deleted, still holding space',
        'core-host-disk-shell.deleted_help':
          'A file that has been unlinked while a process still has it open keeps its blocks. {{df}} counts them; {{du}}, {{ls}}, {{find}} and the tree map cannot see them at all. This is the one kind of missing space no directory walk can ever find, and restarting the process listed is what frees it.',
        'core-host-disk-shell.directories_title':
          'Biggest directories on the machine',
      }),
      es: Object.freeze({
        'core-host-disk-shell.filesystems_title':
          'Sistemas de archivos y el espacio que no se explica',
        'core-host-disk-shell.filesystems': 'Sistemas de archivos',
        'core-host-disk-shell.mounted_at': 'Montado en',
        'core-host-disk-shell.device': 'Dispositivo',
        'core-host-disk-shell.type': 'Tipo',
        'core-host-disk-shell.used': 'Usado',
        'core-host-disk-shell.free': 'Libre',
        'core-host-disk-shell.full': 'Lleno',
        'core-host-disk-shell.mount_points_more':
          '+{{count, number}} puntos de montaje',
        'core-host-disk-shell.mount_points_more_tip':
          'El mismo sistema de archivos también está montado en estos lugares. Son montajes bind, así que el espacio se cuenta una vez.',
        'core-host-disk-shell.mount_also_at':
          '{{mount}} también está montado en',
        'core-host-disk-shell.mount_points_help':
          'Un sistema de archivos montado en varios lugares. El disco conserva estos bytes una sola vez; por eso esta tabla muestra una fila en vez de una por cada punto de montaje.',
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
        'core-host-disk-shell.filesystems': 'फाइल सिस्टम',
        'core-host-disk-shell.mounted_at': 'माउंट स्थान',
        'core-host-disk-shell.device': 'डिवाइस',
        'core-host-disk-shell.type': 'प्रकार',
        'core-host-disk-shell.used': 'उपयोग हुआ',
        'core-host-disk-shell.free': 'खाली',
        'core-host-disk-shell.full': 'भरा',
        'core-host-disk-shell.mount_points_more':
          '+{{count, number}} माउंट पॉइंट',
        'core-host-disk-shell.mount_points_more_tip':
          'यही फाइल सिस्टम इन स्थानों पर भी माउंट है। ये बाइंड माउंट हैं, इसलिए स्थान एक बार गिना जाता है।',
        'core-host-disk-shell.mount_also_at': '{{mount}} यहाँ भी माउंट है',
        'core-host-disk-shell.mount_points_help':
          'एक फाइल सिस्टम कई स्थानों पर माउंट है। डिस्क इन बाइटों को एक बार रखती है — इसलिए यह तालिका हर माउंट पॉइंट के बजाय एक पंक्ति दिखाती है।',
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
        'core-host-disk-shell.filesystems': 'أنظمة الملفات',
        'core-host-disk-shell.mounted_at': 'مركب في',
        'core-host-disk-shell.device': 'الجهاز',
        'core-host-disk-shell.type': 'النوع',
        'core-host-disk-shell.used': 'المستخدم',
        'core-host-disk-shell.free': 'المتاح',
        'core-host-disk-shell.full': 'ممتلئ',
        'core-host-disk-shell.mount_points_more':
          '+{{count, number}} نقاط تركيب',
        'core-host-disk-shell.mount_points_more_tip':
          'نظام الملفات نفسه مركب في هذه الأماكن أيضًا. إنها نقاط تركيب ربط، لذا تُحسب المساحة مرة واحدة.',
        'core-host-disk-shell.mount_also_at': '{{mount}} مركب أيضًا في',
        'core-host-disk-shell.mount_points_help':
          'نظام ملفات واحد مركب في عدة أماكن. يحتفظ القرص بهذه البايتات مرة واحدة، ولهذا يعرض هذا الجدول صفًا واحدًا بدل صف لكل نقطة تركيب.',
        'core-host-disk-shell.deleted_title': 'محذوف، لكنه ما زال يشغل مساحة',
        'core-host-disk-shell.deleted_help':
          'يحتفظ الملف الذي أُلغي ارتباطه بينما ما زالت عملية تفتحه بكتله. يحسبها {{df}}؛ ولا يستطيع {{du}} أو {{ls}} أو {{find}} أو خريطة الشجرة رؤيتها إطلاقًا. هذا هو النوع الوحيد من المساحة المفقودة الذي لا يمكن لأي مسح للمجلدات إيجاده، وإعادة تشغيل العملية المذكورة هي ما يحررها.',
        'core-host-disk-shell.directories_title': 'أكبر المجلدات على الجهاز',
      }),
      zh: Object.freeze({
        'core-host-disk-shell.filesystems_title':
          '文件系统，以及无从解释的空间',
        'core-host-disk-shell.filesystems': '文件系统',
        'core-host-disk-shell.mounted_at': '挂载于',
        'core-host-disk-shell.device': '设备',
        'core-host-disk-shell.type': '类型',
        'core-host-disk-shell.used': '已用',
        'core-host-disk-shell.free': '可用',
        'core-host-disk-shell.full': '已满',
        'core-host-disk-shell.mount_points_more': '+{{count, number}} 个挂载点',
        'core-host-disk-shell.mount_points_more_tip':
          '同一文件系统也挂载在这些位置。这些是绑定挂载，因此空间只计算一次。',
        'core-host-disk-shell.mount_also_at': '{{mount}} 也挂载于',
        'core-host-disk-shell.mount_points_help':
          '一个文件系统挂载在多个位置。磁盘只保存这些字节一次，因此此表显示一行，而不是每个挂载点一行。',
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
