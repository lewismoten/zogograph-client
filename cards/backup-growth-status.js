/* Core card package: backup availability and disk-growth status. */
window.ZogographBackupGrowthStatusCard = {
  manifest: Object.freeze({
    id: 'core-backup-growth-status',
    locales: ['core-backup-growth-status.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-backup-growth-status.nothing_recorded': 'Nothing recorded.',
        'core-backup-growth-status.this_filesystem': 'this filesystem',
        'core-backup-growth-status.used_space_on':
          '{{percent}}% of the used space on {{filesystem}}',
        'core-backup-growth-status.share_unknown':
          'Share of the disk unknown — no filesystem reading recorded.',
        'core-backup-growth-status.walk_incomplete':
          'The disk walk ran out of time, so this total is a lower bound.',
        'core-backup-growth-status.directories_not_opened':
          '{{count}} directories under the backup roots were never opened.',
        'core-backup-growth-status.day_ago': '24 hours ago',
        'core-backup-growth-status.week_ago': '7 days ago',
        'core-backup-growth-status.grew_over': '{{bytes}} over {{duration}}',
        'core-backup-growth-status.growth': 'Growth',
      }),
      es: Object.freeze({
        'core-backup-growth-status.nothing_recorded': 'No hay nada registrado.',
        'core-backup-growth-status.this_filesystem': 'este sistema de archivos',
        'core-backup-growth-status.used_space_on':
          '{{percent}} % del espacio usado en {{filesystem}}',
        'core-backup-growth-status.share_unknown':
          'Se desconoce la proporción del disco: no se registró ninguna lectura del sistema de archivos.',
        'core-backup-growth-status.walk_incomplete':
          'El recorrido del disco se quedó sin tiempo, por lo que este total es un límite inferior.',
        'core-backup-growth-status.directories_not_opened':
          'Nunca se abrieron {{count}} directorios bajo las raíces de las copias de seguridad.',
        'core-backup-growth-status.day_ago': 'hace 24 horas',
        'core-backup-growth-status.week_ago': 'hace 7 días',
        'core-backup-growth-status.grew_over': '{{bytes}} durante {{duration}}',
        'core-backup-growth-status.growth': 'Crecimiento',
      }),
      hi: Object.freeze({
        'core-backup-growth-status.nothing_recorded':
          'कुछ भी रिकॉर्ड नहीं किया गया।',
        'core-backup-growth-status.this_filesystem': 'यह फ़ाइल सिस्टम',
        'core-backup-growth-status.used_space_on':
          '{{filesystem}} पर उपयोग किए गए स्थान का {{percent}}%',
        'core-backup-growth-status.share_unknown':
          'डिस्क में हिस्सा अज्ञात है — कोई फ़ाइल सिस्टम रीडिंग रिकॉर्ड नहीं हुई।',
        'core-backup-growth-status.walk_incomplete':
          'डिस्क स्कैन का समय समाप्त हो गया, इसलिए यह कुल न्यूनतम सीमा है।',
        'core-backup-growth-status.directories_not_opened':
          'बैकअप रूट के अंतर्गत {{count}} डायरेक्टरी कभी नहीं खोली गईं।',
        'core-backup-growth-status.day_ago': '24 घंटे पहले',
        'core-backup-growth-status.week_ago': '7 दिन पहले',
        'core-backup-growth-status.grew_over': '{{duration}} में {{bytes}}',
        'core-backup-growth-status.growth': 'वृद्धि',
      }),
      ar: Object.freeze({
        'core-backup-growth-status.nothing_recorded': 'لا توجد بيانات مسجلة.',
        'core-backup-growth-status.this_filesystem': 'نظام الملفات هذا',
        'core-backup-growth-status.used_space_on':
          '{{percent}}٪ من المساحة المستخدمة على {{filesystem}}',
        'core-backup-growth-status.share_unknown':
          'حصة القرص غير معروفة — لم تُسجل قراءة لنظام الملفات.',
        'core-backup-growth-status.walk_incomplete':
          'انتهى وقت فحص القرص، لذا فإن هذا الإجمالي حد أدنى.',
        'core-backup-growth-status.directories_not_opened':
          'لم تُفتح {{count}} أدلة ضمن جذور النسخ الاحتياطية.',
        'core-backup-growth-status.day_ago': 'قبل 24 ساعة',
        'core-backup-growth-status.week_ago': 'قبل 7 أيام',
        'core-backup-growth-status.grew_over': '{{bytes}} خلال {{duration}}',
        'core-backup-growth-status.growth': 'النمو',
      }),
      zh: Object.freeze({
        'core-backup-growth-status.nothing_recorded': '没有已记录的数据。',
        'core-backup-growth-status.this_filesystem': '此文件系统',
        'core-backup-growth-status.used_space_on':
          '{{filesystem}} 上已用空间的 {{percent}}%',
        'core-backup-growth-status.share_unknown':
          '磁盘占比未知——未记录文件系统读数。',
        'core-backup-growth-status.walk_incomplete':
          '磁盘扫描已超时，因此此总计是下限。',
        'core-backup-growth-status.directories_not_opened':
          '备份根目录下有 {{count}} 个目录从未打开。',
        'core-backup-growth-status.day_ago': '24 小时前',
        'core-backup-growth-status.week_ago': '7 天前',
        'core-backup-growth-status.grew_over': '{{duration}} 内 {{bytes}}',
        'core-backup-growth-status.growth': '增长',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-backup-growth-status.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
