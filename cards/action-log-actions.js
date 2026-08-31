window.ZogographActionLogActionsCard = {
  manifest: Object.freeze({
    id: 'core-action-log-actions',
    locales: ['core-action-log-actions.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-action-log-actions.deleted_file': 'deleted a file',
        'core-action-log-actions.emptied_directory': 'emptied a directory',
        'core-action-log-actions.ran_scan': 'ran a scan',
        'core-action-log-actions.changed_schedule': 'changed the schedule',
        'core-action-log-actions.changed_update_policy':
          'changed the system update policy',
      }),
      es: Object.freeze({
        'core-action-log-actions.deleted_file': 'eliminó un archivo',
        'core-action-log-actions.emptied_directory': 'vació un directorio',
        'core-action-log-actions.ran_scan': 'ejecutó un análisis',
        'core-action-log-actions.changed_schedule': 'cambió la programación',
        'core-action-log-actions.changed_update_policy':
          'cambió la política de actualización del sistema',
      }),
      hi: Object.freeze({
        'core-action-log-actions.deleted_file': 'एक फ़ाइल हटाई',
        'core-action-log-actions.emptied_directory': 'एक डायरेक्टरी खाली की',
        'core-action-log-actions.ran_scan': 'एक स्कैन चलाया',
        'core-action-log-actions.changed_schedule': 'शेड्यूल बदला',
        'core-action-log-actions.changed_update_policy':
          'सिस्टम अपडेट नीति बदली',
      }),
      ar: Object.freeze({
        'core-action-log-actions.deleted_file': 'حذف ملفًا',
        'core-action-log-actions.emptied_directory': 'أفرغ دليلاً',
        'core-action-log-actions.ran_scan': 'شغّل فحصًا',
        'core-action-log-actions.changed_schedule': 'غيّر الجدول الزمني',
        'core-action-log-actions.changed_update_policy':
          'غيّر سياسة تحديث النظام',
      }),
      zh: Object.freeze({
        'core-action-log-actions.deleted_file': '删除了一个文件',
        'core-action-log-actions.emptied_directory': '清空了一个目录',
        'core-action-log-actions.ran_scan': '运行了一次扫描',
        'core-action-log-actions.changed_schedule': '更改了计划',
        'core-action-log-actions.changed_update_policy': '更改了系统更新策略',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-action-log-actions.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
