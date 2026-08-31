/* Core card package: individual symlink repair actions. */
window.ZogographSymlinkActionsCard = {
  manifest: Object.freeze({
    id: 'core-symlink-actions',
    locales: ['core-symlink-actions.'],
    helpers: [
      'ask',
      'basename',
      'confirm',
      'i18n',
      'notice',
      'post',
      'refresh',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-symlink-actions.group': 'Link actions',
        'core-symlink-actions.repoint': 'Repoint',
        'core-symlink-actions.repoint_title': 'Point this link somewhere else',
        'core-symlink-actions.repoint_dialog_title': 'Repoint link',
        'core-symlink-actions.make_directory': 'Make it a folder',
        'core-symlink-actions.make_directory_title':
          'Remove the link and create a real, empty directory in its place',
        'core-symlink-actions.remove': 'Remove',
      }),
      es: Object.freeze({
        'core-symlink-actions.group': 'Acciones de enlace',
        'core-symlink-actions.repoint': 'Redirigir',
        'core-symlink-actions.repoint_title':
          'Hacer que este enlace apunte a otro lugar',
        'core-symlink-actions.repoint_dialog_title': 'Redirigir enlace',
        'core-symlink-actions.make_directory': 'Convertir en carpeta',
        'core-symlink-actions.make_directory_title':
          'Quitar el enlace y crear en su lugar un directorio real vacío',
        'core-symlink-actions.remove': 'Eliminar',
      }),
      hi: Object.freeze({
        'core-symlink-actions.group': 'लिंक कार्रवाइयाँ',
        'core-symlink-actions.repoint': 'पुनः इंगित करें',
        'core-symlink-actions.repoint_title': 'इस लिंक को कहीं और इंगित करें',
        'core-symlink-actions.repoint_dialog_title': 'लिंक पुनः इंगित करें',
        'core-symlink-actions.make_directory': 'इसे फ़ोल्डर बनाएँ',
        'core-symlink-actions.make_directory_title':
          'लिंक हटाएँ और उसकी जगह असली, खाली डायरेक्टरी बनाएँ',
        'core-symlink-actions.remove': 'हटाएँ',
      }),
      ar: Object.freeze({
        'core-symlink-actions.group': 'إجراءات الرابط',
        'core-symlink-actions.repoint': 'إعادة توجيه',
        'core-symlink-actions.repoint_title':
          'اجعل هذا الرابط يشير إلى مكان آخر',
        'core-symlink-actions.repoint_dialog_title': 'إعادة توجيه الرابط',
        'core-symlink-actions.make_directory': 'تحويل إلى مجلد',
        'core-symlink-actions.make_directory_title':
          'أزل الرابط وأنشئ مكانه دليلًا حقيقيًا فارغًا',
        'core-symlink-actions.remove': 'إزالة',
      }),
      zh: Object.freeze({
        'core-symlink-actions.group': '链接操作',
        'core-symlink-actions.repoint': '重新指向',
        'core-symlink-actions.repoint_title': '让此链接指向其他位置',
        'core-symlink-actions.repoint_dialog_title': '重新指向链接',
        'core-symlink-actions.make_directory': '改为文件夹',
        'core-symlink-actions.make_directory_title':
          '移除链接，并在原位置创建真实的空目录',
        'core-symlink-actions.remove': '移除',
      }),
    }),
  }),
  said(how, result, i18n) {
    if (how === 'repoint')
      return i18n.t(
        result.exists
          ? 'storage.link_repointed'
          : 'storage.link_repointed_missing',
        { target: result.now },
        result.exists
          ? `Now points at ${result.now}.`
          : `Now points at ${result.now}. That is not there yet, which may be deliberate.`,
      );
    if (how === 'directory')
      return i18n.t(
        'storage.link_made_directory',
        {},
        'Replaced with an empty directory.',
      );
    return i18n.t(
      'storage.link_removed',
      {},
      'Link removed. What it pointed at was not touched.',
    );
  },
  render(row, helpers) {
    const { ask, basename, confirm, i18n, notice, post, refresh } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    const wrap = document.createElement('div');
    wrap.className = 'row-actions';
    wrap.setAttribute(
      'aria-label',
      t('core-symlink-actions.group', {}, 'Link actions'),
    );
    const act = async (how, extra = {}) => {
      const result = await post('link-fix', { path: row.path, how, ...extra });
      notice(this.said(how, result.result, i18n), true);
      await refresh();
    };
    const add = (text, title, run, danger = false) => {
      const button = document.createElement('button');
      button.className = danger ? 'link danger' : 'link';
      button.textContent = text;
      button.title = title;
      button.addEventListener('click', async () => {
        button.disabled = true;
        try {
          await run();
        } catch (error) {
          notice(String(error.message || error));
        } finally {
          button.disabled = false;
        }
      });
      wrap.appendChild(button);
    };
    add(
      t('core-symlink-actions.repoint', {}, 'Repoint'),
      t(
        'core-symlink-actions.repoint_title',
        {},
        'Point this link somewhere else',
      ),
      async () => {
        const target = await ask(
          t('core-symlink-actions.repoint_dialog_title', {}, 'Repoint link'),
          t(
            'storage.link_repoint_prompt',
            { name: basename(row.path) },
            `Where should ${basename(row.path)} point?`,
          ),
          row.target || '',
          t(
            'storage.link_repoint_hint',
            {},
            "Use an absolute path or a path relative to the link's directory.",
          ),
        );
        if (target !== null && target.trim() !== '')
          await act('repoint', { target: target.trim() });
      },
    );
    add(
      t('core-symlink-actions.make_directory', {}, 'Make it a folder'),
      t(
        'core-symlink-actions.make_directory_title',
        {},
        'Remove the link and create a real, empty directory in its place',
      ),
      async () => {
        if (
          await confirm(
            t(
              'storage.link_make_directory_confirm',
              { path: row.path },
              `Replace ${row.path} with a real, empty directory?\n\nThe link goes; whatever it pointed at is untouched.`,
            ),
          )
        )
          await act('directory');
      },
    );
    add(
      t('core-symlink-actions.remove', {}, 'Remove'),
      t('storage.link_remove_title', {}, 'Delete the link itself'),
      async () => {
        if (
          await confirm(
            t(
              'storage.link_remove_confirm',
              { path: row.path },
              `Delete the link ${row.path}?\n\nOnly the link is removed. Whatever it points at is not touched.`,
            ),
          )
        )
          await act('delete');
      },
      true,
    );
    return wrap;
  },
};
