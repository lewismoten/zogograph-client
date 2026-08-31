/* Core card package: page shell for the complete host service list. */
window.ZogographHostUnitsShellCard = {
  manifest: Object.freeze({
    id: 'core-host-units-shell',
    locales: ['core-host-units-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-host-units-shell.title': 'Every service',
        'core-host-units-shell.help':
          'The whole list, not just the watched ones. {{failed}} and {{masked}} are called out because they are the two states that are silently wrong: a failed unit uses no memory and no CPU, so it is absent from every other panel here rather than standing out in one, and a masked one cannot be started at all until somebody unmasks it.',
        'core-host-units-shell.failed': 'failed',
        'core-host-units-shell.masked': 'masked',
        'core-host-units-shell.running_not_enabled': 'running, not enabled',
        'core-host-units-shell.enabled_not_running': 'enabled, not running',
        'core-host-units-shell.running': 'running',
        'core-host-units-shell.stopped': 'stopped',
      }),
      es: Object.freeze({
        'core-host-units-shell.title': 'Todos los servicios',
        'core-host-units-shell.help':
          'La lista completa, no solo los vigilados. {{failed}} y {{masked}} se destacan porque son los dos estados silenciosamente incorrectos: una unidad fallida no usa memoria ni CPU, por lo que falta en todos los demás paneles en vez de resaltar en uno, y una unidad enmascarada no puede iniciarse hasta que alguien la desenmascare.',
        'core-host-units-shell.failed': 'fallido',
        'core-host-units-shell.masked': 'enmascarado',
        'core-host-units-shell.running_not_enabled':
          'en ejecución, no habilitado',
        'core-host-units-shell.enabled_not_running':
          'habilitado, no en ejecución',
        'core-host-units-shell.running': 'en ejecución',
        'core-host-units-shell.stopped': 'detenido',
      }),
      hi: Object.freeze({
        'core-host-units-shell.title': 'हर सेवा',
        'core-host-units-shell.help':
          'पूरी सूची, केवल निगरानी वाली नहीं। {{failed}} और {{masked}} को अलग दिखाया जाता है क्योंकि ये दो स्थितियाँ चुपचाप गलत होती हैं: विफल यूनिट मेमोरी या CPU उपयोग नहीं करती, इसलिए किसी एक पैनल में उभरने के बजाय बाकी सभी पैनलों से गायब रहती है; और मास्क की गई यूनिट को कोई अनमास्क किए बिना शुरू नहीं किया जा सकता।',
        'core-host-units-shell.failed': 'विफल',
        'core-host-units-shell.masked': 'मास्क किया गया',
        'core-host-units-shell.running_not_enabled': 'चल रहा है, सक्षम नहीं',
        'core-host-units-shell.enabled_not_running': 'सक्षम है, चल नहीं रहा',
        'core-host-units-shell.running': 'चल रहा है',
        'core-host-units-shell.stopped': 'रुका हुआ',
      }),
      ar: Object.freeze({
        'core-host-units-shell.title': 'كل خدمة',
        'core-host-units-shell.help':
          'القائمة الكاملة لا الخدمات المراقبة فقط. يُبرَز {{failed}} و{{masked}} لأنهما الحالتان الخاطئتان بصمت: الوحدة الفاشلة لا تستخدم ذاكرة أو معالجًا، لذا تختفي من كل اللوحات الأخرى هنا بدل أن تبرز في واحدة، والوحدة المحجوبة لا يمكن تشغيلها إطلاقًا حتى يزيل أحد حجبها.',
        'core-host-units-shell.failed': 'فاشل',
        'core-host-units-shell.masked': 'محجوب',
        'core-host-units-shell.running_not_enabled': 'يعمل، غير مُمكّن',
        'core-host-units-shell.enabled_not_running': 'مُمكّن، لا يعمل',
        'core-host-units-shell.running': 'يعمل',
        'core-host-units-shell.stopped': 'متوقف',
      }),
      zh: Object.freeze({
        'core-host-units-shell.title': '每项服务',
        'core-host-units-shell.help':
          '完整列表，而不只是受监视的服务。{{failed}} 和 {{masked}} 会被特别标出，因为它们是两种悄然出错的状态：失败的单元不使用内存或 CPU，因此不会在某个面板中突出，而是从这里的其他所有面板中消失；被屏蔽的单元在有人解除屏蔽前根本无法启动。',
        'core-host-units-shell.failed': '失败',
        'core-host-units-shell.masked': '已屏蔽',
        'core-host-units-shell.running_not_enabled': '正在运行，未启用',
        'core-host-units-shell.enabled_not_running': '已启用，未运行',
        'core-host-units-shell.running': '正在运行',
        'core-host-units-shell.stopped': '已停止',
      }),
    }),
  }),
  text(key, valuesOrHelpers = {}, maybeHelpers = null) {
    const helpers = maybeHelpers || valuesOrHelpers;
    const values = maybeHelpers ? valuesOrHelpers : {};
    return helpers.i18n.t(
      `core-host-units-shell.${key}`,
      values,
      this.manifest.catalogs.en[`core-host-units-shell.${key}`],
    );
  },
  render() {},
};
