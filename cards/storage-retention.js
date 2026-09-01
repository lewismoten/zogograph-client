/* Core card package: account database retention table copy. */
window.ZogographStorageRetentionCard = {
  manifest: Object.freeze({
    id: 'core-storage-retention',
    locales: ['core-storage-retention.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-retention.title': 'How long it keeps things',
        'core-storage-retention.recommended_settings':
          'Use the recommended settings',
        'core-storage-retention.help':
          'Zero means {{forever}}, and for several of these that is the recommendation rather than an oversight — the note beside each one says what it is protecting. Readings are kept in tiers: full detail for the first stretch, then five-minute buckets, then hourly, then daily, each holding the worst moment in its bucket rather than an average. Nothing within a couple of hours of an alert is ever rolled up or cleared, whatever these say.',
        'core-storage-retention.not_writable':
          'lib/config.php is not writable by the web server, so these cannot be changed from here. Edit the file directly, or change it in setup.php.',
        'core-storage-retention.what': 'What',
        'core-storage-retention.holding_now': 'Holding now',
        'core-storage-retention.covers': 'Covers',
        'core-storage-retention.keep_for': 'Keep for',
        'core-storage-retention.recommended': 'Recommended',
        'core-storage-retention.settles_at': 'Settles at',
        'core-storage-retention.days_hint': 'Days. 0 means keep for ever.',
        'core-storage-retention.for_ever': 'for ever',
        'core-storage-retention.days_value': '{{days}} days',
        'core-storage-retention.recommended_marker': '(recommended)',
        'core-storage-retention.forever_recommended_help':
          'Keeping this for ever is the recommendation, not an oversight. {{why}}',
        'core-storage-retention.settled_now':
          'Already holding its full window, so this is roughly what it is now.',
        'core-storage-retention.grows_to':
          'Not full yet. This is what it grows to once it has been collecting long enough.',
        'core-storage-retention.forever_growth':
          'Kept for ever, so it does not settle. This is what a year of it costs.',
        'core-storage-retention.no_history':
          'Not enough history yet to measure a rate.',
        'core-storage-retention.total_settles':
          'Left as it is, the database settles near {{amount}}.',
        'core-storage-retention.total_grows':
          'Left as it is, the database settles near {{amount}} plus about {{growth}} a year from the things set to be kept for ever.',
        'core-storage-retention.shared_table_help':
          'These tiers share one table, so their sizes are parts of it rather than separate files.',
        'core-storage-retention.yearly_growth': '+{{amount}}/yr',
      }),
      es: Object.freeze({
        'core-storage-retention.title': 'Cuánto tiempo conserva las cosas',
        'core-storage-retention.recommended_settings':
          'Usar la configuración recomendada',
        'core-storage-retention.help':
          'Cero significa {{forever}} y, para varios de estos elementos, esa es la recomendación y no un descuido: la nota junto a cada uno indica qué protege. Las lecturas se conservan por niveles: detalle completo al principio, luego grupos de cinco minutos, luego por horas y después por días; cada uno conserva el peor momento de su grupo, no un promedio. Nunca se consolidan ni eliminan datos dentro de unas horas de una alerta, digan lo que digan estas opciones.',
        'core-storage-retention.not_writable':
          'lib/config.php no se puede escribir desde el servidor web, por lo que no se pueden cambiar estos valores desde aquí. Edite el archivo directamente o cámbielo en setup.php.',
        'core-storage-retention.what': 'Qué',
        'core-storage-retention.holding_now': 'Almacenado ahora',
        'core-storage-retention.covers': 'Cubre',
        'core-storage-retention.keep_for': 'Conservar durante',
        'core-storage-retention.recommended': 'Recomendado',
        'core-storage-retention.settles_at': 'Se estabiliza en',
        'core-storage-retention.days_hint':
          'Días. 0 significa conservar para siempre.',
        'core-storage-retention.for_ever': 'para siempre',
        'core-storage-retention.days_value': '{{days}} días',
        'core-storage-retention.recommended_marker': '(recomendado)',
        'core-storage-retention.forever_recommended_help':
          'Conservar esto para siempre es la recomendación, no un descuido. {{why}}',
        'core-storage-retention.settled_now':
          'Ya conserva toda su ventana, así que esto es aproximadamente lo que es ahora.',
        'core-storage-retention.grows_to':
          'Aún no está lleno. Esto es a lo que llegará cuando haya recopilado durante suficiente tiempo.',
        'core-storage-retention.forever_growth':
          'Se conserva para siempre, por lo que no se estabiliza. Esto es lo que cuesta un año.',
        'core-storage-retention.no_history':
          'Todavía no hay suficiente historial para medir una tasa.',
        'core-storage-retention.total_settles':
          'Si se deja como está, la base de datos se estabiliza cerca de {{amount}}.',
        'core-storage-retention.total_grows':
          'Si se deja como está, la base de datos se estabiliza cerca de {{amount}}, más unos {{growth}} al año por los elementos configurados para conservarse para siempre.',
        'core-storage-retention.shared_table_help':
          'Estos niveles comparten una tabla, por lo que sus tamaños son partes de ella y no archivos separados.',
        'core-storage-retention.yearly_growth': '+{{amount}}/año',
      }),
      hi: Object.freeze({
        'core-storage-retention.title': 'यह चीज़ें कितने समय रखता है',
        'core-storage-retention.recommended_settings':
          'अनुशंसित सेटिंग उपयोग करें',
        'core-storage-retention.help':
          'शून्य का अर्थ {{forever}} है और इनमें से कई के लिए यह भूल नहीं, अनुशंसा है—हर एक के पास नोट बताता है कि वह क्या बचा रहा है। रीडिंग स्तरों में रखी जाती हैं: पहले पूरे विवरण, फिर पाँच-मिनट बकेट, फिर घंटे और फिर दिन; हर बकेट औसत के बजाय अपना सबसे बुरा क्षण रखता है। अलर्ट के कुछ घंटे के भीतर कुछ भी रोल-अप या साफ़ नहीं होता, चाहे ये कुछ भी कहें।',
        'core-storage-retention.not_writable':
          'lib/config.php वेब सर्वर द्वारा लिखने योग्य नहीं है, इसलिए इन्हें यहाँ से बदला नहीं जा सकता। फ़ाइल को सीधे संपादित करें या setup.php में बदलें।',
        'core-storage-retention.what': 'क्या',
        'core-storage-retention.holding_now': 'अभी रखा है',
        'core-storage-retention.covers': 'कवर करता है',
        'core-storage-retention.keep_for': 'इतने समय रखें',
        'core-storage-retention.recommended': 'अनुशंसित',
        'core-storage-retention.settles_at': 'इस पर स्थिर होता है',
        'core-storage-retention.days_hint':
          'दिन। 0 का अर्थ है हमेशा के लिए रखना।',
        'core-storage-retention.for_ever': 'हमेशा के लिए',
        'core-storage-retention.days_value': '{{days}} दिन',
        'core-storage-retention.recommended_marker': '(अनुशंसित)',
        'core-storage-retention.forever_recommended_help':
          'इसे हमेशा के लिए रखना अनुशंसा है, कोई चूक नहीं। {{why}}',
        'core-storage-retention.settled_now':
          'यह पहले से अपनी पूरी अवधि रख रहा है, इसलिए यह लगभग अभी जैसा ही है।',
        'core-storage-retention.grows_to':
          'अभी पूरा नहीं हुआ है। पर्याप्त समय तक संग्रह करने पर यह इतना बढ़ेगा।',
        'core-storage-retention.forever_growth':
          'इसे हमेशा के लिए रखा जाता है, इसलिए यह स्थिर नहीं होता। यह एक वर्ष का खर्च है।',
        'core-storage-retention.no_history':
          'दर मापने के लिए अभी पर्याप्त इतिहास नहीं है।',
        'core-storage-retention.total_settles':
          'जैसा है वैसा छोड़ने पर डेटाबेस लगभग {{amount}} पर स्थिर होता है।',
        'core-storage-retention.total_grows':
          'जैसा है वैसा छोड़ने पर डेटाबेस लगभग {{amount}} पर स्थिर होता है, साथ ही हमेशा के लिए रखी चीज़ों से साल में लगभग {{growth}} और बढ़ता है।',
        'core-storage-retention.shared_table_help':
          'ये स्तर एक ही तालिका साझा करते हैं, इसलिए इनके आकार अलग फ़ाइलों के बजाय उसी के भाग हैं।',
        'core-storage-retention.yearly_growth': '+{{amount}}/वर्ष',
      }),
      ar: Object.freeze({
        'core-storage-retention.title': 'مدة الاحتفاظ بالأشياء',
        'core-storage-retention.recommended_settings':
          'استخدم الإعدادات الموصى بها',
        'core-storage-retention.help':
          'الصفر يعني {{forever}}، وهو لعدة من هذه توصية لا سهو — فالملاحظة بجانب كل واحدة تقول ما تحميه. تُحفظ القراءات في طبقات: تفاصيل كاملة في البداية، ثم دلاء خمس دقائق، ثم كل ساعة، ثم يوميًا، يحتفظ كل منها بأسوأ لحظة في دلوه لا بمتوسط. لا يُجمع أو يمسح أي شيء خلال بضع ساعات من تنبيه، مهما قالت هذه الخيارات.',
        'core-storage-retention.not_writable':
          'لا يمكن لخادم الويب الكتابة إلى lib/config.php، لذا لا يمكن تغيير هذه القيم من هنا. حرّر الملف مباشرة أو غيّرها في setup.php.',
        'core-storage-retention.what': 'ما',
        'core-storage-retention.holding_now': 'المحفوظ الآن',
        'core-storage-retention.covers': 'يغطي',
        'core-storage-retention.keep_for': 'احتفظ لمدة',
        'core-storage-retention.recommended': 'الموصى به',
        'core-storage-retention.settles_at': 'يستقر عند',
        'core-storage-retention.days_hint':
          'الأيام. 0 يعني الاحتفاظ إلى الأبد.',
        'core-storage-retention.for_ever': 'إلى الأبد',
        'core-storage-retention.days_value': '{{days}} أيام',
        'core-storage-retention.recommended_marker': '(موصى به)',
        'core-storage-retention.forever_recommended_help':
          'الاحتفاظ بهذا إلى الأبد هو التوصية، وليس سهوًا. {{why}}',
        'core-storage-retention.settled_now':
          'إنه يحتفظ بالفعل بكامل نافذته، لذا فهذا هو حجمه الآن تقريبًا.',
        'core-storage-retention.grows_to':
          'لم يمتلئ بعد. هذا ما سيصل إليه بعد أن يجمع البيانات مدة كافية.',
        'core-storage-retention.forever_growth':
          'يُحتفظ به إلى الأبد، لذلك لا يستقر. هذه كلفة سنة منه.',
        'core-storage-retention.no_history':
          'لا يوجد تاريخ كافٍ بعد لقياس معدل.',
        'core-storage-retention.total_settles':
          'إذا تُرك كما هو، تستقر قاعدة البيانات قرب {{amount}}.',
        'core-storage-retention.total_grows':
          'إذا تُرك كما هو، تستقر قاعدة البيانات قرب {{amount}} مع نحو {{growth}} سنويًا من الأشياء المضبوطة للاحتفاظ بها إلى الأبد.',
        'core-storage-retention.shared_table_help':
          'تشترك هذه الطبقات في جدول واحد، لذا فأحجامها أجزاء منه وليست ملفات منفصلة.',
        'core-storage-retention.yearly_growth': '+{{amount}}/سنة',
      }),
      zh: Object.freeze({
        'core-storage-retention.title': '保留内容的时长',
        'core-storage-retention.recommended_settings': '使用推荐设置',
        'core-storage-retention.help':
          '零表示{{forever}}；对其中几项而言，这是推荐做法而非疏忽——每项旁的说明会写明它在保护什么。读数按层级保留：先是完整细节，然后是五分钟桶、每小时桶、每日桶，每个桶保留其中最差的时刻而不是平均值。无论这些设置如何表述，警报前后几个小时内的内容都绝不会被汇总或清除。',
        'core-storage-retention.not_writable':
          'Web 服务器无法写入 lib/config.php，因此无法在此处更改这些设置。请直接编辑该文件，或在 setup.php 中更改。',
        'core-storage-retention.what': '内容',
        'core-storage-retention.holding_now': '当前占用',
        'core-storage-retention.covers': '涵盖',
        'core-storage-retention.keep_for': '保留时长',
        'core-storage-retention.recommended': '推荐',
        'core-storage-retention.settles_at': '稳定在',
        'core-storage-retention.days_hint': '天。0 表示永久保留。',
        'core-storage-retention.for_ever': '永久',
        'core-storage-retention.days_value': '{{days}} 天',
        'core-storage-retention.recommended_marker': '（推荐）',
        'core-storage-retention.forever_recommended_help':
          '永久保留是推荐设置，并非疏忽。{{why}}',
        'core-storage-retention.settled_now':
          '已保留完整窗口，因此这大致就是它现在的大小。',
        'core-storage-retention.grows_to':
          '尚未填满。收集足够久后，它将增长到这个大小。',
        'core-storage-retention.forever_growth':
          '永久保留，因此不会稳定。这是它一年的成本。',
        'core-storage-retention.no_history': '尚无足够的历史记录来衡量速率。',
        'core-storage-retention.total_settles':
          '保持现状，数据库将稳定在约 {{amount}}。',
        'core-storage-retention.total_grows':
          '保持现状，数据库将稳定在约 {{amount}}，再加上永久保留内容每年约 {{growth}} 的增长。',
        'core-storage-retention.shared_table_help':
          '这些层级共用一张表，因此它们的大小是其中的部分，而不是单独的文件。',
        'core-storage-retention.yearly_growth': '+{{amount}}/年',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-storage-retention.${key}`,
      {},
      this.manifest.catalogs.en[`core-storage-retention.${key}`],
    );
  },
  render({ key, values = {} }, { i18n }) {
    const fallback = this.manifest.catalogs.en[`core-storage-retention.${key}`];
    return i18n.t(`core-storage-retention.${key}`, values, fallback);
  },
};
