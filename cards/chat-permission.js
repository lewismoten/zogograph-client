/* Core card package: approval prompt for a tool that needs permission. */
window.ZogographChatPermissionCard = {
  manifest: Object.freeze({
    id: 'core-chat-permission',
    locales: ['core-chat-permission.'],
    helpers: [
      'deadlines',
      'getPermissionId',
      'humanSpan',
      'notice',
      'permit',
      'seconds',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-chat-permission.wants': 'It wants to {{action}}.',
        'core-chat-permission.allow': 'Allow',
        'core-chat-permission.decline': 'Not this time',
        'core-chat-permission.countdown': 'Treated as no in {{duration}}',
        'core-chat-permission.timed_out': 'No answer — carrying on without it',
        'core-chat-permission.timeout_notice':
          'Nobody answered, so {{assistant}} was told no. It carries on without whatever that would have told it.',
      }),
      es: Object.freeze({
        'core-chat-permission.wants': 'Quiere {{action}}.',
        'core-chat-permission.allow': 'Permitir',
        'core-chat-permission.decline': 'Esta vez no',
        'core-chat-permission.countdown': 'Se tratará como no en {{duration}}',
        'core-chat-permission.timed_out': 'Sin respuesta; se continúa sin ello',
        'core-chat-permission.timeout_notice':
          'Nadie respondió, así que se le indicó que no a {{assistant}}. Continúa sin lo que eso le hubiera dicho.',
      }),
      hi: Object.freeze({
        'core-chat-permission.wants': 'यह {{action}} चाहता है।',
        'core-chat-permission.allow': 'अनुमति दें',
        'core-chat-permission.decline': 'इस बार नहीं',
        'core-chat-permission.countdown':
          '{{duration}} में इसे नहीं माना जाएगा',
        'core-chat-permission.timed_out': 'कोई उत्तर नहीं — इसके बिना जारी है',
        'core-chat-permission.timeout_notice':
          'किसी ने उत्तर नहीं दिया, इसलिए {{assistant}} को मना कर दिया गया। वह उस जानकारी के बिना आगे बढ़ रहा है।',
      }),
      ar: Object.freeze({
        'core-chat-permission.wants': 'يريد {{action}}.',
        'core-chat-permission.allow': 'اسمح',
        'core-chat-permission.decline': 'ليس هذه المرة',
        'core-chat-permission.countdown': 'سيُعامل كرفض خلال {{duration}}',
        'core-chat-permission.timed_out': 'لا إجابة — يستمر بدونه',
        'core-chat-permission.timeout_notice':
          'لم يُجب أحد، لذا أُبلغ {{assistant}} بالرفض. ويستمر دون ما كان سيخبره به ذلك.',
      }),
      zh: Object.freeze({
        'core-chat-permission.wants': '它想要{{action}}。',
        'core-chat-permission.allow': '允许',
        'core-chat-permission.decline': '这次不要',
        'core-chat-permission.countdown': '将在 {{duration}} 后视为拒绝',
        'core-chat-permission.timed_out': '未收到答复——将在没有它的情况下继续',
        'core-chat-permission.timeout_notice':
          '无人答复，因此已告知 {{assistant}} 不允许。它将在没有该信息的情况下继续。',
      }),
    }),
  }),
  render(input, helpers) {
    const { run, assistant } = input;
    const t = (key, values, fallback) => helpers.i18n.t(key, values, fallback);
    const wrap = document.createElement('div');
    wrap.className = 'chat-permission';
    const what = document.createElement('div');
    what.textContent = t(
      'core-chat-permission.wants',
      { action: run.summary.replace(/\.$/, '') },
      'It wants to {{action}}.',
    );
    wrap.appendChild(what);
    if (run.why) {
      const why = document.createElement('div');
      why.className = 'muted small';
      why.textContent = `“${run.why}”`;
      wrap.appendChild(why);
    }
    const actions = document.createElement('div');
    actions.className = 'inspect-actions';
    const answer = async (allow) => {
      for (const button of actions.querySelectorAll('button'))
        button.disabled = true;
      try {
        await helpers.permit(run, allow);
        return true;
      } catch (error) {
        helpers.notice(String(error.message || error));
        return false;
      }
    };
    for (const [key, fallback, allow, className] of [
      ['core-chat-permission.allow', 'Allow', '1', ''],
      ['core-chat-permission.decline', 'Not this time', '0', 'danger'],
    ]) {
      const button = document.createElement('button');
      button.className = `link ${className}`;
      button.textContent = t(key, {}, fallback);
      button.addEventListener('click', () => answer(allow));
      actions.appendChild(button);
    }
    const clock = document.createElement('span');
    clock.className = 'muted small chat-countdown';
    actions.appendChild(clock);
    if (!helpers.deadlines.has(run.id))
      helpers.deadlines.set(run.id, Date.now() + helpers.seconds * 1000);
    const deadline = helpers.deadlines.get(run.id);
    const tick = async () => {
      if (!wrap.isConnected || helpers.getPermissionId() !== run.id)
        return clearInterval(timer);
      const left = Math.max(0, Math.round((deadline - Date.now()) / 1000));
      clock.textContent = t(
        left > 0
          ? 'core-chat-permission.countdown'
          : 'core-chat-permission.timed_out',
        { duration: helpers.humanSpan(left) },
        left > 0
          ? 'Treated as no in {{duration}}'
          : 'No answer — carrying on without it',
      );
      clock.classList.toggle('soon', left <= 15);
      if (left <= 0) {
        clearInterval(timer);
        if (await answer('0'))
          helpers.notice(
            t(
              'core-chat-permission.timeout_notice',
              { assistant },
              'Nobody answered, so {{assistant}} was told no. It carries on without whatever that would have told it.',
            ),
            true,
          );
        helpers.deadlines.delete(run.id);
      }
    };
    const timer = setInterval(tick, 1000);
    tick();
    wrap.appendChild(actions);
    return wrap;
  },
};
