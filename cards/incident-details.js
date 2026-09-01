/* Core card package: incident-detail header and evidence availability copy. */
window.ZogographIncidentDetailsCard = {
  manifest: Object.freeze({
    id: 'core-incident-details',
    locales: ['core-incident-details.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-incident-details.alerts_fired_together':
          '{{count, number}} alerts fired together',
        'core-incident-details.captured_at':
          'Captured {{time}} — the machine as it stood at that moment.',
        'core-incident-details.unreadable':
          'This record could not be read back.',
        'core-incident-details.what_fired': 'What fired',
        'core-incident-details.at_that_moment': 'At that moment',
        'core-incident-details.load': 'Load',
        'core-incident-details.not_reported': 'not reported',
        'core-incident-details.load_per_core': 'Load per core',
        'core-incident-details.cpu': 'CPU',
        'core-incident-details.memory_available': 'Memory available',
        'core-incident-details.memory_used': 'Memory used',
        'core-incident-details.swap_used': 'Swap used',
        'core-incident-details.reading_own_io': 'Reading own I/O',
        'core-incident-details.writing_own_io': 'Writing own I/O',
      }),
      es: Object.freeze({
        'core-incident-details.alerts_fired_together':
          'Se activaron {{count, number}} alertas a la vez',
        'core-incident-details.captured_at':
          'Capturado {{time}}: el estado de la máquina en ese momento.',
        'core-incident-details.unreadable':
          'No se pudo volver a leer este registro.',
        'core-incident-details.what_fired': 'Qué se activó',
        'core-incident-details.at_that_moment': 'En ese momento',
        'core-incident-details.load': 'Carga',
        'core-incident-details.not_reported': 'no informado',
        'core-incident-details.load_per_core': 'Carga por núcleo',
        'core-incident-details.cpu': 'CPU',
        'core-incident-details.memory_available': 'Memoria disponible',
        'core-incident-details.memory_used': 'Memoria usada',
        'core-incident-details.swap_used': 'Intercambio usado',
        'core-incident-details.reading_own_io': 'Lectura de E/S propia',
        'core-incident-details.writing_own_io': 'Escritura de E/S propia',
      }),
      hi: Object.freeze({
        'core-incident-details.alerts_fired_together':
          '{{count, number}} अलर्ट एक साथ सक्रिय हुए',
        'core-incident-details.captured_at':
          '{{time}} पर कैप्चर किया गया — उस क्षण मशीन जैसी थी।',
        'core-incident-details.unreadable':
          'यह रिकॉर्ड फिर से पढ़ा नहीं जा सका।',
        'core-incident-details.what_fired': 'क्या सक्रिय हुआ',
        'core-incident-details.at_that_moment': 'उस क्षण',
        'core-incident-details.load': 'लोड',
        'core-incident-details.not_reported': 'रिपोर्ट नहीं किया गया',
        'core-incident-details.load_per_core': 'प्रति कोर लोड',
        'core-incident-details.cpu': 'CPU',
        'core-incident-details.memory_available': 'उपलब्ध मेमोरी',
        'core-incident-details.memory_used': 'प्रयुक्त मेमोरी',
        'core-incident-details.swap_used': 'प्रयुक्त स्वैप',
        'core-incident-details.reading_own_io': 'अपना I/O पढ़ना',
        'core-incident-details.writing_own_io': 'अपना I/O लिखना',
      }),
      ar: Object.freeze({
        'core-incident-details.alerts_fired_together':
          'تم تشغيل {{count, number}} من التنبيهات معًا',
        'core-incident-details.captured_at':
          'التُقطت في {{time}} — حالة الجهاز في تلك اللحظة.',
        'core-incident-details.unreadable': 'تعذرت قراءة هذا السجل مرة أخرى.',
        'core-incident-details.what_fired': 'ما الذي تم تشغيله',
        'core-incident-details.at_that_moment': 'في تلك اللحظة',
        'core-incident-details.load': 'الحمل',
        'core-incident-details.not_reported': 'غير مُبلّغ عنه',
        'core-incident-details.load_per_core': 'الحمل لكل نواة',
        'core-incident-details.cpu': 'CPU',
        'core-incident-details.memory_available': 'الذاكرة المتاحة',
        'core-incident-details.memory_used': 'الذاكرة المستخدمة',
        'core-incident-details.swap_used': 'مساحة التبديل المستخدمة',
        'core-incident-details.reading_own_io': 'قراءة إدخال/إخراج الحساب',
        'core-incident-details.writing_own_io': 'كتابة إدخال/إخراج الحساب',
      }),
      zh: Object.freeze({
        'core-incident-details.alerts_fired_together':
          '{{count, number}} 个警报同时触发',
        'core-incident-details.captured_at':
          '捕获于 {{time}}——机器当时的状态。',
        'core-incident-details.unreadable': '无法重新读取此记录。',
        'core-incident-details.what_fired': '触发了什么',
        'core-incident-details.at_that_moment': '当时',
        'core-incident-details.load': '负载',
        'core-incident-details.not_reported': '未报告',
        'core-incident-details.load_per_core': '每个核心的负载',
        'core-incident-details.cpu': 'CPU',
        'core-incident-details.memory_available': '可用内存',
        'core-incident-details.memory_used': '已用内存',
        'core-incident-details.swap_used': '已用交换空间',
        'core-incident-details.reading_own_io': '读取自身 I/O',
        'core-incident-details.writing_own_io': '写入自身 I/O',
      }),
    }),
  }),
  text(key, values, helpers) {
    const id = `core-incident-details.${key}`;
    return helpers.i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
  render() {},
};
