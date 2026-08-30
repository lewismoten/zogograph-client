/* Core card package: model-connection test controls and explanation. */
window.ZogographAiConnectionTestCard = {
  manifest: Object.freeze({
    id: 'core-ai-connection-test',
    locales: ['core-ai-connection-test.'],
    helpers: ['note', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-connection-test.action': 'Test the connection',
        'core-ai-connection-test.title':
          'One real request, with a tool attached, using whatever is in the boxes above — including a key you have typed but not saved',
        'core-ai-connection-test.help':
          'The test asks the model one trivial question with one tool attached. It costs a few hundred tokens and tells you four things at once: the address answers, the key is accepted, the model exists, and it will take tools.',
      }),
      es: Object.freeze({
        'core-ai-connection-test.action': 'Probar la conexión',
        'core-ai-connection-test.title':
          'Una solicitud real, con una herramienta adjunta, usando lo que esté en los campos anteriores, incluida una clave escrita sin guardar',
        'core-ai-connection-test.help':
          'La prueba hace una pregunta sencilla al modelo con una herramienta adjunta. Cuesta unos cientos de tokens y confirma la dirección, la clave, el modelo y las herramientas.',
      }),
      hi: Object.freeze({
        'core-ai-connection-test.action': 'कनेक्शन जाँचें',
        'core-ai-connection-test.title':
          'ऊपर के बॉक्स में जो है उसका उपयोग करके एक टूल के साथ वास्तविक अनुरोध, सहेजी न गई कुंजी सहित',
        'core-ai-connection-test.help':
          'परीक्षण एक टूल के साथ मॉडल से एक सरल प्रश्न पूछता है। यह कुछ सौ टोकन खर्च करता है और पता, कुंजी, मॉडल और टूल समर्थन जाँचता है।',
      }),
      ar: Object.freeze({
        'core-ai-connection-test.action': 'اختبر الاتصال',
        'core-ai-connection-test.title':
          'طلب حقيقي واحد مع أداة مرفقة، باستخدام ما في الحقول أعلاه بما في ذلك مفتاح لم يُحفظ',
        'core-ai-connection-test.help':
          'يطرح الاختبار سؤالًا بسيطًا على النموذج مع أداة مرفقة. يستهلك بضع مئات من الرموز ويتحقق من العنوان والمفتاح والنموذج والأدوات.',
      }),
      zh: Object.freeze({
        'core-ai-connection-test.action': '测试连接',
        'core-ai-connection-test.title':
          '使用上方字段中的内容（包括尚未保存的密钥）发出一次附带工具的真实请求',
        'core-ai-connection-test.help':
          '测试会向模型提出一个附带工具的简单问题。它会花费数百个令牌，并同时确认地址、密钥、模型和工具支持。',
      }),
    }),
  }),
  render(_input, helpers) {
    const { note, i18n } = helpers;
    const t = (key, fallback) => i18n.t(key, {}, fallback);
    const wrap = document.createDocumentFragment();
    const testing = document.createElement('div');
    testing.className = 'inspect-actions';
    const test = document.createElement('button');
    test.type = 'button';
    test.id = 'aiTest';
    test.textContent = t(
      'core-ai-connection-test.action',
      'Test the connection',
    );
    test.title = t(
      'core-ai-connection-test.title',
      'One real request, with a tool attached, using whatever is in the boxes above — including a key you have typed but not saved',
    );
    const result = document.createElement('span');
    result.id = 'aiTestResult';
    result.className = 'muted small';
    testing.append(test, result);
    wrap.append(testing);
    wrap.append(
      note(
        t(
          'core-ai-connection-test.help',
          'The test asks the model one trivial question with one tool attached. It costs a few hundred tokens and tells you four things at once: the address answers, the key is accepted, the model exists, and it will take tools.',
        ),
      ),
    );
    return wrap;
  },
};
