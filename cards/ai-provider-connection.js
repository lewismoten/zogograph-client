/* Core card package: provider credentials and the selected available model. */
window.ZogographAiProviderConnectionCard = {
  manifest: Object.freeze({
    id: 'core-ai-provider-connection',
    locales: ['core-ai-provider-connection.'],
    helpers: ['fieldRow', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-provider-connection.provider_label': 'Provider',
        'core-ai-provider-connection.address_label': 'Address',
        'core-ai-provider-connection.address_help':
          "Leave blank for the provider's own. Point it elsewhere for a gateway, a proxy, or a model running on this machine.",
        'core-ai-provider-connection.api_key_label': 'API key',
        'core-ai-provider-connection.api_key_unchanged': '{{mask}} (unchanged)',
        'core-ai-provider-connection.api_key_set_help':
          'A key is set. It is never sent back to this page, so leaving this blank keeps the existing one — type a new one to replace it.',
        'core-ai-provider-connection.api_key_empty_help':
          'Stored in lib/config.php, which is outside the web root. A local model needs none.',
        'core-ai-provider-connection.selected_provider_help':
          "Choose which provider's models to show.",
        'core-ai-provider-connection.available_model_label': 'Available model',
        'core-ai-provider-connection.available_model_help':
          'Models supplied by the provider or added on Available Models.',
        'core-ai-provider-connection.no_available_models':
          'No available models yet — add one on Available Models',
        'core-ai-provider-connection.compatible_keyless_save_help':
          'An Ollama or OpenAI-compatible connection can be saved without an API key.',
        'core-ai-provider-connection.provider_save_unavailable_config':
          'Connection saving is unavailable because PHP cannot write the configuration store.',
        'core-ai-provider-connection.provider_save_unavailable_role':
          'Connection saving is unavailable for your current account. An administrator can save it.',
        'core-ai-provider-connection.provider_save_ready':
          'This connection can be saved without an API key. Saving also keeps the settings on the other Assistant pages.',
      }),
      es: Object.freeze({
        'core-ai-provider-connection.provider_label': 'Proveedor',
        'core-ai-provider-connection.address_label': 'Dirección',
        'core-ai-provider-connection.address_help':
          'Déjela vacía para usar la propia del proveedor. Indique otra para una puerta de enlace, un proxy o un modelo que se ejecute en esta máquina.',
        'core-ai-provider-connection.api_key_label': 'Clave de API',
        'core-ai-provider-connection.api_key_unchanged':
          '{{mask}} (sin cambios)',
        'core-ai-provider-connection.api_key_set_help':
          'Hay una clave configurada. Nunca se devuelve a esta página; déjela vacía para conservarla o escriba una nueva para reemplazarla.',
        'core-ai-provider-connection.api_key_empty_help':
          'Se guarda en lib/config.php, fuera de la raíz web. Un modelo local no necesita ninguna.',
        'core-ai-provider-connection.selected_provider_help':
          'Elija el proveedor cuyos modelos desea mostrar.',
        'core-ai-provider-connection.available_model_label':
          'Modelo disponible',
        'core-ai-provider-connection.available_model_help':
          'Modelos proporcionados por el proveedor o añadidos en Modelos disponibles.',
        'core-ai-provider-connection.no_available_models':
          'Aún no hay modelos disponibles; añada uno en Modelos disponibles',
        'core-ai-provider-connection.compatible_keyless_save_help':
          'Una conexión Ollama o compatible con OpenAI se puede guardar sin una clave de API.',
        'core-ai-provider-connection.provider_save_unavailable_config':
          'No se puede guardar la conexión porque PHP no puede escribir en el almacén de configuración.',
        'core-ai-provider-connection.provider_save_unavailable_role':
          'No puede guardar conexiones con su cuenta actual. Un administrador puede guardarla.',
        'core-ai-provider-connection.provider_save_ready':
          'Esta conexión se puede guardar sin una clave de API. Al guardarla también se conservan los ajustes de las otras páginas del asistente.',
      }),
      hi: Object.freeze({
        'core-ai-provider-connection.provider_label': 'प्रदाता',
        'core-ai-provider-connection.address_label': 'पता',
        'core-ai-provider-connection.address_help':
          'प्रदाता के अपने पते के लिए इसे खाली छोड़ें। गेटवे, प्रॉक्सी या इस मशीन पर चल रहे मॉडल के लिए दूसरा पता दें।',
        'core-ai-provider-connection.api_key_label': 'API कुंजी',
        'core-ai-provider-connection.api_key_unchanged':
          '{{mask}} (अपरिवर्तित)',
        'core-ai-provider-connection.api_key_set_help':
          'एक कुंजी सेट है। इसे इस पृष्ठ पर कभी वापस नहीं भेजा जाता; मौजूदा कुंजी रखने के लिए खाली छोड़ें या बदलने के लिए नई कुंजी लिखें।',
        'core-ai-provider-connection.api_key_empty_help':
          'यह वेब रूट के बाहर lib/config.php में संग्रहीत है। स्थानीय मॉडल को इसकी आवश्यकता नहीं होती।',
        'core-ai-provider-connection.selected_provider_help':
          'जिस प्रदाता के मॉडल दिखाने हैं उसे चुनें।',
        'core-ai-provider-connection.available_model_label': 'उपलब्ध मॉडल',
        'core-ai-provider-connection.available_model_help':
          'प्रदाता द्वारा दिए गए या उपलब्ध मॉडल में जोड़े गए मॉडल।',
        'core-ai-provider-connection.no_available_models':
          'अभी कोई उपलब्ध मॉडल नहीं है — उपलब्ध मॉडल में एक जोड़ें',
        'core-ai-provider-connection.compatible_keyless_save_help':
          'Ollama या OpenAI-संगत कनेक्शन API कुंजी के बिना सहेजा जा सकता है।',
        'core-ai-provider-connection.provider_save_unavailable_config':
          'कनेक्शन सहेजा नहीं जा सकता क्योंकि PHP कॉन्फ़िगरेशन स्टोर में लिख नहीं सकता।',
        'core-ai-provider-connection.provider_save_unavailable_role':
          'आपके वर्तमान खाते से कनेक्शन सहेजा नहीं जा सकता। कोई व्यवस्थापक इसे सहेज सकता है।',
        'core-ai-provider-connection.provider_save_ready':
          'इस कनेक्शन को API कुंजी के बिना सहेजा जा सकता है। सहेजने से सहायक के अन्य पृष्ठों की सेटिंग भी बनी रहती हैं।',
      }),
      ar: Object.freeze({
        'core-ai-provider-connection.provider_label': 'الموفّر',
        'core-ai-provider-connection.address_label': 'العنوان',
        'core-ai-provider-connection.address_help':
          'اتركه فارغًا لاستخدام عنوان الموفّر نفسه. وجّهه إلى مكان آخر لبوابة أو وكيل أو نموذج يعمل على هذا الجهاز.',
        'core-ai-provider-connection.api_key_label': 'مفتاح API',
        'core-ai-provider-connection.api_key_unchanged': '{{mask}} (دون تغيير)',
        'core-ai-provider-connection.api_key_set_help':
          'يوجد مفتاح مضبوط. لا يُرسل أبدًا إلى هذه الصفحة، لذا فإن تركه فارغًا يبقي المفتاح الحالي؛ اكتب مفتاحًا جديدًا لاستبداله.',
        'core-ai-provider-connection.api_key_empty_help':
          'يُخزّن في lib/config.php خارج جذر الويب. لا يحتاج النموذج المحلي إلى مفتاح.',
        'core-ai-provider-connection.selected_provider_help':
          'اختر الموفّر الذي تريد عرض نماذجه.',
        'core-ai-provider-connection.available_model_label': 'النموذج المتاح',
        'core-ai-provider-connection.available_model_help':
          'النماذج التي يوفّرها الموفّر أو التي أُضيفت في النماذج المتاحة.',
        'core-ai-provider-connection.no_available_models':
          'لا توجد نماذج متاحة بعد — أضف نموذجًا في النماذج المتاحة',
        'core-ai-provider-connection.compatible_keyless_save_help':
          'يمكن حفظ اتصال Ollama أو اتصال متوافق مع OpenAI من دون مفتاح API.',
        'core-ai-provider-connection.provider_save_unavailable_config':
          'يتعذر حفظ الاتصال لأن PHP لا يستطيع الكتابة إلى مخزن الإعدادات.',
        'core-ai-provider-connection.provider_save_unavailable_role':
          'لا يمكن لحسابك الحالي حفظ الاتصالات. يمكن للمسؤول حفظه.',
        'core-ai-provider-connection.provider_save_ready':
          'يمكن حفظ هذا الاتصال من دون مفتاح API. الحفظ يبقي أيضاً إعدادات صفحات المساعد الأخرى.',
      }),
      zh: Object.freeze({
        'core-ai-provider-connection.provider_label': '提供商',
        'core-ai-provider-connection.address_label': '地址',
        'core-ai-provider-connection.address_help':
          '留空以使用提供商自己的地址。也可以填写网关、代理或本机运行模型的地址。',
        'core-ai-provider-connection.api_key_label': 'API 密钥',
        'core-ai-provider-connection.api_key_unchanged': '{{mask}}（未更改）',
        'core-ai-provider-connection.api_key_set_help':
          '已设置密钥。它不会发送回此页面，因此留空会保留现有密钥；输入新密钥即可替换。',
        'core-ai-provider-connection.api_key_empty_help':
          '它储存在 Web 根目录之外的 lib/config.php 中。本地模型无需密钥。',
        'core-ai-provider-connection.selected_provider_help':
          '选择要显示其模型的提供商。',
        'core-ai-provider-connection.available_model_label': '可用模型',
        'core-ai-provider-connection.available_model_help':
          '由提供商提供或在“可用模型”中添加的模型。',
        'core-ai-provider-connection.no_available_models':
          '尚无可用模型——请在“可用模型”中添加一个',
        'core-ai-provider-connection.compatible_keyless_save_help':
          '无需 API 密钥即可保存 Ollama 或 OpenAI 兼容连接。',
        'core-ai-provider-connection.provider_save_unavailable_config':
          '无法保存连接，因为 PHP 无法写入配置存储区。',
        'core-ai-provider-connection.provider_save_unavailable_role':
          '您当前的帐户不能保存连接。管理员可以保存它。',
        'core-ai-provider-connection.provider_save_ready':
          '无需 API 密钥即可保存此连接。保存还会保留其他助手页面上的设置。',
      }),
    }),
  }),
  render(data, helpers) {
    const t = (key, values, fallback) =>
      helpers.i18n.t(`core-ai-provider-connection.${key}`, values, fallback);
    const form = document.createElement('div');
    const provider = document.createElement('select');
    provider.id = 'aiProvider';
    const providerNote = document.createElement('div');
    providerNote.className = 'muted small';
    for (const [name, meta] of Object.entries(data.providers)) {
      const option = document.createElement('option');
      option.value = name;
      option.textContent = meta.label;
      option.selected = data.provider === name;
      provider.appendChild(option);
    }
    form.append(
      helpers.fieldRow(t('provider_label', {}, 'Provider'), provider, ''),
      providerNote,
    );

    const base = document.createElement('input');
    base.type = 'text';
    base.id = 'aiBase';
    base.className = 'wide-field';
    base.value = data.base_url || '';
    form.appendChild(
      helpers.fieldRow(
        t('address_label', {}, 'Address'),
        base,
        t(
          'address_help',
          {},
          "Leave blank for the provider's own. Point it elsewhere for a gateway, a proxy, or a model running on this machine.",
        ),
      ),
    );

    const key = document.createElement('input');
    key.type = 'password';
    key.id = 'aiKey';
    key.className = 'wide-field';
    key.autocomplete = 'off';
    key.placeholder = data.has_key
      ? t('api_key_unchanged', { mask: '•'.repeat(24) }, '{{mask}} (unchanged)')
      : '';
    form.appendChild(
      helpers.fieldRow(
        t('api_key_label', {}, 'API key'),
        key,
        data.has_key
          ? t(
              'api_key_set_help',
              {},
              'A key is set. It is never sent back to this page, so leaving this blank keeps the existing one — type a new one to replace it.',
            )
          : t(
              'api_key_empty_help',
              {},
              'Stored in lib/config.php, which is outside the web root. A local model needs none.',
            ),
      ),
    );

    const selected = document.createElement('div');
    selected.className = 'settings';
    const selectedProvider = provider.cloneNode(true);
    selectedProvider.id = 'aiSelectedProvider';
    const model = document.createElement('select');
    model.id = 'aiModel';
    model.className = 'wide-field';
    model.value = data.model || '';
    selected.append(
      helpers.fieldRow(
        t('provider_label', {}, 'Provider'),
        selectedProvider,
        t(
          'selected_provider_help',
          {},
          "Choose which provider's models to show.",
        ),
      ),
      helpers.fieldRow(
        t('available_model_label', {}, 'Available model'),
        model,
        t(
          'available_model_help',
          {},
          'Models supplied by the provider or added on Available Models.',
        ),
      ),
    );
    return {
      form,
      selected,
      provider,
      providerNote,
      base,
      model,
      key,
      selectedProvider,
      t,
    };
  },
};
