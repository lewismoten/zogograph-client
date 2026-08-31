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
        'core-ai-image-costs.plan_month': 'The plan, per month',
        'core-ai-image-costs.plan_month_help':
          'In cents. 495 for a $4.95 plan. This is divided by the allowances below to work out what one picture costs.',
        'core-ai-image-costs.standard_included': 'Standard images included',
        'core-ai-image-costs.standard_included_help':
          'How many the plan covers each month at the standard quality. The plan cost divided by this is what one is reported as costing.',
        'core-ai-image-costs.top_quality_included':
          'Top-quality images included',
        'core-ai-image-costs.top_quality_included_help':
          'The separate allowance for the expensive tier, counted apart from the standard one so that using one does not run the other out.',
        'core-ai-image-costs.standard_extra': 'Each standard one past that',
        'core-ai-image-costs.standard_extra_help':
          'In cents. Once the allowance is gone the plan charges for credits, and this is a real cost rather than a share of the fee.',
        'core-ai-image-costs.hd_extra': '…at HD',
        'core-ai-image-costs.top_quality_extra': '…at the top quality',
        'core-ai-image-costs.cents_help': 'In cents.',
      }),
      es: Object.freeze({
        'core-ai-image-costs.title': 'Lo que cuestan',
        'core-ai-image-costs.allowance_each':
          ' Actualmente {{cost}} por imagen — {{used}} de {{allowance}} usadas este mes.',
        'core-ai-image-costs.help':
          'Una imagen dentro de la asignación no tiene coste marginal; fuera de ella se aplica el precio de créditos.',
        'core-ai-image-costs.plan_month': 'El plan, al mes',
        'core-ai-image-costs.plan_month_help':
          'En céntimos. 495 para un plan de 4,95 $. Se divide entre las asignaciones de abajo para calcular el coste de una imagen.',
        'core-ai-image-costs.standard_included': 'Imágenes estándar incluidas',
        'core-ai-image-costs.standard_included_help':
          'Cuántas cubre el plan cada mes con calidad estándar. El coste del plan dividido por esta cifra es lo que se informa como coste de una imagen.',
        'core-ai-image-costs.top_quality_included':
          'Imágenes de máxima calidad incluidas',
        'core-ai-image-costs.top_quality_included_help':
          'La asignación independiente del nivel caro, contada aparte de la estándar para que usar una no agote la otra.',
        'core-ai-image-costs.standard_extra': 'Cada estándar después de eso',
        'core-ai-image-costs.standard_extra_help':
          'En céntimos. Cuando se termina la asignación, el plan cobra créditos; es un coste real, no una parte de la tarifa.',
        'core-ai-image-costs.hd_extra': '…en HD',
        'core-ai-image-costs.top_quality_extra': '…en máxima calidad',
        'core-ai-image-costs.cents_help': 'En céntimos.',
      }),
      hi: Object.freeze({
        'core-ai-image-costs.title': 'इनकी लागत',
        'core-ai-image-costs.allowance_each':
          ' अभी प्रति चित्र {{cost}} — इस महीने {{allowance}} में से {{used}}।',
        'core-ai-image-costs.help':
          'भत्ते के भीतर चित्र की सीमांत लागत नहीं होती; बाद में क्रेडिट मूल्य लागू होता है।',
        'core-ai-image-costs.plan_month': 'योजना, प्रति माह',
        'core-ai-image-costs.plan_month_help':
          'सेंट में। $4.95 की योजना के लिए 495। एक चित्र की लागत निकालने के लिए इसे नीचे के भत्तों से विभाजित किया जाता है।',
        'core-ai-image-costs.standard_included': 'शामिल मानक चित्र',
        'core-ai-image-costs.standard_included_help':
          'मानक गुणवत्ता पर योजना हर महीने कितने चित्र कवर करती है। योजना लागत को इससे विभाजित कर एक चित्र की बताई गई लागत निकाली जाती है।',
        'core-ai-image-costs.top_quality_included':
          'शामिल सर्वोच्च-गुणवत्ता चित्र',
        'core-ai-image-costs.top_quality_included_help':
          'महँगे स्तर का अलग भत्ता, मानक से अलग गिना जाता है ताकि एक का उपयोग दूसरे को समाप्त न करे।',
        'core-ai-image-costs.standard_extra': 'उसके बाद प्रत्येक मानक चित्र',
        'core-ai-image-costs.standard_extra_help':
          'सेंट में। भत्ता खत्म होने पर योजना क्रेडिट के लिए शुल्क लेती है; यह शुल्क का हिस्सा नहीं, वास्तविक लागत है।',
        'core-ai-image-costs.hd_extra': '…HD में',
        'core-ai-image-costs.top_quality_extra': '…सर्वोच्च गुणवत्ता में',
        'core-ai-image-costs.cents_help': 'सेंट में।',
      }),
      ar: Object.freeze({
        'core-ai-image-costs.title': 'تكلفتها',
        'core-ai-image-costs.allowance_each':
          ' حاليًا {{cost}} لكل صورة — استُخدم {{used}} من {{allowance}} هذا الشهر.',
        'core-ai-image-costs.help':
          'داخل البدل لا توجد تكلفة هامشية للصورة، وبعده يطبق سعر الرصيد.',
        'core-ai-image-costs.plan_month': 'الخطة، شهريًا',
        'core-ai-image-costs.plan_month_help':
          'بالسنتات. 495 لخطة بقيمة 4.95 دولار. يُقسم هذا على البدلات أدناه لمعرفة تكلفة صورة واحدة.',
        'core-ai-image-costs.standard_included': 'الصور القياسية المشمولة',
        'core-ai-image-costs.standard_included_help':
          'عدد ما تغطيه الخطة كل شهر بالجودة القياسية. قسمة تكلفة الخطة على هذا هي ما يُبلغ عنه كتكلِفة للصورة.',
        'core-ai-image-costs.top_quality_included':
          'الصور الأعلى جودة المشمولة',
        'core-ai-image-costs.top_quality_included_help':
          'البدل المنفصل للطبقة المكلفة، ويُحسب بعيدًا عن القياسية كي لا يؤدي استعمال إحداهما إلى استنفاد الأخرى.',
        'core-ai-image-costs.standard_extra': 'كل صورة قياسية بعد ذلك',
        'core-ai-image-costs.standard_extra_help':
          'بالسنتات. بعد نفاد البدل تفرض الخطة رسومًا على الأرصدة، وهذه تكلفة حقيقية وليست حصة من الرسم.',
        'core-ai-image-costs.hd_extra': '…بدقة HD',
        'core-ai-image-costs.top_quality_extra': '…بأعلى جودة',
        'core-ai-image-costs.cents_help': 'بالسنتات.',
      }),
      zh: Object.freeze({
        'core-ai-image-costs.title': '图片费用',
        'core-ai-image-costs.allowance_each':
          ' 当前每张 {{cost}}，本月已用 {{used}} / {{allowance}}。',
        'core-ai-image-costs.help':
          '额度内的图片没有边际成本；超过额度后适用点数价格。',
        'core-ai-image-costs.plan_month': '套餐，每月',
        'core-ai-image-costs.plan_month_help':
          '单位为美分。4.95 美元套餐填 495。该数值除以下方配额，用于计算一张图片的费用。',
        'core-ai-image-costs.standard_included': '包含的标准图片',
        'core-ai-image-costs.standard_included_help':
          '套餐每月在标准质量下涵盖的图片数量。套餐费用除以此数，就是报告的一张图片成本。',
        'core-ai-image-costs.top_quality_included': '包含的最高质量图片',
        'core-ai-image-costs.top_quality_included_help':
          '昂贵层级的独立配额，与标准层级分开计算，使用一个不会耗尽另一个。',
        'core-ai-image-costs.standard_extra': '超出后每张标准图片',
        'core-ai-image-costs.standard_extra_help':
          '单位为美分。配额用完后，套餐会按积分收费；这是真实成本，不是套餐费的一部分。',
        'core-ai-image-costs.hd_extra': '…HD 质量',
        'core-ai-image-costs.top_quality_extra': '…最高质量',
        'core-ai-image-costs.cents_help': '单位为美分。',
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
        'core-ai-image-costs.plan_month',
        'The plan, per month',
        pictures.monthly_cents,
        'core-ai-image-costs.plan_month_help',
        'In cents. 495 for a $4.95 plan. This is divided by the allowances below to work out what one picture costs.',
      ],
      [
        'aiIncludedStandard',
        'core-ai-image-costs.standard_included',
        'Standard images included',
        pictures.included_standard,
        'core-ai-image-costs.standard_included_help',
        'How many the plan covers each month at the standard quality. The plan cost divided by this is what one is reported as costing.' +
          each('standard'),
      ],
      [
        'aiIncludedGenius',
        'core-ai-image-costs.top_quality_included',
        'Top-quality images included',
        pictures.included_genius,
        'core-ai-image-costs.top_quality_included_help',
        'The separate allowance for the expensive tier, counted apart from the standard one so that using one does not run the other out.' +
          each('genius'),
      ],
      [
        'aiPriceStandard',
        'core-ai-image-costs.standard_extra',
        'Each standard one past that',
        pictures.price_standard,
        'core-ai-image-costs.standard_extra_help',
        'In cents. Once the allowance is gone the plan charges for credits, and this is a real cost rather than a share of the fee.',
      ],
      [
        'aiPriceHd',
        'core-ai-image-costs.hd_extra',
        '…at HD',
        pictures.price_hd,
        'core-ai-image-costs.cents_help',
        'In cents.',
      ],
      [
        'aiPriceGenius',
        'core-ai-image-costs.top_quality_extra',
        '…at the top quality',
        pictures.price_genius,
        'core-ai-image-costs.cents_help',
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
