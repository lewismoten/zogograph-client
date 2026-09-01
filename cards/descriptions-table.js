/* Core card package: the account-owned machine descriptions table. */
window.ZogographDescriptionsTableCard = {
  manifest: Object.freeze({
    id: 'core-descriptions-table',
    locales: ['core-descriptions-table.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-descriptions-table.sign_in_to_write':
          'Sign in to write your own.',
        'core-descriptions-table.kind': 'Kind',
        'core-descriptions-table.slab_cache': 'Slab cache',
        'core-descriptions-table.process': 'Process',
        'core-descriptions-table.what': 'What it is',
        'core-descriptions-table.link': 'Link',
        'core-descriptions-table.written': 'Written',
        'core-descriptions-table.empty':
          'Nothing written yet. The built-in list covers {{processes, number}} processes and {{slabs, number}} slab caches; anything on this machine it does not know about shows its name with no mark beside it, which is how to find the ones worth writing down.',
        'core-descriptions-table.forget_prompt': 'Forget what {{name}} is?',
        'core-descriptions-table.forget_help':
          'The note goes; the built-in description takes over again if there is one for that name, and otherwise the name appears with nothing beside it.',
        'core-descriptions-table.forget_action': 'Forget it',
        'core-descriptions-table.saved':
          'Saved. It shows beside that name from now on.',
      }),
      es: Object.freeze({
        'core-descriptions-table.sign_in_to_write':
          'Inicie sesión para escribir las suyas.',
        'core-descriptions-table.kind': 'Tipo',
        'core-descriptions-table.slab_cache': 'Caché slab',
        'core-descriptions-table.process': 'Proceso',
        'core-descriptions-table.what': 'Qué es',
        'core-descriptions-table.link': 'Enlace',
        'core-descriptions-table.written': 'Escrito',
        'core-descriptions-table.empty':
          'Aún no hay nada escrito. La lista integrada cubre {{processes, number}} procesos y {{slabs, number}} cachés slab; cualquier nombre que no conozca esta máquina aparece sin marca, así se encuentran los que merece la pena describir.',
        'core-descriptions-table.forget_prompt': '¿Olvidar qué es {{name}}?',
        'core-descriptions-table.forget_help':
          'La nota se elimina; la descripción integrada vuelve a usarse si existe para ese nombre y, de lo contrario, el nombre aparece sin nada a su lado.',
        'core-descriptions-table.forget_action': 'Olvidarlo',
        'core-descriptions-table.saved':
          'Guardado. A partir de ahora se muestra junto a ese nombre.',
      }),
      hi: Object.freeze({
        'core-descriptions-table.sign_in_to_write':
          'अपने विवरण लिखने के लिए साइन इन करें।',
        'core-descriptions-table.kind': 'प्रकार',
        'core-descriptions-table.slab_cache': 'स्लैब कैश',
        'core-descriptions-table.process': 'प्रक्रिया',
        'core-descriptions-table.what': 'यह क्या है',
        'core-descriptions-table.link': 'लिंक',
        'core-descriptions-table.written': 'लिखा गया',
        'core-descriptions-table.empty':
          'अभी कुछ नहीं लिखा गया है। अंतर्निहित सूची में {{processes, number}} प्रक्रियाएँ और {{slabs, number}} स्लैब कैश हैं; जिस नाम को यह मशीन नहीं जानती उसके पास कोई चिह्न नहीं दिखता, जिससे लिखने योग्य नाम मिलते हैं।',
        'core-descriptions-table.forget_prompt': '{{name}} क्या है यह भूलें?',
        'core-descriptions-table.forget_help':
          'नोट हट जाएगा; उस नाम के लिए अंतर्निहित विवरण होने पर वह फिर लागू होगा, अन्यथा नाम के पास कुछ नहीं दिखेगा।',
        'core-descriptions-table.forget_action': 'इसे भूलें',
        'core-descriptions-table.saved':
          'सहेजा गया। अब से यह उस नाम के पास दिखेगा।',
      }),
      ar: Object.freeze({
        'core-descriptions-table.sign_in_to_write':
          'سجّل الدخول لكتابة وصفك الخاص.',
        'core-descriptions-table.kind': 'النوع',
        'core-descriptions-table.slab_cache': 'ذاكرة slab المخبأة',
        'core-descriptions-table.process': 'عملية',
        'core-descriptions-table.what': 'ما هو',
        'core-descriptions-table.link': 'رابط',
        'core-descriptions-table.written': 'كُتب',
        'core-descriptions-table.empty':
          'لم يُكتب شيء بعد. تغطي القائمة المضمنة {{processes, number}} عملية و{{slabs, number}} من ذواكر slab المخبأة؛ يظهر أي اسم لا تعرفه هذه الآلة من دون علامة، وهكذا تجد الأسماء الجديرة بالوصف.',
        'core-descriptions-table.forget_prompt':
          'هل تريد نسيان ماهية {{name}}؟',
        'core-descriptions-table.forget_help':
          'تُزال الملاحظة؛ ويعود الوصف المضمن إن كان موجودًا لهذا الاسم، وإلا يظهر الاسم من دون شيء بجانبه.',
        'core-descriptions-table.forget_action': 'انسَه',
        'core-descriptions-table.saved':
          'تم الحفظ. سيظهر بجانب ذلك الاسم من الآن فصاعدًا.',
      }),
      zh: Object.freeze({
        'core-descriptions-table.sign_in_to_write': '请登录以编写自己的说明。',
        'core-descriptions-table.kind': '类型',
        'core-descriptions-table.slab_cache': 'Slab 缓存',
        'core-descriptions-table.process': '进程',
        'core-descriptions-table.what': '是什么',
        'core-descriptions-table.link': '链接',
        'core-descriptions-table.written': '已编写',
        'core-descriptions-table.empty':
          '尚未编写任何说明。内置列表涵盖 {{processes, number}} 个进程和 {{slabs, number}} 个 Slab 缓存；此机器不认识的名称旁不会显示标记，因此可据此找到值得说明的名称。',
        'core-descriptions-table.forget_prompt': '忘记 {{name}} 是什么？',
        'core-descriptions-table.forget_help':
          '该说明将被移除；如果该名称有内置说明，将再次使用内置说明，否则名称旁不会显示任何内容。',
        'core-descriptions-table.forget_action': '忘记它',
        'core-descriptions-table.saved': '已保存。现在起它会显示在该名称旁。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-descriptions-table.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
