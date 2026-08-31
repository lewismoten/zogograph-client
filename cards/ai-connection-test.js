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
        'core-ai-connection-test.success':
          'It answered “{{answer}}” in {{milliseconds}} ms and accepted the tools. {{tokens}} tokens{{reasoning}}, about {{cost}}.',
        'core-ai-connection-test.reasoning': ', {{tokens}} of them thinking',
      }),
      es: Object.freeze({
        'core-ai-connection-test.action': 'Probar la conexión',
        'core-ai-connection-test.title':
          'Una solicitud real, con una herramienta adjunta, usando lo que esté en los campos anteriores, incluida una clave escrita sin guardar',
        'core-ai-connection-test.help':
          'La prueba hace una pregunta sencilla al modelo con una herramienta adjunta. Cuesta unos cientos de tokens y confirma la dirección, la clave, el modelo y las herramientas.',
        'core-ai-connection-test.success':
          'Respondió “{{answer}}” en {{milliseconds}} ms y aceptó las herramientas. {{tokens}} tokens{{reasoning}}, aproximadamente {{cost}}.',
        'core-ai-connection-test.reasoning': ', {{tokens}} de razonamiento',
      }),
      hi: Object.freeze({
        'core-ai-connection-test.action': 'कनेक्शन जाँचें',
        'core-ai-connection-test.title':
          'ऊपर के बॉक्स में जो है उसका उपयोग करके एक टूल के साथ वास्तविक अनुरोध, सहेजी न गई कुंजी सहित',
        'core-ai-connection-test.help':
          'परीक्षण एक टूल के साथ मॉडल से एक सरल प्रश्न पूछता है। यह कुछ सौ टोकन खर्च करता है और पता, कुंजी, मॉडल और टूल समर्थन जाँचता है।',
        'core-ai-connection-test.success':
          'इसने {{milliseconds}} ms में “{{answer}}” उत्तर दिया और टूल स्वीकार किए। {{tokens}} टोकन{{reasoning}}, लगभग {{cost}}।',
        'core-ai-connection-test.reasoning': ', जिनमें {{tokens}} सोचने के थे',
      }),
      ar: Object.freeze({
        'core-ai-connection-test.action': 'اختبر الاتصال',
        'core-ai-connection-test.title':
          'طلب حقيقي واحد مع أداة مرفقة، باستخدام ما في الحقول أعلاه بما في ذلك مفتاح لم يُحفظ',
        'core-ai-connection-test.help':
          'يطرح الاختبار سؤالًا بسيطًا على النموذج مع أداة مرفقة. يستهلك بضع مئات من الرموز ويتحقق من العنوان والمفتاح والنموذج والأدوات.',
        'core-ai-connection-test.success':
          'أجاب «{{answer}}» خلال {{milliseconds}} مللي ثانية وقبل الأدوات. {{tokens}} رمزًا{{reasoning}}، بتكلفة تقارب {{cost}}.',
        'core-ai-connection-test.reasoning': '، منها {{tokens}} للتفكير',
      }),
      zh: Object.freeze({
        'core-ai-connection-test.action': '测试连接',
        'core-ai-connection-test.title':
          '使用上方字段中的内容（包括尚未保存的密钥）发出一次附带工具的真实请求',
        'core-ai-connection-test.help':
          '测试会向模型提出一个附带工具的简单问题。它会花费数百个令牌，并同时确认地址、密钥、模型和工具支持。',
        'core-ai-connection-test.success':
          '它在 {{milliseconds}} 毫秒内回答“{{answer}}”并接受了工具。{{tokens}} 个令牌{{reasoning}}，约 {{cost}}。',
        'core-ai-connection-test.reasoning': '，其中 {{tokens}} 个用于推理',
      }),
    }),
  }),
  render(input, helpers) {
    const { note, i18n } = helpers;
    const t = (key, fallback) => i18n.t(key, {}, fallback);
    if (input?.kind === 'success') {
      const { result, humanCount } = input;
      return i18n.t(
        'core-ai-connection-test.success',
        {
          answer: result.said,
          milliseconds: result.ms,
          tokens: humanCount(result.tokens.in + result.tokens.out),
          reasoning: result.tokens.reasoning
            ? i18n.t(
                'core-ai-connection-test.reasoning',
                { tokens: humanCount(result.tokens.reasoning) },
                ', {{tokens}} of them thinking',
              )
            : '',
          cost: result.cost,
        },
        'It answered “{{answer}}” in {{milliseconds}} ms and accepted the tools. {{tokens}} tokens{{reasoning}}, about {{cost}}.',
      );
    }
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
