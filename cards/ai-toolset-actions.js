/* Core card package: save and delete custom AI-tool presets. */
window.ZogographAiToolsetActionsCard = {
  manifest: Object.freeze({
    id: 'core-ai-toolset-actions',
    locales: ['core-ai-toolset-actions.'],
    helpers: [
      'body',
      'selectedToolset',
      'askTextDialog',
      'confirmDialog',
      'post',
      'renderAiSettings',
      'notice',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-toolset-actions.save': 'Save as toolset',
        'core-ai-toolset-actions.delete': 'Delete saved toolset',
        'core-ai-toolset-actions.title': 'Save toolset',
        'core-ai-toolset-actions.name': 'Short preset name',
        'core-ai-toolset-actions.name_hint':
          'Use lowercase letters, numbers, - or _.',
        'core-ai-toolset-actions.label': 'Label',
        'core-ai-toolset-actions.label_hint':
          'This is the name people see in the list.',
        'core-ai-toolset-actions.saved': 'Toolset saved.',
        'core-ai-toolset-actions.builtin':
          'Built-in toolsets cannot be deleted.',
        'core-ai-toolset-actions.confirm': 'Delete toolset “{{label}}”?',
        'core-ai-toolset-actions.delete_title': 'Delete toolset',
        'core-ai-toolset-actions.deleted': 'Toolset deleted.',
      }),
      es: Object.freeze({
        'core-ai-toolset-actions.save': 'Guardar como conjunto',
        'core-ai-toolset-actions.delete': 'Eliminar conjunto guardado',
        'core-ai-toolset-actions.title': 'Guardar conjunto',
        'core-ai-toolset-actions.name': 'Nombre corto',
        'core-ai-toolset-actions.name_hint': 'Use minúsculas, números, - o _.',
        'core-ai-toolset-actions.label': 'Etiqueta',
        'core-ai-toolset-actions.label_hint':
          'Este es el nombre que aparece en la lista.',
        'core-ai-toolset-actions.saved': 'Conjunto guardado.',
        'core-ai-toolset-actions.builtin':
          'Los conjuntos integrados no se pueden eliminar.',
        'core-ai-toolset-actions.confirm': '¿Eliminar el conjunto “{{label}}”?',
        'core-ai-toolset-actions.delete_title': 'Eliminar conjunto',
        'core-ai-toolset-actions.deleted': 'Conjunto eliminado.',
      }),
      hi: Object.freeze({
        'core-ai-toolset-actions.save': 'टूलसेट के रूप में सहेजें',
        'core-ai-toolset-actions.delete': 'सहेजा टूलसेट हटाएँ',
        'core-ai-toolset-actions.title': 'टूलसेट सहेजें',
        'core-ai-toolset-actions.name': 'छोटा प्रीसेट नाम',
        'core-ai-toolset-actions.name_hint':
          'छोटे अक्षर, अंक, - या _ उपयोग करें।',
        'core-ai-toolset-actions.label': 'लेबल',
        'core-ai-toolset-actions.label_hint':
          'लोगों को सूची में यही नाम दिखता है।',
        'core-ai-toolset-actions.saved': 'टूलसेट सहेजा गया।',
        'core-ai-toolset-actions.builtin':
          'अंतर्निहित टूलसेट हटाए नहीं जा सकते।',
        'core-ai-toolset-actions.confirm': 'टूलसेट “{{label}}” हटाएँ?',
        'core-ai-toolset-actions.delete_title': 'टूलसेट हटाएँ',
        'core-ai-toolset-actions.deleted': 'टूलसेट हटाया गया।',
      }),
      ar: Object.freeze({
        'core-ai-toolset-actions.save': 'احفظ كمجموعة أدوات',
        'core-ai-toolset-actions.delete': 'احذف مجموعة أدوات محفوظة',
        'core-ai-toolset-actions.title': 'احفظ مجموعة الأدوات',
        'core-ai-toolset-actions.name': 'اسم قصير للإعداد المسبق',
        'core-ai-toolset-actions.name_hint':
          'استخدم أحرفًا صغيرة وأرقامًا و- أو _.',
        'core-ai-toolset-actions.label': 'التسمية',
        'core-ai-toolset-actions.label_hint': 'هذا هو الاسم الظاهر في القائمة.',
        'core-ai-toolset-actions.saved': 'حُفظت مجموعة الأدوات.',
        'core-ai-toolset-actions.builtin':
          'لا يمكن حذف مجموعات الأدوات المضمنة.',
        'core-ai-toolset-actions.confirm':
          'هل تحذف مجموعة الأدوات “{{label}}”؟',
        'core-ai-toolset-actions.delete_title': 'احذف مجموعة الأدوات',
        'core-ai-toolset-actions.deleted': 'حُذفت مجموعة الأدوات.',
      }),
      zh: Object.freeze({
        'core-ai-toolset-actions.save': '另存为工具集',
        'core-ai-toolset-actions.delete': '删除已保存的工具集',
        'core-ai-toolset-actions.title': '保存工具集',
        'core-ai-toolset-actions.name': '预设短名称',
        'core-ai-toolset-actions.name_hint': '使用小写字母、数字、- 或 _。',
        'core-ai-toolset-actions.label': '标签',
        'core-ai-toolset-actions.label_hint': '这是列表中显示的名称。',
        'core-ai-toolset-actions.saved': '工具集已保存。',
        'core-ai-toolset-actions.builtin': '不能删除内置工具集。',
        'core-ai-toolset-actions.confirm': '删除工具集“{{label}}”？',
        'core-ai-toolset-actions.delete_title': '删除工具集',
        'core-ai-toolset-actions.deleted': '工具集已删除。',
      }),
    }),
  }),
  render(input, helpers) {
    const {
      body,
      selectedToolset,
      askTextDialog,
      confirmDialog,
      post,
      renderAiSettings,
      notice,
      i18n,
    } = helpers;
    const t = (key, vars, fallback) => i18n.t(key, vars, fallback);
    const save = document.createElement('button');
    save.type = 'button';
    save.disabled = !input.writable;
    save.textContent = t('core-ai-toolset-actions.save', {}, 'Save as toolset');
    save.addEventListener('click', async () => {
      const name = await askTextDialog(
        t('core-ai-toolset-actions.title', {}, 'Save toolset'),
        t('core-ai-toolset-actions.name', {}, 'Short preset name'),
        '',
        t(
          'core-ai-toolset-actions.name_hint',
          {},
          'Use lowercase letters, numbers, - or _.',
        ),
      );
      if (!name) return;
      const label = await askTextDialog(
        t('core-ai-toolset-actions.title', {}, 'Save toolset'),
        t('core-ai-toolset-actions.label', {}, 'Label'),
        name,
        t(
          'core-ai-toolset-actions.label_hint',
          {},
          'This is the name people see in the list.',
        ),
      );
      if (label === null) return;
      try {
        const on = [...body.querySelectorAll('input.tool-on:checked')]
          .map((box) => box.dataset.tool)
          .join(',');
        const ask = [...body.querySelectorAll('input.tool-ask:checked')]
          .map((box) => box.dataset.tool)
          .join(',');
        renderAiSettings(
          await post('ai-toolset-save', {
            name,
            label,
            tools_on: on,
            tools_ask: ask,
          }),
        );
        notice(t('core-ai-toolset-actions.saved', {}, 'Toolset saved.'), true);
      } catch (error) {
        notice(String(error.message || error));
      }
    });
    const remove = document.createElement('button');
    remove.type = 'button';
    remove.className = 'link danger';
    remove.disabled = !input.writable;
    remove.textContent = t(
      'core-ai-toolset-actions.delete',
      {},
      'Delete saved toolset',
    );
    remove.addEventListener('click', async () => {
      const set = input.toolsets.find(
        (item) => item.name === selectedToolset(),
      );
      if (!set || set.builtin) {
        notice(
          t(
            'core-ai-toolset-actions.builtin',
            {},
            'Built-in toolsets cannot be deleted.',
          ),
        );
        return;
      }
      if (
        !(await confirmDialog(
          t(
            'core-ai-toolset-actions.confirm',
            { label: set.label },
            'Delete toolset “{{label}}”?',
          ),
          t('core-ai-toolset-actions.delete_title', {}, 'Delete toolset'),
        ))
      )
        return;
      try {
        renderAiSettings(await post('ai-toolset-delete', { name: set.name }));
        notice(
          t('core-ai-toolset-actions.deleted', {}, 'Toolset deleted.'),
          true,
        );
      } catch (error) {
        notice(String(error.message || error));
      }
    });
    return [save, remove];
  },
};
