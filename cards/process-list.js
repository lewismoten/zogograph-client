/* Core card package: the current process list and its optional stop controls. */
window.ZogographProcessListCard = {
  manifest: Object.freeze({
    id: 'core-process-list',
    locales: ['core-process-list.'],
    helpers: [
      'target',
      'stopNote',
      'renderTable',
      'namedThing',
      'userCell',
      'processCpuCell',
      'humanBytes',
      'processAge',
      'stopButtons',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-process-list.process': 'Process',
        'core-process-list.how_many': 'How many',
        'core-process-list.user': 'User',
        'core-process-list.cpu': 'CPU',
        'core-process-list.memory': 'Memory',
        'core-process-list.of_ram': 'Of RAM',
        'core-process-list.running_for': 'Running for',
        'core-process-list.biggest_pid': 'Biggest PID',
        'core-process-list.pid': 'PID',
        'core-process-list.command': 'Command',
        'core-process-list.stop': 'Stop',
      }),
      es: Object.freeze({
        'core-process-list.process': 'Proceso',
        'core-process-list.how_many': 'Cantidad',
        'core-process-list.user': 'Usuario',
        'core-process-list.cpu': 'CPU',
        'core-process-list.memory': 'Memoria',
        'core-process-list.of_ram': 'De RAM',
        'core-process-list.running_for': 'En ejecución',
        'core-process-list.biggest_pid': 'PID principal',
        'core-process-list.pid': 'PID',
        'core-process-list.command': 'Comando',
        'core-process-list.stop': 'Detener',
      }),
      hi: Object.freeze({
        'core-process-list.process': 'प्रक्रिया',
        'core-process-list.how_many': 'कितनी',
        'core-process-list.user': 'उपयोगकर्ता',
        'core-process-list.cpu': 'CPU',
        'core-process-list.memory': 'मेमोरी',
        'core-process-list.of_ram': 'RAM का',
        'core-process-list.running_for': 'चल रहा है',
        'core-process-list.biggest_pid': 'सबसे बड़ा PID',
        'core-process-list.pid': 'PID',
        'core-process-list.command': 'कमांड',
        'core-process-list.stop': 'रोकें',
      }),
      ar: Object.freeze({
        'core-process-list.process': 'العملية',
        'core-process-list.how_many': 'العدد',
        'core-process-list.user': 'المستخدم',
        'core-process-list.cpu': 'CPU',
        'core-process-list.memory': 'الذاكرة',
        'core-process-list.of_ram': 'من الذاكرة',
        'core-process-list.running_for': 'وقت التشغيل',
        'core-process-list.biggest_pid': 'أكبر PID',
        'core-process-list.pid': 'PID',
        'core-process-list.command': 'الأمر',
        'core-process-list.stop': 'إيقاف',
      }),
      zh: Object.freeze({
        'core-process-list.process': '进程',
        'core-process-list.how_many': '数量',
        'core-process-list.user': '用户',
        'core-process-list.cpu': 'CPU',
        'core-process-list.memory': '内存',
        'core-process-list.of_ram': '占内存',
        'core-process-list.running_for': '运行时长',
        'core-process-list.biggest_pid': '最大 PID',
        'core-process-list.pid': 'PID',
        'core-process-list.command': '命令',
        'core-process-list.stop': '停止',
      }),
    }),
  }),

  render(data, helpers) {
    const {
      target,
      stopNote,
      renderTable,
      namedThing,
      userCell,
      processCpuCell,
      humanBytes,
      processAge,
      stopButtons,
      i18n,
    } = helpers;
    if (!target) return;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    const grouped = Boolean(data.grouped);
    const columns = [
      {
        label: t('core-process-list.process', {}, 'Process'),
        render: (row) => namedThing('process', row.name),
      },
      {
        label: t('core-process-list.how_many', {}, 'How many'),
        className: 'size',
        render: (row) => (row.procs > 1 ? row.procs : ''),
      },
      {
        label: t('core-process-list.user', {}, 'User'),
        render: (row) => userCell(row.user),
      },
      {
        label: t('core-process-list.cpu', {}, 'CPU'),
        className: 'size',
        render: (row) => processCpuCell(row.cpu_percent, data.cores),
      },
      {
        label: t('core-process-list.memory', {}, 'Memory'),
        className: 'size',
        render: (row) => humanBytes(row.rss_bytes),
      },
      {
        label: t('core-process-list.of_ram', {}, 'Of RAM'),
        className: 'size',
        render: (row) => `${row.mem_percent.toFixed(1)}%`,
      },
      {
        label: t('core-process-list.running_for', {}, 'Running for'),
        className: 'size',
        render: (row) => processAge(row),
      },
      {
        label: t(
          grouped ? 'core-process-list.biggest_pid' : 'core-process-list.pid',
          {},
          grouped ? 'Biggest PID' : 'PID',
        ),
        className: 'size',
        render: (row) => row.pid,
      },
      {
        label: t('core-process-list.command', {}, 'Command'),
        className: 'cmd',
        render: (row) => row.command,
      },
    ];
    if (data.can_stop) {
      columns.push({
        label: t('core-process-list.stop', {}, 'Stop'),
        className: 'stop-cell',
        render: (row) => stopButtons(row, grouped),
      });
    }
    renderTable(target.id, data.rows || [], columns);
    if (stopNote)
      stopNote.textContent = data.can_stop ? '' : data.stop_reason || '';
  },
};
