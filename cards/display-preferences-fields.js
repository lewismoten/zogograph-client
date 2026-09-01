/* Core card package: display-preferences field labels and help text. */
window.ZogographDisplayPreferencesFieldsCard = {
  manifest: Object.freeze({
    id: 'core-display-preferences-fields',
    locales: ['core-display-preferences-fields.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-display-preferences-fields.text_size': 'Text size',
        'core-display-preferences-fields.text_size_help':
          'Applies to this browser only.',
        'core-display-preferences-fields.motion': 'Motion',
        'core-display-preferences-fields.motion_help':
          'Reduce motion disables decorative animation and transitions in this browser.',
        'core-display-preferences-fields.editor_typeface': 'Editor typeface',
        'core-display-preferences-fields.editor_typeface_help':
          'Changes only local code, diff, and syntax readers. No file content leaves this browser.',
        'core-display-preferences-fields.colour_vision': 'Colour vision',
        'core-display-preferences-fields.colour_vision_help':
          'High distinction uses a blue, orange, and violet status palette. Status words and symbols remain visible in every mode.',
        'core-display-preferences-fields.voice_section': 'Voice',
        'core-display-preferences-fields.speaking': 'Speaking',
        'core-display-preferences-fields.speaking_help':
          'Browser speech synthesis; replies are read aloud only when enabled.',
        'core-display-preferences-fields.voice': 'Voice',
        'core-display-preferences-fields.voice_help':
          'Available voices come from this browser and device.',
        'core-display-preferences-fields.voice_speed': 'Speed',
        'core-display-preferences-fields.voice_speed_help': '0.5× to 2×.',
        'core-display-preferences-fields.voice_pitch': 'Pitch',
        'core-display-preferences-fields.voice_pitch_help': '0 to 2.',
        'core-display-preferences-fields.voice_volume': 'Volume',
        'core-display-preferences-fields.voice_volume_help': '0 to 100%.',
        'core-display-preferences-fields.listening': 'Listening',
        'core-display-preferences-fields.listening_help':
          'After applying, click Start listening to grant microphone permission. Push to talk requires holding the Listen button; Always on restarts recognition until stopped.',
      }),
      es: Object.freeze({
        'core-display-preferences-fields.text_size': 'Tamaño del texto',
        'core-display-preferences-fields.text_size_help':
          'Solo se aplica a este navegador.',
        'core-display-preferences-fields.motion': 'Movimiento',
        'core-display-preferences-fields.motion_help':
          'Reducir movimiento desactiva las animaciones y transiciones decorativas en este navegador.',
        'core-display-preferences-fields.editor_typeface':
          'Tipografía del editor',
        'core-display-preferences-fields.editor_typeface_help':
          'Solo cambia los lectores locales de código, diferencias y sintaxis. Ningún contenido de archivos sale de este navegador.',
        'core-display-preferences-fields.colour_vision': 'Visión del color',
        'core-display-preferences-fields.colour_vision_help':
          'Alto contraste usa una paleta de estado azul, naranja y violeta. Las palabras y símbolos de estado siguen visibles en todos los modos.',
        'core-display-preferences-fields.voice_section': 'Voz',
        'core-display-preferences-fields.speaking': 'Lectura en voz alta',
        'core-display-preferences-fields.speaking_help':
          'Síntesis de voz del navegador; las respuestas solo se leen en voz alta cuando se activa.',
        'core-display-preferences-fields.voice': 'Voz',
        'core-display-preferences-fields.voice_help':
          'Las voces disponibles proceden de este navegador y dispositivo.',
        'core-display-preferences-fields.voice_speed': 'Velocidad',
        'core-display-preferences-fields.voice_speed_help': 'De 0,5× a 2×.',
        'core-display-preferences-fields.voice_pitch': 'Tono',
        'core-display-preferences-fields.voice_pitch_help': 'De 0 a 2.',
        'core-display-preferences-fields.voice_volume': 'Volumen',
        'core-display-preferences-fields.voice_volume_help': 'De 0 a 100 %.',
        'core-display-preferences-fields.listening': 'Escucha',
        'core-display-preferences-fields.listening_help':
          'Después de aplicar, pulse Iniciar escucha para conceder permiso de micrófono. Pulsar para hablar requiere mantener pulsado el botón Escuchar; Siempre activa reinicia el reconocimiento hasta detenerlo.',
      }),
      hi: Object.freeze({
        'core-display-preferences-fields.text_size': 'पाठ आकार',
        'core-display-preferences-fields.text_size_help':
          'केवल इस ब्राउज़र पर लागू होता है।',
        'core-display-preferences-fields.motion': 'गतिशीलता',
        'core-display-preferences-fields.motion_help':
          'गतिशीलता कम करने से इस ब्राउज़र में सजावटी एनीमेशन और संक्रमण बंद हो जाते हैं।',
        'core-display-preferences-fields.editor_typeface': 'संपादक टाइपफ़ेस',
        'core-display-preferences-fields.editor_typeface_help':
          'केवल स्थानीय कोड, डिफ़ और सिंटैक्स रीडर बदलते हैं। कोई फ़ाइल सामग्री इस ब्राउज़र से बाहर नहीं जाती।',
        'core-display-preferences-fields.colour_vision': 'रंग दृष्टि',
        'core-display-preferences-fields.colour_vision_help':
          'उच्च-विभेदन नीले, नारंगी और बैंगनी स्थिति पैलेट का उपयोग करता है। स्थिति शब्द और प्रतीक हर मोड में दिखते रहते हैं।',
        'core-display-preferences-fields.voice_section': 'आवाज़',
        'core-display-preferences-fields.speaking': 'बोलना',
        'core-display-preferences-fields.speaking_help':
          'ब्राउज़र वाक् संश्लेषण; सक्षम होने पर ही उत्तर जोर से पढ़े जाते हैं।',
        'core-display-preferences-fields.voice': 'आवाज़',
        'core-display-preferences-fields.voice_help':
          'उपलब्ध आवाज़ें इस ब्राउज़र और डिवाइस से आती हैं।',
        'core-display-preferences-fields.voice_speed': 'गति',
        'core-display-preferences-fields.voice_speed_help': '0.5× से 2× तक।',
        'core-display-preferences-fields.voice_pitch': 'पिच',
        'core-display-preferences-fields.voice_pitch_help': '0 से 2 तक।',
        'core-display-preferences-fields.voice_volume': 'आवाज़',
        'core-display-preferences-fields.voice_volume_help': '0 से 100% तक।',
        'core-display-preferences-fields.listening': 'सुनना',
        'core-display-preferences-fields.listening_help':
          'लागू करने के बाद माइक्रोफ़ोन अनुमति देने के लिए सुनना शुरू करें पर क्लिक करें। बोलने के लिए दबाएँ में सुनें बटन दबाए रखना होता है; हमेशा चालू पहचान को रोकने तक फिर शुरू करता है।',
      }),
      ar: Object.freeze({
        'core-display-preferences-fields.text_size': 'حجم النص',
        'core-display-preferences-fields.text_size_help':
          'ينطبق على هذا المتصفح فقط.',
        'core-display-preferences-fields.motion': 'الحركة',
        'core-display-preferences-fields.motion_help':
          'يؤدي تقليل الحركة إلى تعطيل الرسوم المتحركة والانتقالات الزخرفية في هذا المتصفح.',
        'core-display-preferences-fields.editor_typeface': 'خط المحرر',
        'core-display-preferences-fields.editor_typeface_help':
          'يغير قارئات التعليمات البرمجية والفروق والبنية المحلية فقط. لا يغادر أي محتوى ملف هذا المتصفح.',
        'core-display-preferences-fields.colour_vision': 'رؤية الألوان',
        'core-display-preferences-fields.colour_vision_help':
          'يستخدم التمييز العالي لوحة حالة زرقاء وبرتقالية وبنفسجية. تبقى كلمات ورموز الحالة مرئية في كل وضع.',
        'core-display-preferences-fields.voice_section': 'الصوت',
        'core-display-preferences-fields.speaking': 'التحدث',
        'core-display-preferences-fields.speaking_help':
          'تركيب الكلام في المتصفح؛ لا تُقرأ الردود بصوت عالٍ إلا عند تفعيله.',
        'core-display-preferences-fields.voice': 'الصوت',
        'core-display-preferences-fields.voice_help':
          'تأتي الأصوات المتاحة من هذا المتصفح والجهاز.',
        'core-display-preferences-fields.voice_speed': 'السرعة',
        'core-display-preferences-fields.voice_speed_help': 'من 0.5× إلى 2×.',
        'core-display-preferences-fields.voice_pitch': 'حدة الصوت',
        'core-display-preferences-fields.voice_pitch_help': 'من 0 إلى 2.',
        'core-display-preferences-fields.voice_volume': 'مستوى الصوت',
        'core-display-preferences-fields.voice_volume_help': 'من 0 إلى 100٪.',
        'core-display-preferences-fields.listening': 'الاستماع',
        'core-display-preferences-fields.listening_help':
          'بعد التطبيق، انقر بدء الاستماع لمنح إذن الميكروفون. يتطلب الضغط للتحدث إبقاء زر الاستماع مضغوطًا؛ ويعيد التشغيل الدائم التعرف حتى إيقافه.',
      }),
      zh: Object.freeze({
        'core-display-preferences-fields.text_size': '文字大小',
        'core-display-preferences-fields.text_size_help': '仅适用于此浏览器。',
        'core-display-preferences-fields.motion': '动态效果',
        'core-display-preferences-fields.motion_help':
          '减少动态效果会关闭此浏览器中的装饰性动画和过渡效果。',
        'core-display-preferences-fields.editor_typeface': '编辑器字体',
        'core-display-preferences-fields.editor_typeface_help':
          '仅改变本地代码、差异和语法阅读器。文件内容不会离开此浏览器。',
        'core-display-preferences-fields.colour_vision': '色觉',
        'core-display-preferences-fields.colour_vision_help':
          '高区分度使用蓝色、橙色和紫色状态调色板。状态文字和符号在每种模式下都保持可见。',
        'core-display-preferences-fields.voice_section': '语音',
        'core-display-preferences-fields.speaking': '朗读',
        'core-display-preferences-fields.speaking_help':
          '浏览器语音合成；仅在启用时朗读回复。',
        'core-display-preferences-fields.voice': '语音',
        'core-display-preferences-fields.voice_help':
          '可用语音来自此浏览器和设备。',
        'core-display-preferences-fields.voice_speed': '语速',
        'core-display-preferences-fields.voice_speed_help': '0.5× 至 2×。',
        'core-display-preferences-fields.voice_pitch': '音调',
        'core-display-preferences-fields.voice_pitch_help': '0 至 2。',
        'core-display-preferences-fields.voice_volume': '音量',
        'core-display-preferences-fields.voice_volume_help': '0 至 100%。',
        'core-display-preferences-fields.listening': '聆听',
        'core-display-preferences-fields.listening_help':
          '应用后，单击“开始聆听”授予麦克风权限。按住说话需要一直按住“聆听”按钮；始终开启会不断重新开始识别，直到停止。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-display-preferences-fields.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
