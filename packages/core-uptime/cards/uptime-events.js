/* Core card package: the shell supplies row formatting and the event data. */
const UptimeEventsCard = {
  manifest: Object.freeze({
    id: 'core-uptime-events',
    locales: ['core-uptime-events.'],
    helpers: ['target', 'renderTable', 'whenSpan', 'humanDuration', 'ui', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-uptime-events.empty':
          'Every reading in this window is present and the machine never restarted. Nothing to explain.',
        'core-uptime-events.restarted': 'Restarted',
        'core-uptime-events.what': 'What',
        'core-uptime-events.from': 'From',
        'core-uptime-events.until': 'Until',
        'core-uptime-events.for': 'For',
        'core-uptime-events.meaning': 'What it means',
        'core-uptime-events.reading_gap': 'Reading gap',
        'core-uptime-events.restart_explanation':
          'Boot time moved, so the machine did restart. It came back at {{booted}}; it went down at some point after the last reading, so this is an upper bound rather than a measurement.',
        'core-uptime-events.gap_collector_not_running':
          'Boot time was unchanged either side, so the machine ran straight through this — it was up, and the collector was not running.',
        'core-uptime-events.gap_before_tracking':
          'These readings predate boot-time recording, so whether the machine restarted here cannot be told. Not an outage — just a stretch that cannot be classified. Gaps from now on will say which.',
        'core-uptime-events.gap_unknown':
          'No boot time either side to compare, so whether the machine stayed up through this cannot be told from these readings.',
      }),
      es: Object.freeze({
        'core-uptime-events.empty':
          'Todas las lecturas de esta ventana están presentes y la máquina nunca se reinició. No hay nada que explicar.',
        'core-uptime-events.restarted': 'Reiniciado',
        'core-uptime-events.what': 'Qué',
        'core-uptime-events.from': 'Desde',
        'core-uptime-events.until': 'Hasta',
        'core-uptime-events.for': 'Durante',
        'core-uptime-events.meaning': 'Qué significa',
        'core-uptime-events.reading_gap': 'Hueco de lecturas',
        'core-uptime-events.restart_explanation':
          'La hora de arranque cambió, así que la máquina sí se reinició. Volvió a las {{booted}}; se apagó en algún momento después de la última lectura, así que es un límite superior y no una medición.',
        'core-uptime-events.gap_collector_not_running':
          'La hora de arranque no cambió a ambos lados, así que la máquina siguió funcionando: estaba activa, pero el recopilador no se ejecutaba.',
        'core-uptime-events.gap_before_tracking':
          'Estas lecturas son anteriores al registro de hora de arranque, por lo que no se puede saber si la máquina se reinició aquí. No es una interrupción; es un tramo que no se puede clasificar.',
        'core-uptime-events.gap_unknown':
          'No hay hora de arranque a ninguno de los lados para comparar, por lo que estas lecturas no permiten saber si la máquina permaneció activa.',
      }),
      hi: Object.freeze({
        'core-uptime-events.empty':
          'इस अवधि की हर रीडिंग मौजूद है और मशीन कभी रीस्टार्ट नहीं हुई। समझाने के लिए कुछ नहीं।',
        'core-uptime-events.restarted': 'रीस्टार्ट हुआ',
        'core-uptime-events.what': 'क्या',
        'core-uptime-events.from': 'से',
        'core-uptime-events.until': 'तक',
        'core-uptime-events.for': 'अवधि',
        'core-uptime-events.meaning': 'इसका अर्थ',
        'core-uptime-events.reading_gap': 'रीडिंग अंतराल',
        'core-uptime-events.restart_explanation':
          'बूट समय बदला, इसलिए मशीन फिर शुरू हुई। यह {{booted}} पर वापस आई; आखिरी रीडिंग के बाद किसी समय बंद हुई, इसलिए यह ऊपरी सीमा है, माप नहीं।',
        'core-uptime-events.gap_collector_not_running':
          'दोनों ओर बूट समय अपरिवर्तित था, इसलिए मशीन चलती रही — वह चालू थी, पर कलेक्टर नहीं चल रहा था।',
        'core-uptime-events.gap_before_tracking':
          'ये रीडिंग बूट-समय रिकॉर्डिंग से पहले की हैं, इसलिए यहां पुनरारंभ हुआ या नहीं कहा नहीं जा सकता। यह आउटेज नहीं, वर्गीकृत न हो सकने वाला अंतराल है।',
        'core-uptime-events.gap_unknown':
          'तुलना के लिए दोनों ओर बूट समय नहीं है, इसलिए इन रीडिंग से नहीं बताया जा सकता कि मशीन चालू रही या नहीं।',
      }),
      ar: Object.freeze({
        'core-uptime-events.empty':
          'كل قراءة في هذه النافذة موجودة ولم يُعد تشغيل الجهاز. لا شيء لتفسيره.',
        'core-uptime-events.restarted': 'أُعيد تشغيله',
        'core-uptime-events.what': 'ما',
        'core-uptime-events.from': 'من',
        'core-uptime-events.until': 'حتى',
        'core-uptime-events.for': 'لمدة',
        'core-uptime-events.meaning': 'ما معناه',
        'core-uptime-events.reading_gap': 'فجوة في القراءات',
        'core-uptime-events.restart_explanation':
          'تغيّر وقت الإقلاع، لذا أعيد تشغيل الجهاز بالفعل. عاد في {{booted}}؛ وتوقف في وقت ما بعد آخر قراءة، لذا فهذا حد أعلى لا قياس.',
        'core-uptime-events.gap_collector_not_running':
          'لم يتغير وقت الإقلاع على الجانبين، لذا استمر الجهاز في العمل — كان يعمل لكن الجامع لم يكن يعمل.',
        'core-uptime-events.gap_before_tracking':
          'هذه القراءات تسبق تسجيل وقت الإقلاع، لذلك لا يمكن معرفة ما إذا كان الجهاز قد أعيد تشغيله هنا. ليست انقطاعًا بل فترة لا يمكن تصنيفها.',
        'core-uptime-events.gap_unknown':
          'لا يوجد وقت إقلاع على أي من الجانبين للمقارنة، لذلك لا يمكن لهذه القراءات تحديد ما إذا ظل الجهاز يعمل.',
      }),
      zh: Object.freeze({
        'core-uptime-events.empty':
          '此窗口中的每个读数都存在，并且机器从未重启。无需解释。',
        'core-uptime-events.restarted': '已重启',
        'core-uptime-events.what': '内容',
        'core-uptime-events.from': '从',
        'core-uptime-events.until': '到',
        'core-uptime-events.for': '持续',
        'core-uptime-events.meaning': '含义',
        'core-uptime-events.reading_gap': '读数缺口',
        'core-uptime-events.restart_explanation':
          '启动时间发生变化，因此机器确实重启了。它在 {{booted}} 恢复；在最后一次读数之后的某个时刻停机，因此这是上限而不是精确测量值。',
        'core-uptime-events.gap_collector_not_running':
          '两侧的启动时间均未变化，因此机器一直在运行；它处于启动状态，但采集器没有运行。',
        'core-uptime-events.gap_before_tracking':
          '这些读数早于启动时间记录，因此无法判断机器是否在此处重启。这不是停机，而是一段无法分类的间隔。',
        'core-uptime-events.gap_unknown':
          '两侧都没有启动时间可供比较，因此这些读数无法判断机器是否一直运行。',
      }),
    }),
  }),
  text(key, valuesOrHelpers = {}, maybeHelpers = null) {
    const helpers = maybeHelpers || valuesOrHelpers;
    const values = maybeHelpers ? valuesOrHelpers : {};
    return helpers.i18n.t(
      `core-uptime-events.${key}`,
      values,
      this.manifest.catalogs.en[`core-uptime-events.${key}`],
    );
  },
  render(bands, helpers) {
    const { target, renderTable, whenSpan, humanDuration, ui, i18n } = helpers;
    if (!target) return;
    if (!bands.length) {
      target.replaceChildren(
        ui.empty(
          i18n.t(
            'core-uptime-events.empty',
            {},
            'Every reading in this window is present and the machine never restarted. Nothing to explain.',
          ),
        ),
      );
      return;
    }
    const restarted = i18n.t('core-uptime-events.restarted', {}, 'Restarted');
    renderTable(target.id, bands, [
      {
        label: i18n.t('core-uptime-events.what', {}, 'What'),
        render: (row) => {
          const span = document.createElement('span');
          span.textContent = row.kind;
          span.className = row.kind === restarted ? 'grew' : 'muted';
          return span;
        },
      },
      {
        label: i18n.t('core-uptime-events.from', {}, 'From'),
        render: (row) => whenSpan(row.from),
      },
      {
        label: i18n.t('core-uptime-events.until', {}, 'Until'),
        render: (row) => whenSpan(row.to),
      },
      {
        label: i18n.t('core-uptime-events.for', {}, 'For'),
        className: 'size',
        render: (row) =>
          (row.kind === restarted ? '≤ ' : '') + humanDuration(row.seconds),
      },
      {
        label: i18n.t('core-uptime-events.meaning', {}, 'What it means'),
        className: 'name',
        render: (row) => row.note,
      },
    ]);
  },
};
Zogograph.cards.register(UptimeEventsCard);
