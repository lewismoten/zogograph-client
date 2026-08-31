/* Core card package: speech and conversation controls in the chat header. */
window.ZogographChatSpeechControlsCard = {
  manifest: Object.freeze({
    id: 'core-chat-speech-controls',
    locales: ['core-chat-speech-controls.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-chat-speech-controls.speak': 'Speak',
        'core-chat-speech-controls.speak_title': 'Read replies aloud',
        'core-chat-speech-controls.listen': 'Listen',
        'core-chat-speech-controls.listen_title': 'Dictate a message',
        'core-chat-speech-controls.new': 'New',
        'core-chat-speech-controls.new_title': 'Start a fresh conversation',
        'core-chat-speech-controls.interrupt': 'Interrupt',
        'core-chat-speech-controls.interrupt_title':
          'Stop what it is doing, keep the conversation',
        'core-chat-speech-controls.stop': 'Stop',
        'core-chat-speech-controls.stop_title': 'End the current model turn',
        'core-chat-speech-controls.speaking': 'Speaking',
        'core-chat-speech-controls.hold_to_talk': 'Hold to talk',
        'core-chat-speech-controls.start_listening': 'Start listening',
        'core-chat-speech-controls.listening': 'Listening…',
        'core-chat-speech-controls.stop_listening': 'Stop listening',
        'core-chat-speech-controls.microphone_stopped':
          'Microphone listening stopped: {{reason}}.',
        'core-chat-speech-controls.microphone_unavailable':
          'Speech recognition is not available in this browser.',
      }),
      es: Object.freeze({
        'core-chat-speech-controls.speak': 'Leer',
        'core-chat-speech-controls.speak_title':
          'Leer las respuestas en voz alta',
        'core-chat-speech-controls.listen': 'Dictar',
        'core-chat-speech-controls.listen_title': 'Dictar un mensaje',
        'core-chat-speech-controls.new': 'Nueva',
        'core-chat-speech-controls.new_title': 'Iniciar una conversación nueva',
        'core-chat-speech-controls.interrupt': 'Interrumpir',
        'core-chat-speech-controls.interrupt_title':
          'Detener lo que está haciendo y conservar la conversación',
        'core-chat-speech-controls.stop': 'Detener',
        'core-chat-speech-controls.stop_title':
          'Terminar el turno actual del modelo',
        'core-chat-speech-controls.speaking': 'Hablando',
        'core-chat-speech-controls.hold_to_talk': 'Mantenga para hablar',
        'core-chat-speech-controls.start_listening': 'Empezar a escuchar',
        'core-chat-speech-controls.listening': 'Escuchando…',
        'core-chat-speech-controls.stop_listening': 'Dejar de escuchar',
        'core-chat-speech-controls.microphone_stopped':
          'La escucha del micrófono se detuvo: {{reason}}.',
        'core-chat-speech-controls.microphone_unavailable':
          'El reconocimiento de voz no está disponible en este navegador.',
      }),
      hi: Object.freeze({
        'core-chat-speech-controls.speak': 'बोलें',
        'core-chat-speech-controls.speak_title': 'जवाब ज़ोर से पढ़ें',
        'core-chat-speech-controls.listen': 'सुनें',
        'core-chat-speech-controls.listen_title': 'संदेश बोलकर लिखें',
        'core-chat-speech-controls.new': 'नया',
        'core-chat-speech-controls.new_title': 'नई बातचीत शुरू करें',
        'core-chat-speech-controls.interrupt': 'रोकें',
        'core-chat-speech-controls.interrupt_title':
          'जो कर रहा है उसे रोकें, बातचीत रखें',
        'core-chat-speech-controls.stop': 'बंद करें',
        'core-chat-speech-controls.stop_title': 'मॉडल का वर्तमान उत्तर रोकें',
        'core-chat-speech-controls.speaking': 'बोल रहा है',
        'core-chat-speech-controls.hold_to_talk': 'बोलने के लिए दबाए रखें',
        'core-chat-speech-controls.start_listening': 'सुनना शुरू करें',
        'core-chat-speech-controls.listening': 'सुन रहा है…',
        'core-chat-speech-controls.stop_listening': 'सुनना बंद करें',
        'core-chat-speech-controls.microphone_stopped':
          'माइक्रोफ़ोन से सुनना बंद हो गया: {{reason}}।',
        'core-chat-speech-controls.microphone_unavailable':
          'इस ब्राउज़र में वाक् पहचान उपलब्ध नहीं है।',
      }),
      ar: Object.freeze({
        'core-chat-speech-controls.speak': 'اقرأ',
        'core-chat-speech-controls.speak_title': 'اقرأ الردود بصوت عالٍ',
        'core-chat-speech-controls.listen': 'أملِ',
        'core-chat-speech-controls.listen_title': 'أملِ رسالة',
        'core-chat-speech-controls.new': 'جديدة',
        'core-chat-speech-controls.new_title': 'ابدأ محادثة جديدة',
        'core-chat-speech-controls.interrupt': 'قاطع',
        'core-chat-speech-controls.interrupt_title':
          'أوقف ما يفعله مع إبقاء المحادثة',
        'core-chat-speech-controls.stop': 'أوقف',
        'core-chat-speech-controls.stop_title': 'أنهِ دور النموذج الحالي',
        'core-chat-speech-controls.speaking': 'يتحدث',
        'core-chat-speech-controls.hold_to_talk': 'اضغط باستمرار للتحدث',
        'core-chat-speech-controls.start_listening': 'ابدأ الاستماع',
        'core-chat-speech-controls.listening': 'يستمع…',
        'core-chat-speech-controls.stop_listening': 'أوقف الاستماع',
        'core-chat-speech-controls.microphone_stopped':
          'توقف الاستماع إلى الميكروفون: {{reason}}.',
        'core-chat-speech-controls.microphone_unavailable':
          'التعرّف على الكلام غير متاح في هذا المتصفح.',
      }),
      zh: Object.freeze({
        'core-chat-speech-controls.speak': '朗读',
        'core-chat-speech-controls.speak_title': '朗读回复',
        'core-chat-speech-controls.listen': '听写',
        'core-chat-speech-controls.listen_title': '听写消息',
        'core-chat-speech-controls.new': '新建',
        'core-chat-speech-controls.new_title': '开始新对话',
        'core-chat-speech-controls.interrupt': '中断',
        'core-chat-speech-controls.interrupt_title': '停止当前操作并保留对话',
        'core-chat-speech-controls.stop': '停止',
        'core-chat-speech-controls.stop_title': '结束当前模型轮次',
        'core-chat-speech-controls.speaking': '正在朗读',
        'core-chat-speech-controls.hold_to_talk': '按住说话',
        'core-chat-speech-controls.start_listening': '开始听写',
        'core-chat-speech-controls.listening': '正在听写…',
        'core-chat-speech-controls.stop_listening': '停止听写',
        'core-chat-speech-controls.microphone_stopped':
          '麦克风听写已停止：{{reason}}。',
        'core-chat-speech-controls.microphone_unavailable':
          '此浏览器不支持语音识别。',
      }),
    }),
  }),
  text(key, variables, helpers) {
    return helpers.i18n.t(
      `core-chat-speech-controls.${key}`,
      variables,
      this.manifest.catalogs.en[`core-chat-speech-controls.${key}`],
    );
  },
  render(_input, helpers) {
    const text = (key) => this.text(key, {}, helpers);
    return {
      speak: text('speak'),
      speakTitle: text('speak_title'),
      listen: text('listen'),
      listenTitle: text('listen_title'),
      new: text('new'),
      newTitle: text('new_title'),
      interrupt: text('interrupt'),
      interruptTitle: text('interrupt_title'),
      stop: text('stop'),
      stopTitle: text('stop_title'),
    };
  },
};
