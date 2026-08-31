/* Core card package: unexplained filesystem space from the host disk walk. */
window.ZogographHostDiskGapCard = {
  manifest: Object.freeze({
    id: 'core-host-disk-gap',
    locales: ['core-host-disk-gap.'],
    helpers: ['target', 'section', 'factGrid', 'note', 'humanBytes', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-host-disk-gap.mount_used': '{{mount}} says used',
        'core-host-disk-gap.found_by_walking': 'Found by walking',
        'core-host-disk-gap.walk_stopped_early': ' (walk stopped early)',
        'core-host-disk-gap.deleted_open': 'Deleted but still open',
        'core-host-disk-gap.unaccounted': 'Still unaccounted for',
        'core-host-disk-gap.title': 'The gap',
        'core-host-disk-gap.incomplete_help':
          'The walk ran out of time, so “found” is a lower bound and the gap is an upper one. Raise host.walk_seconds and run it again before chasing the difference.',
      }),
      es: Object.freeze({
        'core-host-disk-gap.mount_used': '{{mount}} indica usado',
        'core-host-disk-gap.found_by_walking': 'Encontrado al recorrer',
        'core-host-disk-gap.walk_stopped_early':
          ' (el recorrido se detuvo antes)',
        'core-host-disk-gap.deleted_open': 'Eliminado, pero aún abierto',
        'core-host-disk-gap.unaccounted': 'Aún sin contabilizar',
        'core-host-disk-gap.title': 'La diferencia',
        'core-host-disk-gap.incomplete_help':
          'El recorrido se quedó sin tiempo, por lo que «encontrado» es un límite inferior y la diferencia es uno superior. Aumente host.walk_seconds y ejecútelo de nuevo antes de investigar la diferencia.',
      }),
      hi: Object.freeze({
        'core-host-disk-gap.mount_used': '{{mount}} उपयोग हुआ बताता है',
        'core-host-disk-gap.found_by_walking': 'वॉक से मिला',
        'core-host-disk-gap.walk_stopped_early': ' (वॉक जल्दी रुक गया)',
        'core-host-disk-gap.deleted_open': 'हटाया गया, लेकिन अभी खुला है',
        'core-host-disk-gap.unaccounted': 'अभी भी हिसाब से बाहर',
        'core-host-disk-gap.title': 'अंतर',
        'core-host-disk-gap.incomplete_help':
          'वॉक का समय समाप्त हो गया, इसलिए “मिला” निचली सीमा है और अंतर ऊपरी सीमा है। अंतर का पीछा करने से पहले host.walk_seconds बढ़ाएँ और इसे फिर चलाएँ।',
      }),
      ar: Object.freeze({
        'core-host-disk-gap.mount_used': 'يقول {{mount}} إنه مستخدم',
        'core-host-disk-gap.found_by_walking': 'عُثر عليه بالجولة',
        'core-host-disk-gap.walk_stopped_early': ' (توقفت الجولة مبكرًا)',
        'core-host-disk-gap.deleted_open': 'محذوف لكنه ما زال مفتوحًا',
        'core-host-disk-gap.unaccounted': 'ما زال غير محسوب',
        'core-host-disk-gap.title': 'الفجوة',
        'core-host-disk-gap.incomplete_help':
          'نفد وقت الجولة، لذا فإن «العثور» حد أدنى والفجوة حد أعلى. ارفع host.walk_seconds وشغّلها مجددًا قبل تتبع الفرق.',
      }),
      zh: Object.freeze({
        'core-host-disk-gap.mount_used': '{{mount}} 显示已用',
        'core-host-disk-gap.found_by_walking': '遍历找到',
        'core-host-disk-gap.walk_stopped_early': '（遍历提前停止）',
        'core-host-disk-gap.deleted_open': '已删除但仍打开',
        'core-host-disk-gap.unaccounted': '仍无法解释',
        'core-host-disk-gap.title': '差额',
        'core-host-disk-gap.incomplete_help':
          '遍历已超时，因此“找到”的是下限，而差额是上限。在追查差额前，请提高 host.walk_seconds 并再次运行。',
      }),
    }),
  }),
  text(key, values, helpers) {
    return helpers.i18n.t(
      `core-host-disk-gap.${key}`,
      values,
      this.manifest.catalogs.en[`core-host-disk-gap.${key}`],
    );
  },
  render({ gap }, helpers) {
    const { target, section, factGrid, note, humanBytes, i18n } = helpers;
    if (!target || !gap) return;
    const t = (key, values, fallback) =>
      i18n.t(`core-host-disk-gap.${key}`, values, fallback);
    const facts = [
      [
        t('mount_used', { mount: gap.mount }, '{{mount}} says used'),
        humanBytes(gap.used_bytes),
      ],
      [
        t('found_by_walking', {}, 'Found by walking'),
        humanBytes(gap.found_bytes) +
          (gap.complete
            ? ''
            : t('walk_stopped_early', {}, ' (walk stopped early)')),
      ],
    ];
    if (gap.deleted_bytes > 0) {
      facts.push([
        t('deleted_open', {}, 'Deleted but still open'),
        humanBytes(gap.deleted_bytes),
      ]);
    }
    facts.push([
      t('unaccounted', {}, 'Still unaccounted for'),
      humanBytes(gap.missing_bytes),
    ]);
    target.appendChild(section(t('title', {}, 'The gap'), factGrid(facts)));
    if (!gap.complete) {
      target.appendChild(
        note(
          t(
            'incomplete_help',
            {},
            'The walk ran out of time, so “found” is a lower bound and the gap is an upper one. Raise host.walk_seconds and run it again before chasing the difference.',
          ),
        ),
      );
    }
  },
};
