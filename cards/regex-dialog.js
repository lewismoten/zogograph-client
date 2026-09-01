/* Core card package: regex tester dialog guidance and results summary. */
window.ZogographRegexDialogCard = {
  manifest: Object.freeze({
    id: 'core-regex-dialog',
    locales: ['core-regex-dialog.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-regex-dialog.dialog_title': 'Try a pattern',
        'core-regex-dialog.dialog_help':
          'Nothing here is saved or requested on a schedule. It runs the pattern exactly the way the check will, against whatever you paste.',
        'core-regex-dialog.initial_help':
          'Paste some text and press Test it. Every match is shown with its groups numbered, so you can see which number to keep.',
        'core-regex-dialog.no_match':
          'The pattern is valid but matched nothing in that text. Which is worth knowing now rather than as a check that fails every five minutes.',
        'core-regex-dialog.summary':
          'Matches: {{matches, number}}; groups in pattern: {{groups, number}}.',
        'core-regex-dialog.showing_first':
          'Showing the first {{count, number}}.',
      }),
      es: Object.freeze({
        'core-regex-dialog.dialog_title': 'Probar un patrón',
        'core-regex-dialog.dialog_help':
          'Aquí no se guarda nada ni se solicita según un calendario. Ejecuta el patrón exactamente como lo hará la comprobación, con el texto que pegue.',
        'core-regex-dialog.initial_help':
          'Pegue texto y pulse Probarlo. Se muestra cada coincidencia con sus grupos numerados para que pueda ver qué número conservar.',
        'core-regex-dialog.no_match':
          'El patrón es válido, pero no coincidió con nada en ese texto. Es mejor saberlo ahora que tener una comprobación que falle cada cinco minutos.',
        'core-regex-dialog.summary':
          'Coincidencias: {{matches, number}}; grupos en el patrón: {{groups, number}}.',
        'core-regex-dialog.showing_first':
          'Se muestran los primeros {{count, number}}.',
      }),
      hi: Object.freeze({
        'core-regex-dialog.dialog_title': 'पैटर्न जाँचें',
        'core-regex-dialog.dialog_help':
          'यहाँ कुछ भी सहेजा या शेड्यूल पर अनुरोध नहीं किया जाता। यह पैटर्न को ठीक वैसे ही चलाता है जैसे जाँच चलेगी, आपके चिपकाए हुए पाठ पर।',
        'core-regex-dialog.initial_help':
          'कुछ पाठ चिपकाएँ और इसे जाँचें दबाएँ। हर मिलान उसके क्रमांकित समूहों के साथ दिखता है, ताकि आप देख सकें कि कौन-सा नंबर रखना है।',
        'core-regex-dialog.no_match':
          'पैटर्न मान्य है, लेकिन उस पाठ में कुछ भी नहीं मिला। हर पाँच मिनट में विफल होने वाली जाँच से यह अभी जान लेना बेहतर है।',
        'core-regex-dialog.summary':
          'मिलान: {{matches, number}}; पैटर्न में समूह: {{groups, number}}।',
        'core-regex-dialog.showing_first':
          'पहले {{count, number}} दिखाए जा रहे हैं।',
      }),
      ar: Object.freeze({
        'core-regex-dialog.dialog_title': 'اختبر نمطًا',
        'core-regex-dialog.dialog_help':
          'لا يُحفظ هنا شيء ولا يُطلب وفق جدول. يشغّل النمط تمامًا كما سيشغله الفحص، على ما تلصقه.',
        'core-regex-dialog.initial_help':
          'الصق نصًا واضغط اختبره. يظهر كل تطابق مع مجموعاته المرقمة لتتمكن من معرفة الرقم الذي تريد الاحتفاظ به.',
        'core-regex-dialog.no_match':
          'النمط صالح لكنه لم يطابق شيئًا في ذلك النص. من الأفضل معرفة ذلك الآن بدلًا من فحص يفشل كل خمس دقائق.',
        'core-regex-dialog.summary':
          'المطابقات: {{matches, number}}؛ المجموعات في النمط: {{groups, number}}.',
        'core-regex-dialog.showing_first': 'يُعرض أول {{count, number}}.',
      }),
      zh: Object.freeze({
        'core-regex-dialog.dialog_title': '测试模式',
        'core-regex-dialog.dialog_help':
          '这里不会保存任何内容，也不会按计划发出请求。它会完全按照检查方式，针对您粘贴的内容运行该模式。',
        'core-regex-dialog.initial_help':
          '粘贴一些文本并按“测试”。每个匹配项都会显示编号的分组，便于您确定要保留的编号。',
        'core-regex-dialog.no_match':
          '该模式有效，但在该文本中没有匹配项。现在知道这一点总比每五分钟让检查失败一次更好。',
        'core-regex-dialog.summary':
          '匹配项：{{matches, number}}；模式中的分组：{{groups, number}}。',
        'core-regex-dialog.showing_first': '显示前 {{count, number}} 项。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-regex-dialog.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
