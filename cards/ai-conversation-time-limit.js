/* Core card package: maximum duration for one assistant conversation. */
window.ZogographAiConversationTimeLimitCard = {
  manifest: Object.freeze({
    id: 'core-ai-conversation-time-limit',
    locales: ['core-ai-conversation-time-limit.'],
    helpers: ['fieldRow', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-conversation-time-limit.label': 'Stop a conversation after',
        'core-ai-conversation-time-limit.help':
          'Measured from when it started, not from the last message. A conversation left open overnight cannot quietly resume in the morning and carry on spending.',
        'core-ai-conversation-time-limit.none': 'no limit',
        'core-ai-conversation-time-limit.15_minutes': '15 minutes',
        'core-ai-conversation-time-limit.hour': 'an hour',
        'core-ai-conversation-time-limit.4_hours': 'four hours',
        'core-ai-conversation-time-limit.day': 'a day',
      }),
      es: Object.freeze({
        'core-ai-conversation-time-limit.label':
          'Detener una conversación después de',
        'core-ai-conversation-time-limit.help':
          'Se mide desde su inicio, no desde el último mensaje. Una conversación abierta durante la noche no puede reanudarse silenciosamente por la mañana y seguir gastando.',
        'core-ai-conversation-time-limit.none': 'sin límite',
        'core-ai-conversation-time-limit.15_minutes': '15 minutos',
        'core-ai-conversation-time-limit.hour': 'una hora',
        'core-ai-conversation-time-limit.4_hours': 'cuatro horas',
        'core-ai-conversation-time-limit.day': 'un día',
      }),
      hi: Object.freeze({
        'core-ai-conversation-time-limit.label': 'बातचीत को इसके बाद रोकें',
        'core-ai-conversation-time-limit.help':
          'इसे शुरू होने के समय से मापा जाता है, अंतिम संदेश से नहीं। रात भर खुली बातचीत सुबह चुपचाप फिर शुरू होकर खर्च जारी नहीं रख सकती।',
        'core-ai-conversation-time-limit.none': 'कोई सीमा नहीं',
        'core-ai-conversation-time-limit.15_minutes': '15 मिनट',
        'core-ai-conversation-time-limit.hour': 'एक घंटा',
        'core-ai-conversation-time-limit.4_hours': 'चार घंटे',
        'core-ai-conversation-time-limit.day': 'एक दिन',
      }),
      ar: Object.freeze({
        'core-ai-conversation-time-limit.label': 'أوقف المحادثة بعد',
        'core-ai-conversation-time-limit.help':
          'يُقاس من وقت بدئها لا من آخر رسالة. لا يمكن لمحادثة تُترك مفتوحة طوال الليل أن تستأنف بهدوء في الصباح وتواصل الإنفاق.',
        'core-ai-conversation-time-limit.none': 'بلا حد',
        'core-ai-conversation-time-limit.15_minutes': '15 دقيقة',
        'core-ai-conversation-time-limit.hour': 'ساعة',
        'core-ai-conversation-time-limit.4_hours': 'أربع ساعات',
        'core-ai-conversation-time-limit.day': 'يوم',
      }),
      zh: Object.freeze({
        'core-ai-conversation-time-limit.label': '在此时间后停止对话',
        'core-ai-conversation-time-limit.help':
          '从开始时间算起，而不是从最后一条消息算起。整夜保持打开的对话不能在早晨悄悄恢复并继续花费。',
        'core-ai-conversation-time-limit.none': '不限',
        'core-ai-conversation-time-limit.15_minutes': '15 分钟',
        'core-ai-conversation-time-limit.hour': '一小时',
        'core-ai-conversation-time-limit.4_hours': '四小时',
        'core-ai-conversation-time-limit.day': '一天',
      }),
    }),
  }),
  render(data, helpers) {
    const t = (key, fallback) =>
      helpers.i18n.t(`core-ai-conversation-time-limit.${key}`, {}, fallback);
    const select = document.createElement('select');
    select.id = 'aiMaxSeconds';
    for (const [value, key, fallback] of [
      [0, 'none', 'no limit'],
      [900, '15_minutes', '15 minutes'],
      [3600, 'hour', 'an hour'],
      [14400, '4_hours', 'four hours'],
      [86400, 'day', 'a day'],
    ]) {
      const option = document.createElement('option');
      option.value = String(value);
      option.textContent = t(key, fallback);
      option.selected = Number(data.max_seconds) === value;
      select.appendChild(option);
    }
    return helpers.fieldRow(
      t('label', 'Stop a conversation after'),
      select,
      t(
        'help',
        'Measured from when it started, not from the last message. A conversation left open overnight cannot quietly resume in the morning and carry on spending.',
      ),
    );
  },
};
