/* Core card package: root collector approval dialog text and outcomes. */
window.ZogographChatHostRequestCard = {
  manifest: Object.freeze({
    id: 'core-chat-host-request',
    locales: ['core-chat-host-request.'],
    keys: Object.freeze([
      'core-chat-host-request.title',
      'core-chat-host-request.why',
      'core-chat-host-request.why_reason',
      'core-chat-host-request.decline',
      'core-chat-host-request.confirm',
      'core-chat-host-request.explainer',
      'core-chat-host-request.unknown',
      'core-chat-host-request.not_approved',
      'core-chat-host-request.queued',
    ]),
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        title: "{{assistant}} wants to ask root's collector: {{job}}",
        why: "{{cost}} It runs on the collector's next pass, not now.",
        why_reason:
          "“{{reason}}” — {{cost}} It runs on the collector's next pass, not now.",
        decline: 'Treated as no',
        confirm: 'Ask the host',
        explainer:
          'This queues a job for the process running as root. Nothing runs in this window and nothing runs now.',
        unknown: 'There is no host job called “{{job}}”.',
        not_approved:
          'Not approved — either declined or nobody answered. Treat it as a no, carry on with what you can see from this account, and say what you cannot answer because of it.',
        queued:
          "Queued. It runs on the collector's next pass — use get_pending_host_requests to see whether it has landed, or wait_for_new_readings to wait for it. Do not assume the answer is available yet.",
      }),
      es: Object.freeze({
        title: '{{assistant}} quiere pedir al recolector de root: {{job}}',
        why: '{{cost}} Se ejecuta en el próximo ciclo del recolector, no ahora.',
        why_reason:
          '“{{reason}}” — {{cost}} Se ejecuta en el próximo ciclo del recolector, no ahora.',
        decline: 'Se trata como no',
        confirm: 'Preguntar al host',
        explainer:
          'Esto pone en cola un trabajo para el proceso que se ejecuta como root. No se ejecuta nada en esta ventana ni ahora.',
        unknown: 'No existe un trabajo del host llamado “{{job}}”.',
        not_approved:
          'No se aprobó: se rechazó o nadie respondió. Trátelo como un no, continúe con lo que pueda ver desde esta cuenta e indique lo que no pueda responder por ello.',
        queued:
          'En cola. Se ejecuta en el próximo ciclo del recolector; use get_pending_host_requests para ver si se realizó o wait_for_new_readings para esperarlo. No suponga todavía que la respuesta está disponible.',
      }),
      hi: Object.freeze({
        title: '{{assistant}} root के कलेक्टर से पूछना चाहता है: {{job}}',
        why: '{{cost}} यह अभी नहीं, कलेक्टर के अगले चक्र में चलता है।',
        why_reason:
          '“{{reason}}” — {{cost}} यह अभी नहीं, कलेक्टर के अगले चक्र में चलता है।',
        decline: 'नहीं माना गया',
        confirm: 'होस्ट से पूछें',
        explainer:
          'यह root के रूप में चल रही प्रक्रिया के लिए एक कार्य कतार में लगाता है। इस विंडो में और अभी कुछ भी नहीं चलता।',
        unknown: '“{{job}}” नाम का कोई होस्ट कार्य नहीं है।',
        not_approved:
          'अनुमोदित नहीं हुआ — या तो अस्वीकार हुआ या किसी ने उत्तर नहीं दिया। इसे नहीं मानें, इस खाते से जो देख सकते हैं उसके साथ आगे बढ़ें और बताएं कि इसके कारण आप क्या उत्तर नहीं दे सकते।',
        queued:
          'कतार में है। यह कलेक्टर के अगले चक्र में चलेगा — यह देखने के लिए get_pending_host_requests का उपयोग करें कि यह पूरा हुआ या नहीं, या प्रतीक्षा के लिए wait_for_new_readings का उपयोग करें। अभी यह न मानें कि उत्तर उपलब्ध है।',
      }),
      ar: Object.freeze({
        title: 'يريد {{assistant}} سؤال جامع root: {{job}}',
        why: '{{cost}} يعمل في الدورة التالية للجامع، وليس الآن.',
        why_reason:
          '“{{reason}}” — {{cost}} يعمل في الدورة التالية للجامع، وليس الآن.',
        decline: 'يُعامل كأنه لا',
        confirm: 'اسأل المضيف',
        explainer:
          'يضع هذا مهمة في قائمة انتظار العملية التي تعمل بصفة root. لا يعمل شيء في هذه النافذة ولا يعمل شيء الآن.',
        unknown: 'لا توجد مهمة مضيف باسم “{{job}}”.',
        not_approved:
          'لم تُوافق — إما رُفضت أو لم يُجب أحد. تعامل معها كأنها لا، وتابع بما يمكنك رؤيته من هذا الحساب، وقل ما لا يمكنك الإجابة عنه بسبب ذلك.',
        queued:
          'وُضعت في قائمة الانتظار. تعمل في الدورة التالية للجامع — استخدم get_pending_host_requests لمعرفة ما إذا كانت قد وصلت، أو wait_for_new_readings لانتظارها. لا تفترض أن الإجابة متاحة بعد.',
      }),
      zh: Object.freeze({
        title: '{{assistant}} 想询问 root 收集器：{{job}}',
        why: '{{cost}} 它会在收集器下一次运行时执行，而不是现在。',
        why_reason:
          '“{{reason}}”——{{cost}} 它会在收集器下一次运行时执行，而不是现在。',
        decline: '视为拒绝',
        confirm: '询问主机',
        explainer:
          '这会为以 root 身份运行的进程排入任务。此窗口中不会运行任何内容，现在也不会运行任何内容。',
        unknown: '没有名为“{{job}}”的主机任务。',
        not_approved:
          '未获批准——可能是被拒绝，或者无人回应。请将其视为否定结果，继续处理从此帐户能看到的内容，并说明因此无法回答的内容。',
        queued:
          '已排队。它会在收集器下一次运行时执行——使用 get_pending_host_requests 查看是否已完成，或使用 wait_for_new_readings 等待它。暂时不要假设答案已经可用。',
      }),
    }),
  }),
  text(key, variables, helpers) {
    return helpers.i18n.t(
      `core-chat-host-request.${key}`,
      variables,
      this.manifest.catalogs.en[key],
    );
  },
  render(_input, helpers) {
    for (const [locale, catalog] of Object.entries(this.manifest.catalogs)) {
      helpers.i18n.registerCatalog(
        locale,
        Object.fromEntries(
          Object.entries(catalog).map(([key, value]) => [
            `core-chat-host-request.${key}`,
            value,
          ]),
        ),
        false,
        this.manifest.id,
      );
    }
    return this.text('confirm', {}, helpers);
  },
};
