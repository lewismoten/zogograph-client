/* Core card package: the shell provides the scoped capture-request action. */
window.ZogographNetworkCaptureRequestCard = {
  manifest: Object.freeze({
    id: 'core-network-capture-request',
    locales: ['core-network-capture-request.'],
    helpers: [
      'target',
      'note',
      'post',
      'notice',
      'refreshNetwork',
      'ago',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-network-capture-request.ask': 'Ask for a capture',
        'core-network-capture-request.requested':
          'Asked for. The host collector takes it on its next run — every five minutes with the suggested crontab line.',
        'core-network-capture-request.read_asked':
          'Read what the traffic asked for',
        'core-network-capture-request.read_asked_warning':
          'Counting packets tells you there were 748 on port 443 and nothing about what they wanted. This reads three things that travel in the clear — the host name in a TLS handshake, and the Host and page of an HTTP request — and counts them like everything else. It does mean tcpdump prints the first 512 bytes of each packet for this run, on a machine you may be sharing. Nothing decrypts, no payload is stored, and query strings are cut off before counting: a path says which page, and a query string is where tokens and search terms live.',
        'core-network-capture-request.pending':
          'A {{filter}} capture on {{interface}} was asked for {{when}} and is waiting for the next host collection.',
      }),
      es: Object.freeze({
        'core-network-capture-request.ask': 'Solicitar una captura',
        'core-network-capture-request.requested':
          'Solicitada. El recopilador del host la realizará en su próxima ejecución: cada cinco minutos con la línea de crontab sugerida.',
        'core-network-capture-request.read_asked': 'Leer qué pidió el tráfico',
        'core-network-capture-request.read_asked_warning':
          'Contar paquetes indica que hubo 748 en el puerto 443, pero no qué querían. Esto lee tres cosas que viajan sin cifrar: el nombre de host en un protocolo TLS y el Host y la página de una solicitud HTTP; luego las cuenta como todo lo demás. tcpdump imprime los primeros 512 bytes de cada paquete de esta ejecución, en una máquina que quizá compartes. No se descifra nada, no se guarda ninguna carga útil y las cadenas de consulta se eliminan antes del recuento: una ruta dice qué página y una consulta es donde viven los tokens y términos de búsqueda.',
        'core-network-capture-request.pending':
          'Se solicitó una captura {{filter}} en {{interface}} {{when}} y está esperando la próxima recopilación del host.',
      }),
      hi: Object.freeze({
        'core-network-capture-request.ask': 'कैप्चर का अनुरोध करें',
        'core-network-capture-request.requested':
          'अनुरोध किया गया। होस्ट कलेक्टर इसे अपने अगले रन में लेगा — सुझाई गई crontab पंक्ति के साथ हर पांच मिनट में।',
        'core-network-capture-request.read_asked':
          'ट्रैफ़िक ने क्या मांगा वह पढ़ें',
        'core-network-capture-request.read_asked_warning':
          'पैकेट गिनने से पता चलता है कि पोर्ट 443 पर 748 थे, पर वे क्या चाहते थे यह नहीं। यह साफ़ रूप में जाने वाली तीन चीज़ें पढ़ता है — TLS हैंडशेक में होस्ट नाम और HTTP अनुरोध का Host व पेज — और उन्हें बाकी सब की तरह गिनता है। इसका अर्थ है कि tcpdump इस रन के हर पैकेट के पहले 512 बाइट प्रिंट करता है, ऐसी मशीन पर जिसे आप साझा कर सकते हैं। कुछ भी डिक्रिप्ट नहीं होता, कोई पेलोड संग्रहीत नहीं होता, और गिनने से पहले क्वेरी स्ट्रिंग काट दी जाती है: पथ बताता है कि कौन सा पेज है और क्वेरी स्ट्रिंग में टोकन व खोज शब्द रहते हैं।',
        'core-network-capture-request.pending':
          '{{interface}} पर {{filter}} कैप्चर का अनुरोध {{when}} किया गया था और यह अगले होस्ट संग्रह की प्रतीक्षा में है।',
      }),
      ar: Object.freeze({
        'core-network-capture-request.ask': 'اطلب التقاطًا',
        'core-network-capture-request.requested':
          'تم الطلب. سيأخذه جامع المضيف في تشغيله التالي — كل خمس دقائق مع سطر crontab المقترح.',
        'core-network-capture-request.read_asked': 'اقرأ ما طلبته الحركة',
        'core-network-capture-request.read_asked_warning':
          'عدّ الحزم يخبرك بوجود 748 على المنفذ 443 ولا يخبرك بما طلبته. يقرأ هذا ثلاثة أشياء تسافر دون تشفير — اسم المضيف في مصافحة TLS، وHost والصفحة في طلب HTTP — ويعدها كأي شيء آخر. وهذا يعني أن tcpdump يطبع أول 512 بايت من كل حزمة لهذا التشغيل، على جهاز قد تشاركه. لا يُفك تشفير شيء ولا تُخزن أي حمولة، وتُقطع سلاسل الاستعلام قبل العد: المسار يحدد الصفحة وسلسلة الاستعلام هي موضع الرموز ومصطلحات البحث.',
        'core-network-capture-request.pending':
          'طُلب التقاط {{filter}} على {{interface}} {{when}} وهو ينتظر جمع المضيف التالي.',
      }),
      zh: Object.freeze({
        'core-network-capture-request.ask': '请求抓包',
        'core-network-capture-request.requested':
          '已请求。主机采集器会在下次运行时执行——使用建议的 crontab 条目时每五分钟一次。',
        'core-network-capture-request.read_asked': '读取流量请求的内容',
        'core-network-capture-request.read_asked_warning':
          '统计数据包只能说明端口 443 上有 748 个数据包，不能说明它们请求了什么。这会读取三种明文传输的信息——TLS 握手中的主机名，以及 HTTP 请求中的 Host 和页面——并像处理其他信息一样统计。它确实意味着 tcpdump 会在本次运行中打印每个数据包的前 512 个字节，所在机器可能由多人共享。不会解密，不会存储任何有效负载，并且会在统计前截去查询字符串：路径说明是哪一页，而查询字符串通常包含令牌和搜索词。',
        'core-network-capture-request.pending':
          '{{when}} 在 {{interface}} 上请求了 {{filter}} 抓包，正在等待下一次主机采集。',
      }),
    }),
  }),

  render(data, helpers) {
    const { target, note, post, notice, refreshNetwork, ago, i18n } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (!target) return;
    if (!data.signedIn) {
      target.replaceChildren();
      return;
    }

    const wrap = document.createDocumentFragment();
    const form = document.createElement('div');
    form.className = 'pattern-row';
    const interfaces = (data.interfaces || []).filter((row) => !row.loopback);
    const picker = document.createElement('select');
    picker.id = 'captureInterface';
    for (const row of interfaces.length ? interfaces : [{ name: 'eth0' }]) {
      const option = document.createElement('option');
      option.value = row.name;
      option.textContent = row.name;
      picker.appendChild(option);
    }
    form.appendChild(picker);

    const filter = document.createElement('select');
    filter.id = 'captureFilter';
    for (const item of data.filters || []) {
      const option = document.createElement('option');
      option.value = item.key;
      option.textContent = item.label;
      option.title = item.about;
      filter.appendChild(option);
    }
    form.appendChild(filter);

    const go = document.createElement('button');
    go.textContent = t(
      'core-network-capture-request.ask',
      {},
      'Ask for a capture',
    );
    go.addEventListener('click', async () => {
      go.disabled = true;
      try {
        await post('request-capture', {
          interface: picker.value,
          filter: filter.value,
          read_asked: readAsked.checked ? '1' : '0',
        });
        notice(
          t(
            'core-network-capture-request.requested',
            {},
            'Asked for. The host collector takes it on its next run — every five minutes with the suggested crontab line.',
          ),
          true,
        );
        await refreshNetwork();
      } catch (error) {
        notice(String(error.message || error));
        go.disabled = false;
      }
    });
    form.appendChild(go);
    wrap.appendChild(form);

    const deeper = document.createElement('label');
    deeper.className = 'pill';
    const readAsked = document.createElement('input');
    readAsked.type = 'checkbox';
    readAsked.id = 'captureReadAsked';
    deeper.appendChild(readAsked);
    deeper.appendChild(
      document.createTextNode(
        ` ${t(
          'core-network-capture-request.read_asked',
          {},
          'Read what the traffic asked for',
        )}`,
      ),
    );
    wrap.appendChild(deeper);

    const warning = document.createElement('p');
    warning.className = 'muted small';
    warning.textContent = t(
      'core-network-capture-request.read_asked_warning',
      {},
      'Counting packets tells you there were 748 on port 443 and nothing about what they wanted. This reads three things that travel in the clear — the host name in a TLS handshake, and the Host and page of an HTTP request — and counts them like everything else. It does mean tcpdump prints the first 512 bytes of each packet for this run, on a machine you may be sharing. Nothing decrypts, no payload is stored, and query strings are cut off before counting: a path says which page, and a query string is where tokens and search terms live.',
    );
    wrap.appendChild(warning);

    const about = document.createElement('p');
    about.className = 'muted small';
    const describe = () => {
      const chosen = (data.filters || []).find(
        (item) => item.key === filter.value,
      );
      about.textContent = chosen ? chosen.about : '';
    };
    filter.addEventListener('change', describe);
    describe();
    wrap.appendChild(about);

    for (const pending of data.pending || []) {
      wrap.appendChild(
        note(
          t(
            'core-network-capture-request.pending',
            {
              filter: pending.filter,
              interface: pending.interface,
              when: ago(pending.requested_at),
            },
            'A {{filter}} capture on {{interface}} was asked for {{when}} and is waiting for the next host collection.',
          ),
        ),
      );
    }
    target.replaceChildren(wrap);
  },
};
