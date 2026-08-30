/* Core card package: the shell supplies fact, timestamp, and pending-task helpers. */
window.ZogographCapabilitySummaryCard = {
  manifest: Object.freeze({
    id: 'core-capability-summary',
    locales: ['core-capability-summary.'],
    helpers: ['target', 'factGrid', 'note', 'ago', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-capability-summary.worth_having': 'Worth having',
        'core-capability-summary.whole_catalogue': 'Whole catalogue',
        'core-capability-summary.available_here':
          '{{available}} of {{total}} available here',
        'core-capability-summary.host_only': 'On the host, not reachable',
        'core-capability-summary.alternate': 'Only under another path',
        'core-capability-summary.missing': 'Not installed anywhere',
        'core-capability-summary.host_not_asked': 'Host not asked',
        'core-capability-summary.host_not_asked_value':
          '{{count}} — press “Ask the host”',
        'core-capability-summary.account_checked': 'Account checked {{when}}',
        'core-capability-summary.host_checked': 'host checked {{when}}',
        'core-capability-summary.pending_never':
          '{{count, number}} requests waiting; the oldest was asked {{oldest}} — but root’s collector has never run here, so nothing will answer them. It must be installed in root’s crontab; this account cannot do it.',
        'core-capability-summary.pending_stale':
          '{{count, number}} requests waiting; the oldest was asked {{oldest}}. Root’s collector last ran {{lastRun}}, so it is not running on a schedule — these will sit here until it does.',
        'core-capability-summary.pending_unanswered':
          '{{count, number}} requests waiting. The collector has run since they were asked for ({{lastRun}}) without answering them, which usually means it cannot do what was asked rather than that it has not got to it yet.',
        'core-capability-summary.pending_waiting':
          '{{count, number}} requests are waiting for root’s collector, which last ran {{lastRun}}. The countdown is at the top of the page.',
      }),
      es: Object.freeze({
        'core-capability-summary.worth_having': 'Conviene tener',
        'core-capability-summary.whole_catalogue': 'Catálogo completo',
        'core-capability-summary.available_here':
          '{{available}} de {{total}} disponibles aquí',
        'core-capability-summary.host_only': 'En el host, no accesible',
        'core-capability-summary.alternate': 'Solo en otra ruta',
        'core-capability-summary.missing': 'No instalado en ninguna parte',
        'core-capability-summary.host_not_asked': 'No se preguntó al host',
        'core-capability-summary.host_not_asked_value':
          '{{count}} — pulse «Preguntar al host»',
        'core-capability-summary.account_checked': 'Cuenta comprobada {{when}}',
        'core-capability-summary.host_checked': 'host comprobado {{when}}',
        'core-capability-summary.pending_never':
          '{{count, number}} solicitudes esperando; la más antigua se pidió {{oldest}}, pero el recopilador de root nunca se ha ejecutado aquí, por lo que nada las responderá. Debe instalarse en el crontab de root; esta cuenta no puede hacerlo.',
        'core-capability-summary.pending_stale':
          '{{count, number}} solicitudes esperando; la más antigua se pidió {{oldest}}. El recopilador de root se ejecutó por última vez {{lastRun}}, por lo que no sigue un calendario; permanecerán aquí hasta que lo haga.',
        'core-capability-summary.pending_unanswered':
          '{{count, number}} solicitudes esperando. El recopilador se ha ejecutado desde que se solicitaron ({{lastRun}}) sin responderlas, lo que normalmente significa que no puede hacer lo pedido en vez de que aún no haya llegado a ello.',
        'core-capability-summary.pending_waiting':
          '{{count, number}} solicitudes esperan al recopilador de root, que se ejecutó por última vez {{lastRun}}. La cuenta atrás está en la parte superior de la página.',
      }),
      hi: Object.freeze({
        'core-capability-summary.worth_having': 'रखना उपयोगी',
        'core-capability-summary.whole_catalogue': 'पूरा कैटलॉग',
        'core-capability-summary.available_here':
          '{{available}} में से {{total}} यहां उपलब्ध',
        'core-capability-summary.host_only': 'होस्ट पर, पहुंच योग्य नहीं',
        'core-capability-summary.alternate': 'केवल दूसरे पथ पर',
        'core-capability-summary.missing': 'कहीं भी स्थापित नहीं',
        'core-capability-summary.host_not_asked': 'होस्ट से नहीं पूछा गया',
        'core-capability-summary.host_not_asked_value':
          '{{count}} — “होस्ट से पूछें” दबाएं',
        'core-capability-summary.account_checked': 'खाता {{when}} जांचा गया',
        'core-capability-summary.host_checked': 'होस्ट {{when}} जांचा गया',
        'core-capability-summary.pending_never':
          '{{count, number}} अनुरोध प्रतीक्षा में हैं; सबसे पुराने का अनुरोध {{oldest}} किया गया था, लेकिन root का कलेक्टर यहां कभी नहीं चला, इसलिए कोई उनका उत्तर नहीं देगा। इसे root के crontab में स्थापित करना होगा; यह खाता ऐसा नहीं कर सकता।',
        'core-capability-summary.pending_stale':
          '{{count, number}} अनुरोध प्रतीक्षा में हैं; सबसे पुराने का अनुरोध {{oldest}} किया गया था। root का कलेक्टर आखिरी बार {{lastRun}} चला था, इसलिए वह शेड्यूल पर नहीं चल रहा है — ये तब तक यहीं रहेंगे।',
        'core-capability-summary.pending_unanswered':
          '{{count, number}} अनुरोध प्रतीक्षा में हैं। कलेक्टर अनुरोध के बाद ({{lastRun}}) चल चुका है लेकिन उनका उत्तर नहीं दिया, जिसका मतलब आमतौर पर यह है कि वह मांगा गया काम नहीं कर सकता, न कि उस तक अभी पहुंचा नहीं है।',
        'core-capability-summary.pending_waiting':
          '{{count, number}} अनुरोध root के कलेक्टर की प्रतीक्षा में हैं, जो आखिरी बार {{lastRun}} चला था। उलटी गिनती पृष्ठ के शीर्ष पर है।',
      }),
      ar: Object.freeze({
        'core-capability-summary.worth_having': 'مفيد توفره',
        'core-capability-summary.whole_catalogue': 'الفهرس بالكامل',
        'core-capability-summary.available_here':
          '{{available}} من {{total}} متاح هنا',
        'core-capability-summary.host_only': 'على المضيف، لا يمكن الوصول إليه',
        'core-capability-summary.alternate': 'في مسار آخر فقط',
        'core-capability-summary.missing': 'غير مثبت في أي مكان',
        'core-capability-summary.host_not_asked': 'لم يُسأل المضيف',
        'core-capability-summary.host_not_asked_value':
          '{{count}} — اضغط «اسأل المضيف»',
        'core-capability-summary.account_checked': 'فُحص الحساب {{when}}',
        'core-capability-summary.host_checked': 'فُحص المضيف {{when}}',
        'core-capability-summary.pending_never':
          '{{count, number}} طلبات تنتظر؛ طُلب أقدمها {{oldest}}، لكن جامع root لم يُشغّل هنا قط، لذا لن يجيب عنها شيء. يجب تثبيته في crontab الخاص بـ root؛ هذا الحساب لا يستطيع فعل ذلك.',
        'core-capability-summary.pending_stale':
          '{{count, number}} طلبات تنتظر؛ طُلب أقدمها {{oldest}}. آخر تشغيل لجامع root كان {{lastRun}}، لذا فهو لا يعمل وفق جدول — وستبقى هنا إلى أن يعمل.',
        'core-capability-summary.pending_unanswered':
          '{{count, number}} طلبات تنتظر. شُغِّل الجامع منذ طلبها ({{lastRun}}) من دون الإجابة عنها، وهذا يعني عادة أنه لا يستطيع فعل المطلوب لا أنه لم يصل إليها بعد.',
        'core-capability-summary.pending_waiting':
          '{{count, number}} طلبات تنتظر جامع root، الذي شُغّل آخر مرة {{lastRun}}. العد التنازلي في أعلى الصفحة.',
      }),
      zh: Object.freeze({
        'core-capability-summary.worth_having': '值得具备',
        'core-capability-summary.whole_catalogue': '完整目录',
        'core-capability-summary.available_here':
          '此处可用 {{available}} / {{total}}',
        'core-capability-summary.host_only': '在主机上，无法访问',
        'core-capability-summary.alternate': '仅在其他路径下',
        'core-capability-summary.missing': '未在任何位置安装',
        'core-capability-summary.host_not_asked': '尚未询问主机',
        'core-capability-summary.host_not_asked_value':
          '{{count}} — 点击“询问主机”',
        'core-capability-summary.account_checked': '帐户检查于 {{when}}',
        'core-capability-summary.host_checked': '主机检查于 {{when}}',
        'core-capability-summary.pending_never':
          '有 {{count, number}} 个请求正在等待；最早请求于 {{oldest}}，但 root 的采集器从未在此运行，因此没有任何内容会响应它们。必须将其安装到 root 的 crontab 中；此账户无法完成此操作。',
        'core-capability-summary.pending_stale':
          '有 {{count, number}} 个请求正在等待；最早请求于 {{oldest}}。root 的采集器上次运行于 {{lastRun}}，因此并未按计划运行——在它运行前这些请求都会停留在这里。',
        'core-capability-summary.pending_unanswered':
          '有 {{count, number}} 个请求正在等待。采集器自请求之后已运行过（{{lastRun}}）却没有响应，这通常意味着它无法完成所请求的操作，而不是尚未处理到它们。',
        'core-capability-summary.pending_waiting':
          '有 {{count, number}} 个请求正在等待 root 的采集器，该采集器上次运行于 {{lastRun}}。倒计时位于页面顶部。',
      }),
    }),
  }),

  render(data, helpers) {
    const { target, factGrid, note, ago, i18n } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (!target || !data.summary) return;
    const summary = data.summary;
    const available = (count, total) =>
      t(
        'core-capability-summary.available_here',
        { available: count, total },
        '{{available}} of {{total}} available here',
      );
    const facts = [
      [
        t('core-capability-summary.worth_having', {}, 'Worth having'),
        available(summary.key_available, summary.key_total),
      ],
      [
        t('core-capability-summary.whole_catalogue', {}, 'Whole catalogue'),
        available(summary.counts.available, summary.total),
      ],
    ];
    if (summary.counts.host_only) {
      facts.push([
        t(
          'core-capability-summary.host_only',
          {},
          'On the host, not reachable',
        ),
        String(summary.counts.host_only),
      ]);
    }
    if (summary.counts.alternate) {
      facts.push([
        t('core-capability-summary.alternate', {}, 'Only under another path'),
        String(summary.counts.alternate),
      ]);
    }
    if (summary.counts.missing) {
      facts.push([
        t('core-capability-summary.missing', {}, 'Not installed anywhere'),
        String(summary.counts.missing),
      ]);
    }
    if (summary.counts.unknown_host) {
      facts.push([
        t('core-capability-summary.host_not_asked', {}, 'Host not asked'),
        t(
          'core-capability-summary.host_not_asked_value',
          { count: summary.counts.unknown_host },
          '{{count}} — press “Ask the host”',
        ),
      ]);
    }
    const when = [];
    if (data.checked_at) {
      when.push(
        t(
          'core-capability-summary.account_checked',
          { when: ago(data.checked_at) },
          'Account checked {{when}}',
        ),
      );
    }
    if (data.host_checked_at) {
      when.push(
        t(
          'core-capability-summary.host_checked',
          { when: ago(data.host_checked_at) },
          'host checked {{when}}',
        ),
      );
    }
    const fragment = document.createDocumentFragment();
    fragment.appendChild(factGrid(facts));
    if (when.length) fragment.appendChild(note(`${when.join(', ')}.`));
    const waiting = (data.tasks || []).filter((task) => task.pending);
    if (waiting.length) {
      const oldest = Math.min(...waiting.map((task) => task.requested_at));
      const lastRun = Number(data.host_last_run) || 0;
      const stale = !lastRun || Date.now() / 1000 - lastRun > 3600;
      const said = document.createElement('div');
      said.className = stale ? 'band-warn' : 'muted small';
      const values = {
        count: waiting.length,
        oldest: ago(oldest),
        lastRun: lastRun ? ago(lastRun) : '',
      };
      said.textContent = !lastRun
        ? t(
            'core-capability-summary.pending_never',
            values,
            '{{count, number}} requests waiting; the oldest was asked {{oldest}} — but root’s collector has never run here, so nothing will answer them. It must be installed in root’s crontab; this account cannot do it.',
          )
        : stale
          ? t(
              'core-capability-summary.pending_stale',
              values,
              '{{count, number}} requests waiting; the oldest was asked {{oldest}}. Root’s collector last ran {{lastRun}}, so it is not running on a schedule — these will sit here until it does.',
            )
          : lastRun > oldest
            ? t(
                'core-capability-summary.pending_unanswered',
                values,
                '{{count, number}} requests waiting. The collector has run since they were asked for ({{lastRun}}) without answering them, which usually means it cannot do what was asked rather than that it has not got to it yet.',
              )
            : t(
                'core-capability-summary.pending_waiting',
                values,
                '{{count, number}} requests are waiting for root’s collector, which last ran {{lastRun}}. The countdown is at the top of the page.',
              );
      const wrap = document.createElement('div');
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
      fragment.appendChild(wrap);
    }
    target.replaceChildren(fragment);
  },
};
