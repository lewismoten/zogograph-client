window.ZogographCagefsMountsCard = {
  manifest: Object.freeze({
    id: 'core-cagefs-mounts',
    locales: ['core-cagefs-mounts.'],
    helpers: ['target', 'section', 'simpleTable', 'count', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-cagefs-mounts.title': 'The skeleton — {{mounts}}',
        'core-cagefs-mounts.path': 'Path inside the skeleton',
        'core-cagefs-mounts.type': 'Type',
        'core-cagefs-mounts.from': 'From',
        'core-cagefs-mounts.bind': 'Bind',
        'core-cagefs-mounts.read_only': 'Read-only',
        'core-cagefs-mounts.yes': 'yes',
        'core-cagefs-mounts.no': 'no',
      }),
      es: Object.freeze({
        'core-cagefs-mounts.title': 'El esqueleto — {{mounts}}',
        'core-cagefs-mounts.path': 'Ruta dentro del esqueleto',
        'core-cagefs-mounts.type': 'Tipo',
        'core-cagefs-mounts.from': 'Desde',
        'core-cagefs-mounts.bind': 'Vinculación',
        'core-cagefs-mounts.read_only': 'Solo lectura',
        'core-cagefs-mounts.yes': 'sí',
        'core-cagefs-mounts.no': 'no',
      }),
      hi: Object.freeze({
        'core-cagefs-mounts.title': 'स्केलेटन — {{mounts}}',
        'core-cagefs-mounts.path': 'स्केलेटन के अंदर का पथ',
        'core-cagefs-mounts.type': 'प्रकार',
        'core-cagefs-mounts.from': 'से',
        'core-cagefs-mounts.bind': 'बाइंड',
        'core-cagefs-mounts.read_only': 'केवल पढ़ने योग्य',
        'core-cagefs-mounts.yes': 'हाँ',
        'core-cagefs-mounts.no': 'नहीं',
      }),
      ar: Object.freeze({
        'core-cagefs-mounts.title': 'الهيكل — {{mounts}}',
        'core-cagefs-mounts.path': 'المسار داخل الهيكل',
        'core-cagefs-mounts.type': 'النوع',
        'core-cagefs-mounts.from': 'من',
        'core-cagefs-mounts.bind': 'ربط',
        'core-cagefs-mounts.read_only': 'للقراءة فقط',
        'core-cagefs-mounts.yes': 'نعم',
        'core-cagefs-mounts.no': 'لا',
      }),
      zh: Object.freeze({
        'core-cagefs-mounts.title': '骨架 — {{mounts}}',
        'core-cagefs-mounts.path': '骨架内的路径',
        'core-cagefs-mounts.type': '类型',
        'core-cagefs-mounts.from': '来源',
        'core-cagefs-mounts.bind': '绑定',
        'core-cagefs-mounts.read_only': '只读',
        'core-cagefs-mounts.yes': '是',
        'core-cagefs-mounts.no': '否',
      }),
    }),
  }),
  render(data, helpers) {
    const cage = data?.cagefs;
    if (!cage?.present) {
      helpers.target.replaceChildren();
      return;
    }
    const title = helpers.i18n.t(
      'core-cagefs-mounts.title',
      { mounts: helpers.count(cage.count, 'mount') },
      'The skeleton — {{mounts}}',
    );
    helpers.target.replaceChildren(
      helpers.section(
        title,
        helpers.simpleTable(
          [
            helpers.i18n.t(
              'core-cagefs-mounts.path',
              {},
              'Path inside the skeleton',
            ),
            helpers.i18n.t('core-cagefs-mounts.type', {}, 'Type'),
            helpers.i18n.t('core-cagefs-mounts.from', {}, 'From'),
            helpers.i18n.t('core-cagefs-mounts.bind', {}, 'Bind'),
            helpers.i18n.t('core-cagefs-mounts.read_only', {}, 'Read-only'),
          ],
          cage.mounts.map((mount) => [
            mount.relative,
            mount.fs_type,
            mount.fs_root === '/' ? mount.source : mount.fs_root,
            helpers.i18n.t(
              mount.bind ? 'core-cagefs-mounts.yes' : 'core-cagefs-mounts.no',
              {},
              mount.bind ? 'yes' : 'no',
            ),
            helpers.i18n.t(
              mount.read_only
                ? 'core-cagefs-mounts.yes'
                : 'core-cagefs-mounts.no',
              {},
              mount.read_only ? 'yes' : 'no',
            ),
          ]),
          ['name', null, 'name', null, null],
        ),
      ),
    );
  },
};
