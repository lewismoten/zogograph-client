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
