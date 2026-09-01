/* Core card package: a terminal command's current or final outcome. */
window.ZogographTerminalVerdictCard = {
  manifest: Object.freeze({
    id: 'core-terminal-verdict',
    locales: ['core-terminal-verdict.'],
    helpers: ['humanSpan', 'signalNames', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-terminal-verdict.running': 'running',
        'core-terminal-verdict.stopped': 'stopped',
        'core-terminal-verdict.timed_out': 'timed out',
        'core-terminal-verdict.output_ceiling': 'hit the output ceiling',
        'core-terminal-verdict.ok': 'ok',
        'core-terminal-verdict.killed': 'killed ({{signal}})',
        'core-terminal-verdict.signal': 'signal {{number}}',
        'core-terminal-verdict.exit': 'exit {{code}}',
        'core-terminal-verdict.stopped_complete': 'Stopped.',
        'core-terminal-verdict.already_finished': 'It had already finished.',
      }),
      es: Object.freeze({
        'core-terminal-verdict.running': 'en ejecución',
        'core-terminal-verdict.stopped': 'detenido',
        'core-terminal-verdict.timed_out': 'tiempo agotado',
        'core-terminal-verdict.output_ceiling': 'alcanzó el límite de salida',
        'core-terminal-verdict.ok': 'correcto',
        'core-terminal-verdict.killed': 'terminado ({{signal}})',
        'core-terminal-verdict.signal': 'señal {{number}}',
        'core-terminal-verdict.exit': 'salida {{code}}',
        'core-terminal-verdict.stopped_complete': 'Detenido.',
        'core-terminal-verdict.already_finished': 'Ya había terminado.',
      }),
      hi: Object.freeze({
        'core-terminal-verdict.running': 'चल रहा है',
        'core-terminal-verdict.stopped': 'रुका हुआ',
        'core-terminal-verdict.timed_out': 'समय समाप्त',
        'core-terminal-verdict.output_ceiling': 'आउटपुट सीमा तक पहुंच गया',
        'core-terminal-verdict.ok': 'ठीक',
        'core-terminal-verdict.killed': 'समाप्त किया गया ({{signal}})',
        'core-terminal-verdict.signal': 'सिग्नल {{number}}',
        'core-terminal-verdict.exit': 'निकास {{code}}',
        'core-terminal-verdict.stopped_complete': 'रोक दिया गया।',
        'core-terminal-verdict.already_finished':
          'यह पहले ही समाप्त हो चुका था।',
      }),
      ar: Object.freeze({
        'core-terminal-verdict.running': 'قيد التشغيل',
        'core-terminal-verdict.stopped': 'متوقف',
        'core-terminal-verdict.timed_out': 'انتهت المهلة',
        'core-terminal-verdict.output_ceiling': 'بلغ حد الإخراج',
        'core-terminal-verdict.ok': 'حسنًا',
        'core-terminal-verdict.killed': 'تم إيقافه ({{signal}})',
        'core-terminal-verdict.signal': 'إشارة {{number}}',
        'core-terminal-verdict.exit': 'خروج {{code}}',
        'core-terminal-verdict.stopped_complete': 'تم الإيقاف.',
        'core-terminal-verdict.already_finished': 'كان قد انتهى بالفعل.',
      }),
      zh: Object.freeze({
        'core-terminal-verdict.running': '正在运行',
        'core-terminal-verdict.stopped': '已停止',
        'core-terminal-verdict.timed_out': '已超时',
        'core-terminal-verdict.output_ceiling': '达到输出上限',
        'core-terminal-verdict.ok': '正常',
        'core-terminal-verdict.killed': '已终止（{{signal}}）',
        'core-terminal-verdict.signal': '信号 {{number}}',
        'core-terminal-verdict.exit': '退出 {{code}}',
        'core-terminal-verdict.stopped_complete': '已停止。',
        'core-terminal-verdict.already_finished': '它已结束。',
      }),
    }),
  }),

  text(key, { i18n }) {
    const id = `core-terminal-verdict.${key}`;
    return i18n.t(id, {}, this.manifest.catalogs.en[id]);
  },

  render(entry, helpers) {
    const { humanSpan, signalNames, i18n } = helpers;
    const chip = document.createElement('span');
    chip.className = 'term-chip';
    if (entry.running) {
      chip.classList.add('live');
      chip.textContent = i18n.t('core-terminal-verdict.running', {}, 'running');
      return chip;
    }
    const bits = [];
    if (entry.took !== null && entry.took !== undefined)
      bits.push(humanSpan(entry.took));
    if (entry.stopped_by) {
      chip.classList.add('bad');
      bits.push(i18n.t('core-terminal-verdict.stopped', {}, 'stopped'));
    } else if (entry.timed_out) {
      chip.classList.add('bad');
      bits.push(i18n.t('core-terminal-verdict.timed_out', {}, 'timed out'));
    } else if (entry.capped) {
      chip.classList.add('bad');
      bits.push(
        i18n.t(
          'core-terminal-verdict.output_ceiling',
          {},
          'hit the output ceiling',
        ),
      );
    } else if (entry.exit_code === 0) {
      bits.push(i18n.t('core-terminal-verdict.ok', {}, 'ok'));
    } else if (entry.exit_code !== null && entry.exit_code !== undefined) {
      chip.classList.add('bad');
      bits.push(
        entry.exit_code > 128
          ? i18n.t(
              'core-terminal-verdict.killed',
              {
                signal:
                  signalNames[entry.exit_code - 128] ??
                  i18n.t(
                    'core-terminal-verdict.signal',
                    { number: entry.exit_code - 128 },
                    `signal ${entry.exit_code - 128}`,
                  ),
              },
              'killed ({{signal}})',
            )
          : i18n.t(
              'core-terminal-verdict.exit',
              { code: entry.exit_code },
              'exit {{code}}',
            ),
      );
    }
    chip.textContent = bits.join(' · ');
    return chip;
  },
};
