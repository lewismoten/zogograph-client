/* Core card package: saved provider/model price editor. */
window.ZogographAiPriceDialogCard = {
  manifest: Object.freeze({
    id: 'core-ai-price-dialog',
    locales: ['core-ai-price-dialog.'],
    helpers: ['fieldRow', 'showFormDialog', 'onSave', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-price-dialog.provider': 'Provider',
        'core-ai-price-dialog.model': 'Model',
        'core-ai-price-dialog.model_help':
          'Exactly as the provider names it — this is matched against what is sent.',
        'core-ai-price-dialog.input': 'Input, per million',
        'core-ai-price-dialog.cents_help': 'In cents.',
        'core-ai-price-dialog.output': 'Output, per million',
        'core-ai-price-dialog.output_help':
          'In cents. Usually several times the input price.',
        'core-ai-price-dialog.cached': 'Cached input, per million',
        'core-ai-price-dialog.cached_help':
          'In cents. Usually about a tenth of the input price, and most of the bill on a long conversation.',
        'core-ai-price-dialog.cache_write_5m':
          'Cache write (5 minutes), per million',
        'core-ai-price-dialog.cache_write_5m_help':
          'Anthropic prompt-cache write price, in cents.',
        'core-ai-price-dialog.cache_write_1h':
          'Cache write (1 hour), per million',
        'core-ai-price-dialog.cache_write_1h_help':
          'Anthropic long prompt-cache write price, in cents.',
        'core-ai-price-dialog.cache_refresh':
          'Cache hit / refresh, per million',
        'core-ai-price-dialog.cache_refresh_help':
          'Provider cache-hit or refresh price, in cents.',
        'core-ai-price-dialog.active_from': 'Active from',
        'core-ai-price-dialog.active_from_help': 'Prices apply from this date.',
        'core-ai-price-dialog.retired_on': 'Retired on',
        'core-ai-price-dialog.retired_on_help':
          'Leave blank until this price is retired.',
        'core-ai-price-dialog.edit': 'Edit remembered prices',
        'core-ai-price-dialog.add': 'Remember prices for a model',
      }),
      es: Object.freeze({
        'core-ai-price-dialog.provider': 'Proveedor',
        'core-ai-price-dialog.model': 'Modelo',
        'core-ai-price-dialog.model_help':
          'Exactamente como lo nombra el proveedor; así se compara con lo enviado.',
        'core-ai-price-dialog.input': 'Entrada, por millón',
        'core-ai-price-dialog.cents_help': 'En centavos.',
        'core-ai-price-dialog.output': 'Salida, por millón',
        'core-ai-price-dialog.output_help':
          'En centavos; normalmente varias veces el precio de entrada.',
        'core-ai-price-dialog.cached': 'Entrada en caché, por millón',
        'core-ai-price-dialog.cached_help':
          'En centavos; normalmente alrededor de una décima parte de la entrada y gran parte de una conversación larga.',
        'core-ai-price-dialog.cache_write_5m':
          'Escritura de caché (5 minutos), por millón',
        'core-ai-price-dialog.cache_write_5m_help':
          'Precio de escritura de caché de prompt de Anthropic, en centavos.',
        'core-ai-price-dialog.cache_write_1h':
          'Escritura de caché (1 hora), por millón',
        'core-ai-price-dialog.cache_write_1h_help':
          'Precio de escritura de prompt largo de Anthropic, en centavos.',
        'core-ai-price-dialog.cache_refresh':
          'Acierto o actualización de caché, por millón',
        'core-ai-price-dialog.cache_refresh_help':
          'Precio del proveedor para acierto o actualización de caché, en centavos.',
        'core-ai-price-dialog.active_from': 'Activo desde',
        'core-ai-price-dialog.active_from_help':
          'Los precios se aplican desde esta fecha.',
        'core-ai-price-dialog.retired_on': 'Retirado el',
        'core-ai-price-dialog.retired_on_help':
          'Déjelo vacío hasta retirar este precio.',
        'core-ai-price-dialog.edit': 'Editar precios guardados',
        'core-ai-price-dialog.add': 'Guardar precios para un modelo',
      }),
      hi: Object.freeze({
        'core-ai-price-dialog.provider': 'प्रदाता',
        'core-ai-price-dialog.model': 'मॉडल',
        'core-ai-price-dialog.model_help':
          'ठीक वही नाम जो प्रदाता उपयोग करता है — इसी का भेजे जाने वाले नाम से मिलान होता है।',
        'core-ai-price-dialog.input': 'इनपुट, प्रति मिलियन',
        'core-ai-price-dialog.cents_help': 'सेंट में।',
        'core-ai-price-dialog.output': 'आउटपुट, प्रति मिलियन',
        'core-ai-price-dialog.output_help':
          'सेंट में। सामान्यतः इनपुट मूल्य से कई गुना।',
        'core-ai-price-dialog.cached': 'कैश इनपुट, प्रति मिलियन',
        'core-ai-price-dialog.cached_help':
          'सेंट में। सामान्यतः इनपुट मूल्य का लगभग दसवां भाग और लंबी बातचीत के बिल का बड़ा हिस्सा।',
        'core-ai-price-dialog.cache_write_5m':
          'कैश लेखन (5 मिनट), प्रति मिलियन',
        'core-ai-price-dialog.cache_write_5m_help':
          'Anthropic प्रॉम्प्ट-कैश लेखन मूल्य, सेंट में।',
        'core-ai-price-dialog.cache_write_1h':
          'कैश लेखन (1 घंटा), प्रति मिलियन',
        'core-ai-price-dialog.cache_write_1h_help':
          'Anthropic लंबे प्रॉम्प्ट लेखन मूल्य, सेंट में।',
        'core-ai-price-dialog.cache_refresh': 'कैश हिट / रीफ्रेश, प्रति मिलियन',
        'core-ai-price-dialog.cache_refresh_help':
          'प्रदाता कैश-हिट या रीफ्रेश मूल्य, सेंट में।',
        'core-ai-price-dialog.active_from': 'से सक्रिय',
        'core-ai-price-dialog.active_from_help':
          'कीमतें इस तारीख से लागू होती हैं।',
        'core-ai-price-dialog.retired_on': 'इस तारीख को सेवानिवृत्त',
        'core-ai-price-dialog.retired_on_help':
          'इस कीमत के सेवानिवृत्त होने तक खाली छोड़ें।',
        'core-ai-price-dialog.edit': 'सहेजी कीमतें संपादित करें',
        'core-ai-price-dialog.add': 'मॉडल के लिए कीमतें याद रखें',
      }),
      ar: Object.freeze({
        'core-ai-price-dialog.provider': 'المزود',
        'core-ai-price-dialog.model': 'النموذج',
        'core-ai-price-dialog.model_help':
          'بالضبط كما يسميه المزود؛ يطابق ذلك الاسم المرسل.',
        'core-ai-price-dialog.input': 'الإدخال، لكل مليون',
        'core-ai-price-dialog.cents_help': 'بالسنتات.',
        'core-ai-price-dialog.output': 'الإخراج، لكل مليون',
        'core-ai-price-dialog.output_help':
          'بالسنتات، وعادة عدة أضعاف سعر الإدخال.',
        'core-ai-price-dialog.cached': 'الإدخال المخبأ، لكل مليون',
        'core-ai-price-dialog.cached_help':
          'بالسنتات؛ عادة نحو عُشر سعر الإدخال ومعظم فاتورة محادثة طويلة.',
        'core-ai-price-dialog.cache_write_5m':
          'كتابة التخزين المؤقت (5 دقائق)، لكل مليون',
        'core-ai-price-dialog.cache_write_5m_help':
          'سعر كتابة ذاكرة مطالبة Anthropic المؤقتة، بالسنتات.',
        'core-ai-price-dialog.cache_write_1h':
          'كتابة التخزين المؤقت (ساعة)، لكل مليون',
        'core-ai-price-dialog.cache_write_1h_help':
          'سعر كتابة مطالبة Anthropic طويلة، بالسنتات.',
        'core-ai-price-dialog.cache_refresh':
          'إصابة أو تحديث التخزين المؤقت، لكل مليون',
        'core-ai-price-dialog.cache_refresh_help':
          'سعر المزود لإصابة أو تحديث التخزين المؤقت، بالسنتات.',
        'core-ai-price-dialog.active_from': 'نشط من',
        'core-ai-price-dialog.active_from_help': 'تسري الأسعار من هذا التاريخ.',
        'core-ai-price-dialog.retired_on': 'أوقف في',
        'core-ai-price-dialog.retired_on_help':
          'اتركه فارغاً حتى يتوقف هذا السعر.',
        'core-ai-price-dialog.edit': 'تحرير الأسعار المحفوظة',
        'core-ai-price-dialog.add': 'حفظ أسعار لنموذج',
      }),
      zh: Object.freeze({
        'core-ai-price-dialog.provider': '提供商',
        'core-ai-price-dialog.model': '模型',
        'core-ai-price-dialog.model_help':
          '必须与提供商的名称完全一致；它会与实际发送的名称匹配。',
        'core-ai-price-dialog.input': '输入，每百万',
        'core-ai-price-dialog.cents_help': '单位为美分。',
        'core-ai-price-dialog.output': '输出，每百万',
        'core-ai-price-dialog.output_help':
          '单位为美分，通常是输入价格的数倍。',
        'core-ai-price-dialog.cached': '缓存输入，每百万',
        'core-ai-price-dialog.cached_help':
          '单位为美分，通常约为输入价格的十分之一，并占长对话账单的大部分。',
        'core-ai-price-dialog.cache_write_5m': '缓存写入（5 分钟），每百万',
        'core-ai-price-dialog.cache_write_5m_help':
          'Anthropic 提示词缓存写入价格，单位为美分。',
        'core-ai-price-dialog.cache_write_1h': '缓存写入（1 小时），每百万',
        'core-ai-price-dialog.cache_write_1h_help':
          'Anthropic 长提示词写入价格，单位为美分。',
        'core-ai-price-dialog.cache_refresh': '缓存命中／刷新，每百万',
        'core-ai-price-dialog.cache_refresh_help':
          '提供商缓存命中或刷新价格，单位为美分。',
        'core-ai-price-dialog.active_from': '生效日期',
        'core-ai-price-dialog.active_from_help': '价格从此日期开始适用。',
        'core-ai-price-dialog.retired_on': '退役日期',
        'core-ai-price-dialog.retired_on_help': '在此价格退役前请留空。',
        'core-ai-price-dialog.edit': '编辑已保存的价格',
        'core-ai-price-dialog.add': '记住模型价格',
      }),
    }),
  }),
  render({ row, providers, selectedProvider, selectedModel }, helpers) {
    const t = (key, fallback) =>
      helpers.i18n.t(`core-ai-price-dialog.${key}`, {}, fallback);
    const parts = [];
    const provider = document.createElement('select');
    for (const [key, meta] of Object.entries(providers)) {
      const option = document.createElement('option');
      option.value = key;
      option.textContent = meta.label ?? key;
      option.selected = (row?.provider ?? selectedProvider) === key;
      provider.appendChild(option);
    }
    parts.push(helpers.fieldRow(t('provider', 'Provider'), provider, ''));
    const model = document.createElement('input');
    model.type = 'text';
    model.className = 'wide-field';
    model.spellcheck = false;
    model.value = row?.model ?? selectedModel ?? '';
    parts.push(
      helpers.fieldRow(
        t('model', 'Model'),
        model,
        t(
          'model_help',
          'Exactly as the provider names it — this is matched against what is sent.',
        ),
      ),
    );
    const boxes = {};
    for (const [key, label, hint, fallback, help] of [
      ['in', 'input', 'cents_help', 'Input, per million', 'In cents.'],
      [
        'out',
        'output',
        'output_help',
        'Output, per million',
        'In cents. Usually several times the input price.',
      ],
      [
        'cached',
        'cached',
        'cached_help',
        'Cached input, per million',
        'In cents. Usually about a tenth of the input price, and most of the bill on a long conversation.',
      ],
      [
        'cache_write_5m',
        'cache_write_5m',
        'cache_write_5m_help',
        'Cache write (5 minutes), per million',
        'Anthropic prompt-cache write price, in cents.',
      ],
      [
        'cache_write_1h',
        'cache_write_1h',
        'cache_write_1h_help',
        'Cache write (1 hour), per million',
        'Anthropic long prompt-cache write price, in cents.',
      ],
      [
        'cache_refresh',
        'cache_refresh',
        'cache_refresh_help',
        'Cache hit / refresh, per million',
        'Provider cache-hit or refresh price, in cents.',
      ],
    ]) {
      const box = document.createElement('input');
      box.type = 'number';
      box.min = '0';
      box.step = '0.01';
      box.className = 'days';
      box.value = String(row?.[key] ?? 0);
      boxes[key] = box;
      parts.push(helpers.fieldRow(t(label, fallback), box, t(hint, help)));
    }
    const from = document.createElement('input');
    from.type = 'date';
    from.value = row?.active_from ?? new Date().toISOString().slice(0, 10);
    parts.push(
      helpers.fieldRow(
        t('active_from', 'Active from'),
        from,
        t('active_from_help', 'Prices apply from this date.'),
      ),
    );
    const retired = document.createElement('input');
    retired.type = 'date';
    retired.value = row?.retired_on ?? '';
    parts.push(
      helpers.fieldRow(
        t('retired_on', 'Retired on'),
        retired,
        t('retired_on_help', 'Leave blank until this price is retired.'),
      ),
    );
    return helpers.showFormDialog(
      row
        ? t('edit', 'Edit remembered prices')
        : t('add', 'Remember prices for a model'),
      parts,
      () =>
        helpers.onSave({
          price_id: row?.key ?? '',
          provider: provider.value,
          model: model.value.trim(),
          price_in: boxes.in.value,
          price_out: boxes.out.value,
          price_cached: boxes.cached.value,
          cache_write_5m: boxes.cache_write_5m.value,
          cache_write_1h: boxes.cache_write_1h.value,
          cache_refresh: boxes.cache_refresh.value,
          active_from: from.value,
          retired_on: retired.value,
        }),
    );
  },
};
