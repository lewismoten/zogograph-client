/* Core card package: chat context-window explanation and compaction notice. */
window.ZogographChatContextCard = {
  manifest: Object.freeze({
    id: 'core-chat-context',
    locales: ['core-chat-context.'],
    helpers: ['count', 'humanCount', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-chat-context.automatic':
          'Full. The older half gets summarised on the next turn, automatically.\n\nClick to do it now instead.',
        'core-chat-context.manual':
          'Full, and automatic summarising is switched off — so this conversation will eventually be refused by the provider.\n\nClick to summarise it now.',
        'core-chat-context.space':
          'About {{percent}}% of the way to the point where the older half is summarised — roughly {{left}} more tokens.\n\nClick to summarise it now instead of waiting.',
        'core-chat-context.detail':
          "{{explanation}}\n\nNothing is deleted: the messages stay in the panel and in the record, and a marker shows where the model's view begins. About {{used}} of {{window}} tokens are in use, estimated from the length of the conversation rather than reported by the provider.",
        'core-chat-context.compacted':
          "{{messages}} summarised. They are still in the conversation — the dashed line shows where the model's view now begins, and opens onto the summary.",
      }),
      es: Object.freeze({
        'core-chat-context.automatic':
          'Lleno. La mitad más antigua se resumirá automáticamente en el siguiente turno.\n\nHaz clic para hacerlo ahora.',
        'core-chat-context.manual':
          'Está lleno y el resumen automático está desactivado, así que el proveedor acabará rechazando esta conversación.\n\nHaz clic para resumirla ahora.',
        'core-chat-context.space':
          'Se ha recorrido aproximadamente el {{percent}}% hasta resumir la mitad más antigua; quedan unos {{left}} tokens.\n\nHaz clic para resumirla ahora en vez de esperar.',
        'core-chat-context.detail':
          '{{explanation}}\n\nNo se elimina nada: los mensajes permanecen en el panel y el registro, y una marca muestra dónde empieza la vista del modelo. Se usan unos {{used}} de {{window}} tokens, calculados por la longitud de la conversación y no informados por el proveedor.',
        'core-chat-context.compacted':
          'Se resumieron {{messages}}. Aún están en la conversación; la línea discontinua muestra dónde empieza ahora la vista del modelo y lleva al resumen.',
      }),
      hi: Object.freeze({
        'core-chat-context.automatic':
          'पूरा भर गया है। पुराना आधा भाग अगले टर्न में अपने-आप सारांशित होगा।\n\nअभी करने के लिए क्लिक करें।',
        'core-chat-context.manual':
          'यह भर गया है और स्वचालित सारांश बंद है, इसलिए प्रदाता अंततः इस बातचीत को अस्वीकार कर देगा।\n\nअभी सारांशित करने के लिए क्लिक करें।',
        'core-chat-context.space':
          'पुराने आधे भाग के सारांशित होने के बिंदु तक लगभग {{percent}}% पहुंच चुके हैं — लगभग {{left}} और टोकन।\n\nप्रतीक्षा करने के बजाय अभी सारांशित करने के लिए क्लिक करें।',
        'core-chat-context.detail':
          '{{explanation}}\n\nकुछ भी हटाया नहीं जाता: संदेश पैनल और रिकॉर्ड में रहते हैं, और एक चिह्न बताता है कि मॉडल का दृश्य कहाँ से शुरू होता है। बातचीत की लंबाई से अनुमानित {{used}} में से {{window}} टोकन उपयोग में हैं।',
        'core-chat-context.compacted':
          '{{messages}} का सारांश बनाया गया। वे अभी भी बातचीत में हैं — टूटी रेखा दिखाती है कि मॉडल का दृश्य अब कहाँ से शुरू होता है और सारांश तक जाती है।',
      }),
      ar: Object.freeze({
        'core-chat-context.automatic':
          'ممتلئ. سيُلخّص النصف الأقدم تلقائيًا في الدور التالي.\n\nانقر لفعل ذلك الآن.',
        'core-chat-context.manual':
          'ممتلئ والتلخيص التلقائي معطّل، لذا سيرفض المزوّد هذه المحادثة في النهاية.\n\nانقر لتلخيصها الآن.',
        'core-chat-context.space':
          'تم الوصول إلى نحو {{percent}}٪ من نقطة تلخيص النصف الأقدم — يتبقى نحو {{left}} رمزًا.\n\nانقر للتلخيص الآن بدل الانتظار.',
        'core-chat-context.detail':
          '{{explanation}}\n\nلا يُحذف شيء: تبقى الرسائل في اللوحة والسجل، وتوضح علامة موضع بداية عرض النموذج. يُستخدم نحو {{used}} من {{window}} رمزًا، مقدّرة من طول المحادثة.',
        'core-chat-context.compacted':
          'لُخّص {{messages}}. ما زالت في المحادثة؛ يوضح الخط المتقطع موضع بداية عرض النموذج الآن ويقود إلى الملخص.',
      }),
      zh: Object.freeze({
        'core-chat-context.automatic':
          '已满。较早的一半会在下一轮自动摘要。\n\n点击即可现在执行。',
        'core-chat-context.manual':
          '已满，且自动摘要已关闭，因此提供商最终会拒绝此对话。\n\n点击即可现在摘要。',
        'core-chat-context.space':
          '距离摘要较早一半的阈值约为 {{percent}}%，还约有 {{left}} 个令牌。\n\n点击即可现在摘要，无需等待。',
        'core-chat-context.detail':
          '{{explanation}}\n\n不会删除任何内容：消息保留在面板和记录中，标记会显示模型视图的起点。当前约使用 {{used}} / {{window}} 个令牌，这是按对话长度估算的。',
        'core-chat-context.compacted':
          '已摘要 {{messages}}。它们仍在对话中；虚线显示模型视图现在从哪里开始，并通向摘要。',
      }),
    }),
  }),

  render(input, helpers) {
    const { count, humanCount, i18n } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (input.kind === 'compacted')
      return t(
        'core-chat-context.compacted',
        { messages: count(input.compacted, 'message') },
        "{{messages}} summarised. They are still in the conversation — the dashed line shows where the model's view now begins, and opens onto the summary.",
      );
    const { room } = input;
    const explanation = room.full
      ? t(
          room.automatic
            ? 'core-chat-context.automatic'
            : 'core-chat-context.manual',
          {},
          room.automatic
            ? 'Full. The older half gets summarised on the next turn, automatically.\n\nClick to do it now instead.'
            : 'Full, and automatic summarising is switched off — so this conversation will eventually be refused by the provider.\n\nClick to summarise it now.',
        )
      : t(
          'core-chat-context.space',
          {
            percent:
              room.soft > 0 ? Math.round((room.tokens / room.soft) * 100) : 0,
            left: humanCount(Math.max(0, room.soft - room.tokens)),
          },
          'About {{percent}}% of the way to the point where the older half is summarised — roughly {{left}} more tokens.\n\nClick to summarise it now instead of waiting.',
        );
    return t(
      'core-chat-context.detail',
      {
        explanation,
        used: humanCount(room.tokens),
        window: humanCount(room.window),
      },
      "{{explanation}}\n\nNothing is deleted: the messages stay in the panel and in the record, and a marker shows where the model's view begins. About {{used}} of {{window}} tokens are in use, estimated from the length of the conversation rather than reported by the provider.",
    );
  },
};
