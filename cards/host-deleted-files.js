/* Core card package: unlinked files that still hold filesystem space. */
window.ZogographHostDeletedFilesCard = {
  manifest: Object.freeze({
    id: 'core-host-deleted-files',
    locales: ['core-host-deleted-files.'],
    helpers: ['target', 'section', 'note', 'simpleTable', 'humanBytes', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-host-deleted-files.holding': 'Deleted, still holding {{amount}}',
        'core-host-deleted-files.help':
          'These files have been unlinked but a process still has them open, so the blocks stay allocated. df counts them; du, ls, find and the treemap cannot see them. Restarting the process listed frees the space.',
        'core-host-deleted-files.path': 'Path',
        'core-host-deleted-files.held_by': 'Held by',
        'core-host-deleted-files.pid': 'PID',
        'core-host-deleted-files.size': 'Size',
        'core-host-deleted-files.none':
          'None — no process is holding space in a deleted file.',
        'core-host-deleted-files.root_only':
          'This needs /proc/<pid>/fd, which only root can read. Run the host collector to fill it in.',
      }),
      es: Object.freeze({
        'core-host-deleted-files.holding':
          'Eliminado, pero aún retiene {{amount}}',
        'core-host-deleted-files.help':
          'Estos archivos se desvincularon, pero un proceso aún los tiene abiertos, por lo que los bloques siguen asignados. df los cuenta; du, ls, find y el mapa de árbol no pueden verlos. Reiniciar el proceso indicado libera el espacio.',
        'core-host-deleted-files.path': 'Ruta',
        'core-host-deleted-files.held_by': 'Retenido por',
        'core-host-deleted-files.pid': 'PID',
        'core-host-deleted-files.size': 'Tamaño',
        'core-host-deleted-files.none':
          'Ninguno: ningún proceso retiene espacio en un archivo eliminado.',
        'core-host-deleted-files.root_only':
          'Esto necesita /proc/<pid>/fd, que solo root puede leer. Ejecute el recopilador del host para completarlo.',
      }),
      hi: Object.freeze({
        'core-host-deleted-files.holding':
          'हटाया गया, फिर भी {{amount}} रोके हुए है',
        'core-host-deleted-files.help':
          'इन फ़ाइलों का लिंक हटाया जा चुका है, लेकिन एक प्रक्रिया अभी भी इन्हें खोले हुए है, इसलिए ब्लॉक आवंटित रहते हैं। df इन्हें गिनता है; du, ls, find और ट्रीमैप इन्हें नहीं देख सकते। सूचीबद्ध प्रक्रिया को पुनः आरंभ करने से स्थान खाली होता है।',
        'core-host-deleted-files.path': 'पथ',
        'core-host-deleted-files.held_by': 'इसके पास है',
        'core-host-deleted-files.pid': 'PID',
        'core-host-deleted-files.size': 'आकार',
        'core-host-deleted-files.none':
          'कोई नहीं — कोई प्रक्रिया हटाई गई फ़ाइल में स्थान नहीं रोके हुए है।',
        'core-host-deleted-files.root_only':
          'इसके लिए /proc/<pid>/fd चाहिए, जिसे केवल root पढ़ सकता है। इसे भरने के लिए होस्ट कलेक्टर चलाएँ।',
      }),
      ar: Object.freeze({
        'core-host-deleted-files.holding': 'محذوف، لكنه ما زال يحجز {{amount}}',
        'core-host-deleted-files.help':
          'أُلغيت روابط هذه الملفات لكن عملية لا تزال تحتفظ بها مفتوحة، لذا تبقى الكتل مخصصة. يحسبها df؛ ولا يستطيع du وls وfind وخريطة الشجرة رؤيتها. تؤدي إعادة تشغيل العملية المذكورة إلى تحرير المساحة.',
        'core-host-deleted-files.path': 'المسار',
        'core-host-deleted-files.held_by': 'تحتفظ به',
        'core-host-deleted-files.pid': 'PID',
        'core-host-deleted-files.size': 'الحجم',
        'core-host-deleted-files.none':
          'لا شيء — لا توجد عملية تحتجز مساحة في ملف محذوف.',
        'core-host-deleted-files.root_only':
          'يتطلب هذا /proc/<pid>/fd، ولا يستطيع قراءته إلا root. شغّل جامع المضيف لملئه.',
      }),
      zh: Object.freeze({
        'core-host-deleted-files.holding': '已删除，但仍占用 {{amount}}',
        'core-host-deleted-files.help':
          '这些文件已被解除链接，但进程仍将其打开，因此块仍处于已分配状态。df 会统计它们；du、ls、find 和树状图都看不到它们。重启列出的进程即可释放空间。',
        'core-host-deleted-files.path': '路径',
        'core-host-deleted-files.held_by': '占用者',
        'core-host-deleted-files.pid': 'PID',
        'core-host-deleted-files.size': '大小',
        'core-host-deleted-files.none': '无——没有进程在已删除文件中占用空间。',
        'core-host-deleted-files.root_only':
          '这需要 /proc/<pid>/fd，只有 root 可以读取。运行主机采集器以填充此项。',
      }),
    }),
  }),
  render({ deleted = [], deletedBytes = 0, deletedReadable = false }, helpers) {
    const { target, section, note, simpleTable, humanBytes, i18n } = helpers;
    if (!target) return;
    const t = (key, values, fallback) =>
      i18n.t(`core-host-deleted-files.${key}`, values, fallback);
    if (deleted.length) {
      const body = document.createDocumentFragment();
      body.appendChild(
        note(
          t(
            'help',
            {},
            'These files have been unlinked but a process still has them open, so the blocks stay allocated. df counts them; du, ls, find and the treemap cannot see them. Restarting the process listed frees the space.',
          ),
        ),
      );
      body.appendChild(
        simpleTable(
          ['path', 'held_by', 'pid', 'size'].map((key, index) =>
            t(key, {}, ['Path', 'Held by', 'PID', 'Size'][index]),
          ),
          deleted.map((file) => [
            file.path,
            file.process + (file.holders > 1 ? ` (×${file.holders})` : ''),
            String(file.pid),
            humanBytes(file.bytes),
          ]),
          ['name', null, 'size', 'size'],
        ),
      );
      target.appendChild(
        section(
          t(
            'holding',
            { amount: humanBytes(deletedBytes) },
            'Deleted, still holding {{amount}}',
          ),
          body,
        ),
      );
      return;
    }
    target.appendChild(
      note(
        deletedReadable
          ? t(
              'none',
              {},
              'None — no process is holding space in a deleted file.',
            )
          : t(
              'root_only',
              {},
              'This needs /proc/<pid>/fd, which only root can read. Run the host collector to fill it in.',
            ),
      ),
    );
  },
};
