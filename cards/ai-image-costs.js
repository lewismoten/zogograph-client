/* Core card package: allowance and credit costs for AI images. */
window.ZogographAiImageCostsCard = {
  manifest: Object.freeze({
    id: 'core-ai-image-costs',
    locales: ['core-ai-image-costs.'],
    helpers: ['fieldRow', 'section', 'note', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-image-costs.title': 'What they cost',
        'core-ai-image-costs.allowance_each':
          ' Currently {{cost}} per picture — {{used}} of {{allowance}} used this month.',
        'core-ai-image-costs.help':
          'A picture inside the allowance has no marginal cost — the fee was paid for exactly this — so what a conversation reports is the fee divided by the allowance. It is what that picture cost you, not what one more would cost, and because both tiers divide the same fee a full month of both would add up to more than the fee. Past the allowance the credit price applies and is a real marginal cost.',
      }),
      es: Object.freeze({
        'core-ai-image-costs.title': 'Lo que cuestan',
        'core-ai-image-costs.allowance_each':
          ' Actualmente {{cost}} por imagen — {{used}} de {{allowance}} usadas este mes.',
        'core-ai-image-costs.help':
          'Una imagen dentro de la asignación no tiene coste marginal; fuera de ella se aplica el precio de créditos.',
      }),
      hi: Object.freeze({
        'core-ai-image-costs.title': 'इनकी लागत',
        'core-ai-image-costs.allowance_each':
          ' अभी प्रति चित्र {{cost}} — इस महीने {{allowance}} में से {{used}}।',
        'core-ai-image-costs.help':
          'भत्ते के भीतर चित्र की सीमांत लागत नहीं होती; बाद में क्रेडिट मूल्य लागू होता है।',
      }),
      ar: Object.freeze({
        'core-ai-image-costs.title': 'تكلفتها',
        'core-ai-image-costs.allowance_each':
          ' حاليًا {{cost}} لكل صورة — استُخدم {{used}} من {{allowance}} هذا الشهر.',
        'core-ai-image-costs.help':
          'داخل البدل لا توجد تكلفة هامشية للصورة، وبعده يطبق سعر الرصيد.',
      }),
      zh: Object.freeze({
        'core-ai-image-costs.title': '图片费用',
        'core-ai-image-costs.allowance_each':
          ' 当前每张 {{cost}}，本月已用 {{used}} / {{allowance}}。',
        'core-ai-image-costs.help':
          '额度内的图片没有边际成本；超过额度后适用点数价格。',
      }),
    }),
  }),
  render(input, helpers) {
    const { pictures } = input;
    const { fieldRow, section, note, i18n } = helpers;
    const t = (key, vars, fallback) => i18n.t(key, vars, fallback);
    const tiers = pictures.allowance?.tiers ?? {};
    const each = (tier) =>
      tiers[tier]?.each
        ? t(
            'core-ai-image-costs.allowance_each',
            {
              cost: (tiers[tier].each / 100).toFixed(6).replace(/0+$/, ''),
              used: tiers[tier].used,
              allowance: tiers[tier].allowance,
            },
            ' Currently {{cost}} per picture — {{used}} of {{allowance}} used this month.',
          )
        : '';
    const money = document.createElement('div');
    money.className = 'settings';
    for (const [id, labelKey, label, value, hintKey, hint] of [
      [
        'aiMonthlyCents',
        'ai.images_plan_month',
        'The plan, per month',
        pictures.monthly_cents,
        'ai.images_plan_month_help',
        'In cents. 495 for a $4.95 plan. This is divided by the allowances below to work out what one picture costs.',
      ],
      [
        'aiIncludedStandard',
        'ai.images_standard_included',
        'Standard images included',
        pictures.included_standard,
        'ai.images_standard_included_help',
        'How many the plan covers each month at the standard quality. The plan cost divided by this is what one is reported as costing.' +
          each('standard'),
      ],
      [
        'aiIncludedGenius',
        'ai.images_top_quality_included',
        'Top-quality images included',
        pictures.included_genius,
        'ai.images_top_quality_included_help',
        'The separate allowance for the expensive tier, counted apart from the standard one so that using one does not run the other out.' +
          each('genius'),
      ],
      [
        'aiPriceStandard',
        'ai.images_standard_extra',
        'Each standard one past that',
        pictures.price_standard,
        'ai.images_standard_extra_help',
        'In cents. Once the allowance is gone the plan charges for credits, and this is a real cost rather than a share of the fee.',
      ],
      [
        'aiPriceHd',
        'ai.images_hd_extra',
        '…at HD',
        pictures.price_hd,
        'ai.images_cents_help',
        'In cents.',
      ],
      [
        'aiPriceGenius',
        'ai.images_top_quality_extra',
        '…at the top quality',
        pictures.price_genius,
        'ai.images_cents_help',
        'In cents.',
      ],
    ]) {
      const box = document.createElement('input');
      box.type = 'number';
      box.id = id;
      box.min = '0';
      box.step = '0.01';
      box.className = 'days';
      box.value = String(value ?? 0);
      money.appendChild(
        fieldRow(i18n.t(labelKey, {}, label), box, i18n.t(hintKey, {}, hint)),
      );
    }
    return [
      section(t('core-ai-image-costs.title', {}, 'What they cost'), money),
      note(
        t(
          'core-ai-image-costs.help',
          {},
          'A picture inside the allowance has no marginal cost — the fee was paid for exactly this — so what a conversation reports is the fee divided by the allowance. It is what that picture cost you, not what one more would cost, and because both tiers divide the same fee a full month of both would add up to more than the fee. Past the allowance the credit price applies and is a real marginal cost.',
        ),
      ),
    ];
  },
};
