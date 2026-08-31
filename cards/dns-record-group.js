window.ZogographDnsRecordGroupCard = {
  manifest: Object.freeze({
    id: 'core-dns-record-group',
    locales: ['core-dns-record-group.'],
    helpers: [
      'target',
      'renderTable',
      'recordValueCell',
      'humanDuration',
      'humanTime',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-dns-record-group.title': 'What a domain publishes',
        'core-dns-record-group.domain': 'Domain',
        'core-dns-record-group.sweep': 'Look it all up',
        'core-dns-record-group.overview_help':
          'A name resolving is not the same as a zone being right. Mail stops arriving because an MX was dropped or an SPF edited; a subdomain starts pointing somewhere that is not yours; a TTL cut to sixty seconds during a migration is never put back. None of that shows anywhere else here. Names come from a zone transfer where your nameserver allows one, from cPanel’s own record of this account’s domains, and from what was found last time — not from guessing at names.',
        'core-dns-record-group.ttl': 'TTL',
        'core-dns-record-group.short_ttl_help':
          'A short TTL means resolvers re-ask constantly. Often left over from a migration.',
        'core-dns-record-group.gone_since': 'Gone since {{date}}',
        'core-dns-record-group.seen_since': 'Seen since {{date}}',
        'core-dns-record-group.name': 'Name',
        'core-dns-record-group.type': 'Type',
        'core-dns-record-group.value': 'Value',
      }),
      es: Object.freeze({
        'core-dns-record-group.title': 'Lo que publica un dominio',
        'core-dns-record-group.domain': 'Dominio',
        'core-dns-record-group.sweep': 'Consultar todo',
        'core-dns-record-group.overview_help':
          'Que un nombre resuelva no significa que la zona sea correcta. El correo deja de llegar porque se eliminó un MX o se editó un SPF; un subdominio comienza a apuntar a un lugar que no es suyo; un TTL reducido a sesenta segundos durante una migración nunca se restablece. Nada de eso aparece en otra parte. Los nombres proceden de una transferencia de zona cuando su servidor de nombres la permite, del propio registro de cPanel de los dominios de esta cuenta y de lo encontrado la última vez, no de adivinar nombres.',
        'core-dns-record-group.ttl': 'TTL',
        'core-dns-record-group.short_ttl_help':
          'Un TTL corto hace que los resolutores vuelvan a consultar constantemente. Suele quedar de una migración.',
        'core-dns-record-group.gone_since': 'Eliminado desde {{date}}',
        'core-dns-record-group.seen_since': 'Visto desde {{date}}',
        'core-dns-record-group.name': 'Nombre',
        'core-dns-record-group.type': 'Tipo',
        'core-dns-record-group.value': 'Valor',
      }),
      hi: Object.freeze({
        'core-dns-record-group.title': 'डोमेन क्या प्रकाशित करता है',
        'core-dns-record-group.domain': 'डोमेन',
        'core-dns-record-group.sweep': 'सब कुछ खोजें',
        'core-dns-record-group.overview_help':
          'किसी नाम का रिज़ॉल्व होना इस बात के बराबर नहीं कि ज़ोन सही है। MX हटने या SPF बदलने से मेल आना बंद हो जाता है; कोई सबडोमेन ऐसी जगह इंगित करने लगता है जो आपकी नहीं है; माइग्रेशन के दौरान साठ सेकंड किया TTL कभी वापस नहीं होता। यह सब यहाँ और कहीं दिखाई नहीं देता। नाम उस ज़ोन ट्रांसफ़र से आते हैं जिसे आपका नेमसर्वर अनुमति दे, इस खाते के डोमेनों के cPanel रिकॉर्ड से, और पिछली बार मिले नामों से—नाम अनुमान लगाने से नहीं।',
        'core-dns-record-group.ttl': 'TTL',
        'core-dns-record-group.short_ttl_help':
          'छोटे TTL का अर्थ है कि रिज़ॉल्वर लगातार फिर से पूछते हैं। यह अक्सर माइग्रेशन के बाद बचा रह जाता है।',
        'core-dns-record-group.gone_since': '{{date}} से अनुपस्थित',
        'core-dns-record-group.seen_since': '{{date}} से देखा गया',
        'core-dns-record-group.name': 'नाम',
        'core-dns-record-group.type': 'प्रकार',
        'core-dns-record-group.value': 'मान',
      }),
      ar: Object.freeze({
        'core-dns-record-group.title': 'ما الذي ينشره نطاق',
        'core-dns-record-group.domain': 'النطاق',
        'core-dns-record-group.sweep': 'ابحث عن الكل',
        'core-dns-record-group.overview_help':
          'حل الاسم لا يعني أن المنطقة صحيحة. يتوقف وصول البريد لأن سجل MX حُذف أو عُدّل SPF؛ ويبدأ نطاق فرعي بالإشارة إلى مكان ليس لك؛ ولا يُعاد ضبط TTL الذي خُفّض إلى ستين ثانية أثناء ترحيل. لا يظهر شيء من ذلك في أي مكان آخر هنا. تأتي الأسماء من نقل المنطقة حيثما يسمح خادم الأسماء لديك، ومن سجل cPanel الخاص بنطاقات هذا الحساب، ومن آخر ما وُجد، لا من تخمين الأسماء.',
        'core-dns-record-group.ttl': 'TTL',
        'core-dns-record-group.short_ttl_help':
          'يعني TTL القصير أن المحلِّلات تعيد السؤال باستمرار. وغالبًا ما يكون متبقيًا من عملية ترحيل.',
        'core-dns-record-group.gone_since': 'غائب منذ {{date}}',
        'core-dns-record-group.seen_since': 'شوهد منذ {{date}}',
        'core-dns-record-group.name': 'الاسم',
        'core-dns-record-group.type': 'النوع',
        'core-dns-record-group.value': 'القيمة',
      }),
      zh: Object.freeze({
        'core-dns-record-group.title': '域名发布了什么',
        'core-dns-record-group.domain': '域名',
        'core-dns-record-group.sweep': '全部查询',
        'core-dns-record-group.overview_help':
          '名称能够解析并不代表 DNS 区域设置正确。MX 记录被删或 SPF 被修改会让邮件停止送达；子域名会开始指向不属于您的地方；迁移期间被降至六十秒的 TTL 从未调回。这里其他地方都不会显示这些情况。名称来自您的名称服务器允许的区域传送、cPanel 对此账户域名自身的记录，以及上次查到的结果，而不是猜测名称。',
        'core-dns-record-group.ttl': 'TTL',
        'core-dns-record-group.short_ttl_help':
          'TTL 过短意味着解析器会不断重新查询。这通常是迁移后遗留下来的。',
        'core-dns-record-group.gone_since': '自 {{date}} 起已消失',
        'core-dns-record-group.seen_since': '自 {{date}} 起已发现',
        'core-dns-record-group.name': '名称',
        'core-dns-record-group.type': '类型',
        'core-dns-record-group.value': '值',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-dns-record-group.${key}`,
      {},
      this.manifest.catalogs.en[`core-dns-record-group.${key}`],
    );
  },
  render(data, helpers) {
    if (!helpers.target) return;
    helpers.renderTable(
      helpers.target.id,
      data.rows,
      [
        {
          label: helpers.i18n.t('core-dns-record-group.name', {}, 'Name'),
          className: 'name',
          render: (row) => {
            const span = document.createElement('span');
            span.textContent = row.name;
            if (row.gone) span.className = 'muted struck';
            return span;
          },
        },
        {
          label: helpers.i18n.t('core-dns-record-group.type', {}, 'Type'),
          render: (row) => row.type,
        },
        {
          label: helpers.i18n.t('core-dns-record-group.value', {}, 'Value'),
          className: 'cmd',
          render: helpers.recordValueCell,
        },
        {
          label: helpers.i18n.t('core-dns-record-group.ttl', {}, 'TTL'),
          className: 'size',
          render: (row) => {
            const span = document.createElement('span');
            span.textContent = helpers.humanDuration(Number(row.ttl));
            if (Number(row.ttl) > 0 && Number(row.ttl) <= 300) {
              span.className = 'band-warn';
              span.title = helpers.i18n.t(
                'core-dns-record-group.short_ttl_help',
                {},
                'A short TTL means resolvers re-ask constantly. Often left over from a migration.',
              );
            }
            return span;
          },
        },
        {
          label: '',
          render: (row) => {
            const span = document.createElement('span');
            span.className = 'muted small';
            span.textContent = helpers.i18n.t(
              row.gone
                ? 'core-dns-record-group.gone_since'
                : 'core-dns-record-group.seen_since',
              {
                date: helpers
                  .humanTime(row.gone ? row.gone_at : row.first_seen)
                  .split(',')[0],
              },
              row.gone ? 'Gone since {{date}}' : 'Seen since {{date}}',
            );
            return span;
          },
        },
      ],
      null,
      (row) => (row.gone ? 'row-bad' : ''),
      (row) => `${row.name}|${row.type}|${row.value}`,
    );
  },
};
