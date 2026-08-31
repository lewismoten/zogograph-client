/* Core card package: disclosure copy for the page-review chat tool. */
window.ZogographChatPageReviewCard = {
  manifest: Object.freeze({
    id: 'core-chat-page-review',
    locales: ['core-chat-page-review.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-chat-page-review.title': 'Review page HTML',
        'core-chat-page-review.requested_reason':
          '{{assistant}} asked for the page structure: {{reason}}',
        'core-chat-page-review.requested':
          '{{assistant}} asked for the current page structure.',
        'core-chat-page-review.help':
          'Review this before sending it. Delete or replace any element or text you do not want shared.',
        'core-chat-page-review.redacted': '[redacted]',
      }),
      es: Object.freeze({
        'core-chat-page-review.title': 'Revisar el HTML de la página',
        'core-chat-page-review.requested_reason':
          '{{assistant}} solicitó la estructura de la página: {{reason}}',
        'core-chat-page-review.requested':
          '{{assistant}} solicitó la estructura de la página actual.',
        'core-chat-page-review.help':
          'Revise esto antes de enviarlo. Elimine o sustituya cualquier elemento o texto que no quiera compartir.',
        'core-chat-page-review.redacted': '[censurado]',
      }),
      hi: Object.freeze({
        'core-chat-page-review.title': 'पृष्ठ HTML की समीक्षा करें',
        'core-chat-page-review.requested_reason':
          '{{assistant}} ने पृष्ठ संरचना मांगी: {{reason}}',
        'core-chat-page-review.requested':
          '{{assistant}} ने वर्तमान पृष्ठ संरचना मांगी।',
        'core-chat-page-review.help':
          'इसे भेजने से पहले इसकी समीक्षा करें। ऐसा कोई भी तत्व या पाठ हटाएँ या बदलें जिसे आप साझा नहीं करना चाहते।',
        'core-chat-page-review.redacted': '[गोपित]',
      }),
      ar: Object.freeze({
        'core-chat-page-review.title': 'مراجعة HTML الصفحة',
        'core-chat-page-review.requested_reason':
          'طلب {{assistant}} بنية الصفحة: {{reason}}',
        'core-chat-page-review.requested':
          'طلب {{assistant}} بنية الصفحة الحالية.',
        'core-chat-page-review.help':
          'راجع هذا قبل إرساله. احذف أو استبدل أي عنصر أو نص لا تريد مشاركته.',
        'core-chat-page-review.redacted': '[منقح]',
      }),
      zh: Object.freeze({
        'core-chat-page-review.title': '查看页面 HTML',
        'core-chat-page-review.requested_reason':
          '{{assistant}} 请求查看页面结构：{{reason}}',
        'core-chat-page-review.requested':
          '{{assistant}} 请求查看当前页面结构。',
        'core-chat-page-review.help':
          '发送前请先查看。删除或替换任何您不想共享的元素或文本。',
        'core-chat-page-review.redacted': '[已涂黑]',
      }),
    }),
  }),
  render(input, helpers) {
    return helpers.i18n.t(
      `core-chat-page-review.${input.key}`,
      input.values || {},
      input.fallback,
    );
  },
};
