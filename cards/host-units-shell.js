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
        'core-host-units-shell.sign_in': 'Sign in to see the service list.',
        'core-host-units-shell.host_reading':
          'From the host reading of {{when}}.',
        'core-host-units-shell.empty_attention':
          'Nothing is failed, masked, or running when it should not be. That is the answer this page exists to give.',
        'core-host-units-shell.empty_filters':
          'Nothing matches those filters. The services are still there — only what is drawn has changed.',
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
        'core-host-units-shell.sign_in':
          'Inicie sesión para ver la lista de servicios.',
        'core-host-units-shell.host_reading':
          'Según la lectura del host de {{when}}.',
        'core-host-units-shell.empty_attention':
          'No hay nada fallido, enmascarado ni en ejecución cuando no debería estarlo. Esa es la respuesta que esta página existe para dar.',
        'core-host-units-shell.empty_filters':
          'Nada coincide con esos filtros. Los servicios siguen ahí; solo ha cambiado lo que se muestra.',
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
        'core-host-units-shell.sign_in': 'सेवा सूची देखने के लिए साइन इन करें।',
        'core-host-units-shell.host_reading': '{{when}} के होस्ट रीडिंग से।',
        'core-host-units-shell.empty_attention':
          'कुछ भी विफल, मास्क या अपेक्षा के विपरीत चल नहीं रहा है। यही उत्तर देने के लिए यह पृष्ठ है।',
        'core-host-units-shell.empty_filters':
          'इन फ़िल्टरों से कुछ नहीं मिलता। सेवाएं अभी भी हैं — केवल दिखाई जाने वाली चीज़ बदली है।',
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
        'core-host-units-shell.sign_in': 'سجّل الدخول لرؤية قائمة الخدمات.',
        'core-host-units-shell.host_reading': 'من قراءة المضيف في {{when}}.',
        'core-host-units-shell.empty_attention':
          'لا توجد خدمة فاشلة أو محجوبة أو تعمل حين لا ينبغي لها ذلك. هذه هي الإجابة التي وُجدت هذه الصفحة لتقديمها.',
        'core-host-units-shell.empty_filters':
          'لا شيء يطابق هذه المرشحات. الخدمات ما زالت موجودة — الذي تغيّر هو المعروض فقط.',
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
        'core-host-units-shell.sign_in': '登录后可查看服务列表。',
        'core-host-units-shell.host_reading': '来自 {{when}} 的主机读数。',
        'core-host-units-shell.empty_attention':
          '没有失败、被屏蔽或在不应运行时运行的服务。这正是此页面要回答的问题。',
        'core-host-units-shell.empty_filters':
          '没有内容匹配这些筛选条件。服务仍然存在——只改变了显示内容。',
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
