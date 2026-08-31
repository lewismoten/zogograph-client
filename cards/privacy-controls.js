/* Core card package: session-only controls that hide identifying account details. */
window.ZogographPrivacyControlsCard = {
  manifest: Object.freeze({
    id: 'core-privacy-controls',
    locales: ['core-privacy-controls.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-privacy-controls.hide_ips': 'Hide IPs',
        'core-privacy-controls.show_ips': 'Show IPs',
        'core-privacy-controls.hide_ips_title':
          'Hide IP addresses in this browser session',
        'core-privacy-controls.show_ips_title':
          'Show IP addresses again in this browser session',
        'core-privacy-controls.hide_account': 'Hide account',
        'core-privacy-controls.show_account': 'Show account',
        'core-privacy-controls.hide_account_title':
          'Hide this account name in this browser session',
        'core-privacy-controls.show_account_title':
          'Show this account name again in this browser session',
      }),
      es: Object.freeze({
        'core-privacy-controls.hide_ips': 'Ocultar IP',
        'core-privacy-controls.show_ips': 'Mostrar IP',
        'core-privacy-controls.hide_ips_title':
          'Ocultar direcciones IP en esta sesión del navegador',
        'core-privacy-controls.show_ips_title':
          'Volver a mostrar direcciones IP en esta sesión del navegador',
        'core-privacy-controls.hide_account': 'Ocultar cuenta',
        'core-privacy-controls.show_account': 'Mostrar cuenta',
        'core-privacy-controls.hide_account_title':
          'Ocultar este nombre de cuenta en esta sesión del navegador',
        'core-privacy-controls.show_account_title':
          'Volver a mostrar este nombre de cuenta en esta sesión del navegador',
      }),
      hi: Object.freeze({
        'core-privacy-controls.hide_ips': 'IP छिपाएँ',
        'core-privacy-controls.show_ips': 'IP दिखाएँ',
        'core-privacy-controls.hide_ips_title':
          'इस ब्राउज़र सत्र में IP पते छिपाएँ',
        'core-privacy-controls.show_ips_title':
          'इस ब्राउज़र सत्र में IP पते फिर से दिखाएँ',
        'core-privacy-controls.hide_account': 'खाता छिपाएँ',
        'core-privacy-controls.show_account': 'खाता दिखाएँ',
        'core-privacy-controls.hide_account_title':
          'इस ब्राउज़र सत्र में इस खाते का नाम छिपाएँ',
        'core-privacy-controls.show_account_title':
          'इस ब्राउज़र सत्र में इस खाते का नाम फिर से दिखाएँ',
      }),
      ar: Object.freeze({
        'core-privacy-controls.hide_ips': 'إخفاء عناوين IP',
        'core-privacy-controls.show_ips': 'إظهار عناوين IP',
        'core-privacy-controls.hide_ips_title':
          'إخفاء عناوين IP في جلسة المتصفح هذه',
        'core-privacy-controls.show_ips_title':
          'إظهار عناوين IP مجددًا في جلسة المتصفح هذه',
        'core-privacy-controls.hide_account': 'إخفاء الحساب',
        'core-privacy-controls.show_account': 'إظهار الحساب',
        'core-privacy-controls.hide_account_title':
          'إخفاء اسم هذا الحساب في جلسة المتصفح هذه',
        'core-privacy-controls.show_account_title':
          'إظهار اسم هذا الحساب مجددًا في جلسة المتصفح هذه',
      }),
      zh: Object.freeze({
        'core-privacy-controls.hide_ips': '隐藏 IP',
        'core-privacy-controls.show_ips': '显示 IP',
        'core-privacy-controls.hide_ips_title': '在此浏览器会话中隐藏 IP 地址',
        'core-privacy-controls.show_ips_title':
          '在此浏览器会话中再次显示 IP 地址',
        'core-privacy-controls.hide_account': '隐藏账户',
        'core-privacy-controls.show_account': '显示账户',
        'core-privacy-controls.hide_account_title':
          '在此浏览器会话中隐藏该账户名称',
        'core-privacy-controls.show_account_title':
          '在此浏览器会话中再次显示该账户名称',
      }),
    }),
  }),
  render(input, helpers) {
    return helpers.i18n.t(
      `core-privacy-controls.${input.key}`,
      {},
      input.fallback,
    );
  },
};
