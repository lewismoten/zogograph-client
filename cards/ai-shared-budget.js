/* Core card package: assistant budget shared by chats and unattended work. */
window.ZogographAiSharedBudgetCard = {
  manifest: Object.freeze({
    id: 'core-ai-shared-budget',
    locales: ['core-ai-shared-budget.'],
    helpers: ['fieldRow', 'note', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-shared-budget.daily_label': 'Shared daily spend (cents)',
        'core-ai-shared-budget.daily_help':
          'Across every conversation and automated alert investigation. Zero means no daily cap.',
        'core-ai-shared-budget.monthly_label': 'Shared monthly spend (cents)',
        'core-ai-shared-budget.monthly_help':
          'UTC calendar month, across every conversation and automated alert investigation. Zero means no monthly cap.',
        'core-ai-shared-budget.usage': 'Shared AI usage — {{usage}}.',
      }),
      es: Object.freeze({
        'core-ai-shared-budget.daily_label':
          'Gasto diario compartido (centavos)',
        'core-ai-shared-budget.daily_help':
          'Entre todas las conversaciones e investigaciones automáticas de alertas. Cero significa que no hay tope diario.',
        'core-ai-shared-budget.monthly_label':
          'Gasto mensual compartido (centavos)',
        'core-ai-shared-budget.monthly_help':
          'Mes natural UTC, entre todas las conversaciones e investigaciones automáticas de alertas. Cero significa que no hay tope mensual.',
        'core-ai-shared-budget.usage': 'Uso compartido de IA — {{usage}}.',
      }),
      hi: Object.freeze({
        'core-ai-shared-budget.daily_label': 'साझा दैनिक खर्च (सेंट)',
        'core-ai-shared-budget.daily_help':
          'हर बातचीत और स्वचालित चेतावनी जाँच के लिए। शून्य का अर्थ कोई दैनिक सीमा नहीं है।',
        'core-ai-shared-budget.monthly_label': 'साझा मासिक खर्च (सेंट)',
        'core-ai-shared-budget.monthly_help':
          'UTC कैलेंडर माह में, हर बातचीत और स्वचालित चेतावनी जाँच के लिए। शून्य का अर्थ कोई मासिक सीमा नहीं है।',
        'core-ai-shared-budget.usage': 'साझा AI उपयोग — {{usage}}।',
      }),
      ar: Object.freeze({
        'core-ai-shared-budget.daily_label':
          'الإنفاق اليومي المشترك (بالسنتات)',
        'core-ai-shared-budget.daily_help':
          'عبر كل محادثة وتحقيق تلقائي في التنبيهات. الصفر يعني عدم وجود حد يومي.',
        'core-ai-shared-budget.monthly_label':
          'الإنفاق الشهري المشترك (بالسنتات)',
        'core-ai-shared-budget.monthly_help':
          'شهر تقويمي وفق UTC، عبر كل محادثة وتحقيق تلقائي في التنبيهات. الصفر يعني عدم وجود حد شهري.',
        'core-ai-shared-budget.usage':
          'استخدام الذكاء الاصطناعي المشترك — {{usage}}.',
      }),
      zh: Object.freeze({
        'core-ai-shared-budget.daily_label': '共享每日支出（美分）',
        'core-ai-shared-budget.daily_help':
          '适用于每段对话和自动告警调查。零表示不设每日上限。',
        'core-ai-shared-budget.monthly_label': '共享每月支出（美分）',
        'core-ai-shared-budget.monthly_help':
          '按 UTC 自然月计算，适用于每段对话和自动告警调查。零表示不设每月上限。',
        'core-ai-shared-budget.usage': '共享 AI 使用量 — {{usage}}。',
      }),
    }),
  }),
  render(data, helpers) {
    const t = (key, values, fallback) =>
      helpers.i18n.t(`core-ai-shared-budget.${key}`, values, fallback);
    const nodes = [];
    for (const [id, key, value, label, help] of [
      [
        'aiDailyCents',
        'daily',
        data.daily_cents,
        'Shared daily spend (cents)',
        'Across every conversation and automated alert investigation. Zero means no daily cap.',
      ],
      [
        'aiSharedMonthlyCents',
        'monthly',
        data.monthly_cents,
        'Shared monthly spend (cents)',
        'UTC calendar month, across every conversation and automated alert investigation. Zero means no monthly cap.',
      ],
    ]) {
      const input = document.createElement('input');
      input.type = 'number';
      input.id = id;
      input.min = '0';
      input.step = '1';
      input.className = 'days';
      input.value = String(value || 0);
      nodes.push(
        helpers.fieldRow(
          t(`${key}_label`, {}, label),
          input,
          t(`${key}_help`, {}, help),
        ),
      );
    }
    const usage = ['daily', 'monthly'].flatMap((period) => {
      const budget = data.shared_budget?.[period];
      if (!budget || !Number(budget.limit_millicents)) return [];
      return [
        `${period}: ${(Number(budget.used_millicents) / 1000).toFixed(2)} of ${(Number(budget.limit_millicents) / 1000).toFixed(2)} cents`,
      ];
    });
    if (usage.length)
      nodes.push(
        helpers.note(
          t(
            'usage',
            { usage: usage.join('; ') },
            `Shared AI usage — ${usage.join('; ')}.`,
          ),
        ),
      );
    return nodes;
  },
};
