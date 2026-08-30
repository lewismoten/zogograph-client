/* Core card package: destructive retention control for AI conversations. */
window.ZogographAiForgetFormCard = {
  manifest: Object.freeze({
    id: 'core-ai-forget-form',
    locales: ['core-ai-forget-form.'],
    helpers: [
      'confirmDialog',
      'post',
      'notice',
      'count',
      'humanBytes',
      'renderAiStore',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-forget-form.help':
          'Removes whole conversations that have been finished longer than this, with their messages, their tool runs and their cost records. The running totals on the {{assistant}} settings page are added up from those records, so they will drop too — this is deleting history, not tidying it.',
        'core-ai-forget-form.week': 'older than a week',
        'core-ai-forget-form.month': 'older than a month',
        'core-ai-forget-form.three_months': 'older than three months',
        'core-ai-forget-form.year': 'older than a year',
        'core-ai-forget-form.all': 'every finished conversation',
        'core-ai-forget-form.action': 'Forget them',
        'core-ai-forget-form.confirm':
          'Delete {{choice}}?\n\nThe transcripts, the tool results and what each one cost all go. This cannot be undone from here.',
        'core-ai-forget-form.result':
          '{{conversations}} forgotten, {{bytes}} reclaimed.',
        'core-ai-forget-form.nothing': 'There was nothing that old to forget.',
      }),
      es: Object.freeze({
        'core-ai-forget-form.help':
          'Elimina conversaciones terminadas, incluidos mensajes, herramientas y costes.',
        'core-ai-forget-form.week': 'de hace más de una semana',
        'core-ai-forget-form.month': 'de hace más de un mes',
        'core-ai-forget-form.three_months': 'de hace más de tres meses',
        'core-ai-forget-form.year': 'de hace más de un año',
        'core-ai-forget-form.all': 'toda conversación terminada',
        'core-ai-forget-form.action': 'Olvidarlas',
        'core-ai-forget-form.confirm':
          '¿Eliminar {{choice}}? Esta acción no se puede deshacer.',
        'core-ai-forget-form.result':
          '{{conversations}} olvidadas; {{bytes}} recuperados.',
        'core-ai-forget-form.nothing': 'No había nada tan antiguo que olvidar.',
      }),
      hi: Object.freeze({
        'core-ai-forget-form.help':
          'पुरानी पूरी बातचीत, संदेश, टूल रन और लागत रिकॉर्ड हटाता है।',
        'core-ai-forget-form.week': 'एक सप्ताह से पुरानी',
        'core-ai-forget-form.month': 'एक महीने से पुरानी',
        'core-ai-forget-form.three_months': 'तीन महीने से पुरानी',
        'core-ai-forget-form.year': 'एक साल से पुरानी',
        'core-ai-forget-form.all': 'हर समाप्त बातचीत',
        'core-ai-forget-form.action': 'भूलें',
        'core-ai-forget-form.confirm':
          '{{choice}} हटाएँ? इसे वापस नहीं किया जा सकता।',
        'core-ai-forget-form.result':
          '{{conversations}} भूली गईं, {{bytes}} मुक्त हुए।',
        'core-ai-forget-form.nothing': 'भूलने के लिए इतना पुराना कुछ नहीं था।',
      }),
      ar: Object.freeze({
        'core-ai-forget-form.help':
          'يزيل المحادثات المكتملة ورسائلها ونتائج أدواتها وسجلات تكلفتها.',
        'core-ai-forget-form.week': 'أقدم من أسبوع',
        'core-ai-forget-form.month': 'أقدم من شهر',
        'core-ai-forget-form.three_months': 'أقدم من ثلاثة أشهر',
        'core-ai-forget-form.year': 'أقدم من سنة',
        'core-ai-forget-form.all': 'كل محادثة مكتملة',
        'core-ai-forget-form.action': 'انسها',
        'core-ai-forget-form.confirm':
          'هل تحذف {{choice}}؟ لا يمكن التراجع عن ذلك.',
        'core-ai-forget-form.result':
          'نُسيت {{conversations}} واستُعيد {{bytes}}.',
        'core-ai-forget-form.nothing': 'لا يوجد شيء قديم لهذا الحد.',
      }),
      zh: Object.freeze({
        'core-ai-forget-form.help':
          '删除超过所选时间的完整对话、消息、工具运行记录和费用记录。',
        'core-ai-forget-form.week': '超过一周',
        'core-ai-forget-form.month': '超过一个月',
        'core-ai-forget-form.three_months': '超过三个月',
        'core-ai-forget-form.year': '超过一年',
        'core-ai-forget-form.all': '所有已结束的对话',
        'core-ai-forget-form.action': '删除',
        'core-ai-forget-form.confirm': '删除 {{choice}}？此操作无法撤销。',
        'core-ai-forget-form.result':
          '已删除 {{conversations}}，释放 {{bytes}}。',
        'core-ai-forget-form.nothing': '没有符合条件的旧对话。',
      }),
    }),
  }),
  render(input, helpers) {
    const {
      confirmDialog,
      post,
      notice,
      count,
      humanBytes,
      renderAiStore,
      i18n,
    } = helpers;
    const t = (key, vars, fallback) => i18n.t(key, vars, fallback);
    const wrap = document.createElement('div');
    const said = document.createElement('p');
    said.className = 'muted small';
    said.textContent = t(
      'core-ai-forget-form.help',
      { assistant: input.assistant },
      'Removes whole conversations that have been finished longer than this, with their messages, their tool runs and their cost records. The running totals on the {{assistant}} settings page are added up from those records, so they will drop too — this is deleting history, not tidying it.',
    );
    const row = document.createElement('div');
    row.className = 'pattern-row';
    const days = document.createElement('select');
    for (const [value, key, fallback] of [
      [7, 'week', 'older than a week'],
      [30, 'month', 'older than a month'],
      [90, 'three_months', 'older than three months'],
      [365, 'year', 'older than a year'],
      [0, 'all', 'every finished conversation'],
    ]) {
      const option = document.createElement('option');
      option.value = String(value);
      option.textContent = t(`core-ai-forget-form.${key}`, {}, fallback);
      option.selected = value === 90;
      days.appendChild(option);
    }
    const go = document.createElement('button');
    go.type = 'button';
    go.className = 'danger';
    go.textContent = t('core-ai-forget-form.action', {}, 'Forget them');
    go.addEventListener('click', async () => {
      const choice = days.options[days.selectedIndex].textContent;
      if (
        !(await confirmDialog(
          t(
            'core-ai-forget-form.confirm',
            { choice },
            'Delete {{choice}}?\n\nThe transcripts, the tool results and what each one cost all go. This cannot be undone from here.',
          ),
        ))
      )
        return;
      go.disabled = true;
      try {
        const result = await post('ai-forget', { days: days.value });
        notice(
          result.sessions
            ? t(
                'core-ai-forget-form.result',
                {
                  conversations: count(result.sessions, 'conversation'),
                  bytes: humanBytes(result.reclaimed),
                },
                '{{conversations}} forgotten, {{bytes}} reclaimed.',
              )
            : t(
                'core-ai-forget-form.nothing',
                {},
                'There was nothing that old to forget.',
              ),
          true,
        );
        renderAiStore(result);
      } catch (error) {
        notice(String(error.message || error));
      } finally {
        go.disabled = false;
      }
    });
    row.append(days, go);
    wrap.append(said, row);
    return wrap;
  },
};
