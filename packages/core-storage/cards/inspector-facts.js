/* Built-in storage package: inspected-path facts grid. */
const StorageInspectorFactsCard = {
  manifest: Object.freeze({
    id: 'core-storage-inspector-facts',
    locales: ['core-storage-inspector-facts.'],
    owner: 'core-storage',
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-inspector-facts.kind': 'Kind',
        'core-storage-inspector-facts.size': 'Size',
        'core-storage-inspector-facts.directory': 'directory',
        'core-storage-inspector-facts.type': 'Type',
        'core-storage-inspector-facts.watched_as': 'Watched as',
        'core-storage-inspector-facts.last_modified': 'Last modified',
        'core-storage-inspector-facts.last_read': 'Last read',
        'core-storage-inspector-facts.inode_last_changed': 'Inode last changed',
        'core-storage-inspector-facts.owner': 'Owner',
        'core-storage-inspector-facts.permissions': 'Permissions',
        'core-storage-inspector-facts.unknown': 'unknown',
        'core-storage-inspector-facts.hard_links': 'Hard links',
        'core-storage-inspector-facts.hard_links_detail':
          '{{count, number}} — the same bytes are reachable by {{count, number}} paths, and removing this one frees nothing until the last goes',
        'core-storage-inspector-facts.subdirectories': 'Subdirectories',
        'core-storage-inspector-facts.subdirectories_detail':
          '{{count, number}}, counted from the link count',
        'core-storage-inspector-facts.inode': 'Inode',
        'core-storage-inspector-facts.points_at': 'Points at',
        'core-storage-inspector-facts.unreadable': 'unreadable',
        'core-storage-inspector-facts.target_exists': 'Target exists',
        'core-storage-inspector-facts.yes': 'yes',
        'core-storage-inspector-facts.no_link_broken':
          'no — this link is broken',
        'core-storage-inspector-facts.what_it_is': 'What it is',
        'core-storage-inspector-facts.what_is_in_it': 'What is in it',
        'core-storage-inspector-facts.directly_inside': 'Directly inside',
        'core-storage-inspector-facts.files_below_here': 'Files below here',
        'core-storage-inspector-facts.measurement': 'Measurement',
        'core-storage-inspector-facts.serves': 'Serves',
        'core-storage-inspector-facts.how_known': 'How that is known',
        'core-storage-inspector-facts.so_far': ' so far',
        'core-storage-inspector-facts.measurement_incomplete':
          'The walk ran out of time. The size above is a lower bound — at least that much, not exactly that much.',
        'core-storage-inspector-facts.site_unknown':
          '🌐 a website, domain unknown',
        'core-storage-inspector-facts.on_disk': 'On disk',
        'core-storage-inspector-facts.block_padding': '(block padding)',
        'core-storage-inspector-facts.sparse_file': '(sparse file)',
        'core-storage-inspector-facts.or_more': ' or more',
        'core-storage-inspector-facts.permission_owner': 'owner',
        'core-storage-inspector-facts.permission_group': 'group',
        'core-storage-inspector-facts.permission_everyone_else':
          'everyone else',
        'core-storage-inspector-facts.permission_read': 'read',
        'core-storage-inspector-facts.permission_write': 'write',
        'core-storage-inspector-facts.permission_enter': 'enter',
        'core-storage-inspector-facts.permission_run': 'run',
        'core-storage-inspector-facts.permission_detail':
          '{{who}}: {{permissions}}',
        'core-storage-inspector-facts.permission_and': ' and ',
        'core-storage-inspector-facts.permission_nothing': 'nothing',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-storage/locales/es/inspector-facts.json',
      hi: 'packages/core-storage/locales/hi/inspector-facts.json',
      ar: 'packages/core-storage/locales/ar/inspector-facts.json',
      zh: 'packages/core-storage/locales/zh/inspector-facts.json',
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    return i18n.t(
      'core-storage-inspector-facts.' + key,
      values,
      this.manifest.catalogs.en['core-storage-inspector-facts.' + key],
    );
  },
};
Zogograph.cards.register(StorageInspectorFactsCard);
