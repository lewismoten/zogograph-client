/* Core card package: observed system software and watched program versions. */
window.ZogographInstalledSoftwareCard = {
  manifest: Object.freeze({
    id: 'core-installed-software',
    locales: ['core-installed-software.'],
    helpers: [
      'target',
      'note',
      'section',
      'simpleTable',
      'humanSpan',
      'ago',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-installed-software.title': 'What is installed now',
        'core-installed-software.last_checked': 'Last checked {{ago}}.',
        'core-installed-software.system': 'System software',
        'core-installed-software.what': 'What',
        'core-installed-software.is': 'Is',
        'core-installed-software.unchanged_for': 'Unchanged for',
        'core-installed-software.why': 'Why it matters',
        'core-installed-software.empty': 'Nothing has been recorded yet.',
        'core-installed-software.watched': 'Watched programs',
        'core-installed-software.program': 'Program',
        'core-installed-software.version': 'Version',
        'core-installed-software.where': 'Where',
        'core-installed-software.fingerprint':
          'file fingerprint {{fingerprint}}',
        'core-installed-software.fingerprint_note':
          'A file fingerprint changes when its contents change, even when the program cannot report a version.',
      }),
      es: Object.freeze({
        'core-installed-software.title': 'Qué está instalado ahora',
        'core-installed-software.last_checked': 'Última comprobación {{ago}}.',
        'core-installed-software.system': 'Software del sistema',
        'core-installed-software.what': 'Qué',
        'core-installed-software.is': 'Es',
        'core-installed-software.unchanged_for': 'Sin cambios durante',
        'core-installed-software.why': 'Por qué importa',
        'core-installed-software.empty': 'Aún no se ha registrado nada.',
        'core-installed-software.watched': 'Programas vigilados',
        'core-installed-software.program': 'Programa',
        'core-installed-software.version': 'Versión',
        'core-installed-software.where': 'Ubicación',
        'core-installed-software.fingerprint':
          'huella del archivo {{fingerprint}}',
        'core-installed-software.fingerprint_note':
          'La huella de un archivo cambia cuando cambia su contenido, incluso si el programa no puede indicar una versión.',
      }),
      hi: Object.freeze({
        'core-installed-software.title': 'अभी क्या इंस्टॉल है',
        'core-installed-software.last_checked': 'आखिरी जाँच {{ago}}।',
        'core-installed-software.system': 'सिस्टम सॉफ़्टवेयर',
        'core-installed-software.what': 'क्या',
        'core-installed-software.is': 'है',
        'core-installed-software.unchanged_for': 'इतने समय से अपरिवर्तित',
        'core-installed-software.why': 'यह क्यों महत्वपूर्ण है',
        'core-installed-software.empty': 'अभी कुछ रिकॉर्ड नहीं किया गया है।',
        'core-installed-software.watched': 'निगरानी वाले प्रोग्राम',
        'core-installed-software.program': 'प्रोग्राम',
        'core-installed-software.version': 'संस्करण',
        'core-installed-software.where': 'स्थान',
        'core-installed-software.fingerprint':
          'फ़ाइल फिंगरप्रिंट {{fingerprint}}',
        'core-installed-software.fingerprint_note':
          'फ़ाइल की सामग्री बदलने पर उसका फिंगरप्रिंट बदलता है, भले प्रोग्राम संस्करण न बता सके।',
      }),
      ar: Object.freeze({
        'core-installed-software.title': 'ما المثبت الآن',
        'core-installed-software.last_checked': 'آخر فحص {{ago}}.',
        'core-installed-software.system': 'برامج النظام',
        'core-installed-software.what': 'ما هو',
        'core-installed-software.is': 'هو',
        'core-installed-software.unchanged_for': 'لم يتغير منذ',
        'core-installed-software.why': 'لماذا يهم',
        'core-installed-software.empty': 'لم يُسجل شيء بعد.',
        'core-installed-software.watched': 'البرامج المراقبة',
        'core-installed-software.program': 'البرنامج',
        'core-installed-software.version': 'الإصدار',
        'core-installed-software.where': 'المكان',
        'core-installed-software.fingerprint': 'بصمة الملف {{fingerprint}}',
        'core-installed-software.fingerprint_note':
          'تتغير بصمة الملف عندما يتغير محتواه، حتى إن لم يتمكن البرنامج من الإبلاغ عن إصدار.',
      }),
      zh: Object.freeze({
        'core-installed-software.title': '当前安装了什么',
        'core-installed-software.last_checked': '上次检查 {{ago}}。',
        'core-installed-software.system': '系统软件',
        'core-installed-software.what': '项目',
        'core-installed-software.is': '当前值',
        'core-installed-software.unchanged_for': '保持不变时长',
        'core-installed-software.why': '重要原因',
        'core-installed-software.empty': '尚未记录任何内容。',
        'core-installed-software.watched': '受监控的程序',
        'core-installed-software.program': '程序',
        'core-installed-software.version': '版本',
        'core-installed-software.where': '位置',
        'core-installed-software.fingerprint': '文件指纹 {{fingerprint}}',
        'core-installed-software.fingerprint_note':
          '文件内容变化时其指纹会变化，即使程序无法报告版本也一样。',
      }),
    }),
  }),

  text(key, helpers) {
    return helpers.i18n.t(
      `core-installed-software.${key}`,
      {},
      this.manifest.catalogs.en[`core-installed-software.${key}`],
    );
  },

  render(data, helpers) {
    const { target, note, section, simpleTable, humanSpan, ago, i18n } =
      helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (!target) return;
    const parts = [];
    if (data.last_checked)
      parts.push(
        note(
          t(
            'core-installed-software.last_checked',
            { ago: ago(data.last_checked) },
            'Last checked {{ago}}.',
          ),
        ),
      );
    if (data.now?.length) {
      parts.push(
        section(
          t('core-installed-software.system', {}, 'System software'),
          simpleTable(
            [
              t('core-installed-software.what', {}, 'What'),
              t('core-installed-software.is', {}, 'Is'),
              t('core-installed-software.unchanged_for', {}, 'Unchanged for'),
              t('core-installed-software.why', {}, 'Why it matters'),
            ],
            data.now.map((row) => [
              row.label,
              row.value,
              humanSpan(row.age),
              row.note || '',
            ]),
            ['name', 'cmd', null, 'muted'],
          ),
        ),
      );
    } else
      parts.push(
        note(
          t(
            'core-installed-software.empty',
            {},
            'Nothing has been recorded yet.',
          ),
        ),
      );
    const watched = (data.capabilities || []).filter(
      (row) => row.version || row.fingerprint,
    );
    if (watched.length) {
      parts.push(
        section(
          t('core-installed-software.watched', {}, 'Watched programs'),
          simpleTable(
            [
              t('core-installed-software.program', {}, 'Program'),
              t('core-installed-software.version', {}, 'Version'),
              t('core-installed-software.where', {}, 'Where'),
            ],
            watched.map((row) => [
              row.name,
              row.version ||
                (row.fingerprint
                  ? t(
                      'core-installed-software.fingerprint',
                      { fingerprint: row.fingerprint },
                      'file fingerprint {{fingerprint}}',
                    )
                  : '—'),
              row.path,
            ]),
            ['name', 'cmd', 'cmd'],
          ),
        ),
      );
      parts.push(
        note(
          t(
            'core-installed-software.fingerprint_note',
            {},
            'A file fingerprint changes when its contents change, even when the program cannot report a version.',
          ),
        ),
      );
    }
    target.replaceChildren(...parts);
  },
};
