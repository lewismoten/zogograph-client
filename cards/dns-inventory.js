/* Core card package: DNS inventory status and record group labels. */
window.ZogographDnsInventoryCard = {
  manifest: Object.freeze({
    id: 'core-dns-inventory',
    locales: ['core-dns-inventory.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-dns-inventory.sign_in_records': 'Sign in to see DNS records.',
        'core-dns-inventory.no_domains_watched':
          "No domains are being watched yet. Add one on Domain names, then come back — a zone sweep is offered for domains you have said you care about rather than for any name, because it is a few dozen lookups against somebody's nameserver.",
        'core-dns-inventory.dig_unavailable':
          'dig is not available to this account, so zones cannot be looked up here. On Red Hat it is in bind-utils; on Debian, dnsutils.',
        'core-dns-inventory.no_records_sweep':
          'Nothing recorded for this domain yet. Press “Look it all up”.',
        'core-dns-inventory.no_records': 'Nothing recorded for this domain.',
        'core-dns-inventory.inventory_summary_last':
          '{{records, number}} records across {{names, number}} names, last looked up {{last}}.',
        'core-dns-inventory.inventory_summary':
          '{{records, number}} records across {{names, number}} names.',
        'core-dns-inventory.group_addresses': 'Where the names point',
        'core-dns-inventory.group_mail': 'Mail',
        'core-dns-inventory.group_delegation': 'Who is in charge of the zone',
        'core-dns-inventory.group_certificates': 'Who may issue certificates',
        'core-dns-inventory.group_proof': 'Proof of ownership',
        'core-dns-inventory.group_text': 'Other text records',
        'core-dns-inventory.group_other': 'Everything else',
      }),
      es: Object.freeze({
        'core-dns-inventory.sign_in_records':
          'Inicie sesión para ver los registros DNS.',
        'core-dns-inventory.no_domains_watched':
          'Todavía no se vigila ningún dominio. Añada uno en Nombres de dominio y vuelva después: se ofrece un barrido de zona para los dominios que ha indicado que le importan, no para cualquier nombre, porque supone unas docenas de consultas al servidor de nombres de otra persona.',
        'core-dns-inventory.dig_unavailable':
          'dig no está disponible para esta cuenta, por lo que aquí no se pueden consultar zonas. En Red Hat está en bind-utils; en Debian, en dnsutils.',
        'core-dns-inventory.no_records_sweep':
          'Aún no hay nada registrado para este dominio. Pulse «Buscar todo».',
        'core-dns-inventory.no_records':
          'No hay nada registrado para este dominio.',
        'core-dns-inventory.inventory_summary_last':
          '{{records, number}} registros en {{names, number}} nombres; última consulta: {{last}}.',
        'core-dns-inventory.inventory_summary':
          '{{records, number}} registros en {{names, number}} nombres.',
        'core-dns-inventory.group_addresses': 'A dónde apuntan los nombres',
        'core-dns-inventory.group_mail': 'Correo',
        'core-dns-inventory.group_delegation': 'Quién está a cargo de la zona',
        'core-dns-inventory.group_certificates':
          'Quién puede emitir certificados',
        'core-dns-inventory.group_proof': 'Prueba de propiedad',
        'core-dns-inventory.group_text': 'Otros registros de texto',
        'core-dns-inventory.group_other': 'Todo lo demás',
      }),
      hi: Object.freeze({
        'core-dns-inventory.sign_in_records':
          'DNS रिकॉर्ड देखने के लिए साइन इन करें।',
        'core-dns-inventory.no_domains_watched':
          'अभी तक कोई डोमेन देखा नहीं जा रहा है। डोमेन नाम में एक जोड़ें, फिर वापस आएँ — ज़ोन स्वीप केवल उन डोमेन के लिए दिया जाता है जिनकी आप परवाह बताते हैं, किसी भी नाम के लिए नहीं, क्योंकि यह किसी के नेमसर्वर पर कई दर्जन लुकअप करता है।',
        'core-dns-inventory.dig_unavailable':
          'dig इस खाते के लिए उपलब्ध नहीं है, इसलिए यहाँ ज़ोन नहीं देखे जा सकते। Red Hat पर यह bind-utils में है; Debian पर dnsutils में।',
        'core-dns-inventory.no_records_sweep':
          'इस डोमेन के लिए अभी कुछ भी दर्ज नहीं है। “सब देखो” दबाएँ।',
        'core-dns-inventory.no_records': 'इस डोमेन के लिए कुछ भी दर्ज नहीं है।',
        'core-dns-inventory.inventory_summary_last':
          '{{records, number}} रिकॉर्ड, {{names, number}} नामों में; अंतिम बार {{last}} देखा गया।',
        'core-dns-inventory.inventory_summary':
          '{{records, number}} रिकॉर्ड, {{names, number}} नामों में।',
        'core-dns-inventory.group_addresses': 'नाम कहाँ इंगित करते हैं',
        'core-dns-inventory.group_mail': 'मेल',
        'core-dns-inventory.group_delegation': 'ज़ोन का प्रभारी कौन है',
        'core-dns-inventory.group_certificates':
          'प्रमाणपत्र कौन जारी कर सकता है',
        'core-dns-inventory.group_proof': 'स्वामित्व का प्रमाण',
        'core-dns-inventory.group_text': 'अन्य टेक्स्ट रिकॉर्ड',
        'core-dns-inventory.group_other': 'अन्य सभी',
      }),
      ar: Object.freeze({
        'core-dns-inventory.sign_in_records': 'سجّل الدخول لعرض سجلات DNS.',
        'core-dns-inventory.no_domains_watched':
          'لا توجد نطاقات قيد المراقبة بعد. أضف واحدًا في أسماء النطاقات ثم عُد هنا — يُعرض مسح المنطقة للنطاقات التي قلت إنك تهتم بها بدلًا من أي اسم، لأنه يجري بضع عشرات من عمليات البحث على خادم أسماء شخص آخر.',
        'core-dns-inventory.dig_unavailable':
          'الأمر dig غير متاح لهذا الحساب، لذا لا يمكن البحث عن المناطق هنا. يوجد في Red Hat ضمن bind-utils، وفي Debian ضمن dnsutils.',
        'core-dns-inventory.no_records_sweep':
          'لم يُسجَّل شيء لهذا النطاق بعد. اضغط «ابحث عنها كلها».',
        'core-dns-inventory.no_records': 'لم يُسجَّل شيء لهذا النطاق.',
        'core-dns-inventory.inventory_summary_last':
          '{{records, number}} سجلًا عبر {{names, number}} اسمًا، وآخر بحث كان {{last}}.',
        'core-dns-inventory.inventory_summary':
          '{{records, number}} سجلًا عبر {{names, number}} اسمًا.',
        'core-dns-inventory.group_addresses': 'إلى أين تشير الأسماء',
        'core-dns-inventory.group_mail': 'البريد',
        'core-dns-inventory.group_delegation': 'من المسؤول عن المنطقة',
        'core-dns-inventory.group_certificates': 'من يمكنه إصدار الشهادات',
        'core-dns-inventory.group_proof': 'إثبات الملكية',
        'core-dns-inventory.group_text': 'سجلات نصية أخرى',
        'core-dns-inventory.group_other': 'كل شيء آخر',
      }),
      zh: Object.freeze({
        'core-dns-inventory.sign_in_records': '登录以查看 DNS 记录。',
        'core-dns-inventory.no_domains_watched':
          '尚未监测任何域名。请在“域名”中添加一个，然后返回——仅对您表示关心的域名提供区域扫描，而不是任意名称，因为这会对他人的名称服务器进行数十次查询。',
        'core-dns-inventory.dig_unavailable':
          '此账户无法使用 dig，因此无法在这里查询区域。在 Red Hat 上它位于 bind-utils；在 Debian 上位于 dnsutils。',
        'core-dns-inventory.no_records_sweep':
          '尚未记录此域名的任何内容。请按“全部查询”。',
        'core-dns-inventory.no_records': '尚未记录此域名的任何内容。',
        'core-dns-inventory.inventory_summary_last':
          '{{records, number}} 条记录，涉及 {{names, number}} 个名称；上次查询于 {{last}}。',
        'core-dns-inventory.inventory_summary':
          '{{records, number}} 条记录，涉及 {{names, number}} 个名称。',
        'core-dns-inventory.group_addresses': '名称指向的位置',
        'core-dns-inventory.group_mail': '邮件',
        'core-dns-inventory.group_delegation': '谁负责该区域',
        'core-dns-inventory.group_certificates': '谁可以签发证书',
        'core-dns-inventory.group_proof': '所有权证明',
        'core-dns-inventory.group_text': '其他文本记录',
        'core-dns-inventory.group_other': '其他所有内容',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-dns-inventory.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
