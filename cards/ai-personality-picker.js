/* Core card package: select, save, and delete AI personality profiles. */
window.ZogographAiPersonalityPickerCard = {
  manifest: Object.freeze({
    id: 'core-ai-personality-picker',
    locales: ['core-ai-personality-picker.'],
    helpers: [
      'askTextDialog',
      'askFirst',
      'post',
      'renderAiSettings',
      'notice',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-personality-picker.own': '— your own wording —',
        'core-ai-personality-picker.built_in': 'Built in',
        'core-ai-personality-picker.yours': 'Yours',
        'core-ai-personality-picker.unsaved':
          'The boxes below are not one of these. Save it as one to come back to it.',
        'core-ai-personality-picker.filled':
          'Filled in with {{name}}. Press Save to apply it.',
        'core-ai-personality-picker.save': 'Save these as a personality',
        'core-ai-personality-picker.save_title': 'Save personality',
        'core-ai-personality-picker.name': 'Name',
        'core-ai-personality-picker.default_name': 'My assistant',
        'core-ai-personality-picker.name_hint':
          'This is the name people see in the list.',
        'core-ai-personality-picker.saved': 'Saved. It is in the list now.',
        'core-ai-personality-picker.delete': 'Delete this personality',
        'core-ai-personality-picker.delete_confirm': 'Delete {{name}}?',
        'core-ai-personality-picker.delete_help':
          'It goes from the list. The boxes keep what is in them, so whatever is in force stays in force until you change it.',
        'core-ai-personality-picker.delete_action': 'Delete it',
        'core-ai-personality-picker.deleted': 'Deleted.',
      }),
      es: Object.freeze({
        'core-ai-personality-picker.own': '— tus propias palabras —',
        'core-ai-personality-picker.built_in': 'Incluidas',
        'core-ai-personality-picker.yours': 'Tuyas',
        'core-ai-personality-picker.unsaved':
          'Los campos de abajo no corresponden a ninguna de estas. Guárdalos para volver a usarlos.',
        'core-ai-personality-picker.filled':
          'Se completó con {{name}}. Pulsa Guardar para aplicarlo.',
        'core-ai-personality-picker.save': 'Guardar como personalidad',
        'core-ai-personality-picker.save_title': 'Guardar personalidad',
        'core-ai-personality-picker.name': 'Nombre',
        'core-ai-personality-picker.default_name': 'Mi asistente',
        'core-ai-personality-picker.name_hint':
          'Este es el nombre que aparece en la lista.',
        'core-ai-personality-picker.saved': 'Guardada. Ahora está en la lista.',
        'core-ai-personality-picker.delete': 'Eliminar esta personalidad',
        'core-ai-personality-picker.delete_confirm': '¿Eliminar {{name}}?',
        'core-ai-personality-picker.delete_help':
          'Se quita de la lista. Los campos conservan su contenido, por lo que seguirá activo hasta que lo cambies.',
        'core-ai-personality-picker.delete_action': 'Eliminar',
        'core-ai-personality-picker.deleted': 'Eliminada.',
      }),
      hi: Object.freeze({
        'core-ai-personality-picker.own': '— आपके अपने शब्द —',
        'core-ai-personality-picker.built_in': 'अंतर्निहित',
        'core-ai-personality-picker.yours': 'आपके',
        'core-ai-personality-picker.unsaved':
          'नीचे के बॉक्स इनमें से कोई नहीं हैं। बाद में वापस आने के लिए इन्हें सहेजें।',
        'core-ai-personality-picker.filled':
          '{{name}} के साथ भरा गया। लागू करने के लिए सहेजें दबाएँ।',
        'core-ai-personality-picker.save': 'इसे व्यक्तित्व के रूप में सहेजें',
        'core-ai-personality-picker.save_title': 'व्यक्तित्व सहेजें',
        'core-ai-personality-picker.name': 'नाम',
        'core-ai-personality-picker.default_name': 'मेरा सहायक',
        'core-ai-personality-picker.name_hint':
          'यह वह नाम है जो सूची में दिखाई देता है।',
        'core-ai-personality-picker.saved': 'सहेजा गया। अब यह सूची में है।',
        'core-ai-personality-picker.delete': 'यह व्यक्तित्व हटाएँ',
        'core-ai-personality-picker.delete_confirm': '{{name}} को हटाएँ?',
        'core-ai-personality-picker.delete_help':
          'यह सूची से हट जाएगा। बॉक्स अपना वर्तमान मान रखते हैं, इसलिए बदलाव तक वही सक्रिय रहेगा।',
        'core-ai-personality-picker.delete_action': 'हटाएँ',
        'core-ai-personality-picker.deleted': 'हटा दिया गया।',
      }),
      ar: Object.freeze({
        'core-ai-personality-picker.own': '— صياغتك الخاصة —',
        'core-ai-personality-picker.built_in': 'مضمنة',
        'core-ai-personality-picker.yours': 'الخاصة بك',
        'core-ai-personality-picker.unsaved':
          'المربعات أدناه ليست واحدة من هذه. احفظها لتعود إليها لاحقًا.',
        'core-ai-personality-picker.filled':
          'تمت التعبئة بـ {{name}}. اضغط حفظ لتطبيقها.',
        'core-ai-personality-picker.save': 'احفظها كشخصية',
        'core-ai-personality-picker.save_title': 'حفظ الشخصية',
        'core-ai-personality-picker.name': 'الاسم',
        'core-ai-personality-picker.default_name': 'مساعدي',
        'core-ai-personality-picker.name_hint':
          'هذا هو الاسم الذي يظهر في القائمة.',
        'core-ai-personality-picker.saved': 'تم الحفظ. أصبحت الآن في القائمة.',
        'core-ai-personality-picker.delete': 'احذف هذه الشخصية',
        'core-ai-personality-picker.delete_confirm': 'حذف {{name}}؟',
        'core-ai-personality-picker.delete_help':
          'ستُحذف من القائمة. تحتفظ المربعات بمحتواها، لذلك يبقى ما هو مفعّل حتى تغيّره.',
        'core-ai-personality-picker.delete_action': 'احذفها',
        'core-ai-personality-picker.deleted': 'تم الحذف.',
      }),
      zh: Object.freeze({
        'core-ai-personality-picker.own': '— 使用您自己的措辞 —',
        'core-ai-personality-picker.built_in': '内置',
        'core-ai-personality-picker.yours': '您的',
        'core-ai-personality-picker.unsaved':
          '下方字段不属于其中任何一个。将其保存为一个人格，以便以后恢复使用。',
        'core-ai-personality-picker.filled':
          '已用 {{name}} 填充。按保存以应用它。',
        'core-ai-personality-picker.save': '将其保存为人格',
        'core-ai-personality-picker.save_title': '保存人格',
        'core-ai-personality-picker.name': '名称',
        'core-ai-personality-picker.default_name': '我的助手',
        'core-ai-personality-picker.name_hint': '这是人们在列表中看到的名称。',
        'core-ai-personality-picker.saved': '已保存。现在它在列表中。',
        'core-ai-personality-picker.delete': '删除此人格',
        'core-ai-personality-picker.delete_confirm': '删除 {{name}}？',
        'core-ai-personality-picker.delete_help':
          '它会从列表中移除。字段会保留其内容，因此当前设置会一直有效，直到您更改它。',
        'core-ai-personality-picker.delete_action': '删除',
        'core-ai-personality-picker.deleted': '已删除。',
      }),
    }),
  }),
  render(input, helpers) {
    const { askTextDialog, askFirst, post, renderAiSettings, notice, i18n } =
      helpers;
    const t = (key, vars, fallback) => i18n.t(key, vars, fallback);
    const { boxes } = input;
    const list = input.personalities ?? [];
    const wrap = document.createElement('div');
    wrap.className = 'persona-picker';
    const row = document.createElement('div');
    row.className = 'pattern-row';
    const pick = document.createElement('select');
    pick.id = 'aiPersonalityPick';
    const own = document.createElement('option');
    own.value = '';
    own.textContent = t(
      'core-ai-personality-picker.own',
      {},
      '— your own wording —',
    );
    pick.appendChild(own);
    const built = document.createElement('optgroup');
    built.label = t('core-ai-personality-picker.built_in', {}, 'Built in');
    const mine = document.createElement('optgroup');
    mine.label = t('core-ai-personality-picker.yours', {}, 'Yours');
    for (const entry of list) {
      const option = document.createElement('option');
      option.value = entry.key;
      option.textContent = `${entry.emoji} ${entry.name}`;
      option.selected = entry.in_use;
      (entry.mine ? mine : built).appendChild(option);
    }
    pick.appendChild(built);
    if (mine.childElementCount) pick.appendChild(mine);
    row.appendChild(pick);
    const said = document.createElement('span');
    said.className = 'muted small';
    row.appendChild(said);
    wrap.appendChild(row);
    const chosen = () => list.find((entry) => entry.key === pick.value) ?? null;
    const forget = document.createElement('button');
    const sync = () => {
      const entry = chosen();
      said.textContent = entry
        ? entry.who
        : t(
            'core-ai-personality-picker.unsaved',
            {},
            'The boxes below are not one of these. Save it as one to come back to it.',
          );
      forget.hidden = !entry?.mine;
    };
    pick.addEventListener('change', () => {
      const entry = chosen();
      if (entry) {
        boxes.name.value = entry.name;
        boxes.emoji.value = entry.emoji;
        boxes.personality.value = entry.character;
        notice(
          t(
            'core-ai-personality-picker.filled',
            { name: entry.name },
            'Filled in with {{name}}. Press Save to apply it.',
          ),
          true,
        );
      }
      sync();
    });
    const drifted = () => {
      const entry = chosen();
      if (
        entry &&
        (boxes.name.value !== entry.name ||
          boxes.emoji.value !== entry.emoji ||
          boxes.personality.value.trim() !== entry.character.trim())
      ) {
        pick.value = '';
        sync();
      }
    };
    for (const box of [boxes.name, boxes.emoji, boxes.personality])
      box.addEventListener('input', drifted);
    const actions = document.createElement('div');
    actions.className = 'inspect-actions';
    const keep = document.createElement('button');
    keep.type = 'button';
    keep.className = 'link';
    keep.textContent = t(
      'core-ai-personality-picker.save',
      {},
      'Save these as a personality',
    );
    keep.addEventListener('click', async () => {
      const label = await askTextDialog(
        t('core-ai-personality-picker.save_title', {}, 'Save personality'),
        t('core-ai-personality-picker.name', {}, 'Name'),
        boxes.name.value ||
          t('core-ai-personality-picker.default_name', {}, 'My assistant'),
        t(
          'core-ai-personality-picker.name_hint',
          {},
          'This is the name people see in the list.',
        ),
      );
      if (label === null) return;
      post('ai-personality-save', {
        label,
        name: boxes.name.value,
        emoji: boxes.emoji.value,
        personality: boxes.personality.value,
      })
        .then((fresh) => {
          renderAiSettings(fresh);
          notice(
            t(
              'core-ai-personality-picker.saved',
              {},
              'Saved. It is in the list now.',
            ),
            true,
          );
        })
        .catch((error) => notice(String(error.message || error)));
    });
    actions.appendChild(keep);
    forget.type = 'button';
    forget.className = 'link danger';
    forget.textContent = t(
      'core-ai-personality-picker.delete',
      {},
      'Delete this personality',
    );
    forget.hidden = true;
    forget.addEventListener('click', async () => {
      const entry = chosen();
      if (!entry?.mine) return;
      const yes = await askFirst(
        t(
          'core-ai-personality-picker.delete_confirm',
          { name: entry.name },
          'Delete {{name}}?',
        ),
        [
          t(
            'core-ai-personality-picker.delete_help',
            {},
            'It goes from the list. The boxes keep what is in them, so whatever is in force stays in force until you change it.',
          ),
        ],
        t('core-ai-personality-picker.delete_action', {}, 'Delete it'),
        true,
      );
      if (!yes) return;
      try {
        renderAiSettings(await post('ai-personality-forget', { id: entry.id }));
        notice(t('core-ai-personality-picker.deleted', {}, 'Deleted.'), true);
      } catch (error) {
        notice(String(error.message || error));
      }
    });
    actions.appendChild(forget);
    wrap.appendChild(actions);
    sync();
    return wrap;
  },
};
