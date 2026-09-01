/* Core card package: prioritized system findings and deliberate exceptions. */
window.ZogographDiagnosisResultsCard = {
  manifest: Object.freeze({
    id: 'core-diagnosis-results',
    locales: ['core-diagnosis-results.'],
    helpers: ['target', 'section', 'findingRow', 'restore', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-diagnosis-results.title': 'What is wrong',
        'core-diagnosis-results.check_again': 'Check again',
        'core-diagnosis-results.sign_in': 'Sign in to see the diagnosis.',
        'core-diagnosis-results.reading_everything': 'Reading everything…',
        'core-diagnosis-results.help':
          'There are forty pages here, each answering one question well — which helps when you already know the question. This reads all of them at once. Every line carries the measurement it came from and a link to the page that shows it in full, and nothing here is inferred from a correlation.',
        'core-diagnosis-results.none':
          'Nothing to report. Every check ran and none of them found anything — which is a different statement from nothing having been measured.',
        'core-diagnosis-results.critical': 'Attention needed now: {{count}}.',
        'core-diagnosis-results.warning': '{{count}} worth looking at.',
        'core-diagnosis-results.ok': 'Nothing urgent.',
        'core-diagnosis-results.ignored': 'Ignored findings',
        'core-diagnosis-results.ignored_help':
          'These are intentional exceptions. Restore one to include it in later checks.',
        'core-diagnosis-results.restore': 'Restore',
        'core-diagnosis-results.not_measured': 'not measured',
        'core-diagnosis-results.ignore_action': 'Ignore this finding',
        'core-diagnosis-results.ignore_title':
          'Hide this exact diagnostic on future checks until the configuration is changed.',
        'core-diagnosis-results.ignore_confirm':
          'Ignore “{{title}}” now and on future checks?\n\nUse this only for an intentional configuration.',
        'core-diagnosis-results.ignore_success':
          'Ignored for future checks. The decision is in the audit log.',
        'core-diagnosis-results.export_preparing':
          'Preparing the sanitized diagnostic snapshot…',
      }),
      es: Object.freeze({
        'core-diagnosis-results.title': 'Qué está mal',
        'core-diagnosis-results.check_again': 'Comprobar de nuevo',
        'core-diagnosis-results.sign_in':
          'Inicie sesión para ver el diagnóstico.',
        'core-diagnosis-results.reading_everything': 'Leyendo todo…',
        'core-diagnosis-results.help':
          'Hay cuarenta páginas aquí y cada una responde bien a una pregunta, lo que ayuda cuando ya conoce la pregunta. Esta las lee todas de una vez. Cada línea incluye la medición de la que procede y un enlace a la página que la muestra completa; aquí no se infiere nada de una correlación.',
        'core-diagnosis-results.none':
          'No hay nada que informar. Se ejecutaron todas las comprobaciones y ninguna encontró nada; eso es distinto de no haber medido nada.',
        'core-diagnosis-results.critical':
          'Se necesita atención ahora: {{count}}.',
        'core-diagnosis-results.warning': '{{count}} merece atención.',
        'core-diagnosis-results.ok': 'Nada urgente.',
        'core-diagnosis-results.ignored': 'Hallazgos ignorados',
        'core-diagnosis-results.ignored_help':
          'Son excepciones intencionales. Restaure uno para incluirlo en comprobaciones posteriores.',
        'core-diagnosis-results.restore': 'Restaurar',
        'core-diagnosis-results.not_measured': 'no medido',
        'core-diagnosis-results.ignore_action': 'Ignorar este hallazgo',
        'core-diagnosis-results.ignore_title':
          'Oculta este diagnóstico exacto en comprobaciones futuras hasta que cambie la configuración.',
        'core-diagnosis-results.ignore_confirm':
          '¿Ignorar “{{title}}” ahora y en comprobaciones futuras?\n\nÚselo solo para una configuración intencional.',
        'core-diagnosis-results.ignore_success':
          'Se ignorará en comprobaciones futuras. La decisión queda en el registro de auditoría.',
        'core-diagnosis-results.export_preparing':
          'Se está preparando la instantánea de diagnóstico depurada…',
      }),
      hi: Object.freeze({
        'core-diagnosis-results.title': 'क्या गलत है',
        'core-diagnosis-results.check_again': 'फिर जाँचें',
        'core-diagnosis-results.sign_in': 'निदान देखने के लिए साइन इन करें।',
        'core-diagnosis-results.reading_everything': 'सब कुछ पढ़ा जा रहा है…',
        'core-diagnosis-results.help':
          'यहाँ चालीस पेज हैं और हर पेज एक प्रश्न का अच्छा उत्तर देता है, जो तब मदद करता है जब आप प्रश्न पहले से जानते हों। यह उन सबको एक साथ पढ़ता है। हर पंक्ति में उसकी माप और उसे पूरा दिखाने वाले पेज का लिंक है; यहाँ किसी सहसंबंध से कोई निष्कर्ष नहीं निकाला जाता।',
        'core-diagnosis-results.none':
          'रिपोर्ट करने के लिए कुछ नहीं है। हर जाँच चली और किसी को कुछ नहीं मिला; यह कुछ भी न मापे जाने से अलग है।',
        'core-diagnosis-results.critical': 'अभी ध्यान चाहिए: {{count}}।',
        'core-diagnosis-results.warning': '{{count}} पर ध्यान देना उचित है।',
        'core-diagnosis-results.ok': 'कुछ भी तत्काल नहीं है।',
        'core-diagnosis-results.ignored': 'अनदेखे निष्कर्ष',
        'core-diagnosis-results.ignored_help':
          'ये जानबूझकर किए गए अपवाद हैं। बाद की जाँचों में शामिल करने के लिए एक को पुनर्स्थापित करें।',
        'core-diagnosis-results.restore': 'पुनर्स्थापित करें',
        'core-diagnosis-results.not_measured': 'मापा नहीं गया',
        'core-diagnosis-results.ignore_action': 'इस निष्कर्ष को अनदेखा करें',
        'core-diagnosis-results.ignore_title':
          'कॉन्फ़िगरेशन बदलने तक भविष्य की जाँचों में इस सटीक निदान को छिपाएँ।',
        'core-diagnosis-results.ignore_confirm':
          '“{{title}}” को अभी और भविष्य की जाँचों में अनदेखा करें?\n\nइसे केवल जानबूझकर की गई कॉन्फ़िगरेशन के लिए उपयोग करें।',
        'core-diagnosis-results.ignore_success':
          'भविष्य की जाँचों के लिए अनदेखा किया गया। निर्णय ऑडिट लॉग में है।',
        'core-diagnosis-results.export_preparing':
          'स्वच्छ निदान स्नैपशॉट तैयार किया जा रहा है…',
      }),
      ar: Object.freeze({
        'core-diagnosis-results.title': 'ما الخطأ',
        'core-diagnosis-results.check_again': 'تحقق مجددًا',
        'core-diagnosis-results.sign_in': 'سجّل الدخول لرؤية التشخيص.',
        'core-diagnosis-results.reading_everything': 'جارٍ قراءة كل شيء…',
        'core-diagnosis-results.help':
          'توجد هنا أربعون صفحة، تجيب كل واحدة منها عن سؤال بصورة جيدة، وهذا مفيد حين تعرف السؤال مسبقًا. تقرأ هذه الصفحة كلها دفعة واحدة. يحمل كل سطر القياس الذي أتى منه ورابطًا للصفحة التي تعرضه كاملًا، ولا يُستنتج هنا أي شيء من مجرد ارتباط.',
        'core-diagnosis-results.none':
          'لا يوجد ما يُبلّغ عنه. عملت كل الفحوصات ولم يجد أي منها شيئًا، وهذا يختلف عن عدم قياس شيء.',
        'core-diagnosis-results.critical': 'تحتاج إلى اهتمام الآن: {{count}}.',
        'core-diagnosis-results.warning': '{{count}} يستحق النظر إليه.',
        'core-diagnosis-results.ok': 'لا شيء عاجل.',
        'core-diagnosis-results.ignored': 'نتائج متجاهلة',
        'core-diagnosis-results.ignored_help':
          'هذه استثناءات مقصودة. استعد واحدة لإدراجها في الفحوصات اللاحقة.',
        'core-diagnosis-results.restore': 'استعادة',
        'core-diagnosis-results.not_measured': 'غير مقاس',
        'core-diagnosis-results.ignore_action': 'تجاهل هذه النتيجة',
        'core-diagnosis-results.ignore_title':
          'إخفاء هذا التشخيص الدقيق في الفحوصات القادمة إلى أن يتغير الإعداد.',
        'core-diagnosis-results.ignore_confirm':
          'هل تريد تجاهل “{{title}}” الآن وفي الفحوصات القادمة؟\n\nاستخدم ذلك فقط لإعداد مقصود.',
        'core-diagnosis-results.ignore_success':
          'سيتجاهل هذا في الفحوصات القادمة. القرار موجود في سجل التدقيق.',
        'core-diagnosis-results.export_preparing':
          'جارٍ إعداد لقطة التشخيص المنقّحة…',
      }),
      zh: Object.freeze({
        'core-diagnosis-results.title': '出了什么问题',
        'core-diagnosis-results.check_again': '再次检查',
        'core-diagnosis-results.sign_in': '登录后可查看诊断结果。',
        'core-diagnosis-results.reading_everything': '正在读取全部内容…',
        'core-diagnosis-results.help':
          '这里有四十个页面，每个页面都能很好地回答一个问题——当您已经知道问题时很有帮助。此处会一次读取全部页面。每一行都带有来源读数及完整页面的链接，这里不会根据相关性作出推断。',
        'core-diagnosis-results.none':
          '没有需要报告的内容。所有检查均已运行，且没有发现任何问题；这不同于未进行测量。',
        'core-diagnosis-results.critical': '现在需要关注：{{count}}。',
        'core-diagnosis-results.warning': '{{count}} 项值得查看。',
        'core-diagnosis-results.ok': '没有紧急事项。',
        'core-diagnosis-results.ignored': '已忽略的发现',
        'core-diagnosis-results.ignored_help':
          '这些是有意设定的例外。恢复其中一项即可将其纳入后续检查。',
        'core-diagnosis-results.restore': '恢复',
        'core-diagnosis-results.not_measured': '未测量',
        'core-diagnosis-results.ignore_action': '忽略此发现',
        'core-diagnosis-results.ignore_title':
          '在配置更改前的后续检查中隐藏这一确切诊断。',
        'core-diagnosis-results.ignore_confirm':
          '现在以及在后续检查中忽略“{{title}}”？\n\n仅对有意作出的配置使用此操作。',
        'core-diagnosis-results.ignore_success':
          '后续检查将忽略此项。该决定已记录在审计日志中。',
        'core-diagnosis-results.export_preparing': '正在准备已清理的诊断快照…',
      }),
    }),
  }),

  text(key, valuesOrHelpers = {}, maybeHelpers = null) {
    const helpers = maybeHelpers || valuesOrHelpers;
    const values = maybeHelpers ? valuesOrHelpers : {};
    return helpers.i18n.t(
      `core-diagnosis-results.${key}`,
      values,
      this.manifest.catalogs.en[`core-diagnosis-results.${key}`],
    );
  },

  render(data, helpers) {
    const { target, section, findingRow, restore, i18n } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (!target) return;
    if (!data.findings?.length) {
      const empty = document.createElement('p');
      empty.className = 'band-ok';
      empty.textContent = t(
        'core-diagnosis-results.none',
        {},
        'Nothing to report.',
      );
      target.replaceChildren(empty);
      return;
    }
    const fragment = document.createDocumentFragment();
    const worst = data.findings[0].level;
    const headline = document.createElement('p');
    headline.className = `diagnose-headline band-${worst === 'critical' ? 'crit' : worst === 'warning' ? 'warn' : 'ok'}`;
    headline.textContent =
      worst === 'critical'
        ? t(
            'core-diagnosis-results.critical',
            {
              count: data.findings.filter(
                (finding) => finding.level === 'critical',
              ).length,
            },
            'Attention needed now: {{count}}.',
          )
        : worst === 'warning'
          ? t(
              'core-diagnosis-results.warning',
              {
                count: data.findings.filter(
                  (finding) => finding.level === 'warning',
                ).length,
              },
              '{{count}} worth looking at.',
            )
          : t('core-diagnosis-results.ok', {}, 'Nothing urgent.');
    fragment.appendChild(headline);
    for (const finding of data.findings)
      fragment.appendChild(findingRow(finding));
    if (data.can_restore && data.ignored?.length) {
      const list = document.createElement('div');
      for (const finding of data.ignored) {
        const row = document.createElement('div');
        row.className = 'inspect-actions';
        row.append(document.createTextNode(finding.title));
        const button = document.createElement('button');
        button.className = 'link';
        button.textContent = t('core-diagnosis-results.restore', {}, 'Restore');
        button.addEventListener('click', () => restore(finding));
        row.appendChild(button);
        list.appendChild(row);
      }
      fragment.appendChild(
        section(
          t('core-diagnosis-results.ignored', {}, 'Ignored findings'),
          list,
          t(
            'core-diagnosis-results.ignored_help',
            {},
            'These are intentional exceptions.',
          ),
        ),
      );
    }
    target.replaceChildren(fragment);
  },
};
