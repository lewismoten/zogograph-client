/* Core card package: the shell supplies formatting and the scoped watch action. */
window.ZogographHostDaemonsCard = {
  manifest: Object.freeze({
    id: 'core-host-daemons',
    locales: ['core-host-daemons.'],
    helpers: [
      'target',
      'renderTable',
      'humanDuration',
      'whenSpan',
      'watch',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-host-daemons.title': 'Are the services alive?',
        'core-host-daemons.help':
          'The one question the rest of this page cannot answer. A daemon that has died uses no memory, no CPU and no disk, so it vanishes from every chart above rather than standing out in one. {{not_installed}} is shown apart from {{stopped}} on purpose — a service this machine never ran is not a fault, and colouring it red would make the panel permanently alarming and therefore ignored.',
        'core-host-daemons.empty': 'No service states recorded yet.',
      }),
      es: Object.freeze({
        'core-host-daemons.title': '¿Están activos los servicios?',
        'core-host-daemons.help':
          'La única pregunta que el resto de esta página no puede responder. Un demonio que ha muerto no usa memoria, CPU ni disco, así que desaparece de todos los gráficos en vez de destacar en uno. {{not_installed}} se muestra separado de {{stopped}} deliberadamente: un servicio que esta máquina nunca ejecutó no es un fallo, y colorearlo en rojo haría que el panel estuviera siempre alarmante y se ignorara.',
        'core-host-daemons.empty':
          'Aún no se registraron estados de servicios.',
      }),
      hi: Object.freeze({
        'core-host-daemons.title': 'क्या सेवाएँ चालू हैं?',
        'core-host-daemons.help':
          'यह वह एक प्रश्न है जिसका उत्तर इस पेज का बाकी हिस्सा नहीं दे सकता। बंद हो चुका डेमन मेमोरी, CPU या डिस्क उपयोग नहीं करता, इसलिए वह किसी एक चार्ट में उभरने के बजाय ऊपर के सभी चार्टों से गायब हो जाता है। {{not_installed}} को जानबूझकर {{stopped}} से अलग दिखाया गया है: जो सेवा इस मशीन पर कभी चली ही नहीं वह त्रुटि नहीं है, और उसे लाल दिखाने से पैनल हमेशा चेतावनी जैसा लगेगा और अनदेखा होगा।',
        'core-host-daemons.empty': 'अभी तक कोई सेवा स्थिति दर्ज नहीं हुई।',
      }),
      ar: Object.freeze({
        'core-host-daemons.title': 'هل الخدمات تعمل؟',
        'core-host-daemons.help':
          'هذا هو السؤال الوحيد الذي لا تجيب عنه بقية الصفحة. الخادم الذي توقف لا يستخدم ذاكرة أو معالجًا أو قرصًا، لذا يختفي من كل المخططات أعلاه بدل أن يبرز في واحد منها. يُعرض {{not_installed}} منفصلًا عن {{stopped}} عمدًا: فالخدمة التي لم يشغّلها هذا الجهاز قط ليست عطلًا، وتلوينها بالأحمر سيجعل اللوحة مقلقة دائمًا ثم تُهمَل.',
        'core-host-daemons.empty': 'لم تُسجَّل حالات الخدمات بعد.',
      }),
      zh: Object.freeze({
        'core-host-daemons.title': '服务是否正常运行？',
        'core-host-daemons.help':
          '这是本页其他部分无法回答的唯一问题。已停止的守护进程不使用内存、CPU 或磁盘，因此会从上方所有图表中消失，而不是在某一张图上突出显示。{{not_installed}} 会刻意与 {{stopped}} 分开显示：这台机器从未运行的服务并非故障；将其标红会使面板永远处于警报状态，反而被忽略。',
        'core-host-daemons.empty': '尚未记录服务状态。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-host-daemons.${key}`,
      {},
      this.manifest.catalogs.en[`core-host-daemons.${key}`],
    );
  },

  render(daemons, helpers) {
    const { target, renderTable, humanDuration, whenSpan, watch, i18n } =
      helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (!target) return;
    if (!daemons || !daemons.available || !daemons.rows?.length) {
      target.replaceChildren(
        Object.assign(document.createElement('p'), {
          className: 'muted',
          textContent:
            daemons?.reason ||
            t('core-host-daemons.empty', {}, 'No service states recorded yet.'),
        }),
      );
      return;
    }
    const states = {
      running: {
        text: t('host.daemon_running', {}, 'running'),
        className: 'state-up',
      },
      stopped: {
        text: t('host.daemon_stopped', {}, 'STOPPED'),
        className: 'state-down',
      },
      absent: {
        text: t('host.daemon_not_installed', {}, 'not installed'),
        className: 'state-absent',
      },
      unknown: {
        text: t('host.daemon_cannot_tell', {}, 'cannot tell'),
        className: 'state-absent',
      },
      off: {
        text: t('host.daemon_not_watched', {}, 'not watched'),
        className: 'state-absent',
      },
      pending: {
        text: t('host.daemon_waiting', {}, 'waiting for a reading'),
        className: 'state-absent',
      },
    };
    const now = Date.now() / 1000;
    renderTable(target.id, daemons.rows, [
      {
        label: t('host.daemon_service', {}, 'Service'),
        className: 'name',
        render: (row) => row.label,
      },
      {
        label: t('host.daemon_state', {}, 'State'),
        render: (row) => {
          const span = document.createElement('span');
          if (row.state === 'stopped' && !row.since_known) {
            span.className = 'state-absent';
            span.textContent = t('host.daemon_stopped_lower', {}, 'stopped');
            span.title = t(
              'host.daemon_stopped_all_readings_tip',
              {},
              'Stopped for every reading on record, so this was almost certainly disabled deliberately rather than having failed. It does not raise alerts.',
            );
            return span;
          }
          const shown = states[row.state] ?? states.unknown;
          span.className = shown.className;
          span.textContent = shown.text;
          const tips = {
            off: [
              'host.daemon_not_watched_tip',
              'Switched off here, so the collector is not recording it. Nothing is known about whether it is running.',
            ],
            pending: [
              'host.daemon_waiting_tip',
              'Switched on, but the collector has not run since. It reports on its own schedule.',
            ],
            absent: [
              'host.daemon_not_installed_tip',
              'Neither a systemd unit nor a running process by any of the names this service is known by. Almost certainly not installed on this machine.',
            ],
            unknown: [
              'host.daemon_cannot_tell_tip',
              'systemd could not be asked and no matching process is running. Stopped and never-installed look identical from a process list.',
            ],
            stopped: [
              'host.daemon_stopped_tip',
              'This was running earlier and is not now.',
            ],
          };
          if (tips[row.state]) {
            span.title = t(tips[row.state][0], {}, tips[row.state][1]);
          }
          return span;
        },
      },
      {
        label: t('host.daemon_for', {}, 'For'),
        className: 'size',
        render: (row) => {
          if (['absent', 'unknown', 'off', 'pending'].includes(row.state))
            return '—';
          const at = row.started_at ?? row.since;
          if (!at) return '—';
          const span = document.createElement('span');
          span.textContent = humanDuration(now - at);
          span.title = row.started_at
            ? t('host.daemon_since_systemd', {}, 'Since systemd started it.')
            : row.since_known
              ? t(
                  'host.daemon_since_state',
                  {},
                  'Since this state was first seen here.',
                )
              : t(
                  'host.daemon_at_least_this_long',
                  {},
                  'At least this long — it has been this way for every reading on record.',
                );
          if (!row.started_at && !row.since_known) span.className = 'muted';
          return span;
        },
      },
      {
        label: t('host.daemon_restarts', {}, 'Restarts'),
        className: 'size',
        render: (row) => (row.restarts == null ? '—' : String(row.restarts)),
      },
      {
        label: t('host.daemon_processes', {}, 'Processes'),
        className: 'size',
        render: (row) =>
          row.units_total > 1
            ? t(
                'host.daemon_active_units',
                { active: row.units_active, total: row.units_total },
                '{{active}}/{{total}} units',
              )
            : row.procs
              ? String(row.procs)
              : '—',
      },
      {
        label: t('host.daemon_last_seen_down', {}, 'Last seen down'),
        render: (row) => {
          if (row.last_down_at) return whenSpan(row.last_down_at);
          const span = document.createElement('span');
          span.className = 'muted';
          span.textContent = ['absent', 'off', 'pending'].includes(row.state)
            ? '—'
            : t(
                'host.daemon_not_in_days',
                { days: daemons.history_days },
                'not in {{days, number}} days',
              );
          return span;
        },
      },
      {
        label: '',
        render: (row) => {
          const button = document.createElement('button');
          button.className = row.watched ? 'link' : 'link muted';
          button.textContent = row.watched
            ? t('host.daemon_stop_watching', {}, 'Stop watching')
            : t('host.daemon_watch', {}, 'Watch');
          button.title = row.watched
            ? t(
                'host.daemon_stop_watching_tip',
                {},
                'Stop recording this service. Its history is kept, but nothing more is added and it will not raise alerts.',
              )
            : t(
                'host.daemon_watch_tip',
                {},
                "Start recording this service again from the collector's next run.",
              );
          button.addEventListener('click', () => watch(row, button));
          return button;
        },
      },
    ]);
  },
};
