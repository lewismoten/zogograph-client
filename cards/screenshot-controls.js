/* Core card package: copy used while redacting a screenshot before it leaves the browser. */
window.ZogographScreenshotControlsCard = {
  manifest: Object.freeze({
    id: 'core-screenshot-controls',
    locales: ['core-screenshot-controls.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-screenshot-controls.before_send': 'Before this is sent',
        'core-screenshot-controls.do_not_send': 'Do not send it',
        'core-screenshot-controls.redact': 'Black something out',
        'core-screenshot-controls.crop': 'Keep only part of it',
        'core-screenshot-controls.remove': 'Remove this one',
        'core-screenshot-controls.uncrop': 'Show all of it again',
        'core-screenshot-controls.send': 'Send this',
        'core-screenshot-controls.drag_selected':
          'Drag it to move, drag a corner to resize, or press Delete to remove it.',
        'core-screenshot-controls.drag_redact':
          'Drag a box over anything that should not be sent. You can move, resize or remove it afterwards — nothing is fixed until you send.',
        'core-screenshot-controls.drag_crop':
          'Drag a box around the part worth sending. Everything outside it is left out, and you can undo it.',
        'core-screenshot-controls.areas_redacted':
          '{{count, number}} area blacked out',
        'core-screenshot-controls.cropped_dimensions':
          'cropped to {{width, number}}×{{height, number}}',
        'core-screenshot-controls.redacted': 'Redacted',
        'core-screenshot-controls.unsupported':
          'This browser cannot take a screenshot from a page.',
      }),
      es: Object.freeze({
        'core-screenshot-controls.before_send': 'Antes de enviar esto',
        'core-screenshot-controls.do_not_send': 'No enviarlo',
        'core-screenshot-controls.redact': 'Ocultar algo',
        'core-screenshot-controls.crop': 'Conservar solo una parte',
        'core-screenshot-controls.remove': 'Eliminar esta',
        'core-screenshot-controls.uncrop': 'Mostrarla completa de nuevo',
        'core-screenshot-controls.send': 'Enviar esta',
        'core-screenshot-controls.drag_selected':
          'Arrástrelo para moverlo, arrastre una esquina para cambiar su tamaño o pulse Supr para eliminarlo.',
        'core-screenshot-controls.drag_redact':
          'Arrastre un cuadro sobre cualquier cosa que no deba enviarse. Después puede moverlo, cambiar su tamaño o eliminarlo; nada queda fijado hasta enviarlo.',
        'core-screenshot-controls.drag_crop':
          'Arrastre un cuadro alrededor de la parte que merece enviarse. Todo lo que quede fuera se omite y puede deshacerlo.',
        'core-screenshot-controls.areas_redacted':
          '{{count, number}} área oculta',
        'core-screenshot-controls.cropped_dimensions':
          'recortado a {{width, number}}×{{height, number}}',
        'core-screenshot-controls.redacted': 'Censurado',
        'core-screenshot-controls.unsupported':
          'Este navegador no puede tomar una captura de pantalla desde una página.',
      }),
      hi: Object.freeze({
        'core-screenshot-controls.before_send': 'इसे भेजने से पहले',
        'core-screenshot-controls.do_not_send': 'इसे न भेजें',
        'core-screenshot-controls.redact': 'कुछ काला करें',
        'core-screenshot-controls.crop': 'केवल इसका हिस्सा रखें',
        'core-screenshot-controls.remove': 'इसे हटाएँ',
        'core-screenshot-controls.uncrop': 'फिर से पूरा दिखाएँ',
        'core-screenshot-controls.send': 'इसे भेजें',
        'core-screenshot-controls.drag_selected':
          'इसे खिसकाने के लिए खींचें, आकार बदलने के लिए कोना खींचें या हटाने के लिए Delete दबाएं।',
        'core-screenshot-controls.drag_redact':
          'जिसे नहीं भेजना चाहिए उसके ऊपर एक बॉक्स खींचें। बाद में उसे खिसका, आकार बदल या हटा सकते हैं — भेजने तक कुछ भी स्थायी नहीं है।',
        'core-screenshot-controls.drag_crop':
          'भेजने योग्य हिस्से के चारों ओर एक बॉक्स खींचें। उसके बाहर की हर चीज़ छोड़ दी जाती है और आप इसे पूर्ववत कर सकते हैं।',
        'core-screenshot-controls.areas_redacted':
          '{{count, number}} क्षेत्र काला किया गया',
        'core-screenshot-controls.cropped_dimensions':
          '{{width, number}}×{{height, number}} तक काटा गया',
        'core-screenshot-controls.redacted': 'गोपित',
        'core-screenshot-controls.unsupported':
          'यह ब्राउज़र किसी पृष्ठ से स्क्रीनशॉट नहीं ले सकता।',
      }),
      ar: Object.freeze({
        'core-screenshot-controls.before_send': 'قبل إرسال هذا',
        'core-screenshot-controls.do_not_send': 'لا ترسله',
        'core-screenshot-controls.redact': 'اطمس شيئًا',
        'core-screenshot-controls.crop': 'احتفظ بجزء منه فقط',
        'core-screenshot-controls.remove': 'أزل هذه',
        'core-screenshot-controls.uncrop': 'أظهرها كلها مجددًا',
        'core-screenshot-controls.send': 'أرسل هذه',
        'core-screenshot-controls.drag_selected':
          'اسحبه لتحريكه، أو اسحب زاوية لتغيير حجمه، أو اضغط Delete لإزالته.',
        'core-screenshot-controls.drag_redact':
          'اسحب مربعًا فوق أي شيء لا ينبغي إرساله. يمكنك تحريكه أو تغيير حجمه أو إزالته لاحقًا — لا شيء ثابت حتى ترسله.',
        'core-screenshot-controls.drag_crop':
          'اسحب مربعًا حول الجزء الجدير بالإرسال. يُترك كل ما خارجه ويمكنك التراجع عنه.',
        'core-screenshot-controls.areas_redacted':
          'مُنعت {{count, number}} منطقة',
        'core-screenshot-controls.cropped_dimensions':
          'اقتصّت إلى {{width, number}}×{{height, number}}',
        'core-screenshot-controls.redacted': 'منقح',
        'core-screenshot-controls.unsupported':
          'لا يستطيع هذا المتصفح أخذ لقطة شاشة من صفحة.',
      }),
      zh: Object.freeze({
        'core-screenshot-controls.before_send': '发送前',
        'core-screenshot-controls.do_not_send': '不要发送',
        'core-screenshot-controls.redact': '涂黑部分内容',
        'core-screenshot-controls.crop': '仅保留其中一部分',
        'core-screenshot-controls.remove': '移除这一张',
        'core-screenshot-controls.uncrop': '再次显示全部内容',
        'core-screenshot-controls.send': '发送这一张',
        'core-screenshot-controls.drag_selected':
          '拖动以移动，拖动角落以调整大小，或按 Delete 键删除它。',
        'core-screenshot-controls.drag_redact':
          '在不应发送的内容上拖出一个方框。之后可以移动、调整大小或删除它——在发送前不会固定任何内容。',
        'core-screenshot-controls.drag_crop':
          '围绕值得发送的部分拖出一个方框。其外部的所有内容都会被排除，且您可以撤消操作。',
        'core-screenshot-controls.areas_redacted':
          '已涂黑 {{count, number}} 个区域',
        'core-screenshot-controls.cropped_dimensions':
          '已裁剪为 {{width, number}}×{{height, number}}',
        'core-screenshot-controls.redacted': '已涂黑',
        'core-screenshot-controls.unsupported':
          '此浏览器无法从页面截取屏幕截图。',
      }),
    }),
  }),
  render(input, helpers) {
    return helpers.i18n.t(
      `core-screenshot-controls.${input.key}`,
      input.values,
      input.fallback,
    );
  },
};
