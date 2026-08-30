/* Core card package: image-generation rate and behavior limits. */
window.ZogographAiImageLimitsCard = {
  manifest: Object.freeze({
    id: 'core-ai-image-limits',
    locales: ['core-ai-image-limits.'],
    helpers: ['fieldRow', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-image-limits.per_hour': 'At most, per hour',
        'core-ai-image-limits.self_portrait': 'Let it redraw itself',
        'core-ai-image-limits.reactions': 'Let it react',
      }),
      es: Object.freeze({
        'core-ai-image-limits.per_hour': 'Como máximo por hora',
        'core-ai-image-limits.self_portrait': 'Permitir que se redibuje',
        'core-ai-image-limits.reactions': 'Permitir reacciones',
      }),
      hi: Object.freeze({
        'core-ai-image-limits.per_hour': 'अधिकतम प्रति घंटा',
        'core-ai-image-limits.self_portrait': 'इसे स्वयं को फिर से बनाने दें',
        'core-ai-image-limits.reactions': 'इसे प्रतिक्रिया देने दें',
      }),
      ar: Object.freeze({
        'core-ai-image-limits.per_hour': 'الحد الأقصى في الساعة',
        'core-ai-image-limits.self_portrait': 'اسمح لها بإعادة رسم نفسها',
        'core-ai-image-limits.reactions': 'اسمح لها بالتفاعل',
      }),
      zh: Object.freeze({
        'core-ai-image-limits.per_hour': '每小时最多',
        'core-ai-image-limits.self_portrait': '允许它重绘自己',
        'core-ai-image-limits.reactions': '允许它作出反应',
      }),
    }),
  }),
  render(input, helpers) {
    const { pictures } = input;
    const { fieldRow, i18n } = helpers;
    const t = (key, vars, fallback) => i18n.t(key, vars, fallback);
    const perHour = document.createElement('input');
    perHour.type = 'number';
    perHour.id = 'aiPerHour';
    perHour.min = '0';
    perHour.max = '200';
    perHour.className = 'days';
    perHour.value = String(pictures.per_hour);
    const perHourHelp =
      i18n.t(
        'ai.images_per_hour_help',
        {},
        'Across everything on this panel. A model that finds something amusing will keep doing it, and each one is billed — this is what stops a loop running up a bill before anybody notices. Zero switches picture-making off without losing the rest of these settings.',
      ) +
      (pictures.budget
        ? ` ${i18n.t('ai.images_per_hour_used', { count: pictures.budget.used }, '{{count, number}} made in the last hour.')}`
        : '');
    const self = document.createElement('input');
    self.type = 'checkbox';
    self.id = 'aiSelfPortrait';
    self.checked = pictures.self_portrait;
    const reactions = document.createElement('input');
    reactions.type = 'checkbox';
    reactions.id = 'aiReactions';
    reactions.checked = pictures.reactions;
    return [
      fieldRow(
        t('core-ai-image-limits.per_hour', {}, 'At most, per hour'),
        perHour,
        perHourHelp,
      ),
      fieldRow(
        t('core-ai-image-limits.self_portrait', {}, 'Let it redraw itself'),
        self,
        i18n.t(
          'ai.images_self_portrait_help',
          {},
          'A novelty: it can generate its own portrait, squared off and shrunk to a thumbnail. Shares the hourly limit and asks permission each time.',
        ),
      ),
      fieldRow(
        t('core-ai-image-limits.reactions', {}, 'Let it react'),
        reactions,
        i18n.t(
          'ai.images_reactions_help',
          {},
          'Short animated images, searched rather than generated, for the occasional aside. It chooses a mood, not an image — what comes back is whatever the catalogue returns.',
        ),
      ),
    ];
  },
};
