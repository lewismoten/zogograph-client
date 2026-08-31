/* Core card package: enabling image generation and its provider credential. */
window.ZogographAiImageProviderCard = {
  manifest: Object.freeze({
    id: 'core-ai-image-provider',
    locales: ['core-ai-image-provider.'],
    helpers: ['fieldRow', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-image-provider.enabled': 'Let it make pictures',
        'core-ai-image-provider.key': 'DeepAI key',
        'core-ai-image-provider.key_placeholder': 'your DeepAI key',
        'core-ai-image-provider.enabled_help':
          'Off means the picture tools are not offered at all. Each one costs money per call and asks your permission every time.',
        'core-ai-image-provider.key_unchanged': '{{mask}} (unchanged)',
        'core-ai-image-provider.key_kept':
          'A key is set. Leaving this blank keeps it.',
        'core-ai-image-provider.key_required':
          'From deepai.org. Without one the picture tools stay hidden.',
      }),
      es: Object.freeze({
        'core-ai-image-provider.enabled': 'Permitir que cree imágenes',
        'core-ai-image-provider.key': 'Clave de DeepAI',
        'core-ai-image-provider.key_placeholder': 'su clave de DeepAI',
        'core-ai-image-provider.enabled_help':
          'Al desactivarlo, las herramientas de imágenes no se ofrecen en absoluto. Cada una cuesta dinero por llamada y pide permiso cada vez.',
        'core-ai-image-provider.key_unchanged': '{{mask}} (sin cambios)',
        'core-ai-image-provider.key_kept':
          'Hay una clave configurada. Dejarlo vacío la conserva.',
        'core-ai-image-provider.key_required':
          'De deepai.org. Sin una, las herramientas de imágenes permanecen ocultas.',
      }),
      hi: Object.freeze({
        'core-ai-image-provider.enabled': 'इसे चित्र बनाने दें',
        'core-ai-image-provider.key': 'DeepAI कुंजी',
        'core-ai-image-provider.key_placeholder': 'आपकी DeepAI कुंजी',
        'core-ai-image-provider.enabled_help':
          'बंद होने पर चित्र उपकरण बिल्कुल उपलब्ध नहीं होते। प्रत्येक कॉल पर पैसा लगता है और हर बार आपकी अनुमति मांगी जाती है।',
        'core-ai-image-provider.key_unchanged': '{{mask}} (अपरिवर्तित)',
        'core-ai-image-provider.key_kept':
          'एक कुंजी सेट है। इसे खाली छोड़ने पर वह बनी रहती है।',
        'core-ai-image-provider.key_required':
          'deepai.org से। इसके बिना चित्र उपकरण छिपे रहते हैं।',
      }),
      ar: Object.freeze({
        'core-ai-image-provider.enabled': 'اسمح لها بإنشاء صور',
        'core-ai-image-provider.key': 'مفتاح DeepAI',
        'core-ai-image-provider.key_placeholder': 'مفتاح DeepAI الخاص بك',
        'core-ai-image-provider.enabled_help':
          'عند إيقافه لا تُعرض أدوات الصور مطلقًا. كل واحدة تكلف مالًا لكل طلب وتطلب إذنك في كل مرة.',
        'core-ai-image-provider.key_unchanged': '{{mask}} (دون تغيير)',
        'core-ai-image-provider.key_kept':
          'يوجد مفتاح مضبوط. ترك هذا فارغًا يبقيه.',
        'core-ai-image-provider.key_required':
          'من deepai.org. بدونه تبقى أدوات الصور مخفية.',
      }),
      zh: Object.freeze({
        'core-ai-image-provider.enabled': '允许它生成图片',
        'core-ai-image-provider.key': 'DeepAI 密钥',
        'core-ai-image-provider.key_placeholder': '您的 DeepAI 密钥',
        'core-ai-image-provider.enabled_help':
          '关闭后，图片工具不会提供。每次调用都会产生费用，并且每次都要求您的许可。',
        'core-ai-image-provider.key_unchanged': '{{mask}}（未更改）',
        'core-ai-image-provider.key_kept': '已设置密钥。留空可保留它。',
        'core-ai-image-provider.key_required':
          '来自 deepai.org。没有密钥，图片工具将保持隐藏。',
      }),
    }),
  }),
  render(input, helpers) {
    const { pictures } = input;
    const { fieldRow, i18n } = helpers;
    const on = document.createElement('input');
    on.type = 'checkbox';
    on.id = 'aiPicturesOn';
    on.checked = pictures.enabled;
    const key = document.createElement('input');
    key.type = 'password';
    key.id = 'aiPictureKey';
    key.className = 'wide-field';
    key.autocomplete = 'off';
    key.placeholder = pictures.has_key
      ? i18n.t(
          'core-ai-image-provider.key_unchanged',
          { mask: '•'.repeat(20) },
          '{{mask}} (unchanged)',
        )
      : i18n.t('core-ai-image-provider.key_placeholder', {}, 'your DeepAI key');
    return [
      fieldRow(
        i18n.t('core-ai-image-provider.enabled', {}, 'Let it make pictures'),
        on,
        i18n.t(
          'core-ai-image-provider.enabled_help',
          {},
          'Off means the picture tools are not offered at all. Each one costs money per call and asks your permission every time.',
        ),
      ),
      fieldRow(
        i18n.t('core-ai-image-provider.key', {}, 'DeepAI key'),
        key,
        pictures.has_key
          ? i18n.t(
              'core-ai-image-provider.key_kept',
              {},
              'A key is set. Leaving this blank keeps it.',
            )
          : i18n.t(
              'core-ai-image-provider.key_required',
              {},
              'From deepai.org. Without one the picture tools stay hidden.',
            ),
      ),
    ];
  },
};
