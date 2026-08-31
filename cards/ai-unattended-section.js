/* Core card package: unattended assistant setting section and cost warning. */
window.ZogographAiUnattendedSectionCard = {
  manifest: Object.freeze({
    id: 'core-ai-unattended-section',
    locales: ['core-ai-unattended-section.'],
    helpers: ['section', 'note', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-unattended-section.title': 'What it does without being asked',
        'core-ai-unattended-section.help':
          'Everything in this section costs money on a schedule, with nobody watching. The per-conversation limits above do not apply to it — those are about one conversation, and this is not one.',
      }),
      es: Object.freeze({
        'core-ai-unattended-section.title': 'Lo que hace sin que se le pida',
        'core-ai-unattended-section.help':
          'Todo en esta sección cuesta dinero según una programación, sin nadie mirando. Los límites por conversación no se aplican aquí.',
      }),
      hi: Object.freeze({
        'core-ai-unattended-section.title': 'यह बिना पूछे क्या करता है',
        'core-ai-unattended-section.help':
          'इस भाग की हर चीज़ शेड्यूल पर पैसे खर्च करती है और कोई देख नहीं रहा होता। ऊपर की प्रति-बातचीत सीमाएँ यहाँ लागू नहीं होतीं।',
      }),
      ar: Object.freeze({
        'core-ai-unattended-section.title': 'ما يفعله دون أن يُطلب منه',
        'core-ai-unattended-section.help':
          'كل ما في هذا القسم يكلف مالًا وفق جدول زمني من دون مراقبة. لا تنطبق هنا حدود كل محادثة.',
      }),
      zh: Object.freeze({
        'core-ai-unattended-section.title': '它在无人请求时会做什么',
        'core-ai-unattended-section.help':
          '本节中的一切都会按计划花费费用，且无人看管。上面的单次对话限制不适用于此处。',
      }),
    }),
  }),
  render(input, helpers) {
    const t = (key, fallback) =>
      helpers.i18n.t(`core-ai-unattended-section.${key}`, {}, fallback);
    return [
      helpers.section(
        t('title', 'What it does without being asked'),
        input.content,
      ),
      helpers.note(
        t(
          'help',
          'Everything in this section costs money on a schedule, with nobody watching. The per-conversation limits above do not apply to it — those are about one conversation, and this is not one.',
        ),
      ),
    ];
  },
};
