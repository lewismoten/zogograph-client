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
        'core-cpu-shell.reading_collector': 'Reading the host collector…',
        'core-cpu-shell.no_host_readings': 'No host readings.',
        'core-cpu-shell.no_breakdown':
          'No processor breakdown in this range yet — the collector records it from the version that added these pages onward',
        'core-cpu-shell.only_one_reading':
          'Only one reading so far — a percentage needs two',
        'core-cpu-shell.add_root_cron':
          "As root — ssh in or use WHM's Terminal — add this to root's crontab with crontab -e:",
        'core-cpu-shell.run_root_once':
          'Then run it once by hand to see what it finds: {{command}}',
      }),
      es: Object.freeze({
        'core-cpu-shell.help':
          'Cuánto trabaja el procesador y qué parte puede usar esta cuenta. Dentro de CageFS, el porcentaje propio de la cuenta corresponde a su asignación, no a la máquina; por eso una cuenta limitada y un servidor inactivo son la forma normal de alcanzar un límite, no una contradicción.',
        'core-cpu-shell.reading_collector': 'Leyendo el recolector del host…',
        'core-cpu-shell.no_host_readings': 'No hay lecturas del host.',
        'core-cpu-shell.no_breakdown':
          'Aún no hay desglose del procesador en este intervalo; el recolector lo registra desde la versión que añadió estas páginas',
        'core-cpu-shell.only_one_reading':
          'Solo hay una lectura: un porcentaje necesita dos',
        'core-cpu-shell.add_root_cron':
          'Como root — conéctese por SSH o use la Terminal de WHM — añada esto al crontab de root con crontab -e:',
        'core-cpu-shell.run_root_once':
          'Después ejecútelo una vez a mano para ver qué encuentra: {{command}}',
      }),
      hi: Object.freeze({
        'core-cpu-shell.help':
          'प्रोसेसर कितनी मेहनत कर रहा है और यह खाता उसका कितना उपयोग कर सकता है। CageFS के अंदर खाते का प्रतिशत मशीन का नहीं, उसके आवंटन का होता है; इसलिए सीमित खाता और निष्क्रिय सर्वर किसी सीमा पर पहुँचने का सामान्य रूप हैं, विरोधाभास नहीं।',
        'core-cpu-shell.reading_collector': 'होस्ट कलेक्टर पढ़ा जा रहा है…',
        'core-cpu-shell.no_host_readings': 'कोई होस्ट रीडिंग नहीं है।',
        'core-cpu-shell.no_breakdown':
          'इस अवधि में अभी प्रोसेसर का विभाजन नहीं है — कलेक्टर इसे इन पृष्ठों को जोड़ने वाले संस्करण से रिकॉर्ड करता है',
        'core-cpu-shell.only_one_reading':
          'अभी केवल एक रीडिंग है — प्रतिशत के लिए दो चाहिए',
        'core-cpu-shell.add_root_cron':
          'root के रूप में — SSH करें या WHM Terminal का उपयोग करें — इसे crontab -e से root के crontab में जोड़ें:',
        'core-cpu-shell.run_root_once':
          'फिर यह देखने के लिए इसे एक बार हाथ से चलाएँ कि इसे क्या मिलता है: {{command}}',
      }),
      ar: Object.freeze({
        'core-cpu-shell.help':
          'مدى انشغال المعالج، ومقدار ما قد يستخدمه هذا الحساب منه. داخل CageFS تكون نسبة الحساب من حصته لا من الآلة؛ لذا فالحساب المقيّد والخادم الخامل هما الصورة المعتادة لبلوغ حد، لا تناقضًا.',
        'core-cpu-shell.reading_collector': 'تجري قراءة جامع المضيف…',
        'core-cpu-shell.no_host_readings': 'لا توجد قراءات للمضيف.',
        'core-cpu-shell.no_breakdown':
          'لا يوجد تفصيل للمعالج في هذا النطاق بعد — يسجله الجامع بدءًا من الإصدار الذي أضاف هذه الصفحات',
        'core-cpu-shell.only_one_reading':
          'توجد قراءة واحدة فقط حتى الآن — النسبة المئوية تحتاج إلى اثنتين',
        'core-cpu-shell.add_root_cron':
          'بصفتك root — اتصل عبر SSH أو استخدم طرفية WHM — أضف هذا إلى crontab الخاص بـ root بواسطة crontab -e:',
        'core-cpu-shell.run_root_once':
          'ثم شغّله يدويًا مرة واحدة لترى ما يجده: {{command}}',
      }),
      zh: Object.freeze({
        'core-cpu-shell.help':
          '处理器的工作强度，以及此账户可使用多少。CageFS 内账户自身的百分比是其配额的百分比，而不是整台机器的百分比；因此账户被限制而服务器空闲，是碰到限制的正常表现，并不矛盾。',
        'core-cpu-shell.reading_collector': '正在读取主机收集器…',
        'core-cpu-shell.no_host_readings': '没有主机读数。',
        'core-cpu-shell.no_breakdown':
          '此范围内尚无处理器明细——收集器从添加这些页面的版本起才记录它',
        'core-cpu-shell.only_one_reading': '目前只有一个读数——百分比需要两个',
        'core-cpu-shell.add_root_cron':
          '请以 root 身份通过 SSH 或 WHM Terminal 将以下内容用 crontab -e 添加到 root 的 crontab：',
        'core-cpu-shell.run_root_once':
          '然后手动运行一次，查看它发现了什么：{{command}}',
      }),
    }),
  }),
  text(key, valuesOrHelpers = {}, maybeHelpers = null) {
    const helpers = maybeHelpers || valuesOrHelpers;
    const values = maybeHelpers ? valuesOrHelpers : {};
    return helpers.i18n.t(
      `core-cpu-shell.${key}`,
      values,
      this.manifest.catalogs.en[`core-cpu-shell.${key}`],
    );
  },
  render() {},
};
