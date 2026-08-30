/* Core card package: quality and canvas shape choices for generated images. */
window.ZogographAiImageRenderOptionsCard = {
  manifest: Object.freeze({
    id: 'core-ai-image-render-options',
    locales: ['core-ai-image-render-options.'],
    helpers: ['fieldRow', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-image-render-options.quality': 'Quality',
        'core-ai-image-render-options.shape': 'Shape',
      }),
      es: Object.freeze({
        'core-ai-image-render-options.quality': 'Calidad',
        'core-ai-image-render-options.shape': 'Forma',
      }),
      hi: Object.freeze({
        'core-ai-image-render-options.quality': 'गुणवत्ता',
        'core-ai-image-render-options.shape': 'आकार',
      }),
      ar: Object.freeze({
        'core-ai-image-render-options.quality': 'الجودة',
        'core-ai-image-render-options.shape': 'الشكل',
      }),
      zh: Object.freeze({
        'core-ai-image-render-options.quality': '质量',
        'core-ai-image-render-options.shape': '尺寸',
      }),
    }),
  }),
  render(input, helpers) {
    const { pictures } = input;
    const { fieldRow, i18n } = helpers;
    const t = (key, fallback) => i18n.t(key, {}, fallback);
    const quality = document.createElement('select');
    quality.id = 'aiQuality';
    for (const [key, meta] of Object.entries(pictures.qualities)) {
      const option = document.createElement('option');
      option.value = key;
      option.textContent = `${meta.label} — ${meta.what}`;
      option.selected = pictures.quality === key;
      quality.appendChild(option);
    }
    const shape = document.createElement('select');
    shape.id = 'aiShape';
    for (const [key, meta] of Object.entries(pictures.shapes)) {
      const option = document.createElement('option');
      option.value = key;
      option.textContent = `${meta.label} (${meta.width}×${meta.height})`;
      option.selected = pictures.shape === key;
      shape.appendChild(option);
    }
    return [
      fieldRow(
        t('core-ai-image-render-options.quality', 'Quality'),
        quality,
        '',
      ),
      fieldRow(t('core-ai-image-render-options.shape', 'Shape'), shape, ''),
    ];
  },
};
