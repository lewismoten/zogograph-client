window.ZogographActionLogTableCard = {
  manifest: Object.freeze({
    id: 'core-action-log-table',
    locales: ['core-action-log-table.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-action-log-table.when': 'When',
        'core-action-log-table.what': 'What',
        'core-action-log-table.target': 'Target',
        'core-action-log-table.outcome': 'Outcome',
        'core-action-log-table.freed': 'Freed',
        'core-action-log-table.actor': 'Actor',
        'core-action-log-table.from': 'From',
        'core-action-log-table.address_identity_note':
          'There is one password, so there is one identity. The address a change came from is the only thing that tells two uses of it apart — it is a place, not a person.',
      }),
      es: Object.freeze({
        'core-action-log-table.when': 'Cuándo',
        'core-action-log-table.what': 'Qué',
        'core-action-log-table.target': 'Destino',
        'core-action-log-table.outcome': 'Resultado',
        'core-action-log-table.freed': 'Liberado',
        'core-action-log-table.actor': 'Actor',
        'core-action-log-table.from': 'Desde',
        'core-action-log-table.address_identity_note':
          'Hay una contraseña, por lo que hay una identidad. La dirección desde la que llegó un cambio es lo único que distingue dos usos de ella: es un lugar, no una persona.',
      }),
      hi: Object.freeze({
        'core-action-log-table.when': 'कब',
        'core-action-log-table.what': 'क्या',
        'core-action-log-table.target': 'लक्ष्य',
        'core-action-log-table.outcome': 'परिणाम',
        'core-action-log-table.freed': 'खाली किया',
        'core-action-log-table.actor': 'कर्ता',
        'core-action-log-table.from': 'से',
        'core-action-log-table.address_identity_note':
          'एक पासवर्ड है, इसलिए एक पहचान है। परिवर्तन जिस पते से आया, वही उसके दो उपयोगों में अंतर बताता है — वह स्थान है, व्यक्ति नहीं।',
      }),
      ar: Object.freeze({
        'core-action-log-table.when': 'متى',
        'core-action-log-table.what': 'ما',
        'core-action-log-table.target': 'الهدف',
        'core-action-log-table.outcome': 'النتيجة',
        'core-action-log-table.freed': 'المحرر',
        'core-action-log-table.actor': 'الفاعل',
        'core-action-log-table.from': 'من',
        'core-action-log-table.address_identity_note':
          'توجد كلمة مرور واحدة، لذا توجد هوية واحدة. العنوان الذي جاء منه التغيير هو الشيء الوحيد الذي يميّز استخدامين لها — إنه مكان وليس شخصًا.',
      }),
      zh: Object.freeze({
        'core-action-log-table.when': '时间',
        'core-action-log-table.what': '内容',
        'core-action-log-table.target': '目标',
        'core-action-log-table.outcome': '结果',
        'core-action-log-table.freed': '已释放',
        'core-action-log-table.actor': '执行者',
        'core-action-log-table.from': '来自',
        'core-action-log-table.address_identity_note':
          '只有一个密码，因此只有一个身份。一次更改来自哪个地址，是区分两次使用的唯一信息——它是一个位置，而不是一个人。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-action-log-table.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
