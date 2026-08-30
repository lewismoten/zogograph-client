/* Core card package: previously generated AI pictures. */
window.ZogographAiAssetGalleryCard = {
  manifest: Object.freeze({
    id: 'core-ai-asset-gallery',
    locales: ['core-ai-asset-gallery.'],
    helpers: ['holder', 'api', 'note', 'humanBytes', 'humanTime', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-asset-gallery.unreadable':
          'The picture store could not be read.',
        'core-ai-asset-gallery.empty':
          'None yet. Anything generated is kept here at full size — the provider’s own link expires within days, so a picture that was paid for would otherwise be a dead link by the time anybody looked.',
        'core-ai-asset-gallery.open_full': 'Open it at full size',
      }),
      es: Object.freeze({
        'core-ai-asset-gallery.unreadable':
          'No se pudo leer el almacén de imágenes.',
        'core-ai-asset-gallery.empty': 'Aún no hay ninguna imagen generada.',
        'core-ai-asset-gallery.open_full': 'Abrir a tamaño completo',
      }),
      hi: Object.freeze({
        'core-ai-asset-gallery.unreadable': 'चित्र संग्रह पढ़ा नहीं जा सका।',
        'core-ai-asset-gallery.empty': 'अभी कोई बनाई हुई तस्वीर नहीं है।',
        'core-ai-asset-gallery.open_full': 'पूर्ण आकार में खोलें',
      }),
      ar: Object.freeze({
        'core-ai-asset-gallery.unreadable': 'تعذرت قراءة مخزن الصور.',
        'core-ai-asset-gallery.empty': 'لا توجد صور مُنشأة بعد.',
        'core-ai-asset-gallery.open_full': 'افتح بالحجم الكامل',
      }),
      zh: Object.freeze({
        'core-ai-asset-gallery.unreadable': '无法读取图片存储。',
        'core-ai-asset-gallery.empty': '尚未生成图片。',
        'core-ai-asset-gallery.open_full': '以完整尺寸打开',
      }),
    }),
  }),
  async render(input, helpers) {
    const { holder, api, note, humanBytes, humanTime, i18n } = helpers;
    const t = (key, fallback) => i18n.t(key, {}, fallback);
    let data;
    try {
      data = await api('ai-asset');
    } catch {
      holder.replaceChildren(
        note(
          t(
            'core-ai-asset-gallery.unreadable',
            'The picture store could not be read.',
          ),
        ),
      );
      return;
    }
    if (!data.assets.length) {
      holder.replaceChildren(
        note(
          t(
            'core-ai-asset-gallery.empty',
            'None yet. Anything generated is kept here at full size — the provider’s own link expires within days, so a picture that was paid for would otherwise be a dead link by the time anybody looked.',
          ),
        ),
      );
      return;
    }
    const grid = document.createElement('div');
    grid.className = 'asset-grid';
    for (const asset of data.assets) {
      const card = document.createElement('figure');
      card.className = 'asset';
      const link = document.createElement('a');
      link.href = `aiasset.php?id=${encodeURIComponent(asset.id)}`;
      link.target = '_blank';
      link.rel = 'noopener';
      link.title = t('core-ai-asset-gallery.open_full', 'Open it at full size');
      const image = document.createElement('img');
      image.src = link.href;
      image.alt = asset.prompt;
      image.loading = 'lazy';
      link.appendChild(image);
      card.appendChild(link);
      const caption = document.createElement('figcaption');
      caption.className = 'muted small';
      caption.textContent = asset.prompt;
      caption.title =
        `${asset.width}×${asset.height}, ${humanBytes(asset.bytes)}, ` +
        `${asset.style}${asset.cost ? `, ${asset.cost}` : ''} — ${humanTime(asset.at)}`;
      card.appendChild(caption);
      grid.appendChild(card);
    }
    holder.replaceChildren(grid);
  },
};
