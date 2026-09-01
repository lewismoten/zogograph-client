/* Core card package: queued and completed reverse-DNS lookups. */
window.ZogographNetworkRdnsCard = {
  manifest: Object.freeze({
    id: 'core-network-rdns',
    locales: ['core-network-rdns.'],
    helpers: [
      'target',
      'note',
      'section',
      'renderTable',
      'humanSpan',
      'ago',
      'post',
      'render',
      'notice',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-network-rdns.title': 'What the internet calls an address',
        'core-network-rdns.help':
          'A capture or an access log is a wall of numbers, and the same number means nothing on Monday and something on Friday once somebody has looked it up. This is not {{labels}} — that is what you decided an address means, and is small, hand-written and permanent. This is what the internet says it is: automatic, occasionally wrong, and stale within months.',
        'core-network-rdns.released':
          '{{count, number}} requests had been taken by a collector that never came back — a run killed mid-lookup — and have been put back in the queue.',
        'core-network-rdns.queue_heading':
          '{{waiting, number}} addresses waiting{{progress}}',
        'core-network-rdns.queue_progress': ', {{count, number}} in progress',
        'core-network-rdns.queue_drain':
          'At {{perRun, number}} per collector run, the queue empties in about {{duration}}.{{both}}',
        'core-network-rdns.both_collectors':
          ' Both collectors take one, so it may be quicker than that.',
        'core-network-rdns.empty': 'Nothing was waiting.',
        'core-network-rdns.looked_up': '{{count, number}} addresses looked up',
        'core-network-rdns.resolve_next': 'Resolve the next one now',
        'core-network-rdns.lookup_result':
          '{{address}} → {{name}} ({{milliseconds, number}} ms)',
        'core-network-rdns.lookup': 'Look these up',
        'core-network-rdns.ask_again': 'Ask again',
        'core-network-rdns.placeholder':
          '78.174.88.104, 2001:db8::1 — or paste a whole column',
        'core-network-rdns.no_name': 'no name',
        'core-network-rdns.nothing_waiting': 'Nothing was waiting.',
        'core-network-rdns.own_label_tip':
          'A name you gave this, under Settings → Your own names.',
        'core-network-rdns.name_tip':
          'What the internet says this address is called — a PTR record, which its owner controls and can set to anything.',
        'core-network-rdns.waiting_tip':
          'Waiting to be looked up. One is resolved per collector run.',
        'core-network-rdns.none_tip':
          'Looked up: this address has no PTR record, so it has no name to show. That is a real answer, not a failure.',
        'core-network-rdns.ask': 'name?',
        'core-network-rdns.ask_tip': 'Queue a reverse lookup for this address',
        'core-network-rdns.queued_tip':
          'Queued. One is resolved per collector run.',
      }),
      es: Object.freeze({
        'core-network-rdns.title': 'Cómo llama Internet a una dirección',
        'core-network-rdns.help':
          'Una captura o un registro de acceso es un muro de números, y el mismo número no significa nada el lunes y sí algo el viernes después de que alguien lo haya consultado. Esto no son {{labels}}: son lo que usted decidió que significa una dirección, pequeñas, escritas a mano y permanentes. Esto es lo que dice Internet que es: automático, ocasionalmente incorrecto y obsoleto en meses.',
        'core-network-rdns.released':
          '{{count, number}} solicitudes fueron tomadas por un recolector que no volvió y se han devuelto a la cola.',
        'core-network-rdns.queue_heading':
          '{{waiting, number}} direcciones esperando{{progress}}',
        'core-network-rdns.queue_progress': ', {{count, number}} en curso',
        'core-network-rdns.queue_drain':
          'Con {{perRun, number}} por ejecución del recolector, la cola termina en unos {{duration}}.{{both}}',
        'core-network-rdns.both_collectors':
          ' Ambos recolectores toman una, por lo que puede ser más rápido.',
        'core-network-rdns.empty': 'No había nada esperando.',
        'core-network-rdns.looked_up':
          '{{count, number}} direcciones consultadas',
        'core-network-rdns.resolve_next': 'Resolver la siguiente ahora',
        'core-network-rdns.lookup_result':
          '{{address}} → {{name}} ({{milliseconds, number}} ms)',
        'core-network-rdns.lookup': 'Buscar estas',
        'core-network-rdns.ask_again': 'Preguntar de nuevo',
        'core-network-rdns.placeholder':
          '78.174.88.104, 2001:db8::1 — o pegue una columna completa',
        'core-network-rdns.no_name': 'sin nombre',
        'core-network-rdns.nothing_waiting': 'No había nada en espera.',
        'core-network-rdns.own_label_tip':
          'Un nombre que le dio, en Configuración → Sus propios nombres.',
        'core-network-rdns.name_tip':
          'El nombre que Internet dice que tiene esta dirección: un registro PTR, cuyo propietario controla y puede configurar como quiera.',
        'core-network-rdns.waiting_tip':
          'Esperando consulta. Se resuelve una por ejecución del recopilador.',
        'core-network-rdns.none_tip':
          'Consultada: esta dirección no tiene registro PTR, así que no hay nombre que mostrar. Es una respuesta real, no un fallo.',
        'core-network-rdns.ask': '¿nombre?',
        'core-network-rdns.ask_tip':
          'Poner en cola una búsqueda inversa para esta dirección',
        'core-network-rdns.queued_tip':
          'En cola. Se resuelve una por ejecución del recopilador.',
      }),
      hi: Object.freeze({
        'core-network-rdns.title': 'इंटरनेट किसी पते को क्या कहता है',
        'core-network-rdns.help':
          'कैप्चर या एक्सेस लॉग संख्याओं की दीवार है और वही संख्या सोमवार को कुछ नहीं कहती, पर किसी के देखने के बाद शुक्रवार को कुछ बताती है। यह {{labels}} नहीं है—वह वह है जो आपने किसी पते के अर्थ के लिए तय किया है, छोटा, हाथ से लिखा और स्थायी। यह इंटरनेट के अनुसार उसका नाम है: स्वचालित, कभी-कभी गलत और महीनों में पुराना।',
        'core-network-rdns.released':
          '{{count, number}} अनुरोध ऐसे कलेक्टर ने लिए थे जो वापस नहीं आया; उन्हें कतार में वापस रखा गया है।',
        'core-network-rdns.queue_heading':
          '{{waiting, number}} पते प्रतीक्षारत{{progress}}',
        'core-network-rdns.queue_progress': ', {{count, number}} प्रगति में',
        'core-network-rdns.queue_drain':
          'हर कलेक्टर रन में {{perRun, number}} पर, कतार लगभग {{duration}} में खाली होगी।{{both}}',
        'core-network-rdns.both_collectors':
          ' दोनों कलेक्टर एक लेते हैं, इसलिए यह तेज़ हो सकता है।',
        'core-network-rdns.empty': 'कुछ भी प्रतीक्षारत नहीं था।',
        'core-network-rdns.looked_up': '{{count, number}} पते देखे गए',
        'core-network-rdns.resolve_next': 'अगले को अभी रिज़ॉल्व करें',
        'core-network-rdns.lookup_result':
          '{{address}} → {{name}} ({{milliseconds, number}} ms)',
        'core-network-rdns.lookup': 'इन्हें खोजें',
        'core-network-rdns.ask_again': 'फिर से पूछें',
        'core-network-rdns.placeholder':
          '78.174.88.104, 2001:db8::1 — या पूरा स्तंभ चिपकाएँ',
        'core-network-rdns.no_name': 'कोई नाम नहीं',
        'core-network-rdns.nothing_waiting': 'कोई भी प्रतीक्षा में नहीं था।',
        'core-network-rdns.own_label_tip':
          'आपके द्वारा दिया गया नाम, सेटिंग्स → आपके अपने नाम में।',
        'core-network-rdns.name_tip':
          'इंटरनेट के अनुसार इस पते का नाम — PTR रिकॉर्ड, जिसे उसका मालिक नियंत्रित करता है और कुछ भी सेट कर सकता है।',
        'core-network-rdns.waiting_tip':
          'देखे जाने की प्रतीक्षा है। हर कलेक्टर रन में एक हल किया जाता है।',
        'core-network-rdns.none_tip':
          'देख लिया गया: इस पते का कोई PTR रिकॉर्ड नहीं है, इसलिए दिखाने के लिए नाम नहीं है। यह असली उत्तर है, विफलता नहीं।',
        'core-network-rdns.ask': 'नाम?',
        'core-network-rdns.ask_tip':
          'इस पते के लिए रिवर्स लुकअप कतार में लगाएँ',
        'core-network-rdns.queued_tip':
          'कतार में है। हर कलेक्टर रन में एक हल किया जाता है।',
      }),
      ar: Object.freeze({
        'core-network-rdns.title': 'ما الذي يسميه الإنترنت عنوانًا',
        'core-network-rdns.help':
          'الالتقاط أو سجل الوصول جدار من الأرقام، والرقم نفسه لا يعني شيئًا يوم الاثنين ثم يعني شيئًا يوم الجمعة بعدما يبحث عنه أحد. هذا ليس {{labels}} — فهو ما قررتَ أن يعنيه عنوان ما، وهو صغير ومكتوب يدويًا ودائم. هذا ما يقول الإنترنت إنه: تلقائي وخاطئ أحيانًا وقديم خلال أشهر.',
        'core-network-rdns.released':
          'أُعيد {{count, number}} طلبًا إلى الطابور بعد أن أخذه جامع لم يعد.',
        'core-network-rdns.queue_heading':
          '{{waiting, number}} عناوين تنتظر{{progress}}',
        'core-network-rdns.queue_progress': '، {{count, number}} قيد التنفيذ',
        'core-network-rdns.queue_drain':
          'بمعدل {{perRun, number}} في كل تشغيل للجامع، يفرغ الطابور خلال {{duration}} تقريبًا.{{both}}',
        'core-network-rdns.both_collectors':
          ' يأخذ الجامعان طلبًا، وقد يكون ذلك أسرع.',
        'core-network-rdns.empty': 'لم يكن هناك شيء ينتظر.',
        'core-network-rdns.looked_up': 'تم البحث عن {{count, number}} عناوين',
        'core-network-rdns.resolve_next': 'حل التالي الآن',
        'core-network-rdns.lookup_result':
          '{{address}} ← {{name}} ({{milliseconds, number}} مللي ثانية)',
        'core-network-rdns.lookup': 'ابحث عن هذه',
        'core-network-rdns.ask_again': 'اطلب مرة أخرى',
        'core-network-rdns.placeholder':
          '78.174.88.104، 2001:db8::1 — أو الصق عمودًا كاملًا',
        'core-network-rdns.no_name': 'بلا اسم',
        'core-network-rdns.nothing_waiting': 'لم يكن هناك شيء قيد الانتظار.',
        'core-network-rdns.own_label_tip':
          'اسم منحته أنت لهذا، ضمن الإعدادات ← أسماؤك الخاصة.',
        'core-network-rdns.name_tip':
          'ما يقوله الإنترنت إن هذا العنوان يسمى به — سجل PTR يتحكم فيه مالكه ويمكنه ضبطه على أي شيء.',
        'core-network-rdns.waiting_tip':
          'بانتظار البحث. تُحل واحدة في كل تشغيل للجامع.',
        'core-network-rdns.none_tip':
          'تم البحث: ليس لهذا العنوان سجل PTR، لذا لا يوجد اسم لعرضه. هذه إجابة حقيقية وليست فشلاً.',
        'core-network-rdns.ask': 'اسم؟',
        'core-network-rdns.ask_tip':
          'أضف بحثًا عكسيًا لهذا العنوان إلى الطابور',
        'core-network-rdns.queued_tip':
          'أُضيف إلى الطابور. تُحل واحدة في كل تشغيل للجامع.',
      }),
      zh: Object.freeze({
        'core-network-rdns.title': '互联网如何称呼一个地址',
        'core-network-rdns.help':
          '捕获或访问日志是一堵数字墙；同一个数字在周一毫无意义，但有人查过后到了周五就有了意义。这并非 {{labels}}——那是您决定某地址含义的自定义名称，规模小、手写且永久。这是互联网所说的名称：自动生成、偶尔错误，并会在数月内过时。',
        'core-network-rdns.released':
          '{{count, number}} 个请求曾被未返回的采集器取走，现已放回队列。',
        'core-network-rdns.queue_heading':
          '{{waiting, number}} 个地址正在等待{{progress}}',
        'core-network-rdns.queue_progress': '，{{count, number}} 个处理中',
        'core-network-rdns.queue_drain':
          '每次采集器运行处理 {{perRun, number}} 个，队列约在 {{duration}} 后清空。{{both}}',
        'core-network-rdns.both_collectors':
          ' 两个采集器都会各取一个，因此可能更快。',
        'core-network-rdns.empty': '没有等待中的项目。',
        'core-network-rdns.looked_up': '已查询 {{count, number}} 个地址',
        'core-network-rdns.resolve_next': '立即解析下一个',
        'core-network-rdns.lookup_result':
          '{{address}} → {{name}}（{{milliseconds, number}} 毫秒）',
        'core-network-rdns.lookup': '查询这些',
        'core-network-rdns.ask_again': '再次请求',
        'core-network-rdns.placeholder':
          '78.174.88.104, 2001:db8::1 — 或粘贴整列',
        'core-network-rdns.no_name': '无名称',
        'core-network-rdns.nothing_waiting': '没有等待中的项目。',
        'core-network-rdns.own_label_tip':
          '您为此项指定的名称，位于“设置 → 您自己的名称”。',
        'core-network-rdns.name_tip':
          '互联网称此地址的名称——PTR 记录，其所有者可以控制并设置为任意内容。',
        'core-network-rdns.waiting_tip': '等待查询。每次采集器运行解析一个。',
        'core-network-rdns.none_tip':
          '已查询：此地址没有 PTR 记录，因此没有可显示的名称。这是有效答案，不是失败。',
        'core-network-rdns.ask': '名称？',
        'core-network-rdns.ask_tip': '为此地址排队反向查询',
        'core-network-rdns.queued_tip': '已排队。每次采集器运行解析一个。',
      }),
    }),
  }),

  text(key, valuesOrHelpers = {}, maybeHelpers = null) {
    const helpers = maybeHelpers || valuesOrHelpers;
    const values = maybeHelpers ? valuesOrHelpers : {};
    return helpers.i18n.t(
      `core-network-rdns.${key}`,
      values,
      this.manifest.catalogs.en[`core-network-rdns.${key}`],
    );
  },

  render(data, helpers) {
    const {
      target,
      note,
      section,
      renderTable,
      humanSpan,
      ago,
      post,
      render,
      notice,
      i18n,
    } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (!target) return;
    const parts = [];
    if (data.released > 0)
      parts.push(
        note(
          t(
            'core-network-rdns.released',
            { count: data.released },
            '{{count, number}} requests were put back in the queue.',
          ),
        ),
      );
    const waiting = (data.waiting || []).filter((row) => !row.taken);
    const taken = (data.waiting || []).filter((row) => row.taken);
    if (waiting.length || taken.length) {
      const holder = document.createElement('div');
      holder.id = 'rdnsQueue';
      parts.push(
        section(
          t(
            'core-network-rdns.queue_heading',
            {
              waiting: waiting.length,
              progress: taken.length
                ? t(
                    'core-network-rdns.queue_progress',
                    { count: taken.length },
                    ', {{count, number}} in progress',
                  )
                : '',
            },
            '{{waiting, number}} addresses waiting{{progress}}',
          ),
          holder,
        ),
      );
      parts.push(
        note(
          t(
            'core-network-rdns.queue_drain',
            {
              perRun: data.per_run,
              duration: humanSpan(
                Math.ceil(waiting.length / Math.max(1, data.per_run)) * 300,
              ),
              both: data.host_enabled
                ? t(
                    'core-network-rdns.both_collectors',
                    {},
                    ' Both collectors take one.',
                  )
                : '',
            },
            'At {{perRun, number}} per collector run, the queue empties in about {{duration}}.{{both}}',
          ),
        ),
      );
    } else
      parts.push(
        note(t('core-network-rdns.empty', {}, 'Nothing was waiting.')),
      );
    if (data.known?.length) {
      const holder = document.createElement('div');
      holder.id = 'rdnsKnown';
      parts.push(
        section(
          t(
            'core-network-rdns.looked_up',
            { count: data.known.length },
            '{{count, number}} addresses looked up',
          ),
          holder,
        ),
      );
    }
    target.replaceChildren(...parts);
    if (waiting.length || taken.length)
      renderTable(
        'rdnsQueue',
        data.waiting,
        [
          {
            label: t('network.address', {}, 'Address'),
            className: 'name',
            render: (row) => row.address,
          },
          {
            label: t('network.rdns_asked'),
            render: (row) =>
              t('network.rdns_asked_by', {
                ago: ago(row.asked_at),
                by: row.asked_by,
              }),
          },
          {
            label: t('network.rdns_waiting'),
            render: (row) => humanSpan(row.waited),
          },
          {
            label: '',
            render: (row) => {
              if (!row.taken) return '';
              const span = document.createElement('span');
              span.className = 'band-warn';
              span.textContent = t('network.rdns_looking_now', {
                collector: row.taken_by,
              });
              return span;
            },
          },
          {
            label: '',
            className: 'row-actions',
            render: (row) => {
              const button = document.createElement('button');
              button.className = 'link danger';
              button.textContent = t('action.cancel', {}, 'Cancel');
              button.disabled = row.taken;
              button.title = row.taken
                ? t('network.rdns_taken_tip')
                : t('network.rdns_cancel_tip');
              button.addEventListener('click', async () => {
                button.disabled = true;
                try {
                  render(await post('rdns-cancel', { id: row.id }));
                } catch (error) {
                  notice(String(error.message || error));
                  button.disabled = false;
                }
              });
              return button;
            },
          },
        ],
        null,
        (row) => (row.taken ? 'row-off' : ''),
        (row) => String(row.id),
      );
    if (data.known?.length)
      renderTable(
        'rdnsKnown',
        data.known,
        [
          {
            label: t('network.address', {}, 'Address'),
            className: 'name',
            render: (row) => row.address,
          },
          {
            label: t('network.rdns_called'),
            className: 'name',
            render: (row) => {
              if (row.name) return row.name;
              const span = document.createElement('span');
              span.className = 'muted';
              span.textContent = t('network.rdns_no_name');
              span.title = t('network.rdns_no_name_tip');
              return span;
            },
          },
          {
            label: t('network.rdns_looked_up'),
            render: (row) => {
              const span = document.createElement('span');
              span.textContent = ago(row.checked_at);
              if (row.stale) {
                span.className = 'muted';
                span.title = t('network.rdns_stale_tip', {
                  age: humanSpan(data.fresh_for),
                });
              }
              return span;
            },
          },
          { label: t('network.rdns_by'), render: (row) => row.resolved_by },
          {
            label: '',
            className: 'row-actions',
            render: (row) => {
              const again = document.createElement('button');
              again.className = 'link';
              again.textContent = t(
                'core-network-rdns.ask_again',
                {},
                'Ask again',
              );
              again.title = t('network.rdns_ask_again_tip');
              again.addEventListener('click', async () => {
                again.disabled = true;
                try {
                  render(
                    await post('rdns-ask', {
                      addresses: row.address,
                      force: '1',
                    }),
                  );
                  notice(
                    t('network.rdns_address_queued', { address: row.address }),
                    true,
                  );
                } catch (error) {
                  notice(String(error.message || error));
                } finally {
                  again.disabled = false;
                }
              });
              return again;
            },
          },
        ],
        null,
        null,
        (row) => row.address,
      );
  },
};
