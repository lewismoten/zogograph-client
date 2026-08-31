window.ZogographPendingHostTasksCard = {
  manifest: Object.freeze({
    id: 'core-pending-host-tasks',
    locales: ['core-pending-host-tasks.'],
    helpers: ['count', 'ago', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-pending-host-tasks.never_ran':
          '{{requests}} waiting, the oldest asked {{oldest}} — but root’s collector has never run here, so nothing is going to answer them. It has to be installed in root’s crontab; this account cannot do it.',
        'core-pending-host-tasks.stale':
          '{{requests}} waiting, the oldest asked {{oldest}}. Root’s collector last ran {{last}}, so it is not running on a schedule — these will sit here until it does.',
        'core-pending-host-tasks.ran_since':
          '{{requests}} waiting. The collector has run since they were asked for ({{last}}) without answering them, which usually means it cannot do what was asked rather than that it has not got to it yet.',
        'core-pending-host-tasks.waiting':
          '{{requests}} waiting for root’s collector, which last ran {{last}}. The countdown is at the top of the page.',
        'core-pending-host-tasks.where_it_will_appear':
          'Where it will appear: {{view}}',
        'core-pending-host-tasks.asked_with_cadence':
          'Asked {{time}}. The collector runs about every {{cadence}}.',
        'core-pending-host-tasks.asked_unknown_cadence':
          "Asked {{time}}. The collector's schedule is not known yet.",
      }),
      es: Object.freeze({
        'core-pending-host-tasks.never_ran':
          '{{requests}} en espera; la más antigua se pidió {{oldest}}, pero el recopilador de root nunca se ha ejecutado aquí y nada las responderá. Debe instalarse en el crontab de root; esta cuenta no puede hacerlo.',
        'core-pending-host-tasks.stale':
          '{{requests}} en espera; la más antigua se pidió {{oldest}}. El recopilador de root se ejecutó por última vez {{last}}, por lo que no sigue una programación; permanecerán aquí hasta que lo haga.',
        'core-pending-host-tasks.ran_since':
          '{{requests}} en espera. El recopilador se ha ejecutado desde que se solicitaron ({{last}}) sin responderlas, lo que normalmente significa que no puede hacer lo pedido y no que aún no haya llegado a ello.',
        'core-pending-host-tasks.waiting':
          '{{requests}} en espera del recopilador de root, que se ejecutó por última vez {{last}}. La cuenta atrás está en la parte superior de la página.',
        'core-pending-host-tasks.where_it_will_appear':
          'Dónde aparecerá: {{view}}',
        'core-pending-host-tasks.asked_with_cadence':
          'Se solicitó {{time}}. El recolector se ejecuta aproximadamente cada {{cadence}}.',
        'core-pending-host-tasks.asked_unknown_cadence':
          'Se solicitó {{time}}. Aún no se conoce la programación del recolector.',
      }),
      hi: Object.freeze({
        'core-pending-host-tasks.never_ran':
          '{{requests}} प्रतीक्षा में हैं; सबसे पुराना {{oldest}} माँगा गया था, लेकिन root का कलेक्टर यहाँ कभी नहीं चला, इसलिए कोई उनका उत्तर नहीं देगा। इसे root के crontab में स्थापित करना होगा; यह खाता ऐसा नहीं कर सकता।',
        'core-pending-host-tasks.stale':
          '{{requests}} प्रतीक्षा में हैं; सबसे पुराना {{oldest}} माँगा गया था। root का कलेक्टर आखिरी बार {{last}} चला था, इसलिए यह शेड्यूल पर नहीं चल रहा; ऐसा होने तक ये यहीं रहेंगे।',
        'core-pending-host-tasks.ran_since':
          '{{requests}} प्रतीक्षा में हैं। कलेक्टर इनके अनुरोध के बाद ({{last}}) चला है पर उत्तर नहीं दिया, जिसका अर्थ आमतौर पर यह है कि वह माँगा गया काम नहीं कर सकता, न कि वह अभी तक वहाँ पहुँचा नहीं है।',
        'core-pending-host-tasks.waiting':
          '{{requests}} root के कलेक्टर की प्रतीक्षा में हैं, जो आखिरी बार {{last}} चला था। उलटी गिनती पृष्ठ के शीर्ष पर है।',
        'core-pending-host-tasks.where_it_will_appear':
          'यहाँ दिखाई देगा: {{view}}',
        'core-pending-host-tasks.asked_with_cadence':
          '{{time}} अनुरोध किया गया। कलेक्टर लगभग हर {{cadence}} चलता है।',
        'core-pending-host-tasks.asked_unknown_cadence':
          '{{time}} अनुरोध किया गया। कलेक्टर का शेड्यूल अभी ज्ञात नहीं है।',
      }),
      ar: Object.freeze({
        'core-pending-host-tasks.never_ran':
          '{{requests}} قيد الانتظار؛ أقدمها طُلب {{oldest}}، لكن جامع root لم يعمل هنا قط، لذا لن يجيب عنها شيء. يجب تثبيته في crontab الخاص بـ root؛ لا يستطيع هذا الحساب فعل ذلك.',
        'core-pending-host-tasks.stale':
          '{{requests}} قيد الانتظار؛ أقدمها طُلب {{oldest}}. آخر تشغيل لجامع root كان {{last}}، لذا فهو لا يعمل وفق جدول؛ ستبقى هذه هنا حتى يفعل.',
        'core-pending-host-tasks.ran_since':
          '{{requests}} قيد الانتظار. شغّل الجامع منذ طلبها ({{last}}) دون الإجابة عنها، وهذا يعني عادة أنه لا يستطيع فعل المطلوب لا أنه لم يصل إليه بعد.',
        'core-pending-host-tasks.waiting':
          '{{requests}} بانتظار جامع root، الذي شُغّل آخر مرة {{last}}. العد التنازلي في أعلى الصفحة.',
        'core-pending-host-tasks.where_it_will_appear': 'مكان ظهوره: {{view}}',
        'core-pending-host-tasks.asked_with_cadence':
          'طُلب {{time}}. يعمل الجامع كل {{cadence}} تقريبًا.',
        'core-pending-host-tasks.asked_unknown_cadence':
          'طُلب {{time}}. جدول الجامع غير معروف بعد.',
      }),
      zh: Object.freeze({
        'core-pending-host-tasks.never_ran':
          '{{requests}} 个请求正在等待，最早的请求于 {{oldest}} 提出，但 root 收集器从未在此运行，因此不会得到答复。必须将其安装到 root 的 crontab；此账户无法完成此操作。',
        'core-pending-host-tasks.stale':
          '{{requests}} 个请求正在等待，最早的请求于 {{oldest}} 提出。root 收集器最后一次运行于 {{last}}，因此未按计划运行；在它恢复前这些请求会一直留在这里。',
        'core-pending-host-tasks.ran_since':
          '{{requests}} 个请求正在等待。收集器自请求提出后（{{last}}）已运行但没有答复，这通常表示它无法完成所请求的任务，而不是尚未处理到它。',
        'core-pending-host-tasks.waiting':
          '{{requests}} 个请求正在等待 root 收集器，其最后一次运行于 {{last}}。倒计时位于页面顶部。',
        'core-pending-host-tasks.where_it_will_appear':
          '将在此处显示：{{view}}',
        'core-pending-host-tasks.asked_with_cadence':
          '请求于 {{time}} 提出。收集器大约每 {{cadence}} 运行一次。',
        'core-pending-host-tasks.asked_unknown_cadence':
          '请求于 {{time}} 提出。尚不清楚收集器的计划。',
      }),
    }),
  }),
  text(key, values, helpers) {
    const localeKey = `core-pending-host-tasks.${key}`;
    return helpers.i18n.t(
      localeKey,
      values,
      this.manifest.catalogs.en[localeKey],
    );
  },
  render(data, helpers) {
    const waiting = (data.tasks || []).filter((task) => task.pending);
    if (!waiting.length) return document.createDocumentFragment();
    const oldest = Math.min(...waiting.map((task) => task.requested_at));
    const lastRun = Number(data.host_last_run) || 0;
    const ranSince = lastRun > oldest;
    const stale = !lastRun || Date.now() / 1000 - lastRun > 3600;
    const requests = helpers.count(waiting.length, 'request');
    const key = !lastRun
      ? 'core-pending-host-tasks.never_ran'
      : stale
        ? 'core-pending-host-tasks.stale'
        : ranSince
          ? 'core-pending-host-tasks.ran_since'
          : 'core-pending-host-tasks.waiting';
    const wrap = document.createElement('div');
    const said = document.createElement('div');
    said.className = stale ? 'band-warn' : 'muted small';
    said.textContent = helpers.i18n.t(
      key,
      {
        requests,
        oldest: helpers.ago(oldest),
        last: helpers.ago(lastRun),
      },
      '{{requests}} waiting for root’s collector.',
    );
    wrap.appendChild(said);
    const which = document.createElement('div');
    which.className = 'muted small';
    which.textContent = waiting.map((task) => task.task).join(', ');
    wrap.appendChild(which);
    if (!lastRun && data.host_cron_line) {
      const how = document.createElement('pre');
      how.className = 'paste';
      how.textContent = data.host_cron_line;
      wrap.appendChild(how);
    }
    return wrap;
  },
};
