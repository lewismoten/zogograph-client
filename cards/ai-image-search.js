/* Core card package: reaction-image search provider connection and quota. */
window.ZogographAiImageSearchCard = {
  manifest: Object.freeze({
    id: 'core-ai-image-search',
    locales: ['core-ai-image-search.'],
    helpers: ['fieldRow', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-image-search.service': 'Search service',
        'core-ai-image-search.key': 'Search key',
        'core-ai-image-search.endpoint': 'Endpoint',
        'core-ai-image-search.rate': 'Searches per hour',
        'core-ai-image-search.key_unchanged': '{{mask}} (unchanged)',
        'core-ai-image-search.key_placeholder': 'the key for that service',
        'core-ai-image-search.key_kept':
          'A key is set. Leaving this blank keeps it.',
        'core-ai-image-search.key_required': 'Without one, reactions stay off.',
        'core-ai-image-search.rate_help':
          "Separate from the picture limit above, because the two cost different things: a search is a free read against somebody else's quota, a picture is a bill. A hundred is what these services publish as their free allowance and is the most this will do — going past it gets the key throttled, which breaks the feature for everyone rather than costing money. Set it lower if you share the key.",
        'core-ai-image-search.rate_used': '{{count, number}} in the last hour.',
      }),
      es: Object.freeze({
        'core-ai-image-search.service': 'Servicio de búsqueda',
        'core-ai-image-search.key': 'Clave de búsqueda',
        'core-ai-image-search.endpoint': 'Punto de conexión',
        'core-ai-image-search.rate': 'Búsquedas por hora',
        'core-ai-image-search.key_unchanged': '{{mask}} (sin cambios)',
        'core-ai-image-search.key_placeholder': 'la clave de ese servicio',
        'core-ai-image-search.key_kept':
          'Hay una clave configurada. Dejarlo vacío la conserva.',
        'core-ai-image-search.key_required':
          'Sin una, las reacciones permanecen apagadas.',
        'core-ai-image-search.rate_help':
          'Es independiente del límite de imágenes anterior porque ambos cuestan cosas distintas: una búsqueda es una lectura gratuita contra la cuota de otra persona; una imagen es un cargo. Cien es la asignación gratuita que estos servicios publican y es el máximo que hará esto; superarlo limita la clave, lo que rompe la función para todos en lugar de costar dinero. Redúzcalo si comparte la clave.',
        'core-ai-image-search.rate_used':
          '{{count, number}} en la última hora.',
      }),
      hi: Object.freeze({
        'core-ai-image-search.service': 'खोज सेवा',
        'core-ai-image-search.key': 'खोज कुंजी',
        'core-ai-image-search.endpoint': 'एंडपॉइंट',
        'core-ai-image-search.rate': 'प्रति घंटा खोजें',
        'core-ai-image-search.key_unchanged': '{{mask}} (अपरिवर्तित)',
        'core-ai-image-search.key_placeholder': 'उस सेवा की कुंजी',
        'core-ai-image-search.key_kept':
          'एक कुंजी सेट है। इसे खाली छोड़ने पर वह बनी रहती है।',
        'core-ai-image-search.key_required':
          'इसके बिना प्रतिक्रियाएँ बंद रहती हैं।',
        'core-ai-image-search.rate_help':
          'यह ऊपर दी गई चित्र सीमा से अलग है क्योंकि दोनों की लागत अलग है: खोज किसी और के कोटे के विरुद्ध एक मुफ्त रीड है, चित्र बिल है। सौ वह मुफ्त भत्ता है जो ये सेवाएँ प्रकाशित करती हैं और यही अधिकतम होगा — इसे पार करने पर कुंजी थ्रॉटल हो जाती है, जिससे पैसे लगने के बजाय सभी के लिए सुविधा टूटती है। कुंजी साझा करते हों तो इसे कम रखें।',
        'core-ai-image-search.rate_used': 'पिछले घंटे में {{count, number}}।',
      }),
      ar: Object.freeze({
        'core-ai-image-search.service': 'خدمة البحث',
        'core-ai-image-search.key': 'مفتاح البحث',
        'core-ai-image-search.endpoint': 'نقطة النهاية',
        'core-ai-image-search.rate': 'عمليات البحث في الساعة',
        'core-ai-image-search.key_unchanged': '{{mask}} (دون تغيير)',
        'core-ai-image-search.key_placeholder': 'مفتاح تلك الخدمة',
        'core-ai-image-search.key_kept':
          'يوجد مفتاح مضبوط. ترك هذا فارغًا يبقيه.',
        'core-ai-image-search.key_required': 'بدونه تظل التفاعلات معطلة.',
        'core-ai-image-search.rate_help':
          'هذا منفصل عن حد الصور أعلاه لأنهما يكلفان أشياء مختلفة: البحث قراءة مجانية مقابل حصة شخص آخر، أما الصورة ففاتورة. مئة هي الحصة المجانية التي تنشرها هذه الخدمات وهي الحد الأقصى هنا — تجاوزها يقيّد المفتاح، فيعطّل الميزة للجميع بدلاً من أن يكلف مالًا. اجعله أقل إذا كنت تشارك المفتاح.',
        'core-ai-image-search.rate_used':
          '{{count, number}} في الساعة الماضية.',
      }),
      zh: Object.freeze({
        'core-ai-image-search.service': '搜索服务',
        'core-ai-image-search.key': '搜索密钥',
        'core-ai-image-search.endpoint': '端点',
        'core-ai-image-search.rate': '每小时搜索次数',
        'core-ai-image-search.key_unchanged': '{{mask}}（未更改）',
        'core-ai-image-search.key_placeholder': '该服务的密钥',
        'core-ai-image-search.key_kept': '已设置密钥。留空可保留它。',
        'core-ai-image-search.key_required': '没有密钥，反应功能将保持关闭。',
        'core-ai-image-search.rate_help':
          '此项与上方图片限制分开，因为两者成本不同：搜索是对他人配额的一次免费读取，而图片会产生账单。100 是这些服务公布的免费配额，也是此处的最大值——超过它会使密钥受到限流，导致该功能对所有人失效，而不是花更多钱。如果共用密钥，请设置得更低。',
        'core-ai-image-search.rate_used': '过去一小时内 {{count, number}} 次。',
      }),
    }),
  }),
  render(input, helpers) {
    const { pictures } = input;
    const { fieldRow, i18n } = helpers;
    const t = (key, vars, fallback) => i18n.t(key, vars, fallback);
    const service = document.createElement('select');
    service.id = 'aiSearchService';
    for (const [key, meta] of Object.entries(pictures.services)) {
      const option = document.createElement('option');
      option.value = key;
      option.textContent = meta.label;
      option.selected = pictures.search_service === key;
      service.appendChild(option);
    }
    const serviceNote = document.createElement('div');
    serviceNote.className = 'muted small';
    const key = document.createElement('input');
    key.type = 'password';
    key.id = 'aiSearchKey';
    key.className = 'wide-field';
    key.autocomplete = 'off';
    key.placeholder = pictures.has_search_key
      ? i18n.t(
          'core-ai-image-search.key_unchanged',
          { mask: '•'.repeat(20) },
          '{{mask}} (unchanged)',
        )
      : i18n.t(
          'core-ai-image-search.key_placeholder',
          {},
          'the key for that service',
        );
    const base = document.createElement('input');
    base.type = 'text';
    base.id = 'aiSearchBase';
    base.className = 'wide-field';
    base.spellcheck = false;
    base.value = pictures.search_base || '';
    const rate = document.createElement('input');
    rate.type = 'number';
    rate.id = 'aiSearchRate';
    rate.min = '0';
    rate.max = '100';
    rate.className = 'days';
    rate.value = String(pictures.searches_per_hour);
    const sync = () => {
      const meta = pictures.services[service.value];
      serviceNote.textContent = meta?.note ?? '';
      base.placeholder = meta?.base ?? '';
    };
    service.addEventListener('change', sync);
    sync();
    const rateHelp =
      i18n.t(
        'core-ai-image-search.rate_help',
        {},
        "Separate from the picture limit above, because the two cost different things: a search is a free read against somebody else's quota, a picture is a bill. A hundred is what these services publish as their free allowance and is the most this will do — going past it gets the key throttled, which breaks the feature for everyone rather than costing money. Set it lower if you share the key.",
      ) +
      (pictures.searches
        ? ` ${i18n.t('core-ai-image-search.rate_used', { count: pictures.searches.used }, '{{count, number}} in the last hour.')}`
        : '');
    return [
      fieldRow(
        t('core-ai-image-search.service', {}, 'Search service'),
        service,
        '',
      ),
      serviceNote,
      fieldRow(
        t('core-ai-image-search.key', {}, 'Search key'),
        key,
        pictures.has_search_key
          ? i18n.t(
              'core-ai-image-search.key_kept',
              {},
              'A key is set. Leaving this blank keeps it.',
            )
          : i18n.t(
              'core-ai-image-search.key_required',
              {},
              'Without one, reactions stay off.',
            ),
      ),
      fieldRow(t('core-ai-image-search.endpoint', {}, 'Endpoint'), base, ''),
      fieldRow(
        t('core-ai-image-search.rate', {}, 'Searches per hour'),
        rate,
        rateHelp,
      ),
    ];
  },
};
