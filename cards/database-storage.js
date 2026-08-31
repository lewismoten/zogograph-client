/* Core card package: reusable SQLite-file storage facts and gauges. */
window.ZogographDatabaseStorageCard = {
  manifest: Object.freeze({
    id: 'core-database-storage',
    locales: ['core-database-storage.'],
    helpers: [
      'bandOf',
      'humanBytes',
      'humanCount',
      'count',
      'ago',
      'note',
      'factGrid',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-database-storage.percent': '{{percent, number}}%',
        'core-database-storage.empty_space_amount':
          '{{bytes}} ({{percent, number}}% of it)',
        'core-database-storage.pages_of_size': '{{pages}} of {{pageSize}}',
        'core-database-storage.projected_with_growth':
          '{{projected}}, plus about {{growth}} a year that nothing expires',
        'core-database-storage.write_ahead_log_bytes':
          ' + {{bytes}} write-ahead log',
        'core-database-storage.quick_check_passed': '✓ Quick check passed',
        'core-database-storage.not_checked': 'not checked',
        'core-database-storage.integrity_not_checked': '⚠ {{message}}',
        'core-database-storage.migration_snapshots':
          '{{snapshots}} · newest {{newest}}',
        'core-database-storage.no_migration_snapshot':
          'No migration snapshot needed yet',
        'core-database-storage.file': 'The file',
        'core-database-storage.empty_space_inside': 'Empty space inside it',
        'core-database-storage.integrity': 'Integrity',
        'core-database-storage.migration_recovery': 'Migration recovery',
        'core-database-storage.pages': 'Pages',
        'core-database-storage.journal': 'Journal',
        'core-database-storage.settles_near': 'Settles near',
        'core-database-storage.empty_space': 'Empty space',
        'core-database-storage.empty_space_help':
          '{{bytes}} can be reused before the file grows',
        'core-database-storage.file_vs_settles': 'File versus settles',
        'core-database-storage.file_vs_settles_help':
          '{{current}} now · settles near {{projected}}',
        'core-database-storage.expected_settled_size': 'Expected settled size',
        'core-database-storage.wal_mode': 'Journal mode: write-ahead logging. ',
        'core-database-storage.wal_link': 'How SQLite WAL works ↗',
        'core-database-storage.wal_larger_warning':
          'The write-ahead log is larger than the database itself, which means it has not been checkpointed for a while. It is not lost data — it is the database — but it is worth knowing when comparing this page against what ls says.',
        'core-database-storage.not_on_disk': 'Not on disk.',
        'core-database-storage.table': 'Table',
        'core-database-storage.rows': 'Rows',
      }),
      es: Object.freeze({
        'core-database-storage.percent': '{{percent, number}}%',
        'core-database-storage.empty_space_amount':
          '{{bytes}} ({{percent, number}}% del total)',
        'core-database-storage.pages_of_size': '{{pages}} de {{pageSize}}',
        'core-database-storage.projected_with_growth':
          '{{projected}}, más unos {{growth}} al año si no caduca nada',
        'core-database-storage.write_ahead_log_bytes':
          ' + registro de escritura anticipada de {{bytes}}',
        'core-database-storage.quick_check_passed':
          '✓ Comprobación rápida superada',
        'core-database-storage.not_checked': 'sin comprobar',
        'core-database-storage.integrity_not_checked': '⚠ {{message}}',
        'core-database-storage.migration_snapshots':
          '{{snapshots}} · la más reciente {{newest}}',
        'core-database-storage.no_migration_snapshot':
          'Aún no se necesita una instantánea de migración',
        'core-database-storage.file': 'El archivo',
        'core-database-storage.empty_space_inside': 'Espacio vacío dentro',
        'core-database-storage.integrity': 'Integridad',
        'core-database-storage.migration_recovery': 'Recuperación de migración',
        'core-database-storage.pages': 'Páginas',
        'core-database-storage.journal': 'Diario',
        'core-database-storage.settles_near': 'Se estabiliza cerca de',
        'core-database-storage.empty_space': 'Espacio vacío',
        'core-database-storage.empty_space_help':
          '{{bytes}} se pueden reutilizar antes de que el archivo crezca',
        'core-database-storage.file_vs_settles':
          'Archivo frente al tamaño estable',
        'core-database-storage.file_vs_settles_help':
          '{{current}} ahora · se estabiliza cerca de {{projected}}',
        'core-database-storage.expected_settled_size': 'Tamaño final esperado',
        'core-database-storage.wal_mode':
          'Modo de diario: registro de escritura anticipada. ',
        'core-database-storage.wal_link': 'Cómo funciona SQLite WAL ↗',
        'core-database-storage.wal_larger_warning':
          'El registro de escritura anticipada es mayor que la propia base de datos, lo que significa que no se ha realizado un punto de control desde hace un tiempo. No son datos perdidos: es la base de datos, pero conviene saberlo al comparar esta página con lo que dice ls.',
        'core-database-storage.not_on_disk': 'No está en el disco.',
        'core-database-storage.table': 'Tabla',
        'core-database-storage.rows': 'Filas',
      }),
      hi: Object.freeze({
        'core-database-storage.percent': '{{percent, number}}%',
        'core-database-storage.empty_space_amount':
          '{{bytes}} (इसका {{percent, number}}%)',
        'core-database-storage.pages_of_size': '{{pageSize}} के {{pages}}',
        'core-database-storage.projected_with_growth':
          '{{projected}}, साथ ही लगभग {{growth}} प्रति वर्ष यदि कुछ भी समाप्त न हो',
        'core-database-storage.write_ahead_log_bytes':
          ' + {{bytes}} राइट-अहेड लॉग',
        'core-database-storage.quick_check_passed': '✓ त्वरित जाँच पास हुई',
        'core-database-storage.not_checked': 'जाँचा नहीं गया',
        'core-database-storage.integrity_not_checked': '⚠ {{message}}',
        'core-database-storage.migration_snapshots':
          '{{snapshots}} · नवीनतम {{newest}}',
        'core-database-storage.no_migration_snapshot':
          'अभी माइग्रेशन स्नैपशॉट की आवश्यकता नहीं है',
        'core-database-storage.file': 'फ़ाइल',
        'core-database-storage.empty_space_inside': 'इसके भीतर खाली स्थान',
        'core-database-storage.integrity': 'अखंडता',
        'core-database-storage.migration_recovery': 'माइग्रेशन पुनर्प्राप्ति',
        'core-database-storage.pages': 'पृष्ठ',
        'core-database-storage.journal': 'जर्नल',
        'core-database-storage.settles_near': 'के पास स्थिर होता है',
        'core-database-storage.empty_space': 'खाली स्थान',
        'core-database-storage.empty_space_help':
          'फ़ाइल बढ़ने से पहले {{bytes}} का पुन: उपयोग किया जा सकता है',
        'core-database-storage.file_vs_settles': 'फ़ाइल बनाम स्थिर आकार',
        'core-database-storage.file_vs_settles_help':
          'अब {{current}} · {{projected}} के पास स्थिर होता है',
        'core-database-storage.expected_settled_size': 'अपेक्षित स्थिर आकार',
        'core-database-storage.wal_mode': 'जर्नल मोड: राइट-अहेड लॉगिंग। ',
        'core-database-storage.wal_link': 'SQLite WAL कैसे काम करता है ↗',
        'core-database-storage.wal_larger_warning':
          'राइट-अहेड लॉग डेटाबेस से बड़ा है, जिसका अर्थ है कि कुछ समय से इसका चेकपॉइंट नहीं हुआ है। यह खोया हुआ डेटा नहीं है — यह डेटाबेस ही है — लेकिन इस पृष्ठ की ls के परिणाम से तुलना करते समय यह जानना उपयोगी है।',
        'core-database-storage.not_on_disk': 'डिस्क पर नहीं है।',
        'core-database-storage.table': 'तालिका',
        'core-database-storage.rows': 'पंक्तियाँ',
      }),
      ar: Object.freeze({
        'core-database-storage.percent': '{{percent, number}}%',
        'core-database-storage.empty_space_amount':
          '{{bytes}} ({{percent, number}}% منه)',
        'core-database-storage.pages_of_size': '{{pages}} من {{pageSize}}',
        'core-database-storage.projected_with_growth':
          '{{projected}}، مع نحو {{growth}} سنويًا دون انتهاء أي شيء',
        'core-database-storage.write_ahead_log_bytes':
          ' + سجل كتابة مسبقة بحجم {{bytes}}',
        'core-database-storage.quick_check_passed': '✓ اجتاز الفحص السريع',
        'core-database-storage.not_checked': 'لم يُفحص',
        'core-database-storage.integrity_not_checked': '⚠ {{message}}',
        'core-database-storage.migration_snapshots':
          '{{snapshots}} · الأحدث {{newest}}',
        'core-database-storage.no_migration_snapshot':
          'لا تحتاج لقطة ترحيل بعد',
        'core-database-storage.file': 'الملف',
        'core-database-storage.empty_space_inside': 'مساحة فارغة بداخله',
        'core-database-storage.integrity': 'السلامة',
        'core-database-storage.migration_recovery': 'استرداد الترحيل',
        'core-database-storage.pages': 'الصفحات',
        'core-database-storage.journal': 'السجل',
        'core-database-storage.settles_near': 'يستقر قرب',
        'core-database-storage.empty_space': 'مساحة فارغة',
        'core-database-storage.empty_space_help':
          'يمكن إعادة استخدام {{bytes}} قبل أن يكبر الملف',
        'core-database-storage.file_vs_settles': 'الملف مقابل الحجم المستقر',
        'core-database-storage.file_vs_settles_help':
          '{{current}} الآن · يستقر قرب {{projected}}',
        'core-database-storage.expected_settled_size': 'الحجم المستقر المتوقع',
        'core-database-storage.wal_mode': 'وضع السجل: تسجيل مسبق للكتابة. ',
        'core-database-storage.wal_link': 'كيف يعمل SQLite WAL ↗',
        'core-database-storage.wal_larger_warning':
          'سجل الكتابة المسبقة أكبر من قاعدة البيانات نفسها، ما يعني أنه لم يُنفذ له نقطة تحقق منذ فترة. ليست بيانات مفقودة — إنه قاعدة البيانات — لكن من المفيد معرفة ذلك عند مقارنة هذه الصفحة بما يقوله ls.',
        'core-database-storage.not_on_disk': 'ليست على القرص.',
        'core-database-storage.table': 'الجدول',
        'core-database-storage.rows': 'الصفوف',
      }),
      zh: Object.freeze({
        'core-database-storage.percent': '{{percent, number}}%',
        'core-database-storage.empty_space_amount':
          '{{bytes}}（占总量的 {{percent, number}}%）',
        'core-database-storage.pages_of_size': '{{pageSize}} 的 {{pages}}',
        'core-database-storage.projected_with_growth':
          '{{projected}}，若没有任何内容到期，每年还会增加约 {{growth}}',
        'core-database-storage.write_ahead_log_bytes': ' + {{bytes}} 预写日志',
        'core-database-storage.quick_check_passed': '✓ 快速检查已通过',
        'core-database-storage.not_checked': '未检查',
        'core-database-storage.integrity_not_checked': '⚠ {{message}}',
        'core-database-storage.migration_snapshots':
          '{{snapshots}} · 最新 {{newest}}',
        'core-database-storage.no_migration_snapshot': '尚不需要迁移快照',
        'core-database-storage.file': '文件',
        'core-database-storage.empty_space_inside': '其中的空闲空间',
        'core-database-storage.integrity': '完整性',
        'core-database-storage.migration_recovery': '迁移恢复',
        'core-database-storage.pages': '页',
        'core-database-storage.journal': '日志',
        'core-database-storage.settles_near': '稳定在约',
        'core-database-storage.empty_space': '空闲空间',
        'core-database-storage.empty_space_help': '文件增长前可复用 {{bytes}}',
        'core-database-storage.file_vs_settles': '文件与稳定值',
        'core-database-storage.file_vs_settles_help':
          '当前 {{current}} · 稳定在约 {{projected}}',
        'core-database-storage.expected_settled_size': '预期稳定大小',
        'core-database-storage.wal_mode': '日志模式：预写式日志。 ',
        'core-database-storage.wal_link': 'SQLite WAL 的工作方式 ↗',
        'core-database-storage.wal_larger_warning':
          '预写式日志比数据库本身更大，这意味着已有一段时间没有检查点了。这不是丢失的数据——它就是数据库——但将此页面与 ls 的结果进行比较时，了解这一点很重要。',
        'core-database-storage.not_on_disk': '不在磁盘上。',
        'core-database-storage.table': '表',
        'core-database-storage.rows': '行',
      }),
    }),
  }),
  render({ file, projection = null }, helpers) {
    const { bandOf, humanBytes, humanCount, count, ago, note, factGrid, i18n } =
      helpers;
    const t = (key, values = {}, fallback = key) =>
      i18n.t(`core-database-storage.${key}`, values, fallback);
    const gauge = (label, value, warn, crit, sub, tick) => {
      const box = document.createElement('div');
      const band = bandOf(value, warn, crit);
      box.className = `database-gauge band-${band || 'ok'}`;
      const head = document.createElement('div');
      head.className = 'database-gauge-head';
      const name = document.createElement('strong');
      name.textContent = label;
      const number = document.createElement('span');
      number.textContent = t(
        'percent',
        { percent: Math.round(value) },
        '{{percent, number}}%',
      );
      head.append(name, number);
      const track = document.createElement('div');
      track.className = 'database-gauge-track';
      track.style.setProperty(
        '--fill',
        `${Math.max(0, Math.min(100, value / 1.5))}%`,
      );
      if (tick != null) {
        const marker = document.createElement('span');
        marker.className = 'database-gauge-tick';
        marker.style.setProperty('--tick', `${tick / 1.5}%`);
        marker.title = t('expected_settled_size', {}, 'Expected settled size');
        track.appendChild(marker);
      }
      const said = document.createElement('div');
      said.className = 'muted small';
      said.textContent = sub;
      box.append(head, track, said);
      return box;
    };
    const meters = document.createElement('div');
    meters.className = 'database-gauges';
    meters.appendChild(
      gauge(
        t('empty_space', {}, 'Empty space'),
        Number(file.free_share || 0) * 100,
        25,
        50,
        t(
          'empty_space_help',
          { bytes: humanBytes(file.free_bytes) },
          '{{bytes}} can be reused before the file grows',
        ),
        null,
      ),
    );
    if (projection?.projected > 0) {
      const ratio =
        (Number(file.bytes || 0) / Number(projection.projected)) * 100;
      meters.appendChild(
        gauge(
          t('file_vs_settles', {}, 'File versus settles'),
          ratio,
          105,
          125,
          t(
            'file_vs_settles_help',
            {
              current: humanBytes(file.bytes),
              projected: humanBytes(projection.projected),
            },
            '{{current}} now · settles near {{projected}}',
          ),
          100,
        ),
      );
    }
    const facts = [
      [
        t('file', {}, 'The file'),
        `${humanBytes(file.bytes)}${file.wal_bytes ? t('write_ahead_log_bytes', { bytes: humanBytes(file.wal_bytes) }, ' + {{bytes}} write-ahead log') : ''}`,
      ],
      [
        t('empty_space_inside', {}, 'Empty space inside it'),
        t(
          'empty_space_amount',
          {
            bytes: humanBytes(file.free_bytes),
            percent: Math.round(file.free_share * 100),
          },
          '{{bytes}} ({{percent, number}}% of it)',
        ),
      ],
      [
        t('integrity', {}, 'Integrity'),
        file.integrity?.ok
          ? t('quick_check_passed', {}, '✓ Quick check passed')
          : t(
              'integrity_not_checked',
              {
                message:
                  file.integrity?.message ||
                  t('not_checked', {}, 'not checked'),
              },
              '⚠ {{message}}',
            ),
      ],
      [
        t('migration_recovery', {}, 'Migration recovery'),
        file.integrity?.migration_backups
          ? t(
              'migration_snapshots',
              {
                snapshots: count(
                  file.integrity.migration_backups,
                  'private snapshot',
                ),
                newest: ago(file.integrity.newest_backup_at),
              },
              '{{snapshots}} · newest {{newest}}',
            )
          : t('no_migration_snapshot', {}, 'No migration snapshot needed yet'),
      ],
      [
        t('pages', {}, 'Pages'),
        t(
          'pages_of_size',
          {
            pages: humanCount(file.pages),
            pageSize: humanBytes(file.page_size),
          },
          '{{pages}} of {{pageSize}}',
        ),
      ],
      [t('journal', {}, 'Journal'), file.journal],
    ];
    if (projection)
      facts.push([
        t('settles_near', {}, 'Settles near'),
        projection.known
          ? humanBytes(projection.projected)
          : t(
              'projected_with_growth',
              {
                projected: humanBytes(projection.projected),
                growth: humanBytes(projection.growth),
              },
              '{{projected}}, plus about {{growth}} a year that nothing expires',
            ),
      ]);
    const wrap = document.createDocumentFragment();
    wrap.append(meters, factGrid(facts));
    if (String(file.journal).toLowerCase() === 'wal') {
      const journal = document.createElement('p');
      journal.className = 'muted small';
      journal.append(t('wal_mode', {}, 'Journal mode: write-ahead logging. '));
      const link = document.createElement('a');
      link.href = 'https://www.sqlite.org/wal.html';
      link.target = '_blank';
      link.rel = 'noopener noreferrer';
      link.textContent = t('wal_link', {}, 'How SQLite WAL works ↗');
      journal.appendChild(link);
      wrap.appendChild(journal);
    }
    const where = document.createElement('p');
    where.className = 'muted small';
    where.textContent = file.path;
    wrap.appendChild(where);
    if (file.wal_bytes > file.bytes)
      wrap.appendChild(
        note(
          t(
            'wal_larger_warning',
            {},
            'The write-ahead log is larger than the database itself, which means it has not been checkpointed for a while. It is not lost data — it is the database — but it is worth knowing when comparing this page against what ls says.',
          ),
        ),
      );
    return wrap;
  },
};
