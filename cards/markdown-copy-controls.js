/* Core card package: copy labels for rendered Markdown code blocks. */
window.ZogographMarkdownCopyControlsCard = {
  manifest: Object.freeze({
    id: 'core-markdown-copy-controls',
    locales: ['core-markdown-copy-controls.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-markdown-copy-controls.block': 'Copy this block',
        'core-markdown-copy-controls.language_block':
          'Copy this {{language}} block',
      }),
      es: Object.freeze({
        'core-markdown-copy-controls.block': 'Copiar este bloque',
        'core-markdown-copy-controls.language_block':
          'Copiar este bloque de {{language}}',
      }),
      hi: Object.freeze({
        'core-markdown-copy-controls.block': 'इस ब्लॉक को कॉपी करें',
        'core-markdown-copy-controls.language_block':
          'इस {{language}} ब्लॉक को कॉपी करें',
      }),
      ar: Object.freeze({
        'core-markdown-copy-controls.block': 'انسخ هذه الكتلة',
        'core-markdown-copy-controls.language_block':
          'انسخ كتلة {{language}} هذه',
      }),
      zh: Object.freeze({
        'core-markdown-copy-controls.block': '复制此代码块',
        'core-markdown-copy-controls.language_block':
          '复制此 {{language}} 代码块',
      }),
    }),
  }),
  render(input, helpers) {
    return helpers.i18n.t(
      `core-markdown-copy-controls.${input.key}`,
      input.values || {},
      input.fallback,
    );
  },
};
