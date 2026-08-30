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
      en: Object.freeze({
        'core-empty-categories.ready': 'Empty categories',
        'core-empty-categories.nothing_counted': 'nothing counted here',
        'core-empty-categories.emptied_at': 'emptied {{at}}',
        'core-empty-categories.emptied_help':
          'The next scan will show what is in it now.',
        'core-empty-categories.empty': 'Empty',
        'core-empty-categories.empty_category_confirm':
          'Delete every file counted as {{category}}? {{size}} across {{files}}, as of the last scan. This cannot be undone.',
        'core-empty-categories.empty_result':
          'Emptied {{category}}: {{files}}, {{size}} freed.{{linked}}{{failed}}',
        'core-empty-categories.empty_result_linked':
          ' {{files}} had another name, so freed nothing.',
        'core-empty-categories.empty_result_failed':
          ' {{files}} could not be removed.',
      }),
      es: Object.freeze({
        'core-empty-categories.ready': 'Vaciar categorías',
        'core-empty-categories.nothing_counted': 'no se ha contado nada aquí',
        'core-empty-categories.emptied_at': 'vaciado {{at}}',
        'core-empty-categories.emptied_help':
          'El próximo análisis mostrará lo que contiene ahora.',
        'core-empty-categories.empty': 'Vaciar',
        'core-empty-categories.empty_category_confirm':
          '¿Eliminar todos los archivos contados como {{category}}? {{size}} en {{files}}, según el último análisis. Esto no se puede deshacer.',
        'core-empty-categories.empty_result':
          'Se vació {{category}}: {{files}}, se liberaron {{size}}.{{linked}}{{failed}}',
        'core-empty-categories.empty_result_linked':
          ' {{files}} tenían otro nombre, por lo que no se liberó nada.',
        'core-empty-categories.empty_result_failed':
          ' No se pudieron eliminar {{files}}.',
      }),
      hi: Object.freeze({
        'core-empty-categories.ready': 'श्रेणियाँ खाली करें',
        'core-empty-categories.nothing_counted': 'यहाँ कुछ नहीं गिना गया',
        'core-empty-categories.emptied_at': '{{at}} को खाली किया गया',
        'core-empty-categories.emptied_help':
          'अगला स्कैन दिखाएगा कि अब इसमें क्या है।',
        'core-empty-categories.empty': 'खाली करें',
        'core-empty-categories.empty_category_confirm':
          '{{category}} के रूप में गिनी गई हर फ़ाइल हटाएँ? पिछले स्कैन के अनुसार {{files}} में {{size}}। इसे पूर्ववत नहीं किया जा सकता।',
        'core-empty-categories.empty_result':
          '{{category}} खाली किया गया: {{files}}, {{size}} मुक्त हुआ।{{linked}}{{failed}}',
        'core-empty-categories.empty_result_linked':
          ' {{files}} का दूसरा नाम था, इसलिए कुछ मुक्त नहीं हुआ।',
        'core-empty-categories.empty_result_failed':
          ' {{files}} हटाए नहीं जा सके।',
      }),
      ar: Object.freeze({
        'core-empty-categories.ready': 'إفراغ الفئات',
        'core-empty-categories.nothing_counted': 'لم يُحتسب شيء هنا',
        'core-empty-categories.emptied_at': 'أُفرغ {{at}}',
        'core-empty-categories.emptied_help':
          'سيُظهر الفحص التالي ما فيه الآن.',
        'core-empty-categories.empty': 'إفراغ',
        'core-empty-categories.empty_category_confirm':
          'هل تريد حذف كل ملف محسوب ضمن {{category}}؟ ‏{{size}} عبر {{files}}، وفق آخر فحص. لا يمكن التراجع عن ذلك.',
        'core-empty-categories.empty_result':
          'تم إفراغ {{category}}: ‏{{files}}، وتم تحرير {{size}}.{{linked}}{{failed}}',
        'core-empty-categories.empty_result_linked':
          ' كان لـ {{files}} اسم آخر، لذلك لم يُحرر شيء.',
        'core-empty-categories.empty_result_failed': ' تعذرت إزالة {{files}}.',
      }),
      zh: Object.freeze({
        'core-empty-categories.ready': '清空类别',
        'core-empty-categories.nothing_counted': '此处未统计任何内容',
        'core-empty-categories.emptied_at': '已于 {{at}} 清空',
        'core-empty-categories.emptied_help': '下次扫描将显示其中现在有什么。',
        'core-empty-categories.empty': '清空',
        'core-empty-categories.empty_category_confirm':
          '删除归入 {{category}} 的所有文件？根据上次扫描，共 {{files}}，大小 {{size}}。此操作无法撤销。',
        'core-empty-categories.empty_result':
          '已清空 {{category}}：{{files}}，释放 {{size}}。{{linked}}{{failed}}',
        'core-empty-categories.empty_result_linked':
          ' {{files}} 还有其他名称，因此未释放空间。',
        'core-empty-categories.empty_result_failed': ' 无法删除 {{files}}。',
      }),
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
              'core-empty-categories.nothing_counted',
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
            'core-empty-categories.emptied_at',
            { at: humanTime(emptiedAt) },
            'emptied {{at}}',
          );
          done.title = ZogographI18n.t(
            'core-empty-categories.emptied_help',
            {},
            'The next scan will show what is in it now.',
          );
          card.appendChild(done);
          return card;
        }

        const button = document.createElement('button');
        button.className = 'danger small';
        button.textContent = ZogographI18n.t(
          'core-empty-categories.empty',
          {},
          'Empty',
        );
        button.disabled = !totals.bytes && !totals.files;

        button.addEventListener('click', async () => {
          if (
            !(await confirmDialog(
              ZogographI18n.t(
                'core-empty-categories.empty_category_confirm',
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
                'core-empty-categories.empty_result',
                {
                  category,
                  files: humanCount(result.files_removed),
                  size: humanBytes(result.bytes_freed),
                  linked: result.still_linked
                    ? ZogographI18n.t(
                        'core-empty-categories.empty_result_linked',
                        { files: count(result.still_linked, 'file') },
                        ' {{files}} had another name, so freed nothing.',
                      )
                    : '',
                  failed: result.failed
                    ? ZogographI18n.t(
                        'core-empty-categories.empty_result_failed',
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
