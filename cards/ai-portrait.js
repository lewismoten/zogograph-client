/* Core card package: the current AI portrait and its retention-aware clear action. */
window.ZogographAiPortraitCard = {
  manifest: Object.freeze({
    id: 'core-ai-portrait',
    locales: ['core-ai-portrait.'],
    helpers: [
      'fieldRow',
      'ago',
      'askFirst',
      'post',
      'renderAiSettings',
      'notice',
      'aiPortraitAlt',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-portrait.clear': 'Clear this portrait',
        'core-ai-portrait.cleared': 'Cleared.',
        'core-ai-portrait.label': 'Portrait',
        'core-ai-portrait.help':
          'It drew this itself, the last time it was asked to. Turning off self-portraits below stops it drawing another; this one stays until it does.',
        'core-ai-portrait.caption':
          'Drawn {{elapsed}}, from: “{{description}}”',
        'core-ai-portrait.clear_title': 'Clear the portrait?',
        'core-ai-portrait.clear_hide':
          'The picture stops being shown and the emoji takes its place until it draws another one.',
        'core-ai-portrait.clear_retention':
          'The record of it stays in the image log — it was generated and it cost something, and that is worth keeping whether or not the picture is shown.',
      }),
      es: Object.freeze({
        'core-ai-portrait.clear': 'Borrar este retrato',
        'core-ai-portrait.cleared': 'Borrado.',
        'core-ai-portrait.label': 'Retrato',
        'core-ai-portrait.help':
          'Lo dibujó por sí mismo la última vez que se lo pidieron. Desactivar los autorretratos a continuación evita que dibuje otro; este se conserva hasta entonces.',
        'core-ai-portrait.caption':
          'Dibujado {{elapsed}}, a partir de: “{{description}}”',
        'core-ai-portrait.clear_title': '¿Quitar el retrato?',
        'core-ai-portrait.clear_hide':
          'La imagen dejará de mostrarse y el emoji ocupará su lugar hasta que se dibuje otra.',
        'core-ai-portrait.clear_retention':
          'El registro permanece en el historial de imágenes: se generó y tuvo un costo, y vale la pena conservarlo aunque la imagen deje de mostrarse.',
      }),
      hi: Object.freeze({
        'core-ai-portrait.clear': 'यह चित्र हटाएँ',
        'core-ai-portrait.cleared': 'हटा दिया गया।',
        'core-ai-portrait.label': 'चित्र',
        'core-ai-portrait.help':
          'पिछली बार कहने पर इसने इसे स्वयं बनाया था। नीचे स्वयं-चित्र बंद करने से यह दूसरा नहीं बनाएगा; यह तब तक रहेगा।',
        'core-ai-portrait.caption':
          '{{elapsed}} पहले बनाया गया, स्रोत: “{{description}}”',
        'core-ai-portrait.clear_title': 'चित्र हटाएँ?',
        'core-ai-portrait.clear_hide':
          'चित्र दिखना बंद हो जाएगा और दूसरा चित्र बनने तक उसकी जगह इमोजी दिखेगा।',
        'core-ai-portrait.clear_retention':
          'इसका रिकॉर्ड छवि लॉग में बना रहता है—इसे बनाया गया था और इसकी लागत थी, इसलिए चित्र न दिखने पर भी उसे रखना उपयोगी है।',
      }),
      ar: Object.freeze({
        'core-ai-portrait.clear': 'امسح هذه الصورة',
        'core-ai-portrait.cleared': 'تم المسح.',
        'core-ai-portrait.label': 'صورة شخصية',
        'core-ai-portrait.help':
          'رسم هذه الصورة لنفسه في آخر مرة طُلب منه ذلك. إيقاف الصور الذاتية أدناه يمنعه من رسم أخرى؛ وتبقى هذه حتى يفعل ذلك.',
        'core-ai-portrait.caption': 'رُسم {{elapsed}}، من: “{{description}}”',
        'core-ai-portrait.clear_title': 'هل تمسح الصورة الشخصية؟',
        'core-ai-portrait.clear_hide':
          'تتوقف الصورة عن الظهور ويأخذ الرمز التعبيري مكانها إلى أن يرسم صورة أخرى.',
        'core-ai-portrait.clear_retention':
          'يبقى سجلها في سجل الصور—فقد أُنشئت وكلفت شيئًا، ومن المفيد الاحتفاظ بذلك سواء ظهرت الصورة أم لا.',
      }),
      zh: Object.freeze({
        'core-ai-portrait.clear': '清除此肖像',
        'core-ai-portrait.cleared': '已清除。',
        'core-ai-portrait.label': '肖像',
        'core-ai-portrait.help':
          '这是它上次被要求时为自己绘制的。关闭下面的自画像会阻止它再画一张；在那之前这张会保留。',
        'core-ai-portrait.caption':
          '绘制于 {{elapsed}}，来源：“{{description}}”',
        'core-ai-portrait.clear_title': '清除肖像？',
        'core-ai-portrait.clear_hide':
          '图片将不再显示，表情符号会取而代之，直到它绘制出另一张图片。',
        'core-ai-portrait.clear_retention':
          '其记录仍保留在图像日志中——它曾被生成且产生了成本，无论图片是否显示，都值得保留该记录。',
      }),
    }),
  }),
  render(input, helpers) {
    const { who } = input;
    const {
      fieldRow,
      ago,
      askFirst,
      post,
      renderAiSettings,
      notice,
      aiPortraitAlt,
      i18n,
    } = helpers;
    const portrait = who.portrait;
    const face = document.createElement('div');
    face.className = 'portrait-block';
    const image = document.createElement('img');
    image.className = 'portrait';
    image.src = portrait.url;
    image.alt = aiPortraitAlt(who.name);
    face.appendChild(image);
    const caption = document.createElement('div');
    caption.className = 'muted small';
    caption.textContent = i18n.t(
      'core-ai-portrait.caption',
      { elapsed: ago(portrait.at), description: portrait.as },
      `Drawn ${ago(portrait.at)}, from: "${portrait.as}"`,
    );
    face.appendChild(caption);
    const clear = document.createElement('button');
    clear.type = 'button';
    clear.className = 'link danger';
    clear.textContent = i18n.t(
      'core-ai-portrait.clear',
      {},
      'Clear this portrait',
    );
    clear.addEventListener('click', async () => {
      const yes = await askFirst(
        i18n.t('core-ai-portrait.clear_title', {}, 'Clear the portrait?'),
        [
          i18n.t(
            'core-ai-portrait.clear_hide',
            {},
            'The picture stops being shown and the emoji takes its place until it draws another one.',
          ),
          i18n.t(
            'core-ai-portrait.clear_retention',
            {},
            'The record of it stays in the image log — it was generated and it cost something, and that is worth keeping whether or not the picture is shown.',
          ),
        ],
        i18n.t('notes.clear', {}, 'Clear it'),
        true,
      );
      if (!yes) return;
      try {
        renderAiSettings(await post('ai-portrait-clear', {}));
        notice(i18n.t('core-ai-portrait.cleared', {}, 'Cleared.'), true);
      } catch (error) {
        notice(String(error.message || error));
      }
    });
    face.appendChild(clear);
    return fieldRow(
      i18n.t('core-ai-portrait.label', {}, 'Portrait'),
      face,
      i18n.t(
        'core-ai-portrait.help',
        {},
        'It drew this itself, the last time it was asked to. Turning off self-portraits below stops it drawing another; this one stays until it does.',
      ),
    );
  },
};
