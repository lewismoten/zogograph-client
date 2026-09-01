/* Core card package: path names that are surprising or unsafe at a shell. */
window.ZogographPathHygieneCard = {
  manifest: Object.freeze({
    id: 'core-path-hygiene',
    locales: ['core-path-hygiene.'],
    helpers: [
      'target',
      'note',
      'factGrid',
      'renderTable',
      'humanTime',
      'visiblePathNode',
      'openRename',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-path-hygiene.title': 'Path-name hygiene',
        'core-path-hygiene.sign_in': 'Sign in to review path names.',
        'core-path-hygiene.new_name': 'New name',
        'core-path-hygiene.current_name': 'Current: {{name}}',
        'core-path-hygiene.rename_path': 'Rename path',
        'core-path-hygiene.renamed_to': 'Renamed to {{path}}.',
        'core-path-hygiene.help':
          'An optional, advisory check that runs during the account disk walk. It does not send notifications. Rename only paths you recognize; the change is immediate and recorded in the audit log.',
        'core-path-hygiene.enable':
          'Install and enable Path-name hygiene in the Marketplace to use this check.',
        'core-path-hygiene.no_scan':
          'Enabled, but no completed account disk scan has recorded path names yet.',
        'core-path-hygiene.last_scan': 'Last scan',
        'core-path-hygiene.found': 'Found',
        'core-path-hygiene.stored': 'Stored',
        'core-path-hygiene.none':
          'No shell-confusing path names were found in the latest scan.',
        'core-path-hygiene.exact_path': 'Exact path',
        'core-path-hygiene.why': 'Why',
        'core-path-hygiene.rename': 'Rename',
        'core-path-hygiene.truncated':
          'The scan found more names than this page retains. Rename these, then run another disk scan.',
        'core-path-hygiene.invalid_name':
          'Enter one file or directory name, without a slash.',
        'core-path-hygiene.control_characters_not_allowed':
          'Control characters are not allowed.',
        'core-path-hygiene.whitespace_edge_not_allowed':
          'Names cannot start or end with whitespace.',
        'core-path-hygiene.dash_not_allowed': 'Names cannot start with a dash.',
        'core-path-hygiene.shell_wildcards_not_allowed':
          'Names cannot contain shell or wildcard characters.',
        'core-path-hygiene.safe_to_rename': 'This name is safe to rename.',
      }),
      es: Object.freeze({
        'core-path-hygiene.title': 'Higiene de nombres de ruta',
        'core-path-hygiene.sign_in':
          'Inicie sesión para revisar los nombres de ruta.',
        'core-path-hygiene.new_name': 'Nuevo nombre',
        'core-path-hygiene.current_name': 'Actual: {{name}}',
        'core-path-hygiene.rename_path': 'Cambiar nombre de ruta',
        'core-path-hygiene.renamed_to': 'Se cambió el nombre a {{path}}.',
        'core-path-hygiene.help':
          'Una comprobación opcional y orientativa que se ejecuta durante el recorrido de disco de la cuenta. No envía notificaciones. Cambie solo rutas que reconozca; el cambio es inmediato y queda registrado en la auditoría.',
        'core-path-hygiene.enable':
          'Instale y active Higiene de nombres de ruta en Marketplace para usar esta comprobación.',
        'core-path-hygiene.no_scan':
          'Está activado, pero ningún análisis de disco completado ha registrado nombres de ruta aún.',
        'core-path-hygiene.last_scan': 'Último análisis',
        'core-path-hygiene.found': 'Encontrados',
        'core-path-hygiene.stored': 'Guardados',
        'core-path-hygiene.none':
          'El último análisis no encontró nombres de ruta confusos para la shell.',
        'core-path-hygiene.exact_path': 'Ruta exacta',
        'core-path-hygiene.why': 'Por qué',
        'core-path-hygiene.rename': 'Renombrar',
        'core-path-hygiene.truncated':
          'El análisis encontró más nombres de los que esta página conserva. Renombre estos y ejecute otro análisis de disco.',
        'core-path-hygiene.invalid_name':
          'Introduzca un nombre de archivo o directorio, sin barra.',
        'core-path-hygiene.control_characters_not_allowed':
          'No se permiten caracteres de control.',
        'core-path-hygiene.whitespace_edge_not_allowed':
          'Los nombres no pueden comenzar ni terminar con espacios en blanco.',
        'core-path-hygiene.dash_not_allowed':
          'Los nombres no pueden comenzar con un guion.',
        'core-path-hygiene.shell_wildcards_not_allowed':
          'Los nombres no pueden contener caracteres de shell o comodines.',
        'core-path-hygiene.safe_to_rename':
          'Este nombre es seguro para cambiarlo.',
      }),
      hi: Object.freeze({
        'core-path-hygiene.title': 'पथ-नाम स्वच्छता',
        'core-path-hygiene.sign_in':
          'पथ नामों की समीक्षा करने के लिए साइन इन करें।',
        'core-path-hygiene.new_name': 'नया नाम',
        'core-path-hygiene.current_name': 'वर्तमान: {{name}}',
        'core-path-hygiene.rename_path': 'पथ का नाम बदलें',
        'core-path-hygiene.renamed_to': 'नाम बदलकर {{path}} कर दिया गया।',
        'core-path-hygiene.help':
          'खाते के डिस्क वॉक के दौरान चलने वाली वैकल्पिक, सलाहकारी जाँच। यह सूचना नहीं भेजती। केवल पहचाने गए पथों का नाम बदलें; बदलाव तुरंत होता है और ऑडिट लॉग में दर्ज होता है।',
        'core-path-hygiene.enable':
          'इस जाँच के लिए मार्केटप्लेस में Path-name hygiene इंस्टॉल और सक्षम करें।',
        'core-path-hygiene.no_scan':
          'सक्षम है, लेकिन किसी पूर्ण डिस्क स्कैन ने अभी पथ नाम रिकॉर्ड नहीं किए हैं।',
        'core-path-hygiene.last_scan': 'पिछला स्कैन',
        'core-path-hygiene.found': 'मिले',
        'core-path-hygiene.stored': 'संग्रहीत',
        'core-path-hygiene.none':
          'नवीनतम स्कैन में शेल को भ्रमित करने वाले पथ नाम नहीं मिले।',
        'core-path-hygiene.exact_path': 'सटीक पथ',
        'core-path-hygiene.why': 'क्यों',
        'core-path-hygiene.rename': 'नाम बदलें',
        'core-path-hygiene.truncated':
          'स्कैन को इस पृष्ठ से अधिक नाम मिले। इन्हें बदलें, फिर दूसरा डिस्क स्कैन चलाएँ।',
        'core-path-hygiene.invalid_name':
          'बिना स्लैश के एक फ़ाइल या निर्देशिका नाम दर्ज करें।',
        'core-path-hygiene.control_characters_not_allowed':
          'कंट्रोल वर्णों की अनुमति नहीं है।',
        'core-path-hygiene.whitespace_edge_not_allowed':
          'नाम whitespace से शुरू या समाप्त नहीं हो सकते।',
        'core-path-hygiene.dash_not_allowed': 'नाम डैश से शुरू नहीं हो सकते।',
        'core-path-hygiene.shell_wildcards_not_allowed':
          'नाम में shell या wildcard वर्ण नहीं हो सकते।',
        'core-path-hygiene.safe_to_rename': 'इस नाम का नाम बदलना सुरक्षित है।',
      }),
      ar: Object.freeze({
        'core-path-hygiene.title': 'سلامة أسماء المسارات',
        'core-path-hygiene.sign_in': 'سجّل الدخول لمراجعة أسماء المسارات.',
        'core-path-hygiene.new_name': 'الاسم الجديد',
        'core-path-hygiene.current_name': 'الحالي: {{name}}',
        'core-path-hygiene.rename_path': 'إعادة تسمية المسار',
        'core-path-hygiene.renamed_to': 'أُعيدت التسمية إلى {{path}}.',
        'core-path-hygiene.help':
          'فحص اختياري إرشادي يعمل أثناء مسح قرص الحساب. لا يرسل إشعارات. أعد تسمية المسارات التي تعرفها فقط؛ التغيير فوري ويُسجل في سجل التدقيق.',
        'core-path-hygiene.enable':
          'ثبّت وفعّل فحص نظافة أسماء المسارات من المتجر لاستخدام هذا الفحص.',
        'core-path-hygiene.no_scan':
          'الفحص مفعّل، لكن لم يسجل أي فحص قرص مكتمل أسماء المسارات بعد.',
        'core-path-hygiene.last_scan': 'آخر فحص',
        'core-path-hygiene.found': 'تم العثور على',
        'core-path-hygiene.stored': 'المحفوظة',
        'core-path-hygiene.none':
          'لم يعثر آخر فحص على أسماء مسارات مربكة للصدفة.',
        'core-path-hygiene.exact_path': 'المسار الدقيق',
        'core-path-hygiene.why': 'السبب',
        'core-path-hygiene.rename': 'إعادة تسمية',
        'core-path-hygiene.truncated':
          'وجد الفحص أسماء أكثر مما تحتفظ به هذه الصفحة. أعد تسمية هذه ثم نفذ فحص قرص آخر.',
        'core-path-hygiene.invalid_name':
          'أدخل اسم ملف أو دليل واحدًا، بلا شرطة مائلة.',
        'core-path-hygiene.control_characters_not_allowed':
          'أحرف التحكم غير مسموحة.',
        'core-path-hygiene.whitespace_edge_not_allowed':
          'لا يمكن أن تبدأ الأسماء بمسافة بيضاء أو تنتهي بها.',
        'core-path-hygiene.dash_not_allowed': 'لا يمكن أن تبدأ الأسماء بشرطة.',
        'core-path-hygiene.shell_wildcards_not_allowed':
          'لا يمكن أن تحتوي الأسماء على أحرف shell أو wildcards.',
        'core-path-hygiene.safe_to_rename': 'هذه التسمية آمنة لإعادة التسمية.',
      }),
      zh: Object.freeze({
        'core-path-hygiene.title': '路径名称检查',
        'core-path-hygiene.sign_in': '登录后可检查路径名称。',
        'core-path-hygiene.new_name': '新名称',
        'core-path-hygiene.current_name': '当前：{{name}}',
        'core-path-hygiene.rename_path': '重命名路径',
        'core-path-hygiene.renamed_to': '已重命名为 {{path}}。',
        'core-path-hygiene.help':
          '账户磁盘遍历期间运行的可选建议性检查。它不会发送通知。只重命名您认识的路径；更改会立即生效并记录在审计日志中。',
        'core-path-hygiene.enable':
          '请在市场中安装并启用“路径名卫生”以使用此检查。',
        'core-path-hygiene.no_scan':
          '已启用，但尚未有已完成的帐户磁盘扫描记录路径名。',
        'core-path-hygiene.last_scan': '上次扫描',
        'core-path-hygiene.found': '发现',
        'core-path-hygiene.stored': '已保存',
        'core-path-hygiene.none': '最新扫描未发现容易让 shell 混淆的路径名。',
        'core-path-hygiene.exact_path': '精确路径',
        'core-path-hygiene.why': '原因',
        'core-path-hygiene.rename': '重命名',
        'core-path-hygiene.truncated':
          '扫描发现的名称多于此页面保留的数量。请重命名这些名称，然后再次运行磁盘扫描。',
        'core-path-hygiene.invalid_name':
          '请输入一个文件或目录名称，不要带斜杠。',
        'core-path-hygiene.control_characters_not_allowed': '不允许控制字符。',
        'core-path-hygiene.whitespace_edge_not_allowed':
          '名称不能以空白字符开头或结尾。',
        'core-path-hygiene.dash_not_allowed': '名称不能以短横线开头。',
        'core-path-hygiene.shell_wildcards_not_allowed':
          '名称不能包含 shell 或通配符字符。',
        'core-path-hygiene.safe_to_rename': '此名称可以安全重命名。',
      }),
    }),
  }),
  text(key, valuesOrHelpers = {}, maybeHelpers = null) {
    const helpers = maybeHelpers || valuesOrHelpers;
    const values = maybeHelpers ? valuesOrHelpers : {};
    return helpers.i18n.t(
      `core-path-hygiene.${key}`,
      values,
      this.manifest.catalogs.en[`core-path-hygiene.${key}`],
    );
  },

  render(data, helpers) {
    const {
      target,
      note,
      factGrid,
      renderTable,
      humanTime,
      visiblePathNode,
      openRename,
      i18n,
    } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (!target) return;
    if (!data.enabled) {
      target.replaceChildren(
        note(
          t(
            'core-path-hygiene.enable',
            {},
            'Install and enable Path-name hygiene in the Marketplace to use this check.',
          ),
        ),
      );
      return;
    }
    const last = data.last;
    if (!last) {
      target.replaceChildren(
        note(
          t(
            'core-path-hygiene.no_scan',
            {},
            'Enabled, but no completed account disk scan has recorded path names yet.',
          ),
        ),
      );
      return;
    }
    const parts = [
      factGrid([
        [
          t('core-path-hygiene.last_scan', {}, 'Last scan'),
          humanTime(last.checked_at),
        ],
        [t('core-path-hygiene.found', {}, 'Found'), String(last.total)],
        [
          t('core-path-hygiene.stored', {}, 'Stored'),
          String(last.findings.length),
        ],
      ]),
    ];
    if (!last.findings.length)
      parts.push(
        note(
          t(
            'core-path-hygiene.none',
            {},
            'No shell-confusing path names were found in the latest scan.',
          ),
        ),
      );
    else {
      const holder = document.createElement('div');
      holder.id = 'pathHygieneTable';
      parts.push(holder);
    }
    target.replaceChildren(...parts);
    if (last.findings.length) {
      renderTable('pathHygieneTable', last.findings, [
        {
          label: t('core-path-hygiene.exact_path', {}, 'Exact path'),
          className: 'name',
          render: (row) => {
            const code = document.createElement('code');
            code.append(visiblePathNode(row.path));
            code.title = row.path;
            return code;
          },
        },
        {
          label: t('core-path-hygiene.why', {}, 'Why'),
          render: (row) => row.reason,
        },
        {
          label: '',
          className: 'row-actions',
          render: (row) => {
            const wrap = document.createDocumentFragment();
            if (!data.can_change) return wrap;
            const rename = document.createElement('button');
            rename.className = 'link';
            rename.textContent = t('core-path-hygiene.rename', {}, 'Rename');
            rename.addEventListener('click', () => openRename(row));
            wrap.appendChild(rename);
            return wrap;
          },
        },
      ]);
      if (last.truncated)
        target.appendChild(
          note(
            t(
              'core-path-hygiene.truncated',
              {},
              'The scan found more names than this page retains. Rename these, then run another disk scan.',
            ),
          ),
        );
    }
  },
};
