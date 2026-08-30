/* Core card package: prioritized system findings and deliberate exceptions. */
window.ZogographDiagnosisResultsCard = {
  manifest: Object.freeze({
    id: 'core-diagnosis-results',
    locales: ['core-diagnosis-results.'],
    helpers: ['target', 'section', 'findingRow', 'restore', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-diagnosis-results.none':
          'Nothing to report. Every check ran and none of them found anything — which is a different statement from nothing having been measured.',
        'core-diagnosis-results.critical': 'Attention needed now: {{count}}.',
        'core-diagnosis-results.warning': '{{count}} worth looking at.',
        'core-diagnosis-results.ok': 'Nothing urgent.',
        'core-diagnosis-results.ignored': 'Ignored findings',
        'core-diagnosis-results.ignored_help':
          'These are intentional exceptions. Restore one to include it in later checks.',
        'core-diagnosis-results.restore': 'Restore',
      }),
      es: Object.freeze({
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
      }),
      hi: Object.freeze({
        'core-diagnosis-results.none':
          'रिपोर्ट करने के लिए कुछ नहीं है। हर जाँच चली और किसी को कुछ नहीं मिला; यह कुछ भी न मापे जाने से अलग है।',
        'core-diagnosis-results.critical': 'अभी ध्यान चाहिए: {{count}}।',
        'core-diagnosis-results.warning': '{{count}} पर ध्यान देना उचित है।',
        'core-diagnosis-results.ok': 'कुछ भी तत्काल नहीं है।',
        'core-diagnosis-results.ignored': 'अनदेखे निष्कर्ष',
        'core-diagnosis-results.ignored_help':
          'ये जानबूझकर किए गए अपवाद हैं। बाद की जाँचों में शामिल करने के लिए एक को पुनर्स्थापित करें।',
        'core-diagnosis-results.restore': 'पुनर्स्थापित करें',
      }),
      ar: Object.freeze({
        'core-diagnosis-results.none':
          'لا يوجد ما يُبلّغ عنه. عملت كل الفحوصات ولم يجد أي منها شيئًا، وهذا يختلف عن عدم قياس شيء.',
        'core-diagnosis-results.critical': 'تحتاج إلى اهتمام الآن: {{count}}.',
        'core-diagnosis-results.warning': '{{count}} يستحق النظر إليه.',
        'core-diagnosis-results.ok': 'لا شيء عاجل.',
        'core-diagnosis-results.ignored': 'نتائج متجاهلة',
        'core-diagnosis-results.ignored_help':
          'هذه استثناءات مقصودة. استعد واحدة لإدراجها في الفحوصات اللاحقة.',
        'core-diagnosis-results.restore': 'استعادة',
      }),
      zh: Object.freeze({
        'core-diagnosis-results.none':
          '没有需要报告的内容。所有检查均已运行，且没有发现任何问题；这不同于未进行测量。',
        'core-diagnosis-results.critical': '现在需要关注：{{count}}。',
        'core-diagnosis-results.warning': '{{count}} 项值得查看。',
        'core-diagnosis-results.ok': '没有紧急事项。',
        'core-diagnosis-results.ignored': '已忽略的发现',
        'core-diagnosis-results.ignored_help':
          '这些是有意设定的例外。恢复其中一项即可将其纳入后续检查。',
        'core-diagnosis-results.restore': '恢复',
      }),
    }),
  }),

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
