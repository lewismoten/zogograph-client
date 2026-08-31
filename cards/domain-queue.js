window.ZogographDomainQueueCard = {
  manifest: Object.freeze({
    id: 'core-domain-queue',
    locales: ['core-domain-queue.'],
    helpers: ['note', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-domain-queue.title': 'Domain names',
        'core-domain-queue.waiting':
          '{{due, number}} domains are due, with {{perRun, number}} looked up per run — registries rate-limit anyone who asks faster, and a blocked address reports every domain as unknown. The oldest waits least.',
        'core-domain-queue.empty': 'No domains are being watched.',
      }),
      es: Object.freeze({
        'core-domain-queue.title': 'Nombres de dominio',
        'core-domain-queue.waiting':
          'Hay {{due, number}} dominios pendientes y se consulta {{perRun, number}} en cada ejecución: los registros limitan las consultas rápidas y una dirección bloqueada informa de todos los dominios como desconocidos. Los más antiguos esperan menos.',
        'core-domain-queue.empty': 'No se está vigilando ningún dominio.',
      }),
      hi: Object.freeze({
        'core-domain-queue.title': 'डोमेन नाम',
        'core-domain-queue.waiting':
          '{{due, number}} डोमेन देय हैं और हर रन में {{perRun, number}} देखे जाते हैं — रजिस्ट्रियाँ तेज़ी से पूछने वालों को सीमित करती हैं और ब्लॉक किया गया पता हर डोमेन को अज्ञात बताता है। सबसे पुराने को सबसे कम इंतज़ार है।',
        'core-domain-queue.empty': 'कोई डोमेन नहीं देखा जा रहा है।',
      }),
      ar: Object.freeze({
        'core-domain-queue.title': 'أسماء النطاقات',
        'core-domain-queue.waiting':
          'هناك {{due, number}} نطاقات مستحقة، ويُبحث عن {{perRun, number}} في كل تشغيل — تقيد السجلات من يسأل بسرعة أكبر، والعنوان المحظور يبلغ عن كل نطاق بأنه غير معروف. الأقدم ينتظر أقل.',
        'core-domain-queue.empty': 'لا توجد نطاقات قيد المراقبة.',
      }),
      zh: Object.freeze({
        'core-domain-queue.title': '域名',
        'core-domain-queue.waiting':
          '有 {{due, number}} 个域名待处理，每次运行查询 {{perRun, number}} 个——注册机构会限制查询过快的请求，而被阻止的地址会将每个域名报告为未知。最早的等待时间最短。',
        'core-domain-queue.empty': '尚未监测任何域名。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-domain-queue.${key}`,
      {},
      this.manifest.catalogs.en[`core-domain-queue.${key}`],
    );
  },
  render(data, helpers) {
    const wrap = document.createDocumentFragment();
    if (data.queue && data.queue.due > data.queue.per_run) {
      wrap.appendChild(
        helpers.note(
          helpers.i18n.t(
            'core-domain-queue.waiting',
            { due: data.queue.due, perRun: data.queue.per_run },
            '{{due, number}} domains are due, with {{perRun, number}} looked up per run.',
          ),
        ),
      );
    }
    if (!data.rows.length) {
      wrap.appendChild(
        helpers.note(
          helpers.i18n.t(
            'core-domain-queue.empty',
            {},
            'No domains are being watched.',
          ),
        ),
      );
    }
    return wrap;
  },
};
