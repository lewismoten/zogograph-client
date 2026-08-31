/* Core card package: the shell supplies all rendering and formatting helpers. */
window.ZogographHostFilesCard = {
  manifest: Object.freeze({
    id: 'core-host-files',
    locales: ['core-host-files.'],
    helpers: [
      'target',
      'totalTarget',
      'renderTable',
      'humanBytes',
      'humanCount',
      'whenSpan',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-host-files.linked_total':
          '{{bytes}} across {{files}} files, reached by {{paths}} paths. {{linked}} extra paths are hard links to files already counted, so they use no additional disk.',
        'core-host-files.path': 'Path',
        'core-host-files.size': 'Size',
        'core-host-files.names': 'Names',
        'core-host-files.owner': 'Owner',
        'core-host-files.last_written': 'Last written',
        'core-host-files.empty': 'Nothing over the size threshold.',
        'core-host-files.also_at': 'also at {{path}}',
        'core-host-files.names_all': '{{shown}} paths, 1 file',
        'core-host-files.names_some': '{{shown}} here of {{total}}, 1 file',
        'core-host-files.total': '{{bytes}} across {{files}} files.',
      }),
      es: Object.freeze({
        'core-host-files.linked_total':
          '{{bytes}} entre {{files}} archivos, alcanzados por {{paths}} rutas. {{linked}} rutas adicionales son enlaces duros a archivos ya contados, así que no usan disco adicional.',
        'core-host-files.path': 'Ruta',
        'core-host-files.size': 'Tamaño',
        'core-host-files.names': 'Nombres',
        'core-host-files.owner': 'Propietario',
        'core-host-files.last_written': 'Última escritura',
        'core-host-files.empty': 'No hay nada por encima del umbral de tamaño.',
        'core-host-files.also_at': 'también en {{path}}',
        'core-host-files.names_all': '{{shown}} rutas, 1 archivo',
        'core-host-files.names_some': '{{shown}} aquí de {{total}}, 1 archivo',
        'core-host-files.total': '{{bytes}} entre {{files}} archivos.',
      }),
      hi: Object.freeze({
        'core-host-files.linked_total':
          '{{files}} फ़ाइलों में {{bytes}}, जिन तक {{paths}} पथों से पहुँचा गया। {{linked}} अतिरिक्त पथ पहले से गिनी गई फ़ाइलों के हार्ड लिंक हैं, इसलिए वे अतिरिक्त डिस्क नहीं लेते।',
        'core-host-files.path': 'पथ',
        'core-host-files.size': 'आकार',
        'core-host-files.names': 'नाम',
        'core-host-files.owner': 'स्वामी',
        'core-host-files.last_written': 'अंतिम बार लिखा गया',
        'core-host-files.empty': 'आकार सीमा से ऊपर कुछ नहीं है।',
        'core-host-files.also_at': '{{path}} पर भी',
        'core-host-files.names_all': '{{shown}} पथ, 1 फ़ाइल',
        'core-host-files.names_some':
          '{{total}} में से {{shown}} यहाँ, 1 फ़ाइल',
        'core-host-files.total': '{{files}} फ़ाइलों में {{bytes}}।',
      }),
      ar: Object.freeze({
        'core-host-files.linked_total':
          '{{bytes}} عبر {{files}} ملفات، يمكن الوصول إليها من {{paths}} مسارات. {{linked}} مسارات إضافية هي روابط صلبة لملفات محسوبة بالفعل، لذا لا تستخدم مساحة قرص إضافية.',
        'core-host-files.path': 'المسار',
        'core-host-files.size': 'الحجم',
        'core-host-files.names': 'الأسماء',
        'core-host-files.owner': 'المالك',
        'core-host-files.last_written': 'آخر كتابة',
        'core-host-files.empty': 'لا شيء يتجاوز حد الحجم.',
        'core-host-files.also_at': 'أيضًا في {{path}}',
        'core-host-files.names_all': '{{shown}} مسارات، ملف واحد',
        'core-host-files.names_some': '{{shown}} هنا من {{total}}، ملف واحد',
        'core-host-files.total': '{{bytes}} عبر {{files}} ملفات.',
      }),
      zh: Object.freeze({
        'core-host-files.linked_total':
          '共 {{files}} 个文件，占用 {{bytes}}，可通过 {{paths}} 条路径访问。另有 {{linked}} 条路径是已统计文件的硬链接，因此不占用额外磁盘空间。',
        'core-host-files.path': '路径',
        'core-host-files.size': '大小',
        'core-host-files.names': '名称',
        'core-host-files.owner': '所有者',
        'core-host-files.last_written': '最后写入',
        'core-host-files.empty': '没有内容超过大小阈值。',
        'core-host-files.also_at': '也位于 {{path}}',
        'core-host-files.names_all': '{{shown}} 条路径，1 个文件',
        'core-host-files.names_some':
          '共 {{total}} 条，此处 {{shown}} 条，1 个文件',
        'core-host-files.total': '共 {{files}} 个文件，占用 {{bytes}}。',
      }),
    }),
  }),

  render(input, helpers) {
    const { rows, totals } = input;
    const {
      target,
      totalTarget,
      renderTable,
      humanBytes,
      humanCount,
      whenSpan,
      i18n,
    } = helpers;
    if (!target) return;
    if (!rows.length) {
      target.replaceChildren(
        Object.assign(document.createElement('p'), {
          className: 'muted',
          textContent: i18n.t(
            'core-host-files.empty',
            {},
            'Nothing over the size threshold.',
          ),
        }),
      );
      return;
    }
    const byInode = new Map();
    for (const row of rows) {
      const key = row.inode ? `${row.device}:${row.inode}` : `path:${row.path}`;
      if (!byInode.has(key)) byInode.set(key, []);
      byInode.get(key).push(row);
    }
    const groups = [...byInode.values()];
    renderTable(
      target.id,
      groups,
      [
        {
          label: i18n.t('core-host-files.path', {}, 'Path'),
          className: 'name',
          render: (group) => {
            const box = document.createElement('div');
            box.appendChild(
              Object.assign(document.createElement('div'), {
                textContent: group[0].path,
              }),
            );
            for (const other of group.slice(1))
              box.appendChild(
                Object.assign(document.createElement('div'), {
                  className: 'muted small',
                  textContent: i18n.t(
                    'core-host-files.also_at',
                    { path: other.path },
                    'also at {{path}}',
                  ),
                }),
              );
            return box;
          },
        },
        {
          label: i18n.t('core-host-files.size', {}, 'Size'),
          className: 'size',
          render: (group) => humanBytes(group[0].bytes),
        },
        {
          label: i18n.t('core-host-files.names', {}, 'Names'),
          className: 'size',
          render: (group) => {
            const links = Number(group[0].links ?? 1);
            if (group.length === 1 && links <= 1) return '1';
            return i18n.t(
              group.length === links
                ? 'core-host-files.names_all'
                : 'core-host-files.names_some',
              { shown: humanCount(group.length), total: humanCount(links) },
              group.length === links
                ? `${humanCount(group.length)} paths, 1 file`
                : `${humanCount(group.length)} here of ${humanCount(links)}, 1 file`,
            );
          },
        },
        {
          label: i18n.t('core-host-files.owner', {}, 'Owner'),
          render: (group) => group[0].owner ?? '—',
        },
        {
          label: i18n.t('core-host-files.last_written', {}, 'Last written'),
          className: 'size',
          render: (group) => whenSpan(group[0].mtime),
        },
      ],
      null,
      null,
      (group) => group[0].path,
    );
    if (!totals || !totalTarget) return;
    const values = {
      bytes: humanBytes(totals.bytes),
      files: humanCount(totals.files),
      paths: humanCount(totals.paths),
      linked: humanCount(totals.linked),
    };
    const line =
      totals.linked > 0
        ? i18n.t(
            'core-host-files.linked_total',
            values,
            '{{bytes}} across {{files}} files, reached by {{paths}} paths. {{linked}} extra paths are hard links to files already counted, so they use no additional disk.',
          )
        : i18n.t(
            'core-host-files.total',
            values,
            '{{bytes}} across {{files}} files.',
          );
    if (totalTarget.textContent !== line) totalTarget.textContent = line;
  },
};
