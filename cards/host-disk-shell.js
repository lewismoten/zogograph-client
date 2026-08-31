/* Core card package: page shells driven by the shared host disk walk. */
window.ZogographHostDiskShellCard = {
  manifest: Object.freeze({
    id: 'core-host-disk-shell',
    locales: ['core-host-disk-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-host-disk-shell.filesystems_title':
          'Filesystems, and the space nothing accounts for',
        'core-host-disk-shell.deleted_title': 'Deleted, still holding space',
        'core-host-disk-shell.directories_title':
          'Biggest directories on the machine',
      }),
      es: Object.freeze({
        'core-host-disk-shell.filesystems_title':
          'Sistemas de archivos y el espacio que no se explica',
        'core-host-disk-shell.deleted_title':
          'Eliminado, pero aún ocupa espacio',
        'core-host-disk-shell.directories_title':
          'Directorios más grandes de la máquina',
      }),
      hi: Object.freeze({
        'core-host-disk-shell.filesystems_title':
          'फ़ाइल सिस्टम और वह स्थान जिसका कोई हिसाब नहीं',
        'core-host-disk-shell.deleted_title':
          'हटाया गया, फिर भी जगह घेर रहा है',
        'core-host-disk-shell.directories_title':
          'मशीन पर सबसे बड़ी निर्देशिकाएँ',
      }),
      ar: Object.freeze({
        'core-host-disk-shell.filesystems_title':
          'أنظمة الملفات والمساحة التي لا يفسرها شيء',
        'core-host-disk-shell.deleted_title': 'محذوف، لكنه ما زال يشغل مساحة',
        'core-host-disk-shell.directories_title': 'أكبر المجلدات على الجهاز',
      }),
      zh: Object.freeze({
        'core-host-disk-shell.filesystems_title':
          '文件系统，以及无从解释的空间',
        'core-host-disk-shell.deleted_title': '已删除，但仍占用空间',
        'core-host-disk-shell.directories_title': '机器上最大的目录',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-host-disk-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-host-disk-shell.${key}`],
    );
  },
  render() {},
};
