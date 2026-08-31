/* Core card package: chat token and cost status. */
window.ZogographChatCostCard = {
  manifest: Object.freeze({
    id: 'core-chat-cost',
    locales: ['core-chat-cost.'],
    helpers: ['humanCount', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-chat-cost.value_known': '{{cost}} · {{tokens}} tokens',
        'core-chat-cost.value': '{{tokens}} tokens',
        'core-chat-cost.title':
          'In {{input}}, out {{output}}, of which {{cached}} was cached input.{{unknown}}',
        'core-chat-cost.unknown':
          ' No prices are set, so the cost is not known.',
      }),
      es: Object.freeze({
        'core-chat-cost.value_known': '{{cost}} · {{tokens}} tokens',
        'core-chat-cost.value': '{{tokens}} tokens',
        'core-chat-cost.title':
          'Entrada {{input}}, salida {{output}}, de los cuales {{cached}} son entrada en caché.{{unknown}}',
        'core-chat-cost.unknown':
          ' No hay precios configurados, por lo que se desconoce el coste.',
      }),
      hi: Object.freeze({
        'core-chat-cost.value_known': '{{cost}} · {{tokens}} टोकन',
        'core-chat-cost.value': '{{tokens}} टोकन',
        'core-chat-cost.title':
          'इनपुट {{input}}, आउटपुट {{output}}, जिसमें से {{cached}} कैश किया हुआ इनपुट था।{{unknown}}',
        'core-chat-cost.unknown':
          ' कोई मूल्य सेट नहीं है, इसलिए लागत ज्ञात नहीं है।',
      }),
      ar: Object.freeze({
        'core-chat-cost.value_known': '{{cost}} · {{tokens}} رمزًا',
        'core-chat-cost.value': '{{tokens}} رمزًا',
        'core-chat-cost.title':
          'الإدخال {{input}}، والإخراج {{output}}، منها {{cached}} إدخال مخزّن مؤقتًا.{{unknown}}',
        'core-chat-cost.unknown': ' لم تُضبط أسعار، لذا التكلفة غير معروفة.',
      }),
      zh: Object.freeze({
        'core-chat-cost.value_known': '{{cost}} · {{tokens}} 个令牌',
        'core-chat-cost.value': '{{tokens}} 个令牌',
        'core-chat-cost.title':
          '输入 {{input}}，输出 {{output}}，其中 {{cached}} 为缓存输入。{{unknown}}',
        'core-chat-cost.unknown': ' 尚未设置价格，因此费用未知。',
      }),
    }),
  }),

  render(session, helpers) {
    const { humanCount, i18n } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    const tokens = humanCount(session.tokens.in + session.tokens.out);
    return {
      value: session.spent.known
        ? t(
            'core-chat-cost.value_known',
            { cost: session.spent.said, tokens },
            '{{cost}} · {{tokens}} tokens',
          )
        : t('core-chat-cost.value', { tokens }, '{{tokens}} tokens'),
      title: t(
        'core-chat-cost.title',
        {
          input: humanCount(session.tokens.in),
          output: humanCount(session.tokens.out),
          cached: humanCount(session.tokens.cached),
          unknown: session.spent.known
            ? ''
            : t(
                'core-chat-cost.unknown',
                {},
                ' No prices are set, so the cost is not known.',
              ),
        },
        'In {{input}}, out {{output}}, of which {{cached}} was cached input.{{unknown}}',
      ),
    };
  },
};
