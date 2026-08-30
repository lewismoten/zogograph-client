/* Core card package: schema-derived inputs for trying an AI tool safely. */
window.ZogographAiToolParametersCard = {
  manifest: Object.freeze({
    id: 'core-ai-tool-parameters',
    locales: ['core-ai-tool-parameters.'],
    helpers: ['note', 'fieldRow', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-tool-parameters.none': 'This one takes no parameters.',
        'core-ai-tool-parameters.leave_out': '— leave it out —',
        'core-ai-tool-parameters.required': 'required',
        'core-ai-tool-parameters.optional': 'optional',
        'core-ai-tool-parameters.range': '{{minimum}} to {{maximum}}',
        'core-ai-tool-parameters.max_length': 'up to {{length}} characters',
        'core-ai-tool-parameters.default_why':
          'Review this chart for notable changes.',
      }),
      es: Object.freeze({
        'core-ai-tool-parameters.none':
          'Esta herramienta no recibe parámetros.',
        'core-ai-tool-parameters.leave_out': '— omitir —',
        'core-ai-tool-parameters.required': 'obligatorio',
        'core-ai-tool-parameters.optional': 'opcional',
        'core-ai-tool-parameters.range': '{{minimum}} a {{maximum}}',
        'core-ai-tool-parameters.max_length': 'hasta {{length}} caracteres',
        'core-ai-tool-parameters.default_why':
          'Revise este gráfico para ver cambios destacables.',
      }),
      hi: Object.freeze({
        'core-ai-tool-parameters.none': 'इस टूल के कोई पैरामीटर नहीं हैं।',
        'core-ai-tool-parameters.leave_out': '— छोड़ दें —',
        'core-ai-tool-parameters.required': 'आवश्यक',
        'core-ai-tool-parameters.optional': 'वैकल्पिक',
        'core-ai-tool-parameters.range': '{{minimum}} से {{maximum}}',
        'core-ai-tool-parameters.max_length': '{{length}} वर्ण तक',
        'core-ai-tool-parameters.default_why':
          'उल्लेखनीय बदलावों के लिए इस चार्ट की समीक्षा करें।',
      }),
      ar: Object.freeze({
        'core-ai-tool-parameters.none': 'لا تأخذ هذه الأداة أي معاملات.',
        'core-ai-tool-parameters.leave_out': '— اتركه —',
        'core-ai-tool-parameters.required': 'مطلوب',
        'core-ai-tool-parameters.optional': 'اختياري',
        'core-ai-tool-parameters.range': '{{minimum}} إلى {{maximum}}',
        'core-ai-tool-parameters.max_length': 'حتى {{length}} حرفًا',
        'core-ai-tool-parameters.default_why':
          'راجع هذا المخطط بحثًا عن تغييرات ملحوظة.',
      }),
      zh: Object.freeze({
        'core-ai-tool-parameters.none': '此工具不接受参数。',
        'core-ai-tool-parameters.leave_out': '— 不填写 —',
        'core-ai-tool-parameters.required': '必填',
        'core-ai-tool-parameters.optional': '可选',
        'core-ai-tool-parameters.range': '{{minimum}} 到 {{maximum}}',
        'core-ai-tool-parameters.max_length': '最多 {{length}} 个字符',
        'core-ai-tool-parameters.default_why': '查看此图表是否有明显变化。',
      }),
    }),
  }),
  render(input, helpers) {
    const { tool } = input;
    const { note, fieldRow, i18n } = helpers;
    const t = (key, vars, fallback) => i18n.t(key, vars, fallback);
    if (!tool || tool.why_not) return [];
    const entries = Object.entries(tool.parameters || {});
    if (!entries.length)
      return [
        note(
          t(
            'core-ai-tool-parameters.none',
            {},
            'This one takes no parameters.',
          ),
        ),
      ];
    const example = (spec) => {
      const fields = spec.items || {};
      if (!Object.keys(fields).length) return [];
      const row = {};
      for (const [name, about] of Object.entries(fields))
        row[name] = about.description
          ? String(about.description).split('.')[0].slice(0, 40)
          : '';
      return [row, { ...row }];
    };
    const textDefault = (name) => {
      if (name === 'why' || name === 'caption')
        return t(
          'core-ai-tool-parameters.default_why',
          {},
          'Review this chart for notable changes.',
        );
      if (name === 'view') return 'overview';
      if (name === 'path') return '.';
      return '';
    };
    return entries.map(([name, spec]) => {
      let control;
      if (spec.enum) {
        control = document.createElement('select');
        if (!spec.required) {
          const blank = document.createElement('option');
          blank.value = '';
          blank.textContent = t(
            'core-ai-tool-parameters.leave_out',
            {},
            '— leave it out —',
          );
          control.appendChild(blank);
        }
        for (const value of spec.enum.filter((value) => value !== null)) {
          const option = document.createElement('option');
          option.value = String(value);
          option.textContent = String(value);
          option.selected =
            spec.default !== null && String(spec.default) === String(value);
          control.appendChild(option);
        }
      } else if (spec.type === 'array') {
        control = document.createElement('textarea');
        control.rows = 10;
        control.className = 'wide-field try-textarea';
        control.spellcheck = false;
        control.value = JSON.stringify(example(spec), null, 2);
      } else if (spec.type === 'boolean') {
        control = document.createElement('input');
        control.type = 'checkbox';
        control.checked = spec.default === true;
      } else if (spec.type === 'integer' || spec.type === 'number') {
        control = document.createElement('input');
        control.type = 'number';
        control.className = 'days';
        if (spec.minimum !== null) control.min = String(spec.minimum);
        if (spec.maximum !== null) control.max = String(spec.maximum);
        if (spec.type === 'integer') control.step = '1';
        control.value = String(
          spec.default ?? (name === 'hours' ? 24 : (spec.minimum ?? 1)),
        );
      } else {
        control = document.createElement('input');
        control.type = 'text';
        control.className = 'wide-field';
        if (spec.maxLength) control.maxLength = spec.maxLength;
        control.value = String(spec.default ?? textDefault(name));
      }
      control.dataset.param = name;
      control.dataset.kind = spec.type;
      control.dataset.required = spec.required ? '1' : '';
      const bounds = [
        t(
          spec.required
            ? 'core-ai-tool-parameters.required'
            : 'core-ai-tool-parameters.optional',
          {},
          spec.required ? 'required' : 'optional',
        ),
        spec.minimum !== null && spec.maximum !== null
          ? t(
              'core-ai-tool-parameters.range',
              { minimum: spec.minimum, maximum: spec.maximum },
              '{{minimum}} to {{maximum}}',
            )
          : '',
        spec.maxLength
          ? t(
              'core-ai-tool-parameters.max_length',
              { length: spec.maxLength },
              'up to {{length}} characters',
            )
          : '',
      ]
        .filter(Boolean)
        .join(', ');
      return fieldRow(name, control, `${spec.description} (${bounds})`);
    });
  },
};
