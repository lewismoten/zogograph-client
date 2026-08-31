/* Core card package: explains an unavailable command and its safe next step. */
window.ZogographMissingCommandNoteCard = {
  manifest: Object.freeze({
    id: 'core-missing-command-note',
    locales: ['core-missing-command-note.'],
    helpers: ['note', 'paste', 'showCapabilities', 'checkNow', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-missing-command-note.this_account': 'this account',
        'core-missing-command-note.command_unavailable':
          '{{command}} is not available to {{where}}. As root:',
        'core-missing-command-note.recheck_after_install':
          'After installing it, recheck the capability. ',
        'core-missing-command-note.probe_not_automatic':
          ' The dashboard does not probe automatically, because running a hundred command lookups on every page load would cost more than it saves.',
      }),
      es: Object.freeze({
        'core-missing-command-note.this_account': 'esta cuenta',
        'core-missing-command-note.command_unavailable':
          '{{command}} no está disponible para {{where}}. Como root:',
        'core-missing-command-note.recheck_after_install':
          'Después de instalarlo, vuelva a comprobar la capacidad. ',
        'core-missing-command-note.probe_not_automatic':
          ' El panel no realiza comprobaciones automáticamente, porque ejecutar cien búsquedas de comandos en cada carga de página costaría más de lo que ahorra.',
      }),
      hi: Object.freeze({
        'core-missing-command-note.this_account': 'इस खाते',
        'core-missing-command-note.command_unavailable':
          '{{command}}, {{where}} के लिए उपलब्ध नहीं है। root के रूप में:',
        'core-missing-command-note.recheck_after_install':
          'इसे स्थापित करने के बाद क्षमता को फिर से जाँचें। ',
        'core-missing-command-note.probe_not_automatic':
          ' डैशबोर्ड स्वचालित रूप से जाँच नहीं करता, क्योंकि प्रत्येक पृष्ठ लोड पर सौ कमांड लुकअप चलाने में बचत से अधिक लागत आएगी।',
      }),
      ar: Object.freeze({
        'core-missing-command-note.this_account': 'هذا الحساب',
        'core-missing-command-note.command_unavailable':
          'الأمر {{command}} غير متاح لـ {{where}}. بصفتك root:',
        'core-missing-command-note.recheck_after_install':
          'بعد تثبيته، أعد فحص الإمكانية. ',
        'core-missing-command-note.probe_not_automatic':
          ' لا تتحقق لوحة المعلومات تلقائيًا، لأن إجراء مئة عملية بحث عن أوامر عند كل تحميل للصفحة سيكلف أكثر مما يوفر.',
      }),
      zh: Object.freeze({
        'core-missing-command-note.this_account': '此账户',
        'core-missing-command-note.command_unavailable':
          '{{command}} 不可供{{where}}使用。请以 root 身份执行：',
        'core-missing-command-note.recheck_after_install':
          '安装后，请重新检查该功能。 ',
        'core-missing-command-note.probe_not_automatic':
          ' 仪表板不会自动探测，因为每次加载页面时运行一百次命令查找的成本高于其节省的成本。',
      }),
    }),
  }),
  render(data, helpers) {
    const { missing, where } = data || {};
    const { note, paste, showCapabilities, checkNow, i18n } = helpers;
    if (!missing) return document.createDocumentFragment();
    const t = (key, values, fallback) =>
      i18n.t(`core-missing-command-note.${key}`, values, fallback);
    const wrap = document.createDocumentFragment();
    wrap.appendChild(
      note(
        t(
          'command_unavailable',
          {
            command: missing.command,
            where: where || t('this_account', {}, 'this account'),
          },
          '{{command}} is not available to {{where}}. As root:',
        ),
      ),
    );
    wrap.appendChild(paste(missing.install));
    if (missing.note) wrap.appendChild(note(missing.note));
    const after = document.createElement('p');
    after.className = 'muted small';
    after.append(
      document.createTextNode(
        t(
          'recheck_after_install',
          {},
          'After installing it, recheck the capability. ',
        ),
      ),
    );
    const go = document.createElement('button');
    go.className = 'link';
    go.textContent = checkNow;
    go.addEventListener('click', showCapabilities);
    after.appendChild(go);
    after.append(
      document.createTextNode(
        t(
          'probe_not_automatic',
          {},
          ' The dashboard does not probe automatically, because running a hundred command lookups on every page load would cost more than it saves.',
        ),
      ),
    );
    wrap.appendChild(after);
    return wrap;
  },
};
