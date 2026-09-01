/* Core card package: backup action reachability and safety guidance. */
window.ZogographBackupActionGuidanceCard = {
  manifest: Object.freeze({
    id: 'core-backup-action-guidance',
    locales: ['core-backup-action-guidance.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-backup-action-guidance.no_suggestions':
          'Nothing to suggest from this reading.',
        'core-backup-action-guidance.reach_provider':
          'ask your hosting provider',
        'core-backup-action-guidance.safe_actions':
          'Safe — returns space the backup system already considers disposable',
        'core-backup-action-guidance.review_actions':
          'Worth reviewing — a decision, not a chore',
        'core-backup-action-guidance.dangerous_actions':
          'Dangerous — listed so it can be named, not recommended',
      }),
      es: Object.freeze({
        'core-backup-action-guidance.no_suggestions':
          'No hay nada que sugerir a partir de esta lectura.',
        'core-backup-action-guidance.reach_provider':
          'pregunte a su proveedor de alojamiento',
        'core-backup-action-guidance.safe_actions':
          'Seguro: recupera espacio que el sistema de copias de seguridad ya considera desechable',
        'core-backup-action-guidance.review_actions':
          'Conviene revisarlo: es una decisión, no una tarea rutinaria',
        'core-backup-action-guidance.dangerous_actions':
          'Peligroso: se muestra para poder nombrarlo, no como recomendación',
      }),
      hi: Object.freeze({
        'core-backup-action-guidance.no_suggestions':
          'इस रीडिंग से सुझाव देने के लिए कुछ नहीं है।',
        'core-backup-action-guidance.reach_provider':
          'अपने होस्टिंग प्रदाता से पूछें',
        'core-backup-action-guidance.safe_actions':
          'सुरक्षित — वह जगह वापस करता है जिसे बैकअप प्रणाली पहले ही हटाने योग्य मानती है',
        'core-backup-action-guidance.review_actions':
          'समीक्षा योग्य — यह निर्णय है, नियमित काम नहीं',
        'core-backup-action-guidance.dangerous_actions':
          'खतरनाक — इसे नाम से पहचानने के लिए सूचीबद्ध किया गया है, अनुशंसित नहीं',
      }),
      ar: Object.freeze({
        'core-backup-action-guidance.no_suggestions':
          'لا يوجد ما يُقترح من هذه القراءة.',
        'core-backup-action-guidance.reach_provider': 'اسأل موفر الاستضافة',
        'core-backup-action-guidance.safe_actions':
          'آمن — يستعيد مساحة يعتبرها نظام النسخ الاحتياطي قابلة للتخلص منها بالفعل',
        'core-backup-action-guidance.review_actions':
          'يستحق المراجعة — قرار وليس مهمة روتينية',
        'core-backup-action-guidance.dangerous_actions':
          'خطير — مُدرج لتسميته لا للتوصية به',
      }),
      zh: Object.freeze({
        'core-backup-action-guidance.no_suggestions':
          '此读数没有可提供的建议。',
        'core-backup-action-guidance.reach_provider': '请联系您的托管服务商',
        'core-backup-action-guidance.safe_actions':
          '安全——可释放备份系统已认为可以丢弃的空间',
        'core-backup-action-guidance.review_actions':
          '值得审查——这是一个决定，不是一项例行任务',
        'core-backup-action-guidance.dangerous_actions':
          '危险——列出它是为了能明确提及，而非建议执行',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-backup-action-guidance.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
