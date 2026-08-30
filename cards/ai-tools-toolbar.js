/* Core card package: AI tool group tabs and saved toolset control. */
window.ZogographAiToolsToolbarCard = {
  manifest: Object.freeze({
    id: 'core-ai-tools-toolbar',
    locales: ['core-ai-tools-toolbar.'],
    helpers: [
      'toolGroupLabel',
      'toolGroupTabLabel',
      'selectGroup',
      'applyToolset',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-tools-toolbar.tab': '{{group}} ({{count, number}})',
        'core-ai-tools-toolbar.all_enabled': 'All of these are switched on.',
        'core-ai-tools-toolbar.some_enabled':
          '{{enabled, number}} of {{count, number}} switched on.',
        'core-ai-tools-toolbar.saved': '{{label}} (saved)',
        'core-ai-tools-toolbar.apply': 'Apply toolset',
      }),
      es: Object.freeze({
        'core-ai-tools-toolbar.tab': '{{group}} ({{count, number}})',
        'core-ai-tools-toolbar.all_enabled': 'Todas están activadas.',
        'core-ai-tools-toolbar.some_enabled':
          '{{enabled, number}} de {{count, number}} activadas.',
        'core-ai-tools-toolbar.saved': '{{label}} (guardado)',
        'core-ai-tools-toolbar.apply': 'Aplicar conjunto',
      }),
      hi: Object.freeze({
        'core-ai-tools-toolbar.tab': '{{group}} ({{count, number}})',
        'core-ai-tools-toolbar.all_enabled': 'ये सभी चालू हैं।',
        'core-ai-tools-toolbar.some_enabled':
          '{{count, number}} में से {{enabled, number}} चालू हैं।',
        'core-ai-tools-toolbar.saved': '{{label}} (सहेजा गया)',
        'core-ai-tools-toolbar.apply': 'टूलसेट लागू करें',
      }),
      ar: Object.freeze({
        'core-ai-tools-toolbar.tab': '{{group}} ({{count, number}})',
        'core-ai-tools-toolbar.all_enabled': 'جميعها مفعّلة.',
        'core-ai-tools-toolbar.some_enabled':
          '{{enabled, number}} من {{count, number}} مفعّلة.',
        'core-ai-tools-toolbar.saved': '{{label}} (محفوظ)',
        'core-ai-tools-toolbar.apply': 'طبّق مجموعة الأدوات',
      }),
      zh: Object.freeze({
        'core-ai-tools-toolbar.tab': '{{group}}（{{count, number}}）',
        'core-ai-tools-toolbar.all_enabled': '这些工具均已启用。',
        'core-ai-tools-toolbar.some_enabled':
          '{{count, number}} 个中有 {{enabled, number}} 个已启用。',
        'core-ai-tools-toolbar.saved': '{{label}}（已保存）',
        'core-ai-tools-toolbar.apply': '应用工具集',
      }),
    }),
  }),
  render(input, helpers) {
    const { toolGroupTabLabel, selectGroup, applyToolset, i18n } = helpers;
    const t = (key, vars, fallback) => i18n.t(key, vars, fallback);
    const parts = [];
    const tabs = document.createElement('div');
    tabs.className = 'seg-row';
    for (const [group, list] of input.groups) {
      const tab = document.createElement('button');
      tab.type = 'button';
      tab.className = group === input.selected ? 'seg on' : 'seg';
      tab.textContent = toolGroupTabLabel(group, list.length);
      const enabled = list.filter((tool) => tool.enabled).length;
      tab.title =
        enabled === list.length
          ? t(
              'core-ai-tools-toolbar.all_enabled',
              {},
              'All of these are switched on.',
            )
          : t(
              'core-ai-tools-toolbar.some_enabled',
              { enabled, count: list.length },
              '{{enabled, number}} of {{count, number}} switched on.',
            );
      tab.addEventListener('click', () => selectGroup(group));
      tabs.appendChild(tab);
    }
    parts.push(tabs);
    const presets = document.createElement('div');
    presets.className = 'panel-tools';
    const select = document.createElement('select');
    select.id = 'aiToolsetPick';
    for (const set of input.toolsets) {
      const option = document.createElement('option');
      option.value = set.name;
      option.textContent = set.builtin
        ? set.label
        : t(
            'core-ai-tools-toolbar.saved',
            { label: set.label },
            '{{label}} (saved)',
          );
      select.appendChild(option);
    }
    const apply = document.createElement('button');
    apply.type = 'button';
    apply.textContent = t('core-ai-tools-toolbar.apply', {}, 'Apply toolset');
    apply.disabled = !input.writable;
    apply.addEventListener('click', () => {
      const set = input.toolsets.find((item) => item.name === select.value);
      if (set) applyToolset(set);
    });
    presets.append(select, apply);
    parts.push(presets);
    return parts;
  },
};
