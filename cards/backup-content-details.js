/* Core card package: backup data-quality and directory-detail messages. */
window.ZogographBackupContentDetailsCard = {
  manifest: Object.freeze({
    id: 'core-backup-content-details',
    locales: ['core-backup-content-details.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-backup-content-details.reading_last_walk':
          'Reading the last walk…',
        'core-backup-content-details.unattributed_help':
          "Some of this could not be tied to an account. Nothing under the backup root carried a name matching a directory in /home, and JetBackup's own records were not readable — so it is reported as unattributed rather than assigned to the only account on the machine, which would be a guess.",
        'core-backup-content-details.inferred_accounts_help':
          "Accounts inferred from directory names under the backup root — JetBackup's own metadata was not readable, so these are a reasonable guess rather than its records.",
        'core-backup-content-details.unmeasured_entries_help':
          '{{count}} entries here could not be measured before the walk ran out of time. They are listed with no size rather than omitted. Raise host.walk_seconds and run it again for a complete figure.',
        'core-backup-content-details.at_least': 'at least {{size}}',
        'core-backup-content-details.inside_path': 'Inside {{path}}',
        'core-backup-content-details.other_help':
          'Directories that look like backups but are not managed by anything: a tar.gz somebody made before an upgrade, a dated copy of a site, a database dump. Nothing rotates these, so they stay for ever and are usually the oldest large thing on the disk. Probable is the operative word — each is a guess from the name and shape, and worth looking at before acting.',
      }),
      es: Object.freeze({
        'core-backup-content-details.reading_last_walk':
          'Leyendo el último recorrido…',
        'core-backup-content-details.unattributed_help':
          'Parte de esto no se pudo vincular a una cuenta. Nada bajo la raíz de las copias de seguridad tenía un nombre que coincidiera con un directorio en /home, y los propios registros de JetBackup no se podían leer; por eso se informa como sin atribuir, en vez de asignarlo a la única cuenta de la máquina, lo cual sería una suposición.',
        'core-backup-content-details.inferred_accounts_help':
          'Cuentas inferidas de los nombres de directorios bajo la raíz de las copias de seguridad: los metadatos de JetBackup no se podían leer, por lo que son una suposición razonable y no sus registros.',
        'core-backup-content-details.unmeasured_entries_help':
          'No se pudieron medir {{count}} entradas aquí antes de que se agotara el tiempo del recorrido. Se muestran sin tamaño en vez de omitirse. Aumente host.walk_seconds y ejecútelo de nuevo para obtener una cifra completa.',
        'core-backup-content-details.at_least': 'al menos {{size}}',
        'core-backup-content-details.inside_path': 'Dentro de {{path}}',
        'core-backup-content-details.other_help':
          'Directorios que parecen copias de seguridad pero que no gestiona nada: un tar.gz creado antes de una actualización, una copia fechada de un sitio o un volcado de base de datos. No se rotan, por lo que suelen ser lo más antiguo y grande del disco. Probable es la palabra clave: cada uno es una conjetura basada en su nombre y forma, y conviene revisarlo antes de actuar.',
      }),
      hi: Object.freeze({
        'core-backup-content-details.reading_last_walk':
          'पिछला स्कैन पढ़ा जा रहा है…',
        'core-backup-content-details.unattributed_help':
          'इसका कुछ भाग किसी खाते से नहीं जोड़ा जा सका। बैकअप रूट के अंतर्गत किसी नाम का मिलान /home की डायरेक्टरी से नहीं हुआ और JetBackup के अपने रिकॉर्ड पढ़े नहीं जा सके; इसलिए इसे मशीन के एकमात्र खाते को सौंपने के बजाय असंबद्ध बताया गया है, क्योंकि वह केवल अनुमान होगा।',
        'core-backup-content-details.inferred_accounts_help':
          'बैकअप रूट के अंतर्गत डायरेक्टरी नामों से अनुमानित खाते — JetBackup के अपने मेटाडेटा पढ़े नहीं जा सके, इसलिए ये उसके रिकॉर्ड के बजाय उचित अनुमान हैं।',
        'core-backup-content-details.unmeasured_entries_help':
          'स्कैन का समय समाप्त होने से पहले यहाँ की {{count}} प्रविष्टियां मापी नहीं जा सकीं। उन्हें छोड़ने के बजाय बिना आकार के सूचीबद्ध किया गया है। पूर्ण आंकड़े के लिए host.walk_seconds बढ़ाएं और इसे फिर चलाएं।',
        'core-backup-content-details.at_least': 'कम से कम {{size}}',
        'core-backup-content-details.inside_path': '{{path}} के अंदर',
        'core-backup-content-details.other_help':
          'ऐसी डायरेक्टरियाँ जो बैकअप जैसी दिखती हैं लेकिन किसी के द्वारा प्रबंधित नहीं हैं: अपग्रेड से पहले बना tar.gz, साइट की दिनांकित कॉपी या डेटाबेस डंप। इन्हें कोई नहीं घुमाता, इसलिए वे अक्सर डिस्क की सबसे पुरानी बड़ी चीज़ होती हैं। संभावित शब्द महत्वपूर्ण है: हर एक नाम और आकार के आधार पर अनुमान है, इसलिए कार्रवाई से पहले जाँचें।',
      }),
      ar: Object.freeze({
        'core-backup-content-details.reading_last_walk': 'جارٍ قراءة آخر فحص…',
        'core-backup-content-details.unattributed_help':
          'تعذّر ربط بعض هذه البيانات بحساب. لم يحمل أي شيء تحت جذر النسخ الاحتياطية اسمًا يطابق دليلاً في ‎/home، ولم تكن سجلات JetBackup نفسها قابلة للقراءة؛ لذا يُبلّغ عنه كغير منسوب بدل إسناده إلى الحساب الوحيد على الجهاز، لأن ذلك سيكون تخمينًا.',
        'core-backup-content-details.inferred_accounts_help':
          'حسابات استُنتجت من أسماء الأدلة تحت جذر النسخ الاحتياطية — لم تكن بيانات JetBackup الوصفية قابلة للقراءة، لذا فهي تخمين معقول وليست سجلاته.',
        'core-backup-content-details.unmeasured_entries_help':
          'تعذّر قياس {{count}} من الإدخالات هنا قبل انتهاء وقت الفحص. وهي مدرجة بلا حجم بدل حذفها. زد host.walk_seconds وشغّله مجددًا للحصول على رقم كامل.',
        'core-backup-content-details.at_least': 'على الأقل {{size}}',
        'core-backup-content-details.inside_path': 'داخل {{path}}',
        'core-backup-content-details.other_help':
          'مجلدات تبدو كنسخ احتياطية لكن لا يديرها شيء: ملف tar.gz أُنشئ قبل ترقية أو نسخة مؤرخة من موقع أو تفريغ قاعدة بيانات. لا شيء يدوّرها، لذا غالبًا ما تكون أقدم الأشياء الكبيرة على القرص. كلمة محتملة مهمة: كل منها تخمين من الاسم والشكل ويستحق المراجعة قبل الإجراء.',
      }),
      zh: Object.freeze({
        'core-backup-content-details.reading_last_walk': '正在读取上一次扫描…',
        'core-backup-content-details.unattributed_help':
          '其中一部分无法关联到某个账户。备份根目录下没有名称与 /home 中目录相匹配的内容，JetBackup 自己的记录也无法读取；因此它被报告为未归属，而不是归给机器上的唯一账户，因为那会是猜测。',
        'core-backup-content-details.inferred_accounts_help':
          '根据备份根目录下的目录名称推断账户——JetBackup 自己的元数据无法读取，因此这些是合理的推断，而不是它的记录。',
        'core-backup-content-details.unmeasured_entries_help':
          '扫描超时前，此处有 {{count}} 个条目无法测量。它们以无大小的形式列出，而非被省略。请提高 host.walk_seconds 并再次运行以获取完整数值。',
        'core-backup-content-details.at_least': '至少 {{size}}',
        'core-backup-content-details.inside_path': '{{path}} 内',
        'core-backup-content-details.other_help':
          '看起来像备份、但没有任何管理程序处理的目录：升级前创建的 tar.gz、带日期的网站副本或数据库转储。没有任何机制轮换它们，因此它们常是磁盘上最老的大型内容。可能是关键词：每项都只是根据名称和形状做出的猜测，操作前值得检查。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-backup-content-details.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
