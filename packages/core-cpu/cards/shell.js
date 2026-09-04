/* Core package: static introduction for the CPU page. */
const CpuShellCard = {
  manifest: Object.freeze({
    id: 'core-cpu-shell',
    locales: ['core-cpu-shell.'],
    owner: 'core-cpu',
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-cpu-shell.help':
          'How hard the processor is working, and how much of it this account may use. Inside CageFS the account’s own percentage is of its allowance rather than of the machine, so a pinned account and an idle server are the ordinary shape of hitting a limit rather than a contradiction.',
        'core-cpu-shell.reading_collector': 'Reading the host collector…',
        'core-cpu-shell.no_host_readings': 'No host readings.',
        'core-cpu-shell.no_breakdown':
          'No processor breakdown in this range yet — the collector records it from the version that added these pages onward',
        'core-cpu-shell.only_one_reading':
          'Only one reading so far — a percentage needs two',
        'core-cpu-shell.add_root_cron':
          "As root — ssh in or use WHM's Terminal — add this to root's crontab with crontab -e:",
        'core-cpu-shell.run_root_once':
          'Then run it once by hand to see what it finds: {{command}}',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-cpu/locales/es/shell.json',
      hi: 'packages/core-cpu/locales/hi/shell.json',
      ar: 'packages/core-cpu/locales/ar/shell.json',
      zh: 'packages/core-cpu/locales/zh/shell.json',
    }),
  }),
  text(key, valuesOrHelpers = {}, maybeHelpers = null) {
    const helpers = maybeHelpers || valuesOrHelpers;
    const values = maybeHelpers ? valuesOrHelpers : {};
    return helpers.i18n.t(
      `core-cpu-shell.${key}`,
      values,
      this.manifest.catalogs.en[`core-cpu-shell.${key}`],
    );
  },
  render() {},
};
Zogograph.cards.register(CpuShellCard);
