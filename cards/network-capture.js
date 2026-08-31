/* Core card package: a single network-capture report, live or historical. */
window.ZogographNetworkCaptureCard = {
  manifest: Object.freeze({
    id: 'core-network-capture',
    locales: ['core-network-capture.'],
    helpers: [
      'target',
      'note',
      'humanTime',
      'ago',
      'humanCount',
      'humanBytes',
      'section',
      'factGrid',
      'simpleTable',
      'labelled',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-network-capture.title': 'What is on the wire',
        'core-network-capture.help':
          'The tool of last resort, and treated as one: it opens a live interface as root, runs for as long as you ask, and is the only thing this collector does that could itself be felt on a busy machine. It records counts and endpoints only — how many packets, between which addresses, on which ports, and for DNS the names asked for. No packet contents are stored, ever.',
        'core-network-capture.flags_new':
          'Mostly connection attempts rather than established traffic. From a few addresses that is a client reconnecting in a loop; from many changing ones it is scanning or a flood — the remote list below says which.',
        'core-network-capture.flags_established':
          'Mostly established traffic doing work rather than new connections. This is what a busy site looks like, not an attack.',
        'core-network-capture.remote_help':
          'This machine’s own addresses are excluded: they appear in nearly every packet and would top the table saying nothing. “Opened” counts SYN packets — new connections rather than traffic on existing ones.{{more}}',
        'core-network-capture.remote_more':
          ' The busiest {{shown, number}} of {{total, number}} are shown, so these will not add up to the total.',
        'core-network-capture.cleartext_help':
          'Read from traffic that travels in the clear: the host name in a TLS handshake, and the Host and page of an HTTP request. A TLS row says which host was reached, never what was said to it. Query strings were cut off before counting.',
        'core-network-capture.no_contents_help':
          'Contents were read for this capture and nothing recognisable came out of them — no TLS handshakes and no HTTP requests in the packets sampled. A capture of an established connection sees neither: only the first packet of a connection carries a server name.',
        'core-network-capture.names_asked': 'Names asked for',
        'core-network-capture.remote_address': 'Remote address',
        'core-network-capture.in': 'In',
        'core-network-capture.out': 'Out',
        'core-network-capture.opened': 'Opened',
        'core-network-capture.flag_syn': 'a new connection being attempted',
        'core-network-capture.flag_syn_ack': 'a connection being accepted',
        'core-network-capture.flag_ack':
          'ordinary traffic on an established connection',
        'core-network-capture.flag_psh_ack':
          'application data on an established connection',
        'core-network-capture.flag_fin': 'a connection closing tidily',
        'core-network-capture.flag_rst': 'a connection refused or aborted',
        'core-network-capture.capture_empty': 'No capture has been taken yet.',
        'core-network-capture.capture_failed':
          'The capture of {{time}} did not run: {{reason}}',
        'core-network-capture.interface_filter': 'Interface and filter',
        'core-network-capture.packets_rate': '{{packets}} — {{rate}} a second',
      }),
      es: Object.freeze({
        'core-network-capture.title': 'Qué hay en la red',
        'core-network-capture.help':
          'La herramienta de último recurso, tratada como tal: abre una interfaz activa como root, se ejecuta durante el tiempo indicado y es lo único que hace este recopilador que podría notarse en una máquina ocupada. Solo registra conteos y extremos: cuántos paquetes, entre qué direcciones, en qué puertos y, para DNS, los nombres consultados. Nunca se guarda el contenido de paquetes.',
        'core-network-capture.flags_new':
          'Predominan los intentos de conexión en lugar del tráfico establecido. Desde pocas direcciones puede ser un cliente reconectándose; desde muchas cambiantes puede ser un escaneo o una inundación.',
        'core-network-capture.flags_established':
          'Predomina el tráfico establecido realizando trabajo en lugar de conexiones nuevas. Así se ve un sitio ocupado, no un ataque.',
        'core-network-capture.remote_help':
          'Las direcciones de esta máquina se excluyen: aparecen en casi todos los paquetes y no aportarían información. “Abiertas” cuenta paquetes SYN — conexiones nuevas.{{more}}',
        'core-network-capture.remote_more':
          ' Se muestran las {{shown, number}} más activas de {{total, number}}, por lo que no sumarán el total.',
        'core-network-capture.cleartext_help':
          'Se lee tráfico sin cifrar: el nombre de host en un saludo TLS, y Host y página en una solicitud HTTP. Una fila TLS indica el host alcanzado, nunca su contenido.',
        'core-network-capture.no_contents_help':
          'Se leyeron contenidos y no apareció nada reconocible: no hubo saludos TLS ni solicitudes HTTP en los paquetes muestreados.',
        'core-network-capture.names_asked': 'Nombres consultados',
        'core-network-capture.remote_address': 'Dirección remota',
        'core-network-capture.in': 'Entrada',
        'core-network-capture.out': 'Salida',
        'core-network-capture.opened': 'Abiertas',
        'core-network-capture.flag_syn': 'se intenta una conexión nueva',
        'core-network-capture.flag_syn_ack': 'se acepta una conexión',
        'core-network-capture.flag_ack':
          'tráfico normal en una conexión establecida',
        'core-network-capture.flag_psh_ack':
          'datos de aplicación en una conexión establecida',
        'core-network-capture.flag_fin': 'una conexión se cierra correctamente',
        'core-network-capture.flag_rst':
          'una conexión fue rechazada o cancelada',
        'core-network-capture.capture_empty':
          'Aún no se ha tomado ninguna captura.',
        'core-network-capture.capture_failed':
          'La captura de {{time}} no se ejecutó: {{reason}}',
        'core-network-capture.interface_filter': 'Interfaz y filtro',
        'core-network-capture.packets_rate':
          '{{packets}} — {{rate}} por segundo',
      }),
      hi: Object.freeze({
        'core-network-capture.title': 'नेटवर्क पर क्या है',
        'core-network-capture.help':
          'अंतिम उपाय का उपकरण, और वैसा ही माना जाता है: यह root के रूप में लाइव इंटरफ़ेस खोलता है, आपके कहे समय तक चलता है और इस कलेक्टर का एकमात्र कार्य है जो व्यस्त मशीन पर स्वयं महसूस हो सकता है। यह केवल गिनती और एंडपॉइंट रिकॉर्ड करता है—कितने पैकेट, किन पतों के बीच, किन पोर्ट पर और DNS के लिए पूछे गए नाम। पैकेट सामग्री कभी संग्रहीत नहीं होती।',
        'core-network-capture.flags_new':
          'स्थापित ट्रैफ़िक की तुलना में कनेक्शन प्रयास अधिक हैं। बदलते बहुत से पतों से यह स्कैन या बाढ़ हो सकती है।',
        'core-network-capture.flags_established':
          'नए कनेक्शन के बजाय स्थापित ट्रैफ़िक काम कर रहा है। व्यस्त साइट ऐसी दिखती है, हमला नहीं।',
        'core-network-capture.remote_help':
          'इस मशीन के अपने पते हटाए गए हैं: वे लगभग हर पैकेट में आते हैं। “खोले गए” SYN पैकेट गिनता है।{{more}}',
        'core-network-capture.remote_more':
          ' {{total, number}} में से सबसे व्यस्त {{shown, number}} दिखाए गए हैं।',
        'core-network-capture.cleartext_help':
          'स्पष्ट रूप से जाने वाले ट्रैफ़िक से पढ़ा गया: TLS हैंडशेक में होस्ट नाम और HTTP अनुरोध में Host व पृष्ठ।',
        'core-network-capture.no_contents_help':
          'सामग्री पढ़ी गई पर कुछ पहचानने योग्य नहीं मिला: नमूने में TLS हैंडशेक या HTTP अनुरोध नहीं थे।',
        'core-network-capture.names_asked': 'पूछे गए नाम',
        'core-network-capture.remote_address': 'रिमोट पता',
        'core-network-capture.in': 'आने वाले',
        'core-network-capture.out': 'जाने वाले',
        'core-network-capture.opened': 'खोले गए',
        'core-network-capture.flag_syn': 'नए कनेक्शन का प्रयास',
        'core-network-capture.flag_syn_ack': 'कनेक्शन स्वीकार किया जा रहा है',
        'core-network-capture.flag_ack': 'स्थापित कनेक्शन पर सामान्य ट्रैफ़िक',
        'core-network-capture.flag_psh_ack':
          'स्थापित कनेक्शन पर अनुप्रयोग डेटा',
        'core-network-capture.flag_fin': 'कनेक्शन ठीक से बंद हो रहा है',
        'core-network-capture.flag_rst': 'कनेक्शन अस्वीकृत या रद्द किया गया',
        'core-network-capture.capture_empty':
          'अभी तक कोई कैप्चर नहीं लिया गया है।',
        'core-network-capture.capture_failed':
          '{{time}} का कैप्चर नहीं चला: {{reason}}',
        'core-network-capture.interface_filter': 'इंटरफ़ेस और फ़िल्टर',
        'core-network-capture.packets_rate':
          '{{packets}} — {{rate}} प्रति सेकंड',
      }),
      ar: Object.freeze({
        'core-network-capture.title': 'ما الموجود على الشبكة',
        'core-network-capture.help':
          'أداة الملاذ الأخير، وتُعامل على هذا الأساس: تفتح واجهة حية بصلاحية root وتعمل للمدة التي تطلبها، وهي الشيء الوحيد الذي يفعله هذا الجامع وقد يُحَسّ أثره على جهاز مشغول. تسجل الأعداد والنهايات فقط — كم حزمة، وبين أي عناوين، وعلى أي منافذ، وبالنسبة إلى DNS الأسماء المطلوبة. لا يُخزن محتوى الحزم أبدًا.',
        'core-network-capture.flags_new':
          'معظم الحركة محاولات اتصال وليست حركة قائمة. من عناوين متغيرة كثيرة قد يكون ذلك مسحًا أو إغراقًا.',
        'core-network-capture.flags_established':
          'معظم الحركة لاتصالات قائمة تؤدي عملًا، لا اتصالات جديدة. هذا شكل موقع مشغول وليس هجومًا.',
        'core-network-capture.remote_help':
          'تُستبعد عناوين هذه الآلة لأنها تظهر في كل حزمة تقريبًا. «المفتوحة» تعد حزم SYN.{{more}}',
        'core-network-capture.remote_more':
          ' تظهر أكثر {{shown, number}} عناوين نشاطًا من أصل {{total, number}}.',
        'core-network-capture.cleartext_help':
          'قُرئت من حركة واضحة: اسم المضيف في مصافحة TLS، وHost والصفحة في طلب HTTP.',
        'core-network-capture.no_contents_help':
          'قُرئت المحتويات ولم يظهر شيء معروف: لا مصافحات TLS ولا طلبات HTTP في الحزم المأخوذة.',
        'core-network-capture.names_asked': 'الأسماء المطلوبة',
        'core-network-capture.remote_address': 'العنوان البعيد',
        'core-network-capture.in': 'وارد',
        'core-network-capture.out': 'صادر',
        'core-network-capture.opened': 'مفتوحة',
        'core-network-capture.flag_syn': 'محاولة اتصال جديد',
        'core-network-capture.flag_syn_ack': 'يجري قبول اتصال',
        'core-network-capture.flag_ack': 'حركة عادية لاتصال قائم',
        'core-network-capture.flag_psh_ack': 'بيانات تطبيق لاتصال قائم',
        'core-network-capture.flag_fin': 'اتصال يُغلق بشكل سليم',
        'core-network-capture.flag_rst': 'اتصال رُفض أو أُلغي',
        'core-network-capture.capture_empty': 'لم تُؤخذ لقطة بعد.',
        'core-network-capture.capture_failed':
          'لم تُشغّل لقطة {{time}}: {{reason}}',
        'core-network-capture.interface_filter': 'الواجهة والمرشح',
        'core-network-capture.packets_rate':
          '{{packets}} — {{rate}} في الثانية',
      }),
      zh: Object.freeze({
        'core-network-capture.title': '线路上有什么',
        'core-network-capture.help':
          '最后手段的工具，也应按最后手段对待：它以 root 身份打开实时接口，按您指定的时间运行，是此采集器中唯一可能在繁忙机器上自身也产生影响的操作。它只记录计数和端点——多少数据包、在哪些地址之间、使用哪些端口，以及对 DNS 而言所查询的名称。绝不存储数据包内容。',
        'core-network-capture.flags_new':
          '连接尝试多于已建立连接的流量。来自许多不断变化的地址时，可能是扫描或洪泛。',
        'core-network-capture.flags_established':
          '已建立连接的正常流量多于新连接。这是繁忙站点的表现，并非攻击。',
        'core-network-capture.remote_help':
          '已排除此机器自身的地址：它们几乎出现在每个数据包中。“已打开”统计 SYN 包。{{more}}',
        'core-network-capture.remote_more':
          ' 仅显示 {{total, number}} 个中最活跃的 {{shown, number}} 个，因此不会合计为总数。',
        'core-network-capture.cleartext_help':
          '从明文流量读取：TLS 握手中的主机名，以及 HTTP 请求中的 Host 和页面。',
        'core-network-capture.no_contents_help':
          '已读取内容，但没有可识别的信息：采样包中没有 TLS 握手或 HTTP 请求。',
        'core-network-capture.names_asked': '查询的名称',
        'core-network-capture.remote_address': '远程地址',
        'core-network-capture.in': '入站',
        'core-network-capture.out': '出站',
        'core-network-capture.opened': '已打开',
        'core-network-capture.flag_syn': '正在尝试建立新连接',
        'core-network-capture.flag_syn_ack': '正在接受连接',
        'core-network-capture.flag_ack': '已建立连接上的普通流量',
        'core-network-capture.flag_psh_ack': '已建立连接上的应用数据',
        'core-network-capture.flag_fin': '连接正常关闭',
        'core-network-capture.flag_rst': '连接被拒绝或中止',
        'core-network-capture.capture_empty': '尚未进行抓取。',
        'core-network-capture.capture_failed':
          '{{time}} 的抓取未运行：{{reason}}',
        'core-network-capture.interface_filter': '接口和筛选器',
        'core-network-capture.packets_rate': '{{packets}} — 每秒 {{rate}}',
      }),
    }),
  }),

  text(key, helpers) {
    return helpers.i18n.t(
      `core-network-capture.${key}`,
      {},
      this.manifest.catalogs.en[`core-network-capture.${key}`],
    );
  },

  render(capture, helpers) {
    const {
      target,
      note,
      humanTime,
      ago,
      humanCount,
      humanBytes,
      section,
      factGrid,
      simpleTable,
      labelled,
      i18n,
    } = helpers;
    const t = (key, values, fallback) => {
      const name = key.slice('network.'.length);
      const packageKey = [
        'capture_empty',
        'capture_failed',
        'interface_filter',
        'packets_rate',
      ].includes(name)
        ? `core-network-capture.${name}`
        : key;
      return i18n.t(packageKey, values, fallback);
    };
    if (!target) return;
    if (!capture) {
      target.replaceChildren(note(t('network.capture_empty')));
      return;
    }
    if (!capture.ok) {
      target.replaceChildren(
        note(
          t('network.capture_failed', {
            time: humanTime(capture.taken_at),
            reason: capture.reason,
          }),
        ),
      );
      return;
    }
    const seconds = Number(capture.seconds) || 0;
    const packets = Number(capture.packets) || 0;
    const parts = [];
    const facts = [
      [
        t('network.taken'),
        `${humanTime(capture.taken_at)} (${ago(capture.taken_at)})`,
      ],
      [
        t('network.interface_filter'),
        `${capture.interface} · ${capture.filter_label || capture.filter}`,
      ],
      [t('network.over'), `${seconds}s`],
      [
        t('network.packets'),
        seconds > 0
          ? t('network.packets_rate', {
              packets: humanCount(packets),
              rate: humanCount(Math.round(packets / seconds)),
            })
          : humanCount(packets),
      ],
      [
        t('network.arriving'),
        t('network.capture_direction_detail', {
          packets: humanCount(capture.in_packets),
          bytes: humanBytes(capture.in_bytes),
          rate:
            seconds > 0 ? ` — ${humanBytes(capture.in_bytes / seconds)}/s` : '',
        }),
      ],
      [
        t('network.leaving'),
        t('network.capture_direction_detail', {
          packets: humanCount(capture.out_packets),
          bytes: humanBytes(capture.out_bytes),
          rate:
            seconds > 0
              ? ` — ${humanBytes(capture.out_bytes / seconds)}/s`
              : '',
        }),
      ],
      [
        t('network.distinct_remote_addresses'),
        humanCount(capture.distinct_peers),
      ],
    ];
    if (Number(capture.distinct_names) > 0)
      facts.push([
        t('network.distinct_names_asked'),
        humanCount(capture.distinct_names),
      ]);
    if (capture.unparsed > 0)
      facts.push([
        t('network.counted_unattributed'),
        t('network.unparsed_detail', { count: humanCount(capture.unparsed) }),
      ]);
    if (capture.inferred_local)
      facts.push([
        t('network.inferred_machine'),
        t('network.inferred_machine_detail', {
          address: capture.inferred_local,
        }),
      ]);
    if (capture.hit_limit)
      facts.push([
        t('network.stopped_early'),
        t('network.stopped_early_detail'),
      ]);
    parts.push(section(t('network.the_sample'), factGrid(facts)));
    if ((capture.flags || []).length) {
      const total = capture.flags.reduce(
        (sum, flag) => sum + Number(flag.packets),
        0,
      );
      const byName = Object.fromEntries(
        capture.flags.map((flag) => [flag.label, Number(flag.packets)]),
      );
      const syn = byName.SYN || 0;
      const established = (byName.ACK || 0) + (byName['PSH+ACK'] || 0);
      const meanings = {
        SYN: t(
          'core-network-capture.flag_syn',
          {},
          'a new connection being attempted',
        ),
        'SYN+ACK': t(
          'core-network-capture.flag_syn_ack',
          {},
          'a connection being accepted',
        ),
        ACK: t(
          'core-network-capture.flag_ack',
          {},
          'ordinary traffic on an established connection',
        ),
        'PSH+ACK': t(
          'core-network-capture.flag_psh_ack',
          {},
          'application data on an established connection',
        ),
        FIN: t(
          'core-network-capture.flag_fin',
          {},
          'a connection closing tidily',
        ),
        RST: t(
          'core-network-capture.flag_rst',
          {},
          'a connection refused or aborted',
        ),
        other: '',
      };
      const wrap = document.createDocumentFragment();
      wrap.appendChild(
        simpleTable(
          [
            t('network.flag', {}, 'Flag'),
            t('network.packets', {}, 'Packets'),
            t('network.share', {}, 'Share'),
            t('network.what_it_is', {}, 'What it is'),
          ],
          capture.flags.map((flag) => [
            flag.label,
            humanCount(flag.packets),
            total ? `${((flag.packets / total) * 100).toFixed(1)}%` : '—',
            meanings[flag.label] || '',
          ]),
          [null, 'size', 'size', null],
        ),
      );
      if (total > 0)
        wrap.appendChild(
          note(
            syn > established
              ? t(
                  'core-network-capture.flags_new',
                  {},
                  'Mostly connection attempts rather than established traffic.',
                )
              : t(
                  'core-network-capture.flags_established',
                  {},
                  'Mostly established traffic doing work rather than new connections.',
                ),
          ),
        );
      parts.push(
        section(t('network.traffic_kind', {}, 'What kind of traffic'), wrap),
      );
    }
    if ((capture.peers || []).length) {
      const wrap = document.createDocumentFragment();
      wrap.appendChild(
        simpleTable(
          [
            t('core-network-capture.remote_address', {}, 'Remote address'),
            t('core-network-capture.in', {}, 'In'),
            t('core-network-capture.out', {}, 'Out'),
            t('network.arriving', {}, 'Arriving'),
            t('network.leaving', {}, 'Leaving'),
            t('core-network-capture.opened', {}, 'Opened'),
            '',
          ],
          capture.peers
            .slice(0, 25)
            .map((peer) => [
              labelled(peer.address, peer.own_label, peer),
              humanCount(peer.in_packets),
              humanCount(peer.out_packets),
              humanBytes(peer.in_bytes),
              humanBytes(peer.out_bytes),
              peer.opened ? humanCount(peer.opened) : '—',
              peer.private
                ? t(
                    'network.private_address',
                    {},
                    'a private address — another machine on this network',
                  )
                : '',
            ]),
          ['name', 'size', 'size', 'size', 'size', 'size', null],
        ),
      );
      const more =
        capture.distinct_peers > capture.peers.length
          ? t(
              'core-network-capture.remote_more',
              { shown: capture.peers.length, total: capture.distinct_peers },
              ' The busiest {{shown, number}} of {{total, number}} are shown.',
            )
          : '';
      wrap.appendChild(
        note(
          t(
            'core-network-capture.remote_help',
            { more },
            'This machine’s own addresses are excluded.{{more}}',
          ),
        ),
      );
      parts.push(
        section(
          t('network.peer_endpoints', {}, 'Who is on the other end'),
          wrap,
        ),
      );
    }
    if ((capture.ports || []).length) {
      const total = capture.ports.reduce(
        (sum, port) => sum + Number(port.packets),
        0,
      );
      parts.push(
        section(
          t('network.which_service', {}, 'Which service'),
          simpleTable(
            [
              t('network.port', {}, 'Port'),
              t('network.packets', {}, 'Packets'),
              t('network.share', {}, 'Share'),
              t('network.bytes', {}, 'Bytes'),
              t('network.usually', {}, 'Usually'),
            ],
            capture.ports
              .slice(0, 20)
              .map((port) => [
                labelled(`${port.port}`, port.own_label),
                humanCount(port.packets),
                total ? `${((port.packets / total) * 100).toFixed(1)}%` : '—',
                humanBytes(port.bytes),
                port.service?.name ?? '',
              ]),
            ['size', 'size', 'size', 'size', 'name'],
          ),
        ),
      );
    }
    if ((capture.asked || []).length)
      parts.push(
        section(
          t('network.looked_up', {}, 'What was looked up'),
          simpleTable(
            [
              t('network.record', {}, 'Record'),
              t('network.name', {}, 'Name'),
              t('network.queries', {}, 'Queries'),
            ],
            capture.asked
              .slice(0, 40)
              .map((asked) => [
                asked.how,
                asked.what,
                humanCount(asked.packets),
              ]),
            ['size', 'name', 'size'],
          ),
        ),
      );
    if ((capture.visited || []).length) {
      parts.push(
        section(
          t('network.requested_from_peers', {}, 'What it was asking them for'),
          simpleTable(
            [
              t('network.how', {}, 'How'),
              t('network.what', {}, 'What'),
              t('network.packets', {}, 'Packets'),
            ],
            capture.visited
              .slice(0, 40)
              .map((visit) => [
                { https: 'TLS', http: 'HTTP', page: 'page' }[visit.how] ??
                  visit.how,
                visit.what,
                humanCount(visit.packets),
              ]),
            ['size', 'name', 'size'],
          ),
        ),
      );
      parts.push(
        note(
          t(
            'core-network-capture.cleartext_help',
            {},
            'Read from traffic that travels in the clear.',
          ),
        ),
      );
    } else if (capture.read_asked)
      parts.push(
        note(
          t(
            'core-network-capture.no_contents_help',
            {},
            'Contents were read and nothing recognisable came out of them.',
          ),
        ),
      );
    if ((capture.names || []).length)
      parts.push(
        section(
          t('core-network-capture.names_asked', {}, 'Names asked for'),
          simpleTable(
            [
              t('network.name', {}, 'Name'),
              t('network.queries', {}, 'Queries'),
            ],
            capture.names
              .slice(0, 25)
              .map((name) => [name.label, humanCount(name.packets)]),
            ['name', 'size'],
          ),
        ),
      );
    target.replaceChildren(...parts);
  },
};
