/* Core card package: type row in the inspected-path facts grid. */
window.ZogographStorageInspectorFactsCard = {
  manifest: Object.freeze({
    id: 'core-storage-inspector-facts',
    locales: ['core-storage-inspector-facts.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-inspector-facts.kind': 'Kind',
        'core-storage-inspector-facts.directory': 'directory',
        'core-storage-inspector-facts.type': 'Type',
        'core-storage-inspector-facts.watched_as': 'Watched as',
        'core-storage-inspector-facts.last_modified': 'Last modified',
        'core-storage-inspector-facts.last_read': 'Last read',
        'core-storage-inspector-facts.inode_last_changed': 'Inode last changed',
        'core-storage-inspector-facts.owner': 'Owner',
        'core-storage-inspector-facts.permissions': 'Permissions',
        'core-storage-inspector-facts.unknown': 'unknown',
      }),
      es: Object.freeze({
        'core-storage-inspector-facts.kind': 'Tipo',
        'core-storage-inspector-facts.directory': 'directorio',
        'core-storage-inspector-facts.type': 'Tipo',
        'core-storage-inspector-facts.watched_as': 'Vigilado como',
        'core-storage-inspector-facts.last_modified': 'Última modificación',
        'core-storage-inspector-facts.last_read': 'Última lectura',
        'core-storage-inspector-facts.inode_last_changed':
          'Último cambio de inodo',
        'core-storage-inspector-facts.owner': 'Propietario',
        'core-storage-inspector-facts.permissions': 'Permisos',
        'core-storage-inspector-facts.unknown': 'desconocido',
      }),
      hi: Object.freeze({
        'core-storage-inspector-facts.kind': 'प्रकार',
        'core-storage-inspector-facts.directory': 'डायरेक्टरी',
        'core-storage-inspector-facts.type': 'प्रकार',
        'core-storage-inspector-facts.watched_as': 'के रूप में निगरानी',
        'core-storage-inspector-facts.last_modified': 'अंतिम संशोधन',
        'core-storage-inspector-facts.last_read': 'अंतिम पढ़ा गया',
        'core-storage-inspector-facts.inode_last_changed':
          'इनोड अंतिम बार बदला',
        'core-storage-inspector-facts.owner': 'स्वामी',
        'core-storage-inspector-facts.permissions': 'अनुमतियाँ',
        'core-storage-inspector-facts.unknown': 'अज्ञात',
      }),
      ar: Object.freeze({
        'core-storage-inspector-facts.kind': 'النوع',
        'core-storage-inspector-facts.directory': 'مجلد',
        'core-storage-inspector-facts.type': 'النوع',
        'core-storage-inspector-facts.watched_as': 'مراقب بوصفه',
        'core-storage-inspector-facts.last_modified': 'آخر تعديل',
        'core-storage-inspector-facts.last_read': 'آخر قراءة',
        'core-storage-inspector-facts.inode_last_changed': 'آخر تغيير للعقدة',
        'core-storage-inspector-facts.owner': 'المالك',
        'core-storage-inspector-facts.permissions': 'الأذونات',
        'core-storage-inspector-facts.unknown': 'غير معروف',
      }),
      zh: Object.freeze({
        'core-storage-inspector-facts.kind': '类型',
        'core-storage-inspector-facts.directory': '目录',
        'core-storage-inspector-facts.type': '类型',
        'core-storage-inspector-facts.watched_as': '监控为',
        'core-storage-inspector-facts.last_modified': '最后修改',
        'core-storage-inspector-facts.last_read': '最后读取',
        'core-storage-inspector-facts.inode_last_changed': 'inode 最后更改',
        'core-storage-inspector-facts.owner': '所有者',
        'core-storage-inspector-facts.permissions': '权限',
        'core-storage-inspector-facts.unknown': '未知',
      }),
    }),
  }),
  render({ key }, { i18n }) {
    return i18n.t(
      `core-storage-inspector-facts.${key}`,
      {},
      this.manifest.catalogs.en[`core-storage-inspector-facts.${key}`],
    );
  },
};
