/* Core card package: model-reported progress in a conversation. */
window.ZogographChatProgressCard = {
  manifest: Object.freeze({
    id: 'core-chat-progress',
    locales: ['core-chat-progress.'],
    helpers: ['humanSpan', 'humanTime', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-chat-progress.working': 'Working',
        'core-chat-progress.unknown':
          'Working — the size of this job is not known.',
        'core-chat-progress.steps': 'steps',
        'core-chat-progress.count': '{{done}} of {{total}} {{unit}} done',
        'core-chat-progress.left': ' · about {{duration}} left',
        'core-chat-progress.indeterminate_title':
          'Started {{time}}. The sweep is decoration; it does not track anything.',
        'core-chat-progress.drawn_title':
          'Drawn {{time}}. It does not move on its own.',
      }),
      es: Object.freeze({
        'core-chat-progress.working': 'En curso',
        'core-chat-progress.unknown':
          'En curso: no se conoce el tamaño de este trabajo.',
        'core-chat-progress.steps': 'pasos',
        'core-chat-progress.count':
          '{{done}} de {{total}} {{unit}} completados',
        'core-chat-progress.left': ' · quedan unos {{duration}}',
        'core-chat-progress.indeterminate_title':
          'Comenzó {{time}}. El barrido es decorativo; no mide el progreso.',
        'core-chat-progress.drawn_title':
          'Dibujado {{time}}. No se mueve por sí solo.',
      }),
      hi: Object.freeze({
        'core-chat-progress.working': 'काम चल रहा है',
        'core-chat-progress.unknown':
          'काम चल रहा है — इस कार्य का आकार ज्ञात नहीं है।',
        'core-chat-progress.steps': 'चरण',
        'core-chat-progress.count':
          '{{total}} में से {{done}} {{unit}} पूरे हुए',
        'core-chat-progress.left': ' · लगभग {{duration}} शेष',
        'core-chat-progress.indeterminate_title':
          '{{time}} पर शुरू हुआ। यह गति सजावटी है; यह कुछ ट्रैक नहीं करती।',
        'core-chat-progress.drawn_title':
          '{{time}} पर बनाया गया। यह अपने आप नहीं चलता।',
      }),
      ar: Object.freeze({
        'core-chat-progress.working': 'جارٍ العمل',
        'core-chat-progress.unknown': 'جارٍ العمل — حجم هذه المهمة غير معروف.',
        'core-chat-progress.steps': 'خطوات',
        'core-chat-progress.count': 'اكتمل {{done}} من {{total}} {{unit}}',
        'core-chat-progress.left': ' · يتبقى نحو {{duration}}',
        'core-chat-progress.indeterminate_title':
          'بدأ في {{time}}. الحركة للزينة ولا تتعقب شيئاً.',
        'core-chat-progress.drawn_title': 'رُسم في {{time}}. لا يتحرك بنفسه.',
      }),
      zh: Object.freeze({
        'core-chat-progress.working': '正在处理',
        'core-chat-progress.unknown': '正在处理——此任务的工作量未知。',
        'core-chat-progress.steps': '个步骤',
        'core-chat-progress.count':
          '{{total}} 个 {{unit}} 中已完成 {{done}} 个',
        'core-chat-progress.left': ' · 约剩 {{duration}}',
        'core-chat-progress.indeterminate_title':
          '开始于 {{time}}。扫动效果只是装饰，不跟踪任何进度。',
        'core-chat-progress.drawn_title': '绘制于 {{time}}。它不会自行移动。',
      }),
    }),
  }),
  render(message, helpers) {
    const args = message.arguments || {};
    const kind = ['time', 'count', 'working'].includes(args.kind)
      ? args.kind
      : 'time';
    const t = (key, values, fallback) => helpers.i18n.t(key, values, fallback);
    const card = document.createElement('div');
    card.className = 'chat-progress';
    const head = document.createElement('div');
    head.className = 'chat-progress-head';
    head.textContent = String(
      args.what || t('core-chat-progress.working', {}, 'Working'),
    );
    const track = document.createElement('div');
    track.className = 'chat-progress-track';
    const fill = document.createElement('div');
    fill.className = 'chat-progress-fill';
    track.appendChild(fill);
    const said = document.createElement('div');
    said.className = 'muted small';
    if (kind === 'working') {
      track.classList.add('indeterminate');
      said.textContent = t(
        'core-chat-progress.unknown',
        {},
        'Working — the size of this job is not known.',
      );
    } else if (kind === 'count') {
      const total = Math.max(1, Number(args.total) || 1),
        done = Math.max(0, Math.min(total, Number(args.done) || 0)),
        unit = String(args.unit || t('core-chat-progress.steps', {}, 'steps'));
      fill.style.setProperty('--fill', `${Math.round((done / total) * 100)}%`);
      said.textContent = t(
        'core-chat-progress.count',
        { done, total, unit },
        '{{done}} of {{total}} {{unit}} done',
      );
    } else {
      const percent = Math.max(0, Math.min(100, Number(args.percent) || 0)),
        left = Number(args.seconds_left);
      fill.style.setProperty('--fill', `${percent}%`);
      said.textContent =
        `${percent}%` +
        (Number.isFinite(left) && left > 0
          ? t(
              'core-chat-progress.left',
              { duration: helpers.humanSpan(left, true) },
              ' · about {{duration}} left',
            )
          : '');
    }
    said.title = t(
      kind === 'working'
        ? 'core-chat-progress.indeterminate_title'
        : 'core-chat-progress.drawn_title',
      { time: helpers.humanTime(message.at) },
      kind === 'working'
        ? 'Started {{time}}. The sweep is decoration; it does not track anything.'
        : 'Drawn {{time}}. It does not move on its own.',
    );
    card.append(head, track, said);
    return card;
  },
};
