/* Core card package: first-run guidance is independent from the overview shell. */
window.ZogographOnboardingCard = {
  manifest: Object.freeze({
    id: 'core-onboarding',
    locales: ['core-onboarding.'],
    helpers: ['panel', 'body', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-onboarding.title': 'Finish setting up Zogograph',
        'core-onboarding.intro':
          'Zogograph is configured but does not yet have enough local evidence to show its full dashboard.',
        'core-onboarding.vitals_missing':
          'Set up or run the account collector for its first vital reading.',
        'core-onboarding.open_schedule': 'Open Setup schedule',
        'core-onboarding.vitals_ready':
          'Vital readings are arriving. Keep the collector scheduled so charts can build history.',
        'core-onboarding.review_schedule': 'Review Setup schedule',
        'core-onboarding.scan_missing':
          'Run the first storage scan to identify what uses space.',
        'core-onboarding.open_storage': 'Open Storage',
        'core-onboarding.scan_ready':
          'The first storage scan is complete. Review the largest items and set alerts when ready.',
        'core-onboarding.review_storage': 'Review storage',
      }),
      es: Object.freeze({
        'core-onboarding.title': 'Terminar de configurar Zogograph',
        'core-onboarding.intro':
          'Zogograph está configurado, pero aún no tiene suficientes datos locales para mostrar el panel completo.',
        'core-onboarding.vitals_missing':
          'Configure o ejecute el recopilador de la cuenta para obtener su primera lectura vital.',
        'core-onboarding.open_schedule': 'Abrir programación de Setup',
        'core-onboarding.vitals_ready':
          'Las lecturas vitales están llegando. Mantenga programado el recopilador para que los gráficos acumulen historial.',
        'core-onboarding.review_schedule': 'Revisar programación de Setup',
        'core-onboarding.scan_missing':
          'Ejecute el primer análisis de almacenamiento para identificar qué ocupa espacio.',
        'core-onboarding.open_storage': 'Abrir almacenamiento',
        'core-onboarding.scan_ready':
          'El primer análisis de almacenamiento está completo. Revise los elementos más grandes y configure alertas cuando esté listo.',
        'core-onboarding.review_storage': 'Revisar almacenamiento',
      }),
      hi: Object.freeze({
        'core-onboarding.title': 'Zogograph सेट करना पूरा करें',
        'core-onboarding.intro':
          'Zogograph कॉन्फ़िगर है, लेकिन पूरा डैशबोर्ड दिखाने के लिए अभी पर्याप्त स्थानीय डेटा नहीं है।',
        'core-onboarding.vitals_missing':
          'पहली महत्वपूर्ण रीडिंग के लिए खाता कलेक्टर सेट करें या चलाएँ।',
        'core-onboarding.open_schedule': 'Setup शेड्यूल खोलें',
        'core-onboarding.vitals_ready':
          'महत्वपूर्ण रीडिंग आ रही हैं। चार्ट का इतिहास बनाने के लिए कलेक्टर को शेड्यूल रखें।',
        'core-onboarding.review_schedule': 'Setup शेड्यूल देखें',
        'core-onboarding.scan_missing':
          'कौन-सी चीज़ जगह ले रही है, यह पहचानने के लिए पहला स्टोरेज स्कैन चलाएँ।',
        'core-onboarding.open_storage': 'स्टोरेज खोलें',
        'core-onboarding.scan_ready':
          'पहला स्टोरेज स्कैन पूरा हो गया है। सबसे बड़े आइटम देखें और तैयार होने पर अलर्ट सेट करें।',
        'core-onboarding.review_storage': 'स्टोरेज देखें',
      }),
      ar: Object.freeze({
        'core-onboarding.title': 'أكمل إعداد Zogograph',
        'core-onboarding.intro':
          'تم إعداد Zogograph، لكنه لا يملك بعد بيانات محلية كافية لعرض لوحة المعلومات كاملة.',
        'core-onboarding.vitals_missing':
          'أعِد جامع الحساب أو شغّله للحصول على أول قراءة حيوية.',
        'core-onboarding.open_schedule': 'افتح جدولة الإعداد',
        'core-onboarding.vitals_ready':
          'تصل القراءات الحيوية الآن. أبقِ الجامع مجدولًا كي تبني المخططات سجلًا تاريخيًا.',
        'core-onboarding.review_schedule': 'راجع جدولة الإعداد',
        'core-onboarding.scan_missing':
          'شغّل أول فحص للتخزين لتحديد ما يستهلك المساحة.',
        'core-onboarding.open_storage': 'افتح التخزين',
        'core-onboarding.scan_ready':
          'اكتمل أول فحص للتخزين. راجع أكبر العناصر واضبط التنبيهات عندما تكون مستعدًا.',
        'core-onboarding.review_storage': 'راجع التخزين',
      }),
      zh: Object.freeze({
        'core-onboarding.title': '完成 Zogograph 设置',
        'core-onboarding.intro':
          'Zogograph 已配置完成，但尚无足够的本地数据来显示完整仪表板。',
        'core-onboarding.vitals_missing':
          '请设置或运行账户采集器，以获取第一项关键读数。',
        'core-onboarding.open_schedule': '打开 Setup 计划',
        'core-onboarding.vitals_ready':
          '关键读数正在到达。请保持采集器按计划运行，以便图表积累历史记录。',
        'core-onboarding.review_schedule': '查看 Setup 计划',
        'core-onboarding.scan_missing':
          '运行首次存储扫描，以确定哪些内容占用了空间。',
        'core-onboarding.open_storage': '打开存储',
        'core-onboarding.scan_ready':
          '首次存储扫描已完成。请查看最大的项目，并在准备好后设置警报。',
        'core-onboarding.review_storage': '查看存储',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-onboarding.${key}`,
      {},
      this.manifest.catalogs.en[`core-onboarding.${key}`],
    );
  },
  render(overview, helpers) {
    const { panel, body, i18n } = helpers;
    const t = (key) => this.text(key, { i18n });
    const missingVitals = !overview.vitals;
    const missingScan = !overview.scan;
    panel.hidden = !missingVitals && !missingScan;
    if (panel.hidden) return;
    const intro = document.createElement('p');
    intro.className = 'muted small';
    intro.textContent = t('intro');
    const list = document.createElement('ol');
    list.className = 'onboarding-list';
    const step = (text, href, label) => {
      const item = document.createElement('li');
      item.append(document.createTextNode(text));
      const link = document.createElement('a');
      link.href = href;
      link.textContent = label;
      item.appendChild(link);
      list.appendChild(item);
    };
    step(
      missingVitals
        ? t(
            'vitals_missing',
            'Set up or run the account collector for its first vital reading.',
          )
        : t(
            'vitals_ready',
            'Vital readings are arriving. Keep the collector scheduled so charts can build history.',
          ),
      './setup.php?section=schedule',
      missingVitals
        ? t('open_schedule', 'Open Setup schedule')
        : t('review_schedule', 'Review Setup schedule'),
    );
    step(
      missingScan
        ? t(
            'scan_missing',
            'Run the first storage scan to identify what uses space.',
          )
        : t(
            'scan_ready',
            'The first storage scan is complete. Review the largest items and set alerts when ready.',
          ),
      missingScan ? '#/storage' : '#/storage-files',
      missingScan
        ? t('open_storage', 'Open Storage')
        : t('review_storage', 'Review storage'),
    );
    body.replaceChildren(intro, list);
  },
};
