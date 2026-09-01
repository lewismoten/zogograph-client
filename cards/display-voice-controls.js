/* Core card package: display-preference voice selection and test controls. */
window.ZogographDisplayVoiceControlsCard = {
  manifest: Object.freeze({
    id: 'core-display-voice-controls',
    locales: ['core-display-voice-controls.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-display-voice-controls.voice_browser_default': 'Browser default',
        'core-display-voice-controls.test_voice': 'Test voice',
        'core-display-voice-controls.voice_ready':
          'Zogograph voice settings are ready.',
      }),
      es: Object.freeze({
        'core-display-voice-controls.voice_browser_default':
          'Predeterminada del navegador',
        'core-display-voice-controls.test_voice': 'Probar voz',
        'core-display-voice-controls.voice_ready':
          'La configuración de voz de Zogograph está lista.',
      }),
      hi: Object.freeze({
        'core-display-voice-controls.voice_browser_default':
          'ब्राउज़र डिफ़ॉल्ट',
        'core-display-voice-controls.test_voice': 'आवाज़ जाँचें',
        'core-display-voice-controls.voice_ready':
          'Zogograph की आवाज़ सेटिंग तैयार हैं।',
      }),
      ar: Object.freeze({
        'core-display-voice-controls.voice_browser_default':
          'إعداد المتصفح الافتراضي',
        'core-display-voice-controls.test_voice': 'اختبر الصوت',
        'core-display-voice-controls.voice_ready':
          'إعدادات صوت Zogograph جاهزة.',
      }),
      zh: Object.freeze({
        'core-display-voice-controls.voice_browser_default': '浏览器默认值',
        'core-display-voice-controls.test_voice': '测试语音',
        'core-display-voice-controls.voice_ready': 'Zogograph 语音设置已就绪。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-display-voice-controls.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
