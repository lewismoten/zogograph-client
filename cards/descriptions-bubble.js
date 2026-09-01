/* Core card package: contextual explanations for machine descriptions. */
window.ZogographDescriptionsBubbleCard = {
  manifest: Object.freeze({
    id: 'core-descriptions-bubble',
    locales: ['core-descriptions-bubble.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-descriptions-bubble.bubble_own':
          'Your own note. Click for the link and to edit it.',
        'core-descriptions-bubble.bubble_link':
          'Click to open this, with a link to more.',
        'core-descriptions-bubble.bubble_keep': 'Click to keep this open.',
        'core-descriptions-bubble.owned_here':
          'Written on this machine, about this machine.',
        'core-descriptions-bubble.built_in_help':
          'From the built-in list, which is guessing from the name. If you know better for this machine, write your own — it will replace this everywhere.',
        'core-descriptions-bubble.edit_note': 'Edit this note',
        'core-descriptions-bubble.write_own': 'Write your own',
        'core-descriptions-bubble.slab_help':
          'A kernel slab cache — memory the kernel itself is holding.',
        'core-descriptions-bubble.process_help':
          'A process running on this machine.',
      }),
      es: Object.freeze({
        'core-descriptions-bubble.bubble_own':
          'Su propia nota. Haga clic para abrir el enlace y editarla.',
        'core-descriptions-bubble.bubble_link':
          'Haga clic para abrir esto, con un enlace para saber más.',
        'core-descriptions-bubble.bubble_keep':
          'Haga clic para mantenerlo abierto.',
        'core-descriptions-bubble.owned_here':
          'Escrito en esta máquina, sobre esta máquina.',
        'core-descriptions-bubble.built_in_help':
          'Proviene de la lista integrada, que hace una conjetura a partir del nombre. Si sabe más sobre esta máquina, escriba el suyo: sustituirá este en todas partes.',
        'core-descriptions-bubble.edit_note': 'Editar esta nota',
        'core-descriptions-bubble.write_own': 'Escribir el suyo',
        'core-descriptions-bubble.slab_help':
          'Una caché slab del kernel: memoria que mantiene el propio kernel.',
        'core-descriptions-bubble.process_help':
          'Un proceso que se ejecuta en esta máquina.',
      }),
      hi: Object.freeze({
        'core-descriptions-bubble.bubble_own':
          'आपका अपना नोट। लिंक खोलने और इसे संपादित करने के लिए क्लिक करें।',
        'core-descriptions-bubble.bubble_link':
          'इसे खोलने के लिए क्लिक करें; अधिक जानकारी का लिंक है।',
        'core-descriptions-bubble.bubble_keep':
          'इसे खुला रखने के लिए क्लिक करें।',
        'core-descriptions-bubble.owned_here':
          'इसी मशीन पर, इसी मशीन के बारे में लिखा गया।',
        'core-descriptions-bubble.built_in_help':
          'अंतर्निहित सूची से, जो नाम से अनुमान लगाती है। यदि आप इस मशीन के लिए बेहतर जानते हैं, तो अपना लिखें — यह हर जगह इसे बदल देगा।',
        'core-descriptions-bubble.edit_note': 'इस नोट को संपादित करें',
        'core-descriptions-bubble.write_own': 'अपना लिखें',
        'core-descriptions-bubble.slab_help':
          'एक कर्नेल स्लैब कैश — वह मेमोरी जिसे कर्नेल स्वयं रखता है।',
        'core-descriptions-bubble.process_help':
          'इस मशीन पर चल रही एक प्रक्रिया।',
      }),
      ar: Object.freeze({
        'core-descriptions-bubble.bubble_own':
          'ملاحظتك الخاصة. انقر للرابط ولتحريرها.',
        'core-descriptions-bubble.bubble_link':
          'انقر لفتح هذا، مع رابط للمزيد.',
        'core-descriptions-bubble.bubble_keep': 'انقر لإبقائه مفتوحًا.',
        'core-descriptions-bubble.owned_here':
          'كُتبت على هذه الآلة، عن هذه الآلة.',
        'core-descriptions-bubble.built_in_help':
          'من القائمة المضمنة التي تخمّن من الاسم. إن كنت تعرف أفضل لهذه الآلة، فاكتب وصفك؛ سيحل محل هذا في كل مكان.',
        'core-descriptions-bubble.edit_note': 'حرر هذه الملاحظة',
        'core-descriptions-bubble.write_own': 'اكتب وصفك',
        'core-descriptions-bubble.slab_help':
          'ذاكرة slab مخبأة للنواة — ذاكرة تحتفظ بها النواة نفسها.',
        'core-descriptions-bubble.process_help': 'عملية تعمل على هذه الآلة.',
      }),
      zh: Object.freeze({
        'core-descriptions-bubble.bubble_own':
          '您自己的说明。点击可打开链接并编辑。',
        'core-descriptions-bubble.bubble_link':
          '点击以打开，内有了解更多的链接。',
        'core-descriptions-bubble.bubble_keep': '点击以保持打开。',
        'core-descriptions-bubble.owned_here': '在此机器上编写，说明此机器。',
        'core-descriptions-bubble.built_in_help':
          '来自内置列表，后者根据名称进行猜测。如果您更了解此机器，请编写自己的说明——它将在所有位置替换此说明。',
        'core-descriptions-bubble.edit_note': '编辑此说明',
        'core-descriptions-bubble.write_own': '编写自己的说明',
        'core-descriptions-bubble.slab_help':
          '内核 Slab 缓存——由内核自身保留的内存。',
        'core-descriptions-bubble.process_help': '在此机器上运行的进程。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-descriptions-bubble.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
