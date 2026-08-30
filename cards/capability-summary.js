/* Core card package: the shell supplies fact, timestamp, and pending-task helpers. */
window.ZogographCapabilitySummaryCard = {
  manifest: Object.freeze({
    id: 'core-capability-summary',
    locales: ['core-capability-summary.'],
    helpers: ['target', 'factGrid', 'note', 'ago', 'pendingHostNote', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-capability-summary.worth_having': 'Worth having',
        'core-capability-summary.whole_catalogue': 'Whole catalogue',
        'core-capability-summary.available_here':
          '{{available}} of {{total}} available here',
        'core-capability-summary.host_only': 'On the host, not reachable',
        'core-capability-summary.alternate': 'Only under another path',
        'core-capability-summary.missing': 'Not installed anywhere',
        'core-capability-summary.host_not_asked': 'Host not asked',
        'core-capability-summary.host_not_asked_value':
          '{{count}} — press “Ask the host”',
        'core-capability-summary.account_checked': 'Account checked {{when}}',
        'core-capability-summary.host_checked': 'host checked {{when}}',
      }),
      es: Object.freeze({
        'core-capability-summary.worth_having': 'Conviene tener',
        'core-capability-summary.whole_catalogue': 'Catálogo completo',
        'core-capability-summary.available_here':
          '{{available}} de {{total}} disponibles aquí',
        'core-capability-summary.host_only': 'En el host, no accesible',
        'core-capability-summary.alternate': 'Solo en otra ruta',
        'core-capability-summary.missing': 'No instalado en ninguna parte',
        'core-capability-summary.host_not_asked': 'No se preguntó al host',
        'core-capability-summary.host_not_asked_value':
          '{{count}} — pulse «Preguntar al host»',
        'core-capability-summary.account_checked': 'Cuenta comprobada {{when}}',
        'core-capability-summary.host_checked': 'host comprobado {{when}}',
      }),
      hi: Object.freeze({
        'core-capability-summary.worth_having': 'रखना उपयोगी',
        'core-capability-summary.whole_catalogue': 'पूरा कैटलॉग',
        'core-capability-summary.available_here':
          '{{available}} में से {{total}} यहां उपलब्ध',
        'core-capability-summary.host_only': 'होस्ट पर, पहुंच योग्य नहीं',
        'core-capability-summary.alternate': 'केवल दूसरे पथ पर',
        'core-capability-summary.missing': 'कहीं भी स्थापित नहीं',
        'core-capability-summary.host_not_asked': 'होस्ट से नहीं पूछा गया',
        'core-capability-summary.host_not_asked_value':
          '{{count}} — “होस्ट से पूछें” दबाएं',
        'core-capability-summary.account_checked': 'खाता {{when}} जांचा गया',
        'core-capability-summary.host_checked': 'होस्ट {{when}} जांचा गया',
      }),
      ar: Object.freeze({
        'core-capability-summary.worth_having': 'مفيد توفره',
        'core-capability-summary.whole_catalogue': 'الفهرس بالكامل',
        'core-capability-summary.available_here':
          '{{available}} من {{total}} متاح هنا',
        'core-capability-summary.host_only': 'على المضيف، لا يمكن الوصول إليه',
        'core-capability-summary.alternate': 'في مسار آخر فقط',
        'core-capability-summary.missing': 'غير مثبت في أي مكان',
        'core-capability-summary.host_not_asked': 'لم يُسأل المضيف',
        'core-capability-summary.host_not_asked_value':
          '{{count}} — اضغط «اسأل المضيف»',
        'core-capability-summary.account_checked': 'فُحص الحساب {{when}}',
        'core-capability-summary.host_checked': 'فُحص المضيف {{when}}',
      }),
      zh: Object.freeze({
        'core-capability-summary.worth_having': '值得具备',
        'core-capability-summary.whole_catalogue': '完整目录',
        'core-capability-summary.available_here':
          '此处可用 {{available}} / {{total}}',
        'core-capability-summary.host_only': '在主机上，无法访问',
        'core-capability-summary.alternate': '仅在其他路径下',
        'core-capability-summary.missing': '未在任何位置安装',
        'core-capability-summary.host_not_asked': '尚未询问主机',
        'core-capability-summary.host_not_asked_value':
          '{{count}} — 点击“询问主机”',
        'core-capability-summary.account_checked': '帐户检查于 {{when}}',
        'core-capability-summary.host_checked': '主机检查于 {{when}}',
      }),
    }),
  }),

  render(data, helpers) {
    const { target, factGrid, note, ago, pendingHostNote, i18n } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (!target || !data.summary) return;
    const summary = data.summary;
    const available = (count, total) =>
      t(
        'core-capability-summary.available_here',
        { available: count, total },
        '{{available}} of {{total}} available here',
      );
    const facts = [
      [
        t('core-capability-summary.worth_having', {}, 'Worth having'),
        available(summary.key_available, summary.key_total),
      ],
      [
        t('core-capability-summary.whole_catalogue', {}, 'Whole catalogue'),
        available(summary.counts.available, summary.total),
      ],
    ];
    if (summary.counts.host_only) {
      facts.push([
        t(
          'core-capability-summary.host_only',
          {},
          'On the host, not reachable',
        ),
        String(summary.counts.host_only),
      ]);
    }
    if (summary.counts.alternate) {
      facts.push([
        t('core-capability-summary.alternate', {}, 'Only under another path'),
        String(summary.counts.alternate),
      ]);
    }
    if (summary.counts.missing) {
      facts.push([
        t('core-capability-summary.missing', {}, 'Not installed anywhere'),
        String(summary.counts.missing),
      ]);
    }
    if (summary.counts.unknown_host) {
      facts.push([
        t('core-capability-summary.host_not_asked', {}, 'Host not asked'),
        t(
          'core-capability-summary.host_not_asked_value',
          { count: summary.counts.unknown_host },
          '{{count}} — press “Ask the host”',
        ),
      ]);
    }
    const when = [];
    if (data.checked_at) {
      when.push(
        t(
          'core-capability-summary.account_checked',
          { when: ago(data.checked_at) },
          'Account checked {{when}}',
        ),
      );
    }
    if (data.host_checked_at) {
      when.push(
        t(
          'core-capability-summary.host_checked',
          { when: ago(data.host_checked_at) },
          'host checked {{when}}',
        ),
      );
    }
    const fragment = document.createDocumentFragment();
    fragment.appendChild(factGrid(facts));
    if (when.length) fragment.appendChild(note(`${when.join(', ')}.`));
    fragment.appendChild(pendingHostNote(data));
    target.replaceChildren(fragment);
  },
};
