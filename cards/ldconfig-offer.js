/* Core card package: host library-cache rebuild offer. */
window.ZogographLdconfigOfferCard = {
  manifest: Object.freeze({
    id: 'core-ldconfig-offer',
    locales: ['core-ldconfig-offer.'],
    helpers: ['ago', 'confirm', 'i18n', 'notice', 'note', 'post', 'refresh'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ldconfig-offer.intro':
          'Repaired a link under a library directory and programs still cannot start? The dynamic linker reads a cache, not the directories, so it stays stale until something rebuilds it — and only root can. This asks the host collector to run ldconfig on its next pass.',
        'core-ldconfig-offer.ask': 'Ask the host to rebuild the library cache',
        'core-ldconfig-offer.confirm':
          "Ask root's collector to run ldconfig?\n\nUnlike everything else here, this changes something: it rewrites /etc/ld.so.cache from what is on disk. It is safe to repeat and cannot make the cache worse than the directories it is built from.\n\nIt runs on the collector's next pass, not now.",
        'core-ldconfig-offer.queued':
          "Queued. It runs on the host collector's next pass.",
        'core-ldconfig-offer.nothing_yet':
          'Nothing has run yet, so there is nothing to compare against.',
        'core-ldconfig-offer.previous_finished':
          'The last rebuild was {{time}} and finished.',
        'core-ldconfig-offer.previous_failed':
          'The last rebuild was {{time}} and failed: {{reason}}.',
        'core-ldconfig-offer.no_reason': 'no reason given',
      }),
      es: Object.freeze({
        'core-ldconfig-offer.intro':
          '¿Reparó un enlace bajo un directorio de bibliotecas y los programas aún no pueden iniciarse? El enlazador dinámico lee una caché, no los directorios, por lo que permanece obsoleta hasta que algo la reconstruye, y solo root puede hacerlo. Esto pide al recolector del host que ejecute ldconfig en su próxima pasada.',
        'core-ldconfig-offer.ask':
          'Pedir al host que reconstruya la caché de bibliotecas',
        'core-ldconfig-offer.confirm':
          '¿Pedir al recolector de root que ejecute ldconfig?\n\nA diferencia de todo lo demás aquí, esto cambia algo: reescribe /etc/ld.so.cache a partir de lo que hay en disco. Es seguro repetirlo y no puede dejar la caché peor que los directorios de los que se crea.\n\nSe ejecuta en la siguiente pasada del recolector, no ahora.',
        'core-ldconfig-offer.queued':
          'En cola. Se ejecutará en la siguiente pasada del recolector del host.',
        'core-ldconfig-offer.nothing_yet':
          'Aún no se ha ejecutado nada, por lo que no hay nada con qué comparar.',
        'core-ldconfig-offer.previous_finished':
          'La última reconstrucción fue {{time}} y terminó.',
        'core-ldconfig-offer.previous_failed':
          'La última reconstrucción fue {{time}} y falló: {{reason}}.',
        'core-ldconfig-offer.no_reason': 'sin motivo indicado',
      }),
      hi: Object.freeze({
        'core-ldconfig-offer.intro':
          'लाइब्रेरी डायरेक्टरी के अंतर्गत लिंक ठीक किया लेकिन प्रोग्राम फिर भी शुरू नहीं हो सकते? डायनेमिक लिंकर डायरेक्टरी नहीं, कैश पढ़ता है, इसलिए कुछ उसे फिर से न बनाए तब तक वह पुराना रहता है — और केवल root कर सकता है। यह होस्ट कलेक्टर से उसके अगले पास पर ldconfig चलाने को कहता है।',
        'core-ldconfig-offer.ask':
          'होस्ट से लाइब्रेरी कैश फिर से बनाने को कहें',
        'core-ldconfig-offer.confirm':
          'क्या root के कलेक्टर से ldconfig चलाने को कहें?\n\nयहाँ बाकी सबके विपरीत, यह कुछ बदलता है: यह डिस्क पर मौजूद चीज़ से /etc/ld.so.cache को फिर लिखता है। इसे दोहराना सुरक्षित है और यह कैश को उन डायरेक्टरी से बदतर नहीं बना सकता जिनसे वह बना है।\n\nयह अभी नहीं, कलेक्टर के अगले पास पर चलता है।',
        'core-ldconfig-offer.queued':
          'कतार में है। यह होस्ट कलेक्टर के अगले पास पर चलेगा।',
        'core-ldconfig-offer.nothing_yet':
          'अभी तक कुछ नहीं चला है, इसलिए तुलना करने के लिए कुछ नहीं है।',
        'core-ldconfig-offer.previous_finished':
          'पिछला पुनर्निर्माण {{time}} था और पूरा हुआ।',
        'core-ldconfig-offer.previous_failed':
          'पिछला पुनर्निर्माण {{time}} था और विफल हुआ: {{reason}}।',
        'core-ldconfig-offer.no_reason': 'कोई कारण नहीं दिया गया',
      }),
      ar: Object.freeze({
        'core-ldconfig-offer.intro':
          'هل أصلحت رابطًا تحت دليل مكتبات وما زالت البرامج لا تبدأ؟ يقرأ الرابط الديناميكي ذاكرة مخبئية لا الأدلة، لذا تبقى قديمة إلى أن يعيد شيء بنائها، ولا يستطيع ذلك إلا root. يطلب هذا من جامع المضيف تشغيل ldconfig في مروره التالي.',
        'core-ldconfig-offer.ask':
          'اطلب من المضيف إعادة بناء ذاكرة المكتبات المخبئية',
        'core-ldconfig-offer.confirm':
          'هل تطلب من جامع root تشغيل ldconfig؟\n\nبخلاف كل شيء آخر هنا، يغيّر هذا شيئًا: يعيد كتابة /etc/ld.so.cache مما على القرص. من الآمن تكراره ولا يمكنه جعل الذاكرة المخبئية أسوأ من الأدلة التي بُنيت منها.\n\nسيعمل في مرور الجامع التالي، لا الآن.',
        'core-ldconfig-offer.queued':
          'وُضع في الطابور. سيعمل في مرور جامع المضيف التالي.',
        'core-ldconfig-offer.nothing_yet':
          'لم يُشغّل شيء بعد، لذلك لا يوجد ما يُقارن به.',
        'core-ldconfig-offer.previous_finished':
          'كانت آخر إعادة بناء {{time}} وانتهت.',
        'core-ldconfig-offer.previous_failed':
          'كانت آخر إعادة بناء {{time}} وفشلت: {{reason}}.',
        'core-ldconfig-offer.no_reason': 'لم يُذكر سبب',
      }),
      zh: Object.freeze({
        'core-ldconfig-offer.intro':
          '修复了库目录下的链接，但程序仍无法启动？动态链接器读取的是缓存而非目录，因此在有东西重建缓存前它会一直过期，而这只能由 root 完成。此操作会请求主机收集器在下一次运行时执行 ldconfig。',
        'core-ldconfig-offer.ask': '请求主机重建库缓存',
        'core-ldconfig-offer.confirm':
          '要请求 root 的收集器运行 ldconfig 吗？\n\n与这里的其他操作不同，这会改变内容：它会根据磁盘上的内容重写 /etc/ld.so.cache。可安全重复执行，且不会使缓存比构建它的目录更糟。\n\n它会在收集器下一次运行时执行，而不是现在。',
        'core-ldconfig-offer.queued':
          '已排队。它会在主机收集器下一次运行时执行。',
        'core-ldconfig-offer.nothing_yet':
          '尚未运行任何操作，因此没有可比较的内容。',
        'core-ldconfig-offer.previous_finished':
          '上一次重建是在 {{time}}，且已完成。',
        'core-ldconfig-offer.previous_failed':
          '上一次重建是在 {{time}}，且失败：{{reason}}。',
        'core-ldconfig-offer.no_reason': '未提供原因',
      }),
    }),
  }),
  previous(previous, justAsked, helpers) {
    const { ago, i18n, note } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (!previous?.at) {
      return note(
        justAsked
          ? t(
              'core-ldconfig-offer.nothing_yet',
              {},
              'Nothing has run yet, so there is nothing to compare against.',
            )
          : '',
      );
    }
    const wrap = document.createDocumentFragment();
    const reason =
      previous.error ||
      t('core-ldconfig-offer.no_reason', {}, 'no reason given');
    wrap.appendChild(
      note(
        t(
          previous.ok
            ? 'core-ldconfig-offer.previous_finished'
            : 'core-ldconfig-offer.previous_failed',
          { time: ago(previous.at), reason },
          previous.ok
            ? `The last rebuild was ${ago(previous.at)} and finished.`
            : `The last rebuild was ${ago(previous.at)} and failed: ${reason}.`,
        ),
      ),
    );
    if (previous.text) {
      const pre = document.createElement('pre');
      pre.className = 'paste';
      pre.textContent = previous.text;
      wrap.appendChild(pre);
    }
    return wrap;
  },
  render(input, helpers) {
    const { confirm, i18n, notice, post, refresh } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    const wrap = document.createElement('div');
    wrap.className = 'sub-note muted small';
    const intro = document.createElement('p');
    intro.textContent = t(
      'core-ldconfig-offer.intro',
      {},
      'Repaired a link under a library directory and programs still cannot start? The dynamic linker reads a cache, not the directories, so it stays stale until something rebuilds it — and only root can. This asks the host collector to run ldconfig on its next pass.',
    );
    const actions = document.createElement('div');
    actions.className = 'inspect-actions';
    const ask = document.createElement('button');
    ask.className = 'link';
    ask.textContent = t(
      'core-ldconfig-offer.ask',
      {},
      'Ask the host to rebuild the library cache',
    );
    ask.addEventListener('click', async () => {
      if (
        !(await confirm(
          t(
            'core-ldconfig-offer.confirm',
            {},
            "Ask root's collector to run ldconfig?\n\nUnlike everything else here, this changes something: it rewrites /etc/ld.so.cache from what is on disk. It is safe to repeat and cannot make the cache worse than the directories it is built from.\n\nIt runs on the collector's next pass, not now.",
          ),
        ))
      )
        return;
      ask.disabled = true;
      try {
        const result = await post('ldconfig');
        notice(
          t(
            'core-ldconfig-offer.queued',
            {},
            "Queued. It runs on the host collector's next pass.",
          ),
          true,
        );
        wrap.appendChild(this.previous(result.previous, true, helpers));
        await refresh();
      } catch (error) {
        notice(String(error.message || error));
      } finally {
        ask.disabled = false;
      }
    });
    actions.appendChild(ask);
    wrap.append(intro, actions);
    return wrap;
  },
};
