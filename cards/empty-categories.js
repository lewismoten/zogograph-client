/* Core card package: empty watched storage categories. */
window.ZogographEmptyCategoriesCard = {
  manifest: Object.freeze({
    id: 'core-empty-categories',
    locales: ['core-empty-categories.'],
    helpers: [
      'holder',
      'signedIn',
      'deletable',
      'totalsByCategory',
      'emptiedByCategory',
      'humanBytes',
      'humanTime',
      'humanCount',
      'count',
      'confirmDialog',
      'post',
      'notice',
      'refreshAll',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({ 'core-empty-categories.ready': 'Empty categories' }),
      es: Object.freeze({ 'core-empty-categories.ready': 'Vaciar categorías' }),
      hi: Object.freeze({
        'core-empty-categories.ready': 'श्रेणियाँ खाली करें',
      }),
      ar: Object.freeze({ 'core-empty-categories.ready': 'إفراغ الفئات' }),
      zh: Object.freeze({ 'core-empty-categories.ready': '清空类别' }),
    }),
  }),
  render(input, helpers) {
    const {
      holder,
      signedIn,
      deletable,
      totalsByCategory,
      emptiedByCategory,
      humanBytes,
      humanTime,
      humanCount,
      count,
      confirmDialog,
      post,
      notice,
      refreshAll,
      i18n,
    } = helpers;
    if (!holder) return;
    const ZogographI18n = i18n;
    if (!signedIn || !deletable.length) {
      holder.replaceChildren();
      return;
    }

    holder.replaceChildren(
      ...deletable.map((category) => {
        const totals = (totalsByCategory || {})[category] || {
          bytes: 0,
          files: 0,
        };
        const emptiedAt = (emptiedByCategory || {})[category];

        const card = document.createElement('div');
        card.className = 'card empty-card';

        const label = document.createElement('div');
        label.className = 'label';
        label.textContent =
          category.charAt(0).toUpperCase() + category.slice(1);
        card.appendChild(label);

        const value = document.createElement('div');
        value.className = 'value';
        value.textContent = humanBytes(totals.bytes);
        // Struck through once emptied: the figure was true when the scan ran.
        if (emptiedAt) value.classList.add('struck-through');
        card.appendChild(value);

        const sub = document.createElement('div');
        sub.className = 'sub muted';
        sub.textContent = totals.files
          ? count(totals.files, 'file')
          : ZogographI18n.t(
              'storage.nothing_counted',
              {},
              'nothing counted here',
            );
        if (emptiedAt) sub.classList.add('struck-through');
        card.appendChild(sub);

        if (emptiedAt) {
          /*
           * Emptied already, so no button - the same reasoning as a deleted row,
           * and the same shape: the figures struck through because they were true
           * when the scan ran, and one line saying what happened. The card stays
           * because the category still exists and will fill up again.
           */
          const done = document.createElement('div');
          done.className = 'muted small';
          done.textContent = ZogographI18n.t(
            'storage.emptied_at',
            { at: humanTime(emptiedAt) },
            'emptied {{at}}',
          );
          done.title = ZogographI18n.t(
            'storage.emptied_help',
            {},
            'The next scan will show what is in it now.',
          );
          card.appendChild(done);
          return card;
        }

        const button = document.createElement('button');
        button.className = 'danger small';
        button.textContent = ZogographI18n.t('storage.empty', {}, 'Empty');
        button.disabled = !totals.bytes && !totals.files;

        button.addEventListener('click', async () => {
          if (
            !(await confirmDialog(
              ZogographI18n.t(
                'storage.empty_category_confirm',
                {
                  category,
                  size: humanBytes(totals.bytes),
                  files: count(totals.files, 'file'),
                },
                'Delete every file counted as {{category}}? {{size}} across {{files}}, as of the last scan. This cannot be undone.',
              ),
            ))
          )
            return;

          button.disabled = true;
          try {
            const result = await post('empty', { category });
            notice(
              ZogographI18n.t(
                'storage.empty_result',
                {
                  category,
                  files: humanCount(result.files_removed),
                  size: humanBytes(result.bytes_freed),
                  linked: result.still_linked
                    ? ZogographI18n.t(
                        'storage.empty_result_linked',
                        { files: count(result.still_linked, 'file') },
                        ' {{files}} had another name, so freed nothing.',
                      )
                    : '',
                  failed: result.failed
                    ? ZogographI18n.t(
                        'storage.empty_result_failed',
                        { files: result.failed },
                        ' {{files}} could not be removed.',
                      )
                    : '',
                },
                'Emptied {{category}}: {{files}}, {{size}} freed.{{linked}}{{failed}}',
              ),
              true,
            );
            await refreshAll();
          } catch (error) {
            notice(String(error.message || error));
            button.disabled = false;
          }
        });

        card.appendChild(button);
        return card;
      }),
    );
  },
};
