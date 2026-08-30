window.ZogographFetchLinkedDataCard = {
  manifest: Object.freeze({
    id: 'core-fetch-linked-data',
    locales: ['core-fetch-linked-data.'],
    helpers: ['note', 'section', 'jsonBlock', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-fetch-linked-data.none':
          'This page publishes no structured data. Sites use application/ld+json to tell search engines what a page is about in machine-readable form — an article, a product, an organisation — and plenty of sites simply have none.',
        'core-fetch-linked-data.untyped_entity': 'Untyped entity',
      }),
      es: Object.freeze({
        'core-fetch-linked-data.none':
          'Esta página no publica datos estructurados. Los sitios usan application/ld+json para explicar a los motores de búsqueda de qué trata una página en formato legible por máquinas —un artículo, un producto, una organización— y muchos sitios simplemente no tienen ninguno.',
        'core-fetch-linked-data.untyped_entity': 'Entidad sin tipo',
      }),
      hi: Object.freeze({
        'core-fetch-linked-data.none':
          'यह पृष्ठ कोई संरचित डेटा प्रकाशित नहीं करता। साइटें application/ld+json का उपयोग खोज इंजनों को मशीन-पठनीय रूप में यह बताने के लिए करती हैं कि पृष्ठ किस बारे में है — लेख, उत्पाद या संगठन — और बहुत-सी साइटों में ऐसा कुछ नहीं होता।',
        'core-fetch-linked-data.untyped_entity': 'बिना प्रकार की इकाई',
      }),
      ar: Object.freeze({
        'core-fetch-linked-data.none':
          'لا تنشر هذه الصفحة بيانات منظَّمة. تستخدم المواقع application/ld+json لإخبار محركات البحث، بصيغة مقروءة آليًا، بموضوع الصفحة — مقال أو منتج أو مؤسسة — ولا تملك مواقع كثيرة أيًا منها.',
        'core-fetch-linked-data.untyped_entity': 'كيان بلا نوع',
      }),
      zh: Object.freeze({
        'core-fetch-linked-data.none':
          '此页面未发布结构化数据。网站使用 application/ld+json 以机器可读的形式告诉搜索引擎页面的内容——文章、产品或组织——而许多网站根本没有这些数据。',
        'core-fetch-linked-data.untyped_entity': '未分类实体',
      }),
    }),
  }),
  render(data, helpers) {
    const wrap = document.createDocumentFragment();
    const found = data.linked_data || { blocks: [] };

    if (found.error) {
      wrap.appendChild(helpers.note(found.error));
      return wrap;
    }

    if (!found.blocks.length) {
      wrap.appendChild(
        helpers.note(
          helpers.i18n.t(
            'core-fetch-linked-data.none',
            {},
            'This page publishes no structured data. Sites use application/ld+json to tell search engines what a page is about in machine-readable form — an article, a product, an organisation — and plenty of sites simply have none.',
          ),
        ),
      );
      return wrap;
    }

    for (const block of found.blocks) {
      const heading =
        [block.type, block.name].filter(Boolean).join(' · ') ||
        helpers.i18n.t(
          'core-fetch-linked-data.untyped_entity',
          {},
          'Untyped entity',
        );
      if (block.error) {
        const bad = document.createDocumentFragment();
        bad.appendChild(helpers.note(block.error));
        const pre = document.createElement('pre');
        pre.className = 'paste';
        pre.textContent = block.json;
        bad.appendChild(pre);
        wrap.appendChild(helpers.section(heading, bad));
        continue;
      }
      wrap.appendChild(helpers.section(heading, helpers.jsonBlock(block.json)));
    }
    return wrap;
  },
};
