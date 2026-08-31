/* Core card package: alerts that AI must never delay or suppress. */
window.ZogographAiAlertSafetyFloorCard = {
  manifest: Object.freeze({
    id: 'core-ai-alert-safety-floor',
    locales: ['core-ai-alert-safety-floor.'],
    helpers: ['fieldRow', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-alert-safety-floor.label': 'Except',
        'core-ai-alert-safety-floor.error': 'never for error or critical',
        'core-ai-alert-safety-floor.warning': 'never for warning and above',
        'core-ai-alert-safety-floor.critical': 'never for critical',
        'core-ai-alert-safety-floor.none': 'any of them, including critical',
        'core-ai-alert-safety-floor.help':
          "A critical alert is the one case where a delay and a model's opinion are both worse than a plain fast message. Left at the default it never touches those.",
      }),
      es: Object.freeze({
        'core-ai-alert-safety-floor.label': 'Excepto',
        'core-ai-alert-safety-floor.error': 'nunca para error o crítico',
        'core-ai-alert-safety-floor.warning':
          'nunca para advertencia y superiores',
        'core-ai-alert-safety-floor.critical': 'nunca para crítico',
        'core-ai-alert-safety-floor.none': 'cualquiera, incluso crítico',
        'core-ai-alert-safety-floor.help':
          'Una alerta crítica no debe retrasarse ni depender de la opinión del modelo. Con el valor predeterminado no las toca.',
      }),
      hi: Object.freeze({
        'core-ai-alert-safety-floor.label': 'इसके अलावा',
        'core-ai-alert-safety-floor.error': 'त्रुटि या गंभीर के लिए कभी नहीं',
        'core-ai-alert-safety-floor.warning': 'चेतावनी और ऊपर के लिए कभी नहीं',
        'core-ai-alert-safety-floor.critical': 'गंभीर के लिए कभी नहीं',
        'core-ai-alert-safety-floor.none': 'गंभीर सहित कोई भी',
        'core-ai-alert-safety-floor.help':
          'गंभीर चेतावनी में देरी और मॉडल की राय दोनों साधारण तेज संदेश से बदतर हैं। डिफ़ॉल्ट पर यह उन्हें नहीं छूता।',
      }),
      ar: Object.freeze({
        'core-ai-alert-safety-floor.label': 'باستثناء',
        'core-ai-alert-safety-floor.error': 'أبدًا للخطأ أو الحرج',
        'core-ai-alert-safety-floor.warning': 'أبدًا للتحذير وما فوقه',
        'core-ai-alert-safety-floor.critical': 'أبدًا للحرج',
        'core-ai-alert-safety-floor.none': 'أيًّا منها، بما في ذلك الحرج',
        'core-ai-alert-safety-floor.help':
          'التنبيه الحرج هو الحالة التي يكون فيها التأخير ورأي النموذج أسوأ من رسالة سريعة واضحة. بالإعداد الافتراضي لا يلمسها.',
      }),
      zh: Object.freeze({
        'core-ai-alert-safety-floor.label': '例外',
        'core-ai-alert-safety-floor.error': '错误或严重告警永不处理',
        'core-ai-alert-safety-floor.warning': '警告及以上永不处理',
        'core-ai-alert-safety-floor.critical': '严重告警永不处理',
        'core-ai-alert-safety-floor.none': '任意告警，包括严重告警',
        'core-ai-alert-safety-floor.help':
          '严重告警是延迟和模型意见都不如快速明确信息的情形。使用默认值时它不会处理这些告警。',
      }),
    }),
  }),
  render(data, helpers) {
    const t = (key, fallback) =>
      helpers.i18n.t(`core-ai-alert-safety-floor.${key}`, {}, fallback);
    const select = document.createElement('select');
    select.id = 'aiAlertFloor';
    for (const [value, key, fallback] of [
      ['error', 'error', 'never for error or critical'],
      ['warning', 'warning', 'never for warning and above'],
      ['critical', 'critical', 'never for critical'],
      ['none', 'none', 'any of them, including critical'],
    ]) {
      const option = document.createElement('option');
      option.value = value;
      option.textContent = t(key, fallback);
      option.selected = (data.alerts?.never_above ?? 'error') === value;
      select.appendChild(option);
    }
    return helpers.fieldRow(
      t('label', 'Except'),
      select,
      t(
        'help',
        "A critical alert is the one case where a delay and a model's opinion are both worse than a plain fast message. Left at the default it never touches those.",
      ),
    );
  },
};
