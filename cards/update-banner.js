/* Core card package: a safe prompt to reload after a new deployment. */
window.ZogographUpdateBannerCard = {
  manifest: Object.freeze({
    id: 'core-update-banner',
    locales: ['core-update-banner.'],
    helpers: ['target', 'reload', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-update-banner.available':
          'A newer Zogograph update is available. Reload when you are ready.',
        'core-update-banner.compatibility':
          'A compatibility update is ready. Reload before continuing so this browser uses the matching dashboard.',
        'core-update-banner.reload': 'Reload now',
      }),
      es: Object.freeze({
        'core-update-banner.available':
          'Hay una actualización más reciente de Zogograph disponible. Recargue cuando esté listo.',
        'core-update-banner.compatibility':
          'Hay una actualización de compatibilidad lista. Recargue antes de continuar para usar el panel correspondiente.',
        'core-update-banner.reload': 'Recargar ahora',
      }),
      hi: Object.freeze({
        'core-update-banner.available':
          'Zogograph का नया अपडेट उपलब्ध है। तैयार होने पर पुनः लोड करें।',
        'core-update-banner.compatibility':
          'संगतता अपडेट तैयार है। जारी रखने से पहले पुनः लोड करें ताकि यह ब्राउज़र मिलान वाला डैशबोर्ड उपयोग करे।',
        'core-update-banner.reload': 'अभी पुनः लोड करें',
      }),
      ar: Object.freeze({
        'core-update-banner.available':
          'يتوفر تحديث أحدث لـ Zogograph. أعد التحميل عندما تكون جاهزًا.',
        'core-update-banner.compatibility':
          'تحديث توافق جاهز. أعد التحميل قبل المتابعة ليستخدم هذا المتصفح لوحة المعلومات المطابقة.',
        'core-update-banner.reload': 'أعد التحميل الآن',
      }),
      zh: Object.freeze({
        'core-update-banner.available':
          '有更新版本的 Zogograph 可用。准备好后请重新加载。',
        'core-update-banner.compatibility':
          '兼容性更新已就绪。请在继续前重新加载，以便此浏览器使用匹配的仪表板。',
        'core-update-banner.reload': '立即重新加载',
      }),
    }),
  }),

  render(required, helpers) {
    const { target, reload, i18n } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (!target) return;
    const text = document.createElement('span');
    text.textContent = required
      ? t(
          'core-update-banner.compatibility',
          {},
          'A compatibility update is ready. Reload before continuing so this browser uses the matching dashboard.',
        )
      : t(
          'core-update-banner.available',
          {},
          'A newer Zogograph update is available. Reload when you are ready.',
        );
    const button = document.createElement('button');
    button.type = 'button';
    button.textContent = t('core-update-banner.reload', {}, 'Reload now');
    button.addEventListener('click', reload);
    target.replaceChildren(text, button);
    target.hidden = false;
  },
};
