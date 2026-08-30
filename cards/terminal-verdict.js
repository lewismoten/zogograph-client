/* Core card package: a terminal command's current or final outcome. */
window.ZogographTerminalVerdictCard = {
  manifest: Object.freeze({
    id: 'core-terminal-verdict',
    locales: ['core-terminal-verdict.'],
    helpers: ['humanSpan', 'signalNames', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({ 'core-terminal-verdict.running': 'running' }),
      es: Object.freeze({ 'core-terminal-verdict.running': 'en ejecución' }),
      hi: Object.freeze({ 'core-terminal-verdict.running': 'चल रहा है' }),
      ar: Object.freeze({ 'core-terminal-verdict.running': 'قيد التشغيل' }),
      zh: Object.freeze({ 'core-terminal-verdict.running': '正在运行' }),
    }),
  }),

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
      bits.push(i18n.t('terminal.status_stopped', {}, 'stopped'));
    } else if (entry.timed_out) {
      chip.classList.add('bad');
      bits.push(i18n.t('terminal.status_timed_out', {}, 'timed out'));
    } else if (entry.capped) {
      chip.classList.add('bad');
      bits.push(
        i18n.t('terminal.status_output_ceiling', {}, 'hit the output ceiling'),
      );
    } else if (entry.exit_code === 0) {
      bits.push(i18n.t('terminal.status_ok', {}, 'ok'));
    } else if (entry.exit_code !== null && entry.exit_code !== undefined) {
      chip.classList.add('bad');
      bits.push(
        entry.exit_code > 128
          ? i18n.t(
              'terminal.status_killed',
              {
                signal:
                  signalNames[entry.exit_code - 128] ??
                  i18n.t(
                    'terminal.status_signal',
                    { number: entry.exit_code - 128 },
                    `signal ${entry.exit_code - 128}`,
                  ),
              },
              'killed ({{signal}})',
            )
          : i18n.t(
              'terminal.status_exit',
              { code: entry.exit_code },
              'exit {{code}}',
            ),
      );
    }
    chip.textContent = bits.join(' · ');
    return chip;
  },
};
