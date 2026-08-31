/* Core card package: host job choices offered from the chat. */
window.ZogographChatHostJobsCard = {
  manifest: Object.freeze({
    id: 'core-chat-host-jobs',
    locales: ['core-chat-host-jobs.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-chat-host-jobs.capabilities':
          'Re-check what is installed on the host',
        'core-chat-host-jobs.capabilities_cost':
          'Quick. Runs a handful of version checks.',
        'core-chat-host-jobs.disk_walk': 'Walk the whole filesystem as root',
        'core-chat-host-jobs.disk_walk_cost':
          'Expensive — minutes on a large disk, and it reads the whole time.',
      }),
      es: Object.freeze({
        'core-chat-host-jobs.capabilities':
          'Volver a comprobar lo instalado en el host',
        'core-chat-host-jobs.capabilities_cost':
          'Rápido. Ejecuta unas cuantas comprobaciones de versión.',
        'core-chat-host-jobs.disk_walk':
          'Recorrer todo el sistema de archivos como root',
        'core-chat-host-jobs.disk_walk_cost':
          'Costoso: minutos en un disco grande y lee todo el tiempo.',
      }),
      hi: Object.freeze({
        'core-chat-host-jobs.capabilities':
          'होस्ट पर क्या स्थापित है, फिर से जांचें',
        'core-chat-host-jobs.capabilities_cost':
          'तेज़। कुछ संस्करण जांच चलाता है।',
        'core-chat-host-jobs.disk_walk':
          'root के रूप में पूरा फ़ाइल सिस्टम देखें',
        'core-chat-host-jobs.disk_walk_cost':
          'महंगा — बड़े डिस्क पर कई मिनट लगते हैं और पूरे समय पढ़ता है।',
      }),
      ar: Object.freeze({
        'core-chat-host-jobs.capabilities': 'أعِد فحص ما هو مثبّت على المضيف',
        'core-chat-host-jobs.capabilities_cost':
          'سريع. يشغّل حفنة من فحوص الإصدارات.',
        'core-chat-host-jobs.disk_walk': 'افحص نظام الملفات كله بصفة root',
        'core-chat-host-jobs.disk_walk_cost':
          'مكلف — يستغرق دقائق على قرص كبير ويقرأ طوال الوقت.',
      }),
      zh: Object.freeze({
        'core-chat-host-jobs.capabilities': '重新检查主机上安装的内容',
        'core-chat-host-jobs.capabilities_cost': '很快。运行少量版本检查。',
        'core-chat-host-jobs.disk_walk': '以 root 身份遍历整个文件系统',
        'core-chat-host-jobs.disk_walk_cost':
          '开销较大——在大型磁盘上需要数分钟，并且始终处于读取状态。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-chat-host-jobs.${key}`,
      {},
      this.manifest.catalogs.en[`core-chat-host-jobs.${key}`],
    );
  },
  render(_input, helpers) {
    return {
      capabilities: this.text('capabilities', helpers),
      capabilitiesCost: this.text('capabilities_cost', helpers),
      diskWalk: this.text('disk_walk', helpers),
      diskWalkCost: this.text('disk_walk_cost', helpers),
    };
  },
};
