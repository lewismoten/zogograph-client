window.ZogographDomainWhoisAvailabilityCard = {
  manifest: Object.freeze({
    id: 'core-domain-whois-availability',
    locales: ['core-domain-whois-availability.'],
    helpers: ['note', 'missingCommandNote', 'showListDialog', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-domain-whois-availability.button': '⚠ WHOIS availability',
        'core-domain-whois-availability.help':
          'Explain how domain expiry lookups run',
        'core-domain-whois-availability.host_only':
          'The root collector can run WHOIS. This web account cannot, so the collector fetches expiry details on its normal run.',
        'core-domain-whois-availability.unavailable':
          'This web account and the latest host capability sweep do not show WHOIS as available.',
        'core-domain-whois-availability.title': 'WHOIS availability',
        'core-domain-whois-availability.note':
          "The dashboard separates the web account's permissions from the host collector's tools.",
        'core-domain-whois-availability.account_or_host':
          'this account or the host',
      }),
      es: Object.freeze({
        'core-domain-whois-availability.button': '⚠ Disponibilidad de WHOIS',
        'core-domain-whois-availability.help':
          'Explica cómo se realizan las consultas de caducidad de dominios',
        'core-domain-whois-availability.host_only':
          'El recopilador de root puede ejecutar WHOIS. Esta cuenta web no puede, por lo que el recopilador obtiene los detalles de caducidad en su ejecución normal.',
        'core-domain-whois-availability.unavailable':
          'Esta cuenta web y el último barrido de capacidades del host no muestran que WHOIS esté disponible.',
        'core-domain-whois-availability.title': 'Disponibilidad de WHOIS',
        'core-domain-whois-availability.note':
          'El panel separa los permisos de la cuenta web de las herramientas del recopilador del host.',
        'core-domain-whois-availability.account_or_host':
          'esta cuenta o el host',
      }),
      hi: Object.freeze({
        'core-domain-whois-availability.button': '⚠ WHOIS उपलब्धता',
        'core-domain-whois-availability.help':
          'बताता है कि डोमेन समाप्ति लुकअप कैसे चलते हैं',
        'core-domain-whois-availability.host_only':
          'root कलेक्टर WHOIS चला सकता है। यह वेब खाता नहीं चला सकता, इसलिए कलेक्टर अपने सामान्य रन में समाप्ति विवरण प्राप्त करता है।',
        'core-domain-whois-availability.unavailable':
          'यह वेब खाता और नवीनतम होस्ट क्षमता स्वीप WHOIS को उपलब्ध नहीं दिखाते हैं।',
        'core-domain-whois-availability.title': 'WHOIS उपलब्धता',
        'core-domain-whois-availability.note':
          'डैशबोर्ड वेब खाते की अनुमतियों को होस्ट कलेक्टर के टूल से अलग रखता है।',
        'core-domain-whois-availability.account_or_host': 'यह खाता या होस्ट',
      }),
      ar: Object.freeze({
        'core-domain-whois-availability.button': '⚠ توفر WHOIS',
        'core-domain-whois-availability.help':
          'يوضح كيفية عمل عمليات بحث انتهاء النطاق',
        'core-domain-whois-availability.host_only':
          'يمكن لجامع root تشغيل WHOIS. لا يستطيع حساب الويب هذا ذلك، لذلك يجلب الجامع تفاصيل الانتهاء في تشغيله المعتاد.',
        'core-domain-whois-availability.unavailable':
          'لا يُظهر حساب الويب هذا ولا أحدث مسح لإمكانات المضيف أن WHOIS متاح.',
        'core-domain-whois-availability.title': 'توفر WHOIS',
        'core-domain-whois-availability.note':
          'تفصل لوحة المعلومات أذونات حساب الويب عن أدوات جامع المضيف.',
        'core-domain-whois-availability.account_or_host':
          'هذا الحساب أو المضيف',
      }),
      zh: Object.freeze({
        'core-domain-whois-availability.button': '⚠ WHOIS 可用性',
        'core-domain-whois-availability.help': '说明域名到期查询如何运行',
        'core-domain-whois-availability.host_only':
          'root 采集器可以运行 WHOIS。此 Web 账户不能，因此采集器会在常规运行中获取到期详情。',
        'core-domain-whois-availability.unavailable':
          '此 Web 账户和最近的主机功能扫描均未显示 WHOIS 可用。',
        'core-domain-whois-availability.title': 'WHOIS 可用性',
        'core-domain-whois-availability.note':
          '仪表板将 Web 账户的权限与主机采集器的工具分开。',
        'core-domain-whois-availability.account_or_host': '此账户或主机',
      }),
    }),
  }),
  render(data, helpers) {
    if (data.have_whois) return document.createDocumentFragment();
    const availability = document.createElement('button');
    availability.className = 'link domain-capability';
    availability.textContent = helpers.i18n.t(
      'core-domain-whois-availability.button',
      {},
      '⚠ WHOIS availability',
    );
    availability.title = helpers.i18n.t(
      'core-domain-whois-availability.help',
      {},
      'Explain how domain expiry lookups run',
    );
    availability.addEventListener('click', () => {
      const detail = [
        helpers.note(
          data.host_has_whois
            ? helpers.i18n.t(
                'core-domain-whois-availability.host_only',
                {},
                'The root collector can run WHOIS.',
              )
            : helpers.i18n.t(
                'core-domain-whois-availability.unavailable',
                {},
                'WHOIS is not available.',
              ),
        ),
      ];
      if (data.missing) {
        detail.push(
          helpers.missingCommandNote(
            data.missing,
            helpers.i18n.t(
              'core-domain-whois-availability.account_or_host',
              {},
              'this account or the host',
            ),
          ),
        );
      }
      helpers.showListDialog(
        helpers.i18n.t(
          'core-domain-whois-availability.title',
          {},
          'WHOIS availability',
        ),
        detail,
        helpers.i18n.t(
          'core-domain-whois-availability.note',
          {},
          "The dashboard separates the web account's permissions from the host collector's tools.",
        ),
      );
    });
    return availability;
  },
};
