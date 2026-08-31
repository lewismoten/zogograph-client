/* Core card package: empty and capacity states for saved assistant memories. */
window.ZogographAiMemoryAvailabilityCard = {
  manifest: Object.freeze({
    id: 'core-ai-memory-availability',
    locales: ['core-ai-memory-availability.'],
    helpers: ['note', 'humanCount', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-memory-availability.empty':
          '{{name}} has not written anything down yet. It does that on its own when it works something out that will still be true next month — and you can add one yourself with the button above.',
        'core-ai-memory-availability.capacity':
          '{{count}} memories out of a possible {{maximum}}. Past that, the least useful are dropped — pinning one keeps it whatever happens.',
      }),
      es: Object.freeze({
        'core-ai-memory-availability.empty':
          '{{name}} aún no ha anotado nada. Lo hace por sí solo cuando resuelve algo que seguirá siendo cierto el próximo mes, y usted puede añadir una con el botón de arriba.',
        'core-ai-memory-availability.capacity':
          '{{count}} memorias de un máximo de {{maximum}}. A partir de ahí se descartan las menos útiles; fijar una la conserva pase lo que pase.',
      }),
      hi: Object.freeze({
        'core-ai-memory-availability.empty':
          '{{name}} ने अभी तक कुछ भी नहीं लिखा है। जब वह ऐसी बात समझ लेता है जो अगले महीने भी सच रहेगी, तो वह इसे अपने आप लिख देता है — और आप ऊपर के बटन से स्वयं एक जोड़ सकते हैं।',
        'core-ai-memory-availability.capacity':
          'संभावित {{maximum}} में से {{count}} मेमोरी। इसके बाद सबसे कम उपयोगी मेमोरी हटा दी जाती हैं — किसी को पिन करने पर वह हर स्थिति में बनी रहती है।',
      }),
      ar: Object.freeze({
        'core-ai-memory-availability.empty':
          'لم يدوّن {{name}} أي شيء بعد. يفعل ذلك تلقائيًا عندما يتوصل إلى أمر سيظل صحيحًا الشهر القادم — ويمكنك إضافة واحدة بنفسك بالزر أعلاه.',
        'core-ai-memory-availability.capacity':
          '{{count}} من الذكريات من أصل {{maximum}} ممكنة. بعد ذلك تُحذف الأقل فائدة — وتثبيت واحدة يبقيها مهما حدث.',
      }),
      zh: Object.freeze({
        'core-ai-memory-availability.empty':
          '{{name}} 尚未记下任何内容。它在自行推断出下个月仍然成立的事情时会这样做——您也可以用上方按钮自行添加一条。',
        'core-ai-memory-availability.capacity':
          '{{maximum}} 条可用记忆中已有 {{count}} 条。超过后会丢弃最没用的内容——固定一条会始终保留它。',
      }),
    }),
  }),
  render(data, helpers) {
    const t = (key, values, fallback) =>
      helpers.i18n.t(`core-ai-memory-availability.${key}`, values, fallback);
    if (!data.memories.length)
      return helpers.note(
        t(
          'empty',
          { name: data.who.name },
          '{{name}} has not written anything down yet. It does that on its own when it works something out that will still be true next month — and you can add one yourself with the button above.',
        ),
      );
    const said = document.createElement('p');
    said.className = 'muted small';
    said.textContent = t(
      'capacity',
      { count: data.memories.length, maximum: helpers.humanCount(data.max) },
      '{{count}} memories out of a possible {{maximum}}. Past that, the least useful are dropped — pinning one keeps it whatever happens.',
    );
    return said;
  },
};
