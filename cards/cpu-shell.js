/* Core package: static introduction for the CPU page. */
window.ZogographCpuShellCard = {
  manifest: Object.freeze({
    id: 'core-cpu-shell',
    locales: ['core-cpu-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-cpu-shell.help':
          'How hard the processor is working, and how much of it this account may use. Inside CageFS the account’s own percentage is of its allowance rather than of the machine, so a pinned account and an idle server are the ordinary shape of hitting a limit rather than a contradiction.',
      }),
      es: Object.freeze({
        'core-cpu-shell.help':
          'Cuánto trabaja el procesador y qué parte puede usar esta cuenta. Dentro de CageFS, el porcentaje propio de la cuenta corresponde a su asignación, no a la máquina; por eso una cuenta limitada y un servidor inactivo son la forma normal de alcanzar un límite, no una contradicción.',
      }),
      hi: Object.freeze({
        'core-cpu-shell.help':
          'प्रोसेसर कितनी मेहनत कर रहा है और यह खाता उसका कितना उपयोग कर सकता है। CageFS के अंदर खाते का प्रतिशत मशीन का नहीं, उसके आवंटन का होता है; इसलिए सीमित खाता और निष्क्रिय सर्वर किसी सीमा पर पहुँचने का सामान्य रूप हैं, विरोधाभास नहीं।',
      }),
      ar: Object.freeze({
        'core-cpu-shell.help':
          'مدى انشغال المعالج، ومقدار ما قد يستخدمه هذا الحساب منه. داخل CageFS تكون نسبة الحساب من حصته لا من الآلة؛ لذا فالحساب المقيّد والخادم الخامل هما الصورة المعتادة لبلوغ حد، لا تناقضًا.',
      }),
      zh: Object.freeze({
        'core-cpu-shell.help':
          '处理器的工作强度，以及此账户可使用多少。CageFS 内账户自身的百分比是其配额的百分比，而不是整台机器的百分比；因此账户被限制而服务器空闲，是碰到限制的正常表现，并不矛盾。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-cpu-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-cpu-shell.${key}`],
    );
  },
  render() {},
};
