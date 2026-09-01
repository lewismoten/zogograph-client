/* Core card package: changes recorded over the selected observation window. */
window.ZogographChangeHistoryCard = {
  manifest: Object.freeze({
    id: 'core-change-history',
    locales: ['core-change-history.'],
    helpers: ['target', 'note', 'renderTable', 'whenSpan', 'ago', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-change-history.title': 'What changed underneath',
        'core-change-history.showing': 'Showing',
        'core-change-history.groups_title': 'What changed, by group',
        'core-change-history.empty_checked':
          'Nothing has changed in this range. Last checked {{when}}.',
        'core-change-history.empty_unchecked':
          'Nothing has been checked yet. Press Check now, or open What the host allows.',
        'core-change-history.noticed': 'Noticed',
        'core-change-history.what': 'What',
        'core-change-history.was': 'Was',
        'core-change-history.became': 'Became',
        'core-change-history.by_fingerprint': 'by fingerprint',
        'core-change-history.checked':
          'Checked. Anything that moved since last time is listed above.',
        'core-change-history.help':
          'Almost every {{working_yesterday}} turns out to be something changing underneath — a kernel that came with a package update, an OpenSSL bump that moved a default, a PHP point release that deprecated a function. None of it shows in any other chart here, and the machine does not keep a usable note of it either: the package log belongs to root, is not always present, and records what was {{installed}} rather than what is now answering. Every time on this page is when a change was {{noticed}}, not when it happened — this runs when capabilities are checked, so a weekly check turns a Tuesday upgrade into a Sunday event.',
        'core-change-history.groups_help':
          'Each bar counts the programs whose availability or version moved, coloured by group. Click one to see exactly what. This was on {{capabilities}}, which is a page about what is available {{now}}; a history of changes belongs beside the rest of the history.',
      }),
      es: Object.freeze({
        'core-change-history.title': 'Qué cambió por debajo',
        'core-change-history.showing': 'Mostrando',
        'core-change-history.groups_title': 'Qué cambió, por grupo',
        'core-change-history.empty_checked':
          'No ha cambiado nada en este intervalo. Última comprobación {{when}}.',
        'core-change-history.empty_unchecked':
          'Aún no se ha comprobado nada. Pulse Comprobar ahora o abra Lo que permite el host.',
        'core-change-history.noticed': 'Detectado',
        'core-change-history.what': 'Qué',
        'core-change-history.was': 'Era',
        'core-change-history.became': 'Se convirtió en',
        'core-change-history.by_fingerprint': 'por huella digital',
        'core-change-history.checked':
          'Comprobado. Todo lo que cambió desde la última vez aparece arriba.',
        'core-change-history.help':
          'Casi todo «{{working_yesterday}}» resulta ser algo que cambió por debajo: un kernel que llegó con una actualización de paquete, un aumento de OpenSSL que movió un valor predeterminado, una versión menor de PHP que dejó obsoleta una función. Nada de eso aparece en otro gráfico, y la máquina tampoco conserva una nota útil: el registro de paquetes pertenece a root, no siempre está presente y registra lo que se {{installed}}, no lo que responde ahora. Cada hora de esta página es cuando se {{noticed}} un cambio, no cuando sucedió: esto se ejecuta al comprobar capacidades, por lo que una comprobación semanal convierte una actualización del martes en un evento del domingo.',
        'core-change-history.groups_help':
          'Cada barra cuenta los programas cuya disponibilidad o versión cambió, por color de grupo. Haga clic en una para ver exactamente qué. Esto estaba en {{capabilities}}, una página sobre lo que está disponible {{now}}; el historial de cambios pertenece junto al resto del historial.',
      }),
      hi: Object.freeze({
        'core-change-history.title': 'नीचे क्या बदला',
        'core-change-history.showing': 'दिखा रहा है',
        'core-change-history.groups_title': 'समूह के अनुसार क्या बदला',
        'core-change-history.empty_checked':
          'इस अवधि में कुछ नहीं बदला। आखिरी जाँच {{when}}।',
        'core-change-history.empty_unchecked':
          'अभी कुछ जाँचा नहीं गया है। अभी जाँचें दबाएँ या होस्ट क्या अनुमति देता है खोलें।',
        'core-change-history.noticed': 'देखा गया',
        'core-change-history.what': 'क्या',
        'core-change-history.was': 'था',
        'core-change-history.became': 'बना',
        'core-change-history.by_fingerprint': 'फ़िंगरप्रिंट द्वारा',
        'core-change-history.checked':
          'जाँच हो गई। पिछली बार से बदली हर चीज़ ऊपर सूचीबद्ध है।',
        'core-change-history.help':
          'लगभग हर {{working_yesterday}} के पीछे नीचे कुछ बदलना निकलता है—पैकेज अपडेट के साथ आया कर्नेल, डिफ़ॉल्ट बदलने वाला OpenSSL बंप, किसी फ़ंक्शन को deprecated करने वाला PHP पॉइंट रिलीज़। यह किसी दूसरे चार्ट में नहीं दिखता और मशीन इसका उपयोगी नोट भी नहीं रखती: पैकेज लॉग root का है, हमेशा मौजूद नहीं और बताता है क्या {{installed}} था, न कि अब क्या उत्तर दे रहा है। इस पृष्ठ का हर समय वह है जब बदलाव {{noticed}} हुआ, न कि जब हुआ—यह क्षमताएँ जाँचने पर चलता है, इसलिए साप्ताहिक जाँच मंगलवार का अपग्रेड रविवार की घटना बना देती है।',
        'core-change-history.groups_help':
          'हर बार उन प्रोग्रामों की गिनती करता है जिनकी उपलब्धता या संस्करण बदला, समूह के रंग से। ठीक क्या जानने को एक पर क्लिक करें। यह {{capabilities}} पर था, जो {{now}} उपलब्ध चीज़ों का पृष्ठ है; बदलाव का इतिहास बाकी इतिहास के पास होना चाहिए।',
      }),
      ar: Object.freeze({
        'core-change-history.title': 'ما الذي تغير في الأسفل',
        'core-change-history.showing': 'يعرض',
        'core-change-history.groups_title': 'ما الذي تغير، حسب المجموعة',
        'core-change-history.empty_checked':
          'لم يتغير شيء في هذه المدة. آخر فحص {{when}}.',
        'core-change-history.empty_unchecked':
          'لم يُفحص شيء بعد. اضغط افحص الآن أو افتح ما يسمح به المضيف.',
        'core-change-history.noticed': 'لوحظ',
        'core-change-history.what': 'ما',
        'core-change-history.was': 'كان',
        'core-change-history.became': 'أصبح',
        'core-change-history.by_fingerprint': 'بصمة الملف',
        'core-change-history.checked':
          'اكتمل التحقق. كل ما تغير منذ المرة السابقة مدرج أعلاه.',
        'core-change-history.help':
          'يكاد كل {{working_yesterday}} يتبين أنه شيء يتغير في الأسفل — نواة جاءت مع تحديث حزمة، أو ترقية OpenSSL غيرت افتراضيًا، أو إصدار PHP نقطي أهمل دالة. لا يظهر شيء من ذلك في أي مخطط آخر هنا، ولا تحتفظ الآلة بملاحظة قابلة للاستخدام عنه: سجل الحزم يخص root وليس موجودًا دائمًا، ويسجل ما {{installed}} بدل ما يجيب الآن. كل وقت في هذه الصفحة هو حين كان التغيير {{noticed}}، لا حين حدث — فهذا يعمل عند فحص القدرات، لذا يحول فحص أسبوعي ترقية الثلاثاء إلى حدث يوم الأحد.',
        'core-change-history.groups_help':
          'كل شريط يعد البرامج التي تغير توفرها أو إصدارها، ملونة حسب المجموعة. انقر على واحد لترى ما حدث بالضبط. كان هذا في {{capabilities}}، وهي صفحة عن ما هو متاح {{now}}؛ تاريخ التغييرات مكانه إلى جوار بقية التاريخ.',
      }),
      zh: Object.freeze({
        'core-change-history.title': '底层发生了什么变化',
        'core-change-history.showing': '正在显示',
        'core-change-history.groups_title': '按组查看变化',
        'core-change-history.empty_checked':
          '此时间范围内没有任何变化。上次检查 {{when}}。',
        'core-change-history.empty_unchecked':
          '尚未进行检查。请按“立即检查”，或打开“主机允许的内容”。',
        'core-change-history.noticed': '发现时间',
        'core-change-history.what': '内容',
        'core-change-history.was': '原为',
        'core-change-history.became': '变为',
        'core-change-history.by_fingerprint': '通过指纹发现',
        'core-change-history.checked':
          '已检查。自上次以来发生变化的所有内容均列在上方。',
        'core-change-history.help':
          '几乎每一句“{{working_yesterday}}”最后都会变成底层的某项变化：随软件包更新而来的内核、改变默认值的 OpenSSL 升级、弃用某个函数的 PHP 小版本更新。其他图表不会显示这些内容，机器也没有保留可用记录：软件包日志归 root 所有、不一定存在，而且记录的是已{{installed}}的内容，而不是当前正在响应的内容。本页的每个时间都是发现变化的{{noticed}}，而不是变化发生的时间——它在检查功能能力时运行，因此每周检查会把周二升级变成周日事件。',
        'core-change-history.groups_help':
          '每个柱形统计可用性或版本发生变化的程序，并按组着色。点击一个即可查看具体内容。这原本在{{capabilities}}上，它是关于当前{{now}}可用内容的页面；变化历史应放在其他历史内容旁边。',
      }),
    }),
  }),

  text(key, helpers) {
    return helpers.i18n.t(
      `core-change-history.${key}`,
      {},
      this.manifest.catalogs.en[`core-change-history.${key}`],
    );
  },

  render(data, helpers) {
    const { target, note, renderTable, whenSpan, ago, i18n } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (!target) return;
    if (!data.events?.length) {
      target.replaceChildren(
        note(
          data.last_checked
            ? t(
                'core-change-history.empty_checked',
                { when: ago(data.last_checked) },
                'Nothing has changed in this range. Last checked {{when}}.',
              )
            : t(
                'core-change-history.empty_unchecked',
                {},
                'Nothing has been checked yet. Press Check now, or open What the host allows.',
              ),
        ),
      );
      return;
    }
    const holder = document.createElement('div');
    holder.id = 'changedTable';
    target.replaceChildren(holder);
    renderTable(
      'changedTable',
      data.events,
      [
        {
          label: t('core-change-history.noticed', {}, 'Noticed'),
          render: (row) => whenSpan(row.at),
        },
        {
          label: t('core-change-history.what', {}, 'What'),
          className: 'name',
          render: (row) => {
            const span = document.createElement('span');
            span.textContent = row.label;
            span.title =
              row.kind === 'software'
                ? t('installed.system_software_tip')
                : t('installed.watched_program_tip', {
                    group: row.group ? ` (${row.group})` : '',
                  });
            return span;
          },
        },
        {
          label: t('core-change-history.was', {}, 'Was'),
          className: 'cmd',
          render: (row) => row.from ?? '—',
        },
        {
          label: t('core-change-history.became', {}, 'Became'),
          className: 'cmd',
          render: (row) => row.to,
        },
        {
          label: '',
          render: (row) => {
            if (row.state_from && row.state_from !== row.state_to) {
              const span = document.createElement('span');
              span.className =
                row.state_to === 'absent' ? 'band-crit' : 'band-ok';
              span.textContent = `${row.state_from} → ${row.state_to}`;
              return span;
            }
            if (row.by_fingerprint) {
              const span = document.createElement('span');
              span.className = 'muted small';
              span.textContent = t(
                'core-change-history.by_fingerprint',
                {},
                'by fingerprint',
              );
              span.title = t('installed.fingerprint_change_tip');
              return span;
            }
            return '';
          },
        },
      ],
      null,
      null,
      (row) => `${row.at}|${row.name}`,
    );
  },
};
