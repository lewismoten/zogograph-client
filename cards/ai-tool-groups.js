/* Core package: names shared by AI tool tabs, prompts, and activity. */
window.ZogographAiToolGroupsCard = {
  manifest: Object.freeze({
    id: 'core-ai-tool-groups',
    locales: ['core-ai-tool-groups.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-tool-groups.diagnostic': 'Diagnostic',
        'core-ai-tool-groups.navigation': 'Navigation',
        'core-ai-tool-groups.terminal': 'Terminal',
        'core-ai-tool-groups.host': 'Host',
        'core-ai-tool-groups.memory': 'Memory',
        'core-ai-tool-groups.pictures': 'Pictures',
      }),
      es: Object.freeze({
        'core-ai-tool-groups.diagnostic': 'Diagnóstico',
        'core-ai-tool-groups.navigation': 'Navegación',
        'core-ai-tool-groups.terminal': 'Terminal',
        'core-ai-tool-groups.host': 'Host',
        'core-ai-tool-groups.memory': 'Memoria',
        'core-ai-tool-groups.pictures': 'Imágenes',
      }),
      hi: Object.freeze({
        'core-ai-tool-groups.diagnostic': 'निदान',
        'core-ai-tool-groups.navigation': 'नेविगेशन',
        'core-ai-tool-groups.terminal': 'टर्मिनल',
        'core-ai-tool-groups.host': 'होस्ट',
        'core-ai-tool-groups.memory': 'मेमोरी',
        'core-ai-tool-groups.pictures': 'चित्र',
      }),
      ar: Object.freeze({
        'core-ai-tool-groups.diagnostic': 'التشخيص',
        'core-ai-tool-groups.navigation': 'التنقل',
        'core-ai-tool-groups.terminal': 'الطرفية',
        'core-ai-tool-groups.host': 'المضيف',
        'core-ai-tool-groups.memory': 'الذاكرة',
        'core-ai-tool-groups.pictures': 'الصور',
      }),
      zh: Object.freeze({
        'core-ai-tool-groups.diagnostic': '诊断',
        'core-ai-tool-groups.navigation': '导航',
        'core-ai-tool-groups.terminal': '终端',
        'core-ai-tool-groups.host': '主机',
        'core-ai-tool-groups.memory': '内存',
        'core-ai-tool-groups.pictures': '图片',
      }),
    }),
  }),
  render({ group }, { i18n }) {
    const key = `core-ai-tool-groups.${group}`;
    return i18n.t(key, {}, this.manifest.catalogs.en[key] ?? group);
  },
};
