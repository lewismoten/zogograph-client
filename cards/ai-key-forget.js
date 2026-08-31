/* Core card package: remove the credential saved for an AI provider. */
window.ZogographAiKeyForgetCard = {
  manifest: Object.freeze({
    id: 'core-ai-key-forget',
    locales: ['core-ai-key-forget.'],
    helpers: ['fieldRow', 'confirmDialog', 'onForget', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-key-forget.action': 'Forget the key',
        'core-ai-key-forget.confirm':
          'Remove the stored API key? The assistant will stop working until another is set.',
        'core-ai-key-forget.title': 'Forget key',
      }),
      es: Object.freeze({
        'core-ai-key-forget.action': 'Olvidar la clave',
        'core-ai-key-forget.confirm':
          '¿Eliminar la clave de API guardada? El asistente dejará de funcionar hasta que se configure otra.',
        'core-ai-key-forget.title': 'Olvidar clave',
      }),
      hi: Object.freeze({
        'core-ai-key-forget.action': 'कुंजी हटाएँ',
        'core-ai-key-forget.confirm':
          'सहेजी हुई API कुंजी हटाएँ? दूसरी कुंजी सेट होने तक सहायक काम नहीं करेगा।',
        'core-ai-key-forget.title': 'कुंजी हटाएँ',
      }),
      ar: Object.freeze({
        'core-ai-key-forget.action': 'انسَ المفتاح',
        'core-ai-key-forget.confirm':
          'أزل مفتاح API المحفوظ؟ سيتوقف المساعد عن العمل حتى تضبط مفتاحًا آخر.',
        'core-ai-key-forget.title': 'انسَ المفتاح',
      }),
      zh: Object.freeze({
        'core-ai-key-forget.action': '忘记此密钥',
        'core-ai-key-forget.confirm':
          '移除已保存的 API 密钥？在设置另一个密钥之前，助手将无法工作。',
        'core-ai-key-forget.title': '忘记密钥',
      }),
    }),
  }),
  render(data, helpers) {
    if (!data.hasKey) return null;
    const t = (key, fallback) =>
      helpers.i18n.t(`core-ai-key-forget.${key}`, {}, fallback);
    const forget = document.createElement('button');
    forget.type = 'button';
    forget.className = 'link danger';
    forget.textContent = t('action', 'Forget the key');
    forget.addEventListener('click', async () => {
      if (
        !(await helpers.confirmDialog(
          t(
            'confirm',
            'Remove the stored API key? The assistant will stop working until another is set.',
          ),
          t('title', 'Forget key'),
        ))
      )
        return;
      await helpers.onForget();
    });
    return helpers.fieldRow('', forget, '');
  },
};
