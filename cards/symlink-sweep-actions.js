/* Core card package: confirmed bulk symlink-sweep action. */
window.ZogographSymlinkSweepActionsCard = {
  manifest: Object.freeze({
    id: 'core-symlink-sweep-actions',
    locales: ['core-symlink-sweep-actions.'],
    helpers: ['confirm', 'i18n', 'refresh', 'run'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-symlink-sweep-actions.remove': 'Remove {{count}} broken links',
        'core-symlink-sweep-actions.confirm':
          'Delete {{count}} broken symlinks?\n\nOnly the links are removed. Nothing they point at is touched — they point at nothing, which is why they are on this list.',
      }),
      es: Object.freeze({
        'core-symlink-sweep-actions.remove': 'Eliminar {{count}} enlaces rotos',
        'core-symlink-sweep-actions.confirm':
          '¿Eliminar {{count}} enlaces simbólicos rotos?\n\nSolo se eliminan los enlaces. No se toca nada de lo que señalan: no señalan a nada, por eso están en esta lista.',
      }),
      hi: Object.freeze({
        'core-symlink-sweep-actions.remove': '{{count}} टूटे लिंक हटाएँ',
        'core-symlink-sweep-actions.confirm':
          'क्या {{count}} टूटे सिमलिंक हटाएँ?\n\nकेवल लिंक हटाए जाते हैं। जिस पर वे इंगित करते हैं उसे नहीं छुआ जाता — वे किसी चीज़ पर इंगित नहीं करते, इसीलिए वे इस सूची में हैं।',
      }),
      ar: Object.freeze({
        'core-symlink-sweep-actions.remove': 'إزالة {{count}} روابط معطلة',
        'core-symlink-sweep-actions.confirm':
          'هل تريد حذف {{count}} روابط رمزية معطلة؟\n\nلا تُزال إلا الروابط. لا يُمس ما تشير إليه — فهي لا تشير إلى شيء، ولهذا هي في هذه القائمة.',
      }),
      zh: Object.freeze({
        'core-symlink-sweep-actions.remove': '移除 {{count}} 个损坏链接',
        'core-symlink-sweep-actions.confirm':
          '要删除 {{count}} 个损坏的符号链接吗？\n\n只会移除链接。其指向的内容不会被触及——它们不指向任何内容，因此才在此列表中。',
      }),
    }),
  }),
  render(input, helpers) {
    const { count } = input;
    const { confirm, i18n, refresh, run } = helpers;
    const actions = document.createElement('div');
    actions.className = 'inspect-actions';
    const button = document.createElement('button');
    button.className = 'danger';
    button.textContent = i18n.t(
      'core-symlink-sweep-actions.remove',
      { count },
      `Remove ${count} broken links`,
    );
    button.addEventListener('click', async () => {
      if (
        !(await confirm(
          i18n.t(
            'core-symlink-sweep-actions.confirm',
            { count },
            `Delete ${count} broken symlinks?\n\nOnly the links are removed. Nothing they point at is touched — they point at nothing, which is why they are on this list.`,
          ),
        ))
      )
        return;
      button.disabled = true;
      await run();
      await refresh();
    });
    actions.appendChild(button);
    return actions;
  },
};
