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
        'core-storage-inspector-facts.hard_links': 'Enlaces duros',
        'core-storage-inspector-facts.hard_links_detail':
          '{{count, number}} — se puede acceder a los mismos bytes mediante {{count, number}} rutas; eliminar este no libera nada hasta que se elimine el último',
        'core-storage-inspector-facts.subdirectories': 'Subdirectorios',
        'core-storage-inspector-facts.subdirectories_detail':
          '{{count, number}}, contado a partir del número de enlaces',
        'core-storage-inspector-facts.inode': 'Inodo',
        'core-storage-inspector-facts.points_at': 'Apunta a',
        'core-storage-inspector-facts.unreadable': 'ilegible',
        'core-storage-inspector-facts.target_exists': 'El destino existe',
        'core-storage-inspector-facts.yes': 'sí',
        'core-storage-inspector-facts.no_link_broken':
          'no — este enlace está roto',
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
        'core-storage-inspector-facts.hard_links': 'हार्ड लिंक',
        'core-storage-inspector-facts.hard_links_detail':
          '{{count, number}} — समान बाइट तक {{count, number}} पथों से पहुँचा जा सकता है, और इसे हटाने से आखिरी के जाने तक कुछ भी मुक्त नहीं होगा',
        'core-storage-inspector-facts.subdirectories': 'उपनिर्देशिकाएँ',
        'core-storage-inspector-facts.subdirectories_detail':
          '{{count, number}}, लिंक गिनती से गिना गया',
        'core-storage-inspector-facts.inode': 'इनोड',
        'core-storage-inspector-facts.points_at': 'इस ओर इंगित करता है',
        'core-storage-inspector-facts.unreadable': 'पढ़ा नहीं जा सकता',
        'core-storage-inspector-facts.target_exists': 'लक्ष्य मौजूद है',
        'core-storage-inspector-facts.yes': 'हाँ',
        'core-storage-inspector-facts.no_link_broken':
          'नहीं — यह लिंक टूटा हुआ है',
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
        'core-storage-inspector-facts.hard_links': 'روابط صلبة',
        'core-storage-inspector-facts.hard_links_detail':
          '{{count, number}} — يمكن الوصول إلى البايتات نفسها عبر {{count, number}} مسارات، وإزالة هذا لا تحرر شيئًا حتى يزول الأخير',
        'core-storage-inspector-facts.subdirectories': 'أدلة فرعية',
        'core-storage-inspector-facts.subdirectories_detail':
          '{{count, number}}، محسوبة من عدد الروابط',
        'core-storage-inspector-facts.inode': 'العقدة',
        'core-storage-inspector-facts.points_at': 'يشير إلى',
        'core-storage-inspector-facts.unreadable': 'غير قابل للقراءة',
        'core-storage-inspector-facts.target_exists': 'الهدف موجود',
        'core-storage-inspector-facts.yes': 'نعم',
        'core-storage-inspector-facts.no_link_broken': 'لا — هذا الرابط معطّل',
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
        'core-storage-inspector-facts.hard_links': '硬链接',
        'core-storage-inspector-facts.hard_links_detail':
          '{{count, number}} — 可通过 {{count, number}} 条路径访问相同字节，删除此项在最后一个消失前不会释放任何空间',
        'core-storage-inspector-facts.subdirectories': '子目录',
        'core-storage-inspector-facts.subdirectories_detail':
          '{{count, number}}，按链接数计算',
        'core-storage-inspector-facts.inode': 'inode',
        'core-storage-inspector-facts.points_at': '指向',
        'core-storage-inspector-facts.unreadable': '无法读取',
        'core-storage-inspector-facts.target_exists': '目标存在',
        'core-storage-inspector-facts.yes': '是',
        'core-storage-inspector-facts.no_link_broken': '否 — 此链接已损坏',
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
