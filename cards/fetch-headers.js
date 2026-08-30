window.ZogographFetchHeadersCard = {
  manifest: Object.freeze({
    id: 'core-fetch-headers',
    locales: ['core-fetch-headers.'],
    helpers: ['note', 'helpIcon', 'linkifyText', 'headerParts', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-fetch-headers.none': 'No headers were captured.',
        'core-fetch-headers.help':
          'Point at or click a ❓ for what a header is for. You can write your own meanings for headers under Settings → Your own names, which is where the house ones nobody documents belong.',
      }),
      es: Object.freeze({
        'core-fetch-headers.none': 'No se capturaron cabeceras.',
        'core-fetch-headers.help':
          'Apunte o haga clic en un ❓ para saber para qué sirve una cabecera. Puede escribir sus propios significados para las cabeceras en Configuración → Sus propios nombres, donde corresponden las que nadie documenta.',
      }),
      hi: Object.freeze({
        'core-fetch-headers.none': 'कोई हेडर कैप्चर नहीं किया गया।',
        'core-fetch-headers.help':
          'हेडर किस काम का है यह जानने के लिए ❓ पर इंगित करें या क्लिक करें। आप सेटिंग्स → आपके अपने नाम में हेडरों के अपने अर्थ लिख सकते हैं; वहीं वे आते हैं जिन्हें कोई दस्तावेज़ नहीं करता।',
      }),
      ar: Object.freeze({
        'core-fetch-headers.none': 'لم تُلتقط ترويسات.',
        'core-fetch-headers.help':
          'أشر إلى ❓ أو انقر عليه لمعرفة وظيفة الترويسة. يمكنك كتابة معانيك الخاصة للترويسات ضمن الإعدادات ← أسماؤك الخاصة؛ هناك مكان ما لا يوثقه أحد.',
      }),
      zh: Object.freeze({
        'core-fetch-headers.none': '未捕获标头。',
        'core-fetch-headers.help':
          '将鼠标悬停在 ❓ 上或点击它即可了解标头的用途。您可以在“设置 → 自己的名称”中为标头编写自己的含义；那些无人记录的内部标头就该放在那里。',
      }),
    }),
  }),
  render(data, helpers) {
    const wrap = document.createDocumentFragment();

    if (!data.headers.length) {
      wrap.appendChild(
        helpers.note(
          helpers.i18n.t(
            'core-fetch-headers.none',
            {},
            'No headers were captured.',
          ),
        ),
      );
      return wrap;
    }

    const table = document.createElement('table');
    table.className = 'headers';
    const tbody = document.createElement('tbody');

    for (const header of data.headers) {
      if (!header.name) {
        const row = document.createElement('tr');
        row.className = 'header-break';
        const cell = document.createElement('td');
        cell.colSpan = 2;
        cell.textContent = header.value;
        row.appendChild(cell);
        tbody.appendChild(row);
        continue;
      }

      const row = document.createElement('tr');
      const name = document.createElement('td');
      name.className = 'name';
      name.appendChild(document.createTextNode(header.name));
      if (header.title || header.note)
        name.appendChild(helpers.helpIcon(header));
      row.appendChild(name);

      const value = document.createElement('td');
      value.appendChild(helpers.linkifyText(header.value));
      const parts = header.parts || [];
      const restating =
        parts.length === 1 &&
        !parts[0].name &&
        String(parts[0].value) === String(header.value);
      if (restating) {
        const said = document.createElement('span');
        said.className = 'muted said';
        said.textContent = parts[0].note;
        value.appendChild(said);
      } else if (parts.length) {
        value.appendChild(helpers.headerParts(header));
      }
      row.appendChild(value);
      tbody.appendChild(row);
    }

    table.appendChild(tbody);
    wrap.appendChild(table);
    wrap.appendChild(
      helpers.note(
        helpers.i18n.t(
          'core-fetch-headers.help',
          {},
          'Point at or click a ❓ for what a header is for. You can write your own meanings for headers under Settings → Your own names, which is where the house ones nobody documents belong.',
        ),
      ),
    );
    return wrap;
  },
};
