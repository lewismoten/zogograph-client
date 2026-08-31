/* Core card package: the compact uptime reading that appears on the dashboard. */
window.ZogographUptimeCard = {
  manifest: Object.freeze({
    id: 'core-uptime',
    locales: ['core-uptime.'],
    helpers: [
      'setCard',
      'summary',
      'humanDuration',
      'humanTime',
      'trimPercent',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-uptime.title': 'Uptime and outages',
        'core-uptime.gaps_help':
          'A hole in the readings is not the same as the machine being off, and the charts distinguish them — {{restart_shading}} where the boot time moved, so it really did restart, and {{gap_shading}} where the boot time was unchanged either side and the collector simply was not running. Uptime counts only the first kind; a cron job that stops should not look like a server that keeps falling over.',
        'core-uptime.restart_help':
          'A {{restart_line}} marks each restart the host knows about from {{last}}. Those need not leave a hole at all — a reboot the collector resumes after within a minute has nothing to shade — and they include restarts from before boot times were being recorded, which the readings alone cannot show.',
        'core-uptime.no_boot_time': 'no boot time recorded yet',
        'core-uptime.since': 'since {{time}}',
        'core-uptime.measured_window_up':
          'Up {{percent}}% of the measured window',
        'core-uptime.window_recorded':
          '{{percent}}% of it was actually recorded',
        'core-uptime.no_downtime': 'no downtime detected',
        'core-uptime.restarts_window':
          '{{count, number}} restarts in this window',
        'core-uptime.down_at_most': 'at most {{duration}} down',
        'core-uptime.not_recorded': '{{duration}} not recorded',
        'core-uptime.no_readings_window': 'No readings in this window.',
        'core-uptime.machine': 'The machine',
      }),
      es: Object.freeze({
        'core-uptime.title': 'Tiempo de actividad e interrupciones',
        'core-uptime.gaps_help':
          'Un hueco en las lecturas no significa que la máquina estuviera apagada, y los gráficos los distinguen: {{restart_shading}} donde cambió la hora de arranque, por lo que realmente se reinició, y {{gap_shading}} donde la hora de arranque no cambió a ambos lados y el recolector sencillamente no estaba funcionando. El tiempo de actividad cuenta solo el primer tipo; un trabajo cron que se detiene no debe parecer un servidor que sigue cayéndose.',
        'core-uptime.restart_help':
          'Una {{restart_line}} marca cada reinicio que el host conoce mediante {{last}}. No tienen por qué dejar un hueco: un reinicio tras el que el recolector se reanuda en menos de un minuto no deja nada que sombrear. También incluye reinicios anteriores a que se registraran horas de arranque, que las lecturas por sí solas no pueden mostrar.',
        'core-uptime.no_boot_time': 'aún no se registró la hora de arranque',
        'core-uptime.since': 'desde {{time}}',
        'core-uptime.measured_window_up':
          'Activo el {{percent}}% de la ventana medida',
        'core-uptime.window_recorded': 'se registró realmente el {{percent}}%',
        'core-uptime.no_downtime': 'no se detectó tiempo de inactividad',
        'core-uptime.restarts_window':
          '{{count, number}} reinicios en esta ventana',
        'core-uptime.down_at_most': 'caído como máximo {{duration}}',
        'core-uptime.not_recorded': '{{duration}} sin registrar',
        'core-uptime.no_readings_window': 'No hay lecturas en esta ventana.',
        'core-uptime.machine': 'La máquina',
      }),
      hi: Object.freeze({
        'core-uptime.title': 'अपटाइम और आउटेज',
        'core-uptime.gaps_help':
          'रीडिंग में खाली जगह का अर्थ मशीन बंद होना नहीं है और चार्ट दोनों अलग बताते हैं—जहाँ बूट समय बदला वहाँ {{restart_shading}}, यानी वह सच में रीस्टार्ट हुई; और जहाँ बूट समय दोनों ओर नहीं बदला और कलेक्टर चल ही नहीं रहा था वहाँ {{gap_shading}}। अपटाइम केवल पहला प्रकार गिनता है; बंद होने वाला cron जॉब ऐसे सर्वर जैसा नहीं दिखना चाहिए जो बार-बार गिर रहा हो।',
        'core-uptime.restart_help':
          '{{last}} से होस्ट को ज्ञात हर रीस्टार्ट को {{restart_line}} चिह्नित करती है। उनसे हमेशा खाली जगह नहीं बनती—एक मिनट के अंदर कलेक्टर फिर चलने लगे तो शेड करने को कुछ नहीं—और इनमें वे रीस्टार्ट भी हैं जो बूट समय रिकॉर्ड होने से पहले के थे, जिन्हें केवल रीडिंग नहीं दिखा सकतीं।',
        'core-uptime.no_boot_time': 'अभी कोई बूट समय दर्ज नहीं है',
        'core-uptime.since': '{{time}} से',
        'core-uptime.measured_window_up': 'मापी गई अवधि का {{percent}}% चालू',
        'core-uptime.window_recorded': 'इसका {{percent}}% वास्तव में दर्ज हुआ',
        'core-uptime.no_downtime': 'कोई डाउनटाइम नहीं मिला',
        'core-uptime.restarts_window':
          'इस अवधि में {{count, number}} रीस्टार्ट',
        'core-uptime.down_at_most': 'अधिकतम {{duration}} बंद',
        'core-uptime.not_recorded': '{{duration}} दर्ज नहीं',
        'core-uptime.no_readings_window': 'इस अवधि में कोई रीडिंग नहीं है।',
        'core-uptime.machine': 'मशीन',
      }),
      ar: Object.freeze({
        'core-uptime.title': 'مدة التشغيل والانقطاعات',
        'core-uptime.gaps_help':
          'الفجوة في القراءات ليست هي إيقاف الآلة، وتفرق المخططات بينهما — {{restart_shading}} حيث تغير وقت الإقلاع، فهي أعادت التشغيل فعلًا؛ و{{gap_shading}} حيث لم يتغير وقت الإقلاع على الجانبين ولم يكن الجامع يعمل ببساطة. تحسب مدة التشغيل النوع الأول فقط؛ فلا ينبغي أن تبدو مهمة cron المتوقفة كخادم يستمر في السقوط.',
        'core-uptime.restart_help':
          'يعلّم {{restart_line}} كل إعادة تشغيل يعرفها المضيف من {{last}}. لا يلزم أن تترك هذه فجوة أصلًا — فإعادة تشغيل يستأنف بعدها الجامع خلال دقيقة لا شيء فيها للتظليل — وتشمل عمليات إعادة التشغيل من قبل تسجيل أوقات الإقلاع، التي لا تستطيع القراءات وحدها إظهارها.',
        'core-uptime.no_boot_time': 'لم يُسجل وقت إقلاع بعد',
        'core-uptime.since': 'منذ {{time}}',
        'core-uptime.measured_window_up':
          'كان يعمل خلال {{percent}}% من النافذة المقاسة',
        'core-uptime.window_recorded': 'سُجل فعلياً {{percent}}% منها',
        'core-uptime.no_downtime': 'لم يُكتشف وقت توقف',
        'core-uptime.restarts_window':
          '{{count, number}} عمليات إعادة تشغيل في هذه النافذة',
        'core-uptime.down_at_most': 'متوقف لمدة {{duration}} على الأكثر',
        'core-uptime.not_recorded': '{{duration}} غير مسجلة',
        'core-uptime.no_readings_window': 'لا توجد قراءات في هذه النافذة.',
        'core-uptime.machine': 'الجهاز',
      }),
      zh: Object.freeze({
        'core-uptime.title': '运行时间与中断',
        'core-uptime.gaps_help':
          '读数中的空档不等于机器关机，图表会将两者区分开来——启动时间发生变化的位置为{{restart_shading}}，表示它确实重启了；两侧启动时间未变而收集器根本未运行的位置为{{gap_shading}}。运行时间只统计前一种；停止运行的 cron 任务不应看起来像不断宕机的服务器。',
        'core-uptime.restart_help':
          '主机从 {{last}} 得知的每次重启都会以{{restart_line}}标记。它们不一定会留下空档——收集器在一分钟内恢复的重启没有需要着色的内容——还包括在开始记录启动时间之前的重启，单靠读数无法显示这些重启。',
        'core-uptime.no_boot_time': '尚未记录启动时间',
        'core-uptime.since': '自 {{time}} 起',
        'core-uptime.measured_window_up': '在测量窗口内运行了 {{percent}}%',
        'core-uptime.window_recorded': '其中实际记录了 {{percent}}%',
        'core-uptime.no_downtime': '未检测到停机',
        'core-uptime.restarts_window': '此窗口内重启 {{count, number}} 次',
        'core-uptime.down_at_most': '最多停机 {{duration}}',
        'core-uptime.not_recorded': '有 {{duration}} 未记录',
        'core-uptime.no_readings_window': '此窗口中没有读数。',
        'core-uptime.machine': '此机器',
      }),
    }),
  }),
  text(key, valuesOrHelpers = {}, maybeHelpers = null) {
    const helpers = maybeHelpers || valuesOrHelpers;
    const values = maybeHelpers ? valuesOrHelpers : {};
    return helpers.i18n.t(
      `core-uptime.${key}`,
      values,
      this.manifest.catalogs.en[`core-uptime.${key}`],
    );
  },
  render(input, helpers) {
    const { upFor, bootAt, analysis = {} } = input;
    const { setCard, summary, humanDuration, humanTime, trimPercent, i18n } =
      helpers;
    if (upFor == null) {
      setCard(
        '—',
        i18n.t('core-uptime.no_boot_time', {}, 'no boot time recorded yet'),
      );
    } else {
      setCard(
        humanDuration(upFor),
        i18n.t(
          'core-uptime.since',
          { time: humanTime(bootAt) },
          'since {{time}}',
        ),
        upFor < 3600 ? 'warn' : '',
      );
    }
    if (!summary) return;
    const t = (key, values, fallback) =>
      i18n.t(`core-uptime.${key}`, values, fallback);
    const parts = [];
    if (analysis.uptime_percent != null)
      parts.push(
        t(
          'measured_window_up',
          { percent: trimPercent(analysis.uptime_percent) },
          'Up {{percent}}% of the measured window',
        ),
      );
    if (analysis.coverage_percent != null && analysis.coverage_percent < 100)
      parts.push(
        t(
          'window_recorded',
          { percent: trimPercent(analysis.coverage_percent) },
          '{{percent}}% of it was actually recorded',
        ),
      );
    if (
      analysis.uptime_percent === 100 &&
      analysis.unmonitored > 0 &&
      !analysis.boots
    )
      parts.push(t('no_downtime', {}, 'no downtime detected'));
    if (analysis.boots)
      parts.push(
        t(
          'restarts_window',
          { count: analysis.boots },
          '{{count, number}} restarts in this window',
        ),
      );
    if (analysis.downtime)
      parts.push(
        t(
          'down_at_most',
          { duration: humanDuration(analysis.downtime) },
          'at most {{duration}} down',
        ),
      );
    if (analysis.unmonitored)
      parts.push(
        t(
          'not_recorded',
          { duration: humanDuration(analysis.unmonitored) },
          '{{duration}} not recorded',
        ),
      );
    summary.textContent = parts.length
      ? parts.join(' · ') + '.'
      : t('no_readings_window', {}, 'No readings in this window.');
  },
};
