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
      }),
      es: Object.freeze({
        'core-ai-image-provider.enabled': 'Permitir que cree imágenes',
        'core-ai-image-provider.key': 'Clave de DeepAI',
        'core-ai-image-provider.key_placeholder': 'su clave de DeepAI',
      }),
      hi: Object.freeze({
        'core-ai-image-provider.enabled': 'इसे चित्र बनाने दें',
        'core-ai-image-provider.key': 'DeepAI कुंजी',
        'core-ai-image-provider.key_placeholder': 'आपकी DeepAI कुंजी',
      }),
      ar: Object.freeze({
        'core-ai-image-provider.enabled': 'اسمح لها بإنشاء صور',
        'core-ai-image-provider.key': 'مفتاح DeepAI',
        'core-ai-image-provider.key_placeholder': 'مفتاح DeepAI الخاص بك',
      }),
      zh: Object.freeze({
        'core-ai-image-provider.enabled': '允许它生成图片',
        'core-ai-image-provider.key': 'DeepAI 密钥',
        'core-ai-image-provider.key_placeholder': '您的 DeepAI 密钥',
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
          'ai.images_key_unchanged',
          { mask: '•'.repeat(20) },
          '{{mask}} (unchanged)',
        )
      : i18n.t('core-ai-image-provider.key_placeholder', {}, 'your DeepAI key');
    return [
      fieldRow(
        i18n.t('core-ai-image-provider.enabled', {}, 'Let it make pictures'),
        on,
        i18n.t(
          'ai.images_enabled_help',
          {},
          'Off means the picture tools are not offered at all. Each one costs money per call and asks your permission every time.',
        ),
      ),
      fieldRow(
        i18n.t('core-ai-image-provider.key', {}, 'DeepAI key'),
        key,
        pictures.has_key
          ? i18n.t(
              'ai.images_key_kept',
              {},
              'A key is set. Leaving this blank keeps it.',
            )
          : i18n.t(
              'ai.images_key_required',
              {},
              'From deepai.org. Without one the picture tools stay hidden.',
            ),
      ),
    ];
  },
};
