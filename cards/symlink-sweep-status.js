/* Core card package: symlink-sweep progress and result status. */
window.ZogographSymlinkSweepStatusCard = {
  manifest: Object.freeze({
    id: 'core-symlink-sweep-status',
    locales: ['core-symlink-sweep-status.'],
    helpers: ['i18n', 'spinner'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-symlink-sweep-status.removing': 'Removing…',
        'core-symlink-sweep-status.looking': 'Looking…',
        'core-symlink-sweep-status.removed': '{{count}} links removed.',
        'core-symlink-sweep-status.failed': '{{count}} could not be removed.',
        'core-symlink-sweep-status.left_alone': '{{count}} links left alone.',
        'core-symlink-sweep-status.preview':
          '{{matched}} links with a problem are under that path. {{deleted}} broken links would be removed.',
        'core-symlink-sweep-status.preview_skipped':
          '{{matched}} links with a problem are under that path. {{deleted}} broken links would be removed, and {{skipped}} with other problems left alone — a loop or an escape is never removed on a path alone.',
      }),
      es: Object.freeze({
        'core-symlink-sweep-status.removing': 'Eliminando…',
        'core-symlink-sweep-status.looking': 'Buscando…',
        'core-symlink-sweep-status.removed': 'Se eliminaron {{count}} enlaces.',
        'core-symlink-sweep-status.failed':
          'No se pudieron eliminar {{count}}.',
        'core-symlink-sweep-status.left_alone':
          'Se dejaron intactos {{count}} enlaces.',
        'core-symlink-sweep-status.preview':
          'Hay {{matched}} enlaces con problemas bajo esa ruta. Se eliminarían {{deleted}} enlaces rotos.',
        'core-symlink-sweep-status.preview_skipped':
          'Hay {{matched}} enlaces con problemas bajo esa ruta. Se eliminarían {{deleted}} enlaces rotos y se dejarían intactos {{skipped}} con otros problemas: nunca se elimina un bucle o un escape solo por una ruta.',
      }),
      hi: Object.freeze({
        'core-symlink-sweep-status.removing': 'हटाया जा रहा है…',
        'core-symlink-sweep-status.looking': 'देखा जा रहा है…',
        'core-symlink-sweep-status.removed': '{{count}} लिंक हटाए गए।',
        'core-symlink-sweep-status.failed': '{{count}} को हटाया नहीं जा सका।',
        'core-symlink-sweep-status.left_alone':
          '{{count}} लिंक वैसे ही छोड़ दिए गए।',
        'core-symlink-sweep-status.preview':
          'उस पथ के अंतर्गत समस्या वाले {{matched}} लिंक हैं। {{deleted}} टूटे लिंक हटाए जाएंगे।',
        'core-symlink-sweep-status.preview_skipped':
          'उस पथ के अंतर्गत समस्या वाले {{matched}} लिंक हैं। {{deleted}} टूटे लिंक हटाए जाएंगे और अन्य समस्याओं वाले {{skipped}} वैसे ही छोड़ दिए जाएंगे — केवल पथ के आधार पर लूप या एस्केप कभी नहीं हटाया जाता।',
      }),
      ar: Object.freeze({
        'core-symlink-sweep-status.removing': 'جارٍ الإزالة…',
        'core-symlink-sweep-status.looking': 'جارٍ البحث…',
        'core-symlink-sweep-status.removed': 'أُزيلت {{count}} روابط.',
        'core-symlink-sweep-status.failed': 'تعذر إزالة {{count}}.',
        'core-symlink-sweep-status.left_alone': 'تُركت {{count}} روابط كما هي.',
        'core-symlink-sweep-status.preview':
          'توجد {{matched}} روابط بها مشكلة تحت ذلك المسار. ستُزال {{deleted}} روابط معطلة.',
        'core-symlink-sweep-status.preview_skipped':
          'توجد {{matched}} روابط بها مشكلة تحت ذلك المسار. ستُزال {{deleted}} روابط معطلة وتُترك {{skipped}} ذات مشكلات أخرى — لا يُزال رابط حلقي أو هروب بناء على مسار وحده أبدًا.',
      }),
      zh: Object.freeze({
        'core-symlink-sweep-status.removing': '正在移除…',
        'core-symlink-sweep-status.looking': '正在查找…',
        'core-symlink-sweep-status.removed': '已移除 {{count}} 个链接。',
        'core-symlink-sweep-status.failed': '无法移除 {{count}} 个。',
        'core-symlink-sweep-status.left_alone': '已保留 {{count}} 个链接。',
        'core-symlink-sweep-status.preview':
          '该路径下有 {{matched}} 个有问题的链接。将移除 {{deleted}} 个损坏链接。',
        'core-symlink-sweep-status.preview_skipped':
          '该路径下有 {{matched}} 个有问题的链接。将移除 {{deleted}} 个损坏链接，而 {{skipped}} 个有其他问题的链接会保持不变——绝不会只根据路径移除循环或逃逸链接。',
      }),
    }),
  }),
  render(input, helpers) {
    const {
      confirmed,
      deleted,
      failed,
      hasFailed,
      hasSkipped,
      matched,
      mode,
      skipped,
    } = input;
    const { i18n, spinner } = helpers;
    if (mode === 'loading') {
      return spinner(
        i18n.t(
          confirmed
            ? 'core-symlink-sweep-status.removing'
            : 'core-symlink-sweep-status.looking',
          {},
          confirmed ? 'Removing…' : 'Looking…',
        ),
      );
    }

    const said = document.createElement('p');
    said.className = confirmed
      ? hasFailed
        ? 'band-crit'
        : 'band-ok'
      : 'muted small';
    if (confirmed) {
      const summary = [
        i18n.t(
          'core-symlink-sweep-status.removed',
          { count: deleted },
          `${deleted} links removed.`,
        ),
      ];
      if (hasFailed) {
        summary.push(
          i18n.t(
            'core-symlink-sweep-status.failed',
            { count: failed },
            `${failed} could not be removed.`,
          ),
        );
      }
      if (hasSkipped) {
        summary.push(
          i18n.t(
            'core-symlink-sweep-status.left_alone',
            { count: skipped },
            `${skipped} links left alone.`,
          ),
        );
      }
      said.textContent = summary.join(' ');
      return said;
    }

    const values = { deleted, matched, skipped };
    said.textContent = hasSkipped
      ? i18n.t(
          'core-symlink-sweep-status.preview_skipped',
          values,
          `${matched} links with a problem are under that path. ${deleted} broken links would be removed, and ${skipped} with other problems left alone — a loop or an escape is never removed on a path alone.`,
        )
      : i18n.t(
          'core-symlink-sweep-status.preview',
          values,
          `${matched} links with a problem are under that path. ${deleted} broken links would be removed.`,
        );
    return said;
  },
};
