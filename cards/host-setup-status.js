/* Core card package: host collector setup status and account-side guidance. */
window.ZogographHostSetupStatusCard = {
  manifest: Object.freeze({
    id: 'core-host-setup-status',
    locales: ['core-host-setup-status.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-host-setup-status.database_readable':
          "Working. The collector's database is at {{database}} and this account can read it.",
        'core-host-setup-status.database_unreadable':
          'The database exists at {{database}} but this account cannot read it — which is the ownership step below.',
        'core-host-setup-status.collector_never_run':
          'The collector has never run. Nothing on this site that describes the whole machine will have anything in it until it does.',
        'core-host-setup-status.what_has_to_happen': 'What has to happen',
        'core-host-setup-status.line_to_paste_as_root':
          'The line to paste, as root',
        'core-host-setup-status.no_root_help':
          'No root? The collector runs as an ordinary account too. It collects less, and here is exactly what less means.',
        'core-host-setup-status.only_as_root': 'Only as root',
        'core-host-setup-status.also_as_account': 'Also as your own account',
        'core-host-setup-status.no_host_account':
          'If you do not have a host account',
      }),
      es: Object.freeze({
        'core-host-setup-status.database_readable':
          'Funciona. La base de datos del recopilador está en {{database}} y esta cuenta puede leerla.',
        'core-host-setup-status.database_unreadable':
          'La base de datos existe en {{database}}, pero esta cuenta no puede leerla; ese es el paso de propiedad que aparece abajo.',
        'core-host-setup-status.collector_never_run':
          'El recopilador nunca se ha ejecutado. Nada de este sitio que describa toda la máquina tendrá información hasta que lo haga.',
        'core-host-setup-status.what_has_to_happen': 'Lo que tiene que suceder',
        'core-host-setup-status.line_to_paste_as_root':
          'La línea que se debe pegar como root',
        'core-host-setup-status.no_root_help':
          '¿Sin root? El recopilador también se ejecuta como una cuenta normal. Recopila menos, y esto explica exactamente qué significa menos.',
        'core-host-setup-status.only_as_root': 'Solo como root',
        'core-host-setup-status.also_as_account':
          'También como tu propia cuenta',
        'core-host-setup-status.no_host_account':
          'Si no tienes una cuenta de host',
      }),
      hi: Object.freeze({
        'core-host-setup-status.database_readable':
          'काम कर रहा है। कलेक्टर का डेटाबेस {{database}} पर है और यह खाता इसे पढ़ सकता है।',
        'core-host-setup-status.database_unreadable':
          'डेटाबेस {{database}} पर मौजूद है लेकिन यह खाता इसे नहीं पढ़ सकता — यही नीचे दिया गया स्वामित्व चरण है।',
        'core-host-setup-status.collector_never_run':
          'कलेक्टर कभी नहीं चला है। इस साइट पर पूरे मशीन का वर्णन करने वाली किसी भी चीज़ में इसके चलने तक कुछ नहीं होगा।',
        'core-host-setup-status.what_has_to_happen': 'क्या होना है',
        'core-host-setup-status.line_to_paste_as_root':
          'root के रूप में पेस्ट करने वाली लाइन',
        'core-host-setup-status.no_root_help':
          'root नहीं है? कलेक्टर एक सामान्य खाते के रूप में भी चलता है। यह कम एकत्र करता है, और यहाँ ठीक यही बताया गया है कि कम का क्या अर्थ है।',
        'core-host-setup-status.only_as_root': 'केवल root के रूप में',
        'core-host-setup-status.also_as_account': 'अपने खाते के रूप में भी',
        'core-host-setup-status.no_host_account':
          'यदि आपके पास होस्ट खाता नहीं है',
      }),
      ar: Object.freeze({
        'core-host-setup-status.database_readable':
          'يعمل. قاعدة بيانات الجامع في {{database}} ويمكن لهذا الحساب قراءتها.',
        'core-host-setup-status.database_unreadable':
          'قاعدة البيانات موجودة في {{database}} لكن هذا الحساب لا يستطيع قراءتها — وهذه هي خطوة الملكية أدناه.',
        'core-host-setup-status.collector_never_run':
          'لم يعمل الجامع قط. لن يحتوي أي شيء في هذا الموقع يصف الجهاز كله على بيانات حتى يعمل.',
        'core-host-setup-status.what_has_to_happen': 'ما الذي يجب أن يحدث',
        'core-host-setup-status.line_to_paste_as_root':
          'السطر المراد لصقه بصفتك root',
        'core-host-setup-status.no_root_help':
          'لا تملك root؟ يعمل الجامع كحساب عادي أيضًا. يجمع أقل، وهنا ما يعنيه ذلك بالضبط.',
        'core-host-setup-status.only_as_root': 'بصفتك root فقط',
        'core-host-setup-status.also_as_account': 'وأيضًا بصفتك حسابك الخاص',
        'core-host-setup-status.no_host_account': 'إذا لم يكن لديك حساب مضيف',
      }),
      zh: Object.freeze({
        'core-host-setup-status.database_readable':
          '正在工作。采集器的数据库位于 {{database}}，此账户可以读取它。',
        'core-host-setup-status.database_unreadable':
          '数据库位于 {{database}}，但此账户无法读取它——这正是下面的所有权步骤。',
        'core-host-setup-status.collector_never_run':
          '采集器从未运行过。在它运行之前，此网站上描述整台机器的内容不会有任何数据。',
        'core-host-setup-status.what_has_to_happen': '必须发生什么',
        'core-host-setup-status.line_to_paste_as_root': '以 root 身份粘贴的行',
        'core-host-setup-status.no_root_help':
          '没有 root 权限？采集器也可以作为普通账户运行。它采集得更少，这里准确说明了“更少”的含义。',
        'core-host-setup-status.only_as_root': '仅以 root 身份',
        'core-host-setup-status.also_as_account': '也以你自己的账户身份',
        'core-host-setup-status.no_host_account': '如果你没有主机账户',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-host-setup-status.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
