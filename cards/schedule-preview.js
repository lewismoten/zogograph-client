/* Core card package: schedule preview, offset guidance, and save results. */
window.ZogographSchedulePreviewCard = {
  manifest: Object.freeze({
    id: 'core-schedule-preview',
    locales: ['core-schedule-preview.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-schedule-preview.preview_written': 'This will be written:',
        'core-schedule-preview.apply_changes': 'Apply schedule changes',
        'core-schedule-preview.remove_crontab': 'Remove from the crontab',
        'core-schedule-preview.preview_none':
          "Nothing — saving will remove this dashboard's lines from the crontab.",
        'core-schedule-preview.note_offsets_used':
          'The minute and hour are when it first runs; a repeating schedule then carries on from there at its own interval.',
        'core-schedule-preview.note_offsets_unused':
          'The selected schedules do not use the minute or hour fields.',
        'core-schedule-preview.note_hour_unused':
          'The selected schedules do not use the hour field.',
        'core-schedule-preview.note_minute_unused':
          'The selected schedules do not use the minute field.',
        'core-schedule-preview.updated':
          'Crontab updated with {{count, number}} line(s).',
        'core-schedule-preview.removed_from_crontab':
          'Removed from the crontab.',
      }),
      es: Object.freeze({
        'core-schedule-preview.preview_written': 'Se escribirá lo siguiente:',
        'core-schedule-preview.apply_changes':
          'Aplicar cambios de programación',
        'core-schedule-preview.remove_crontab': 'Eliminar del crontab',
        'core-schedule-preview.preview_none':
          'Nada: al guardar se eliminarán del crontab las líneas de este panel.',
        'core-schedule-preview.note_offsets_used':
          'El minuto y la hora indican cuándo se ejecuta por primera vez; una programación repetitiva continúa desde allí con su propio intervalo.',
        'core-schedule-preview.note_offsets_unused':
          'Las programaciones seleccionadas no usan los campos de minuto ni hora.',
        'core-schedule-preview.note_hour_unused':
          'Las programaciones seleccionadas no usan el campo de hora.',
        'core-schedule-preview.note_minute_unused':
          'Las programaciones seleccionadas no usan el campo de minuto.',
        'core-schedule-preview.updated':
          'Crontab actualizado con {{count, number}} línea(s).',
        'core-schedule-preview.removed_from_crontab': 'Eliminado del crontab.',
      }),
      hi: Object.freeze({
        'core-schedule-preview.preview_written': 'यह लिखा जाएगा:',
        'core-schedule-preview.apply_changes': 'शेड्यूल परिवर्तन लागू करें',
        'core-schedule-preview.remove_crontab': 'crontab से हटाएँ',
        'core-schedule-preview.preview_none':
          'कुछ नहीं — सहेजने से इस डैशबोर्ड की पंक्तियाँ crontab से हट जाएँगी।',
        'core-schedule-preview.note_offsets_used':
          'मिनट और घंटा बताते हैं कि यह पहली बार कब चलता है; दोहराने वाला शेड्यूल फिर अपने अंतराल पर चलता रहता है।',
        'core-schedule-preview.note_offsets_unused':
          'चुने गए शेड्यूल मिनट या घंटे वाले फ़ील्ड का उपयोग नहीं करते।',
        'core-schedule-preview.note_hour_unused':
          'चुने गए शेड्यूल घंटे वाले फ़ील्ड का उपयोग नहीं करते।',
        'core-schedule-preview.note_minute_unused':
          'चुने गए शेड्यूल मिनट वाले फ़ील्ड का उपयोग नहीं करते।',
        'core-schedule-preview.updated':
          'crontab को {{count, number}} पंक्ति के साथ अपडेट किया गया।',
        'core-schedule-preview.removed_from_crontab':
          'crontab से हटा दिया गया।',
      }),
      ar: Object.freeze({
        'core-schedule-preview.preview_written': 'سيُكتب ما يلي:',
        'core-schedule-preview.apply_changes': 'تطبيق تغييرات الجدولة',
        'core-schedule-preview.remove_crontab': 'إزالة من crontab',
        'core-schedule-preview.preview_none':
          'لا شيء — سيؤدي الحفظ إلى إزالة سطور هذه اللوحة من crontab.',
        'core-schedule-preview.note_offsets_used':
          'تحدد الدقيقة والساعة وقت التشغيل الأول؛ ثم تستمر الجدولة المتكررة وفق فاصلها الخاص.',
        'core-schedule-preview.note_offsets_unused':
          'لا تستخدم الجداول المختارة حقلي الدقيقة أو الساعة.',
        'core-schedule-preview.note_hour_unused':
          'لا تستخدم الجداول المختارة حقل الساعة.',
        'core-schedule-preview.note_minute_unused':
          'لا تستخدم الجداول المختارة حقل الدقيقة.',
        'core-schedule-preview.updated':
          'حُدث crontab بـ {{count, number}} سطور.',
        'core-schedule-preview.removed_from_crontab': 'أُزيل من crontab.',
      }),
      zh: Object.freeze({
        'core-schedule-preview.preview_written': '将写入以下内容：',
        'core-schedule-preview.apply_changes': '应用计划更改',
        'core-schedule-preview.remove_crontab': '从 crontab 中移除',
        'core-schedule-preview.preview_none':
          '无内容——保存会从 crontab 中移除此仪表板的行。',
        'core-schedule-preview.note_offsets_used':
          '分钟和小时表示首次运行的时间；重复计划随后会按其自身间隔继续运行。',
        'core-schedule-preview.note_offsets_unused':
          '所选计划不使用分钟或小时字段。',
        'core-schedule-preview.note_hour_unused': '所选计划不使用小时字段。',
        'core-schedule-preview.note_minute_unused': '所选计划不使用分钟字段。',
        'core-schedule-preview.updated':
          '已用 {{count, number}} 行更新 crontab。',
        'core-schedule-preview.removed_from_crontab': '已从 crontab 中移除。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-schedule-preview.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
