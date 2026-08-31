/* Core card package: collector-cadence dial status. */
window.ZogographCollectorDialCard = {
  manifest: Object.freeze({
    id: 'core-collector-dial',
    locales: ['core-collector-dial.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-collector-dial.insufficient_runs':
          'Not enough runs yet to know how often this should happen.',
        'core-collector-dial.never_run': 'This has never run.',
        'core-collector-dial.stopped':
          'Nothing for {{age}}, on something that usually runs every {{interval}}.',
        'core-collector-dial.due_now': 'Due now — nothing for {{age}}.',
        'core-collector-dial.next_in': 'Next one in about {{duration}}.',
        'core-collector-dial.refresh': 'Refresh now',
        'core-collector-dial.refresh_status': 'Collector status; refresh now',
        'core-collector-dial.account': 'This account',
        'core-collector-dial.host': 'The host collector',
        'core-collector-dial.nothing_recorded':
          '{{collector}}: nothing recorded.',
        'core-collector-dial.last_reading':
          '{{collector}}: last reading {{age}} ago.',
        'core-collector-dial.next_expected':
          '{{collector}}: last reading {{age}} ago, next expected in {{duration}}.',
        'core-collector-dial.late':
          '{{collector}}: {{age}} late — it usually runs every {{interval}}.',
        'core-collector-dial.asking': 'Asking the server now.',
        'core-collector-dial.ask': 'Click to ask the server now.',
      }),
      es: Object.freeze({
        'core-collector-dial.insufficient_runs':
          'Todavía no hay suficientes ejecuciones para saber con qué frecuencia debe ocurrir.',
        'core-collector-dial.never_run': 'Esto no se ha ejecutado nunca.',
        'core-collector-dial.stopped':
          'Nada desde hace {{age}}, en algo que normalmente se ejecuta cada {{interval}}.',
        'core-collector-dial.due_now':
          'Toca ahora; no ha habido nada desde hace {{age}}.',
        'core-collector-dial.next_in':
          'La siguiente será dentro de unos {{duration}}.',
        'core-collector-dial.refresh': 'Actualizar ahora',
        'core-collector-dial.refresh_status':
          'Estado del recopilador; actualizar ahora',
        'core-collector-dial.account': 'Esta cuenta',
        'core-collector-dial.host': 'El recopilador del host',
        'core-collector-dial.nothing_recorded':
          '{{collector}}: no hay nada registrado.',
        'core-collector-dial.last_reading':
          '{{collector}}: última lectura hace {{age}}.',
        'core-collector-dial.next_expected':
          '{{collector}}: última lectura hace {{age}}; la siguiente se espera dentro de {{duration}}.',
        'core-collector-dial.late':
          '{{collector}}: {{age}} de retraso; normalmente se ejecuta cada {{interval}}.',
        'core-collector-dial.asking': 'Consultando al servidor ahora.',
        'core-collector-dial.ask':
          'Haga clic para consultar al servidor ahora.',
      }),
      hi: Object.freeze({
        'core-collector-dial.insufficient_runs':
          'यह कितनी बार होना चाहिए, यह जानने के लिए अभी पर्याप्त रन नहीं हुए हैं।',
        'core-collector-dial.never_run': 'यह कभी नहीं चला है।',
        'core-collector-dial.stopped':
          '{{age}} से कुछ नहीं हुआ, जबकि यह आम तौर पर हर {{interval}} में चलता है।',
        'core-collector-dial.due_now':
          'अब होना चाहिए — {{age}} से कुछ नहीं हुआ है।',
        'core-collector-dial.next_in': 'अगला लगभग {{duration}} में है।',
        'core-collector-dial.refresh': 'अभी रीफ़्रेश करें',
        'core-collector-dial.refresh_status':
          'कलेक्टर स्थिति; अभी रीफ़्रेश करें',
        'core-collector-dial.account': 'यह खाता',
        'core-collector-dial.host': 'होस्ट कलेक्टर',
        'core-collector-dial.nothing_recorded':
          '{{collector}}: कुछ भी दर्ज नहीं है।',
        'core-collector-dial.last_reading':
          '{{collector}}: पिछली रीडिंग {{age}} पहले हुई।',
        'core-collector-dial.next_expected':
          '{{collector}}: पिछली रीडिंग {{age}} पहले हुई; अगली {{duration}} में अपेक्षित है।',
        'core-collector-dial.late':
          '{{collector}}: {{age}} देरी; यह आम तौर पर हर {{interval}} में चलता है।',
        'core-collector-dial.asking': 'अभी सर्वर से पूछ रहे हैं।',
        'core-collector-dial.ask': 'अभी सर्वर से पूछने के लिए क्लिक करें।',
      }),
      ar: Object.freeze({
        'core-collector-dial.insufficient_runs':
          'لا توجد عمليات تشغيل كافية بعد لمعرفة عدد مرات حدوث ذلك.',
        'core-collector-dial.never_run': 'لم يُشغَّل هذا من قبل.',
        'core-collector-dial.stopped':
          'لا شيء منذ {{age}}، رغم أنه يعمل عادةً كل {{interval}}.',
        'core-collector-dial.due_now': 'موعده الآن — لا شيء منذ {{age}}.',
        'core-collector-dial.next_in': 'التشغيل التالي بعد نحو {{duration}}.',
        'core-collector-dial.refresh': 'تحديث الآن',
        'core-collector-dial.refresh_status': 'حالة الجامع؛ تحديث الآن',
        'core-collector-dial.account': 'هذا الحساب',
        'core-collector-dial.host': 'جامع المضيف',
        'core-collector-dial.nothing_recorded':
          '{{collector}}: لا توجد قراءة مسجلة.',
        'core-collector-dial.last_reading':
          '{{collector}}: آخر قراءة منذ {{age}}.',
        'core-collector-dial.next_expected':
          '{{collector}}: آخر قراءة منذ {{age}}، والمتوقع التالي خلال {{duration}}.',
        'core-collector-dial.late':
          '{{collector}}: متأخر {{age}} — ويعمل عادةً كل {{interval}}.',
        'core-collector-dial.asking': 'يُسأل الخادم الآن.',
        'core-collector-dial.ask': 'انقر لسؤال الخادم الآن.',
      }),
      zh: Object.freeze({
        'core-collector-dial.insufficient_runs':
          '运行次数还不足，无法判断这应多久发生一次。',
        'core-collector-dial.never_run': '这从未运行过。',
        'core-collector-dial.stopped':
          '已有 {{age}} 没有运行，而它通常每 {{interval}} 运行一次。',
        'core-collector-dial.due_now': '现在该运行了——已有 {{age}} 没有运行。',
        'core-collector-dial.next_in': '下一次大约在 {{duration}} 后。',
        'core-collector-dial.refresh': '立即刷新',
        'core-collector-dial.refresh_status': '采集器状态；立即刷新',
        'core-collector-dial.account': '此账户',
        'core-collector-dial.host': '主机采集器',
        'core-collector-dial.nothing_recorded': '{{collector}}：没有记录。',
        'core-collector-dial.last_reading':
          '{{collector}}：上一次读数在 {{age}} 前。',
        'core-collector-dial.next_expected':
          '{{collector}}：上一次读数在 {{age}} 前，预计 {{duration}} 后进行下一次。',
        'core-collector-dial.late':
          '{{collector}}：已延迟 {{age}}——它通常每 {{interval}} 运行一次。',
        'core-collector-dial.asking': '正在请求服务器。',
        'core-collector-dial.ask': '点击以立即请求服务器。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    return i18n.t(
      `core-collector-dial.${key}`,
      values,
      this.manifest.catalogs.en[`core-collector-dial.${key}`],
    );
  },
};
