/* Core card package: enabling an inactive AI tool subject as one action. */
window.ZogographAiToolExtensionCard = {
  manifest: Object.freeze({
    id: 'core-ai-tool-extension',
    locales: ['core-ai-tool-extension.'],
    helpers: ['toolGroupLabel', 'enableGroup', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-tool-extension.intro':
          '{{assistant}} starts with diagnostic tools. Enable {{group}} tools if this conversation needs that subject.',
        'core-ai-tool-extension.all_enabled':
          'All {{group}} tools are enabled for {{assistant}}.',
        'core-ai-tool-extension.enable': 'Enable this subject ({{count}})',
        'core-ai-tool-extension.enabled': 'Subject enabled — save settings',
        'core-ai-tool-extension.available':
          '{{group}} tools will be available after you save these settings.',
      }),
      es: Object.freeze({
        'core-ai-tool-extension.intro':
          '{{assistant}} empieza con herramientas de diagnóstico. Active {{group}} si las necesita.',
        'core-ai-tool-extension.all_enabled':
          'Todas las herramientas {{group}} están activadas para {{assistant}}.',
        'core-ai-tool-extension.enable': 'Activar este tema ({{count}})',
        'core-ai-tool-extension.enabled': 'Tema activado — guarde los ajustes',
        'core-ai-tool-extension.available':
          'Las herramientas {{group}} estarán disponibles al guardar.',
      }),
      hi: Object.freeze({
        'core-ai-tool-extension.intro':
          '{{assistant}} डायग्नोस्टिक टूल से शुरू होता है। जरूरत हो तो {{group}} टूल सक्षम करें।',
        'core-ai-tool-extension.all_enabled':
          '{{assistant}} के लिए सभी {{group}} टूल सक्षम हैं।',
        'core-ai-tool-extension.enable': 'यह विषय सक्षम करें ({{count}})',
        'core-ai-tool-extension.enabled': 'विषय सक्षम है — सेटिंग सहेजें',
        'core-ai-tool-extension.available':
          'सहेजने के बाद {{group}} टूल उपलब्ध होंगे।',
      }),
      ar: Object.freeze({
        'core-ai-tool-extension.intro':
          'يبدأ {{assistant}} بأدوات التشخيص. فعّل أدوات {{group}} إذا احتاجت المحادثة هذا الموضوع.',
        'core-ai-tool-extension.all_enabled':
          'كل أدوات {{group}} مفعّلة لـ {{assistant}}.',
        'core-ai-tool-extension.enable': 'فعّل هذا الموضوع ({{count}})',
        'core-ai-tool-extension.enabled': 'تم تفعيل الموضوع — احفظ الإعدادات',
        'core-ai-tool-extension.available':
          'ستتوفر أدوات {{group}} بعد حفظ الإعدادات.',
      }),
      zh: Object.freeze({
        'core-ai-tool-extension.intro':
          '{{assistant}} 默认使用诊断工具。如有需要，请启用 {{group}} 工具。',
        'core-ai-tool-extension.all_enabled':
          '{{assistant}} 的所有 {{group}} 工具均已启用。',
        'core-ai-tool-extension.enable': '启用此类别（{{count}}）',
        'core-ai-tool-extension.enabled': '类别已启用 — 请保存设置',
        'core-ai-tool-extension.available':
          '保存设置后即可使用 {{group}} 工具。',
      }),
    }),
  }),
  render(input, helpers) {
    const { toolGroupLabel, enableGroup, i18n } = helpers;
    const t = (key, vars, fallback) => i18n.t(key, vars, fallback);
    const group = toolGroupLabel(input.group);
    const extension = document.createElement('div');
    extension.className = 'tool-extension';
    const text = document.createElement('span');
    text.className = 'muted small';
    text.textContent = input.inactive
      ? t(
          'core-ai-tool-extension.intro',
          { assistant: input.assistant, group },
          '{{assistant}} starts with diagnostic tools. Enable {{group}} tools if this conversation needs that subject.',
        )
      : t(
          'core-ai-tool-extension.all_enabled',
          { assistant: input.assistant, group },
          'All {{group}} tools are enabled for {{assistant}}.',
        );
    extension.appendChild(text);
    if (input.inactive && input.writable) {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'link';
      button.textContent = t(
        'core-ai-tool-extension.enable',
        { count: input.inactive },
        'Enable this subject ({{count}})',
      );
      button.addEventListener('click', () => {
        enableGroup();
        button.disabled = true;
        button.textContent = t(
          'core-ai-tool-extension.enabled',
          {},
          'Subject enabled — save settings',
        );
        text.textContent = t(
          'core-ai-tool-extension.available',
          { group },
          '{{group}} tools will be available after you save these settings.',
        );
      });
      extension.appendChild(button);
    }
    return extension;
  },
};
