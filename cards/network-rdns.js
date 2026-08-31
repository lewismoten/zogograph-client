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
      }),
      es: Object.freeze({
        'core-network-rdns.title': 'Cómo llama Internet a una dirección',
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
      }),
      hi: Object.freeze({
        'core-network-rdns.title': 'इंटरनेट किसी पते को क्या कहता है',
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
      }),
      ar: Object.freeze({
        'core-network-rdns.title': 'ما الذي يسميه الإنترنت عنوانًا',
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
      }),
      zh: Object.freeze({
        'core-network-rdns.title': '互联网如何称呼一个地址',
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
      }),
    }),
  }),

  text(key, helpers) {
    return helpers.i18n.t(
      `core-network-rdns.${key}`,
      {},
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
              again.textContent = t('action.ask_again', {}, 'Ask again');
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
