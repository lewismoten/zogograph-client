/* Core card package: only declared dependencies are received from the shell. */
window.ZogographHostMemoryCard = {
  manifest: Object.freeze({
    id: "core-host-memory", locales: ["core-host-memory."], helpers: ["setCard", "humanBytes", "i18n"],
    catalogs: Object.freeze({
      en: Object.freeze({"core-host-memory.of_total":"of {{total}}","core-host-memory.monitoring_footprint":"Monitoring footprint","core-host-memory.collector_processes":"{{count, number}} collector-tree processes","core-host-memory.available":"Available","core-host-memory.available_help":"the kernel's estimate of what is left","core-host-memory.cache_buffers":"Cache and buffers","core-host-memory.cache_help":"reclaimable — not a problem","core-host-memory.kernel_slab":"Kernel slab","core-host-memory.unreclaimable":"{{amount}} of it unreclaimable","core-host-memory.shared_tmpfs":"Kernel shared memory / tmpfs","core-host-memory.in_dev_shm":"{{amount}} in /dev/shm","core-host-memory.swap":"Swap","core-host-memory.allocated":"{{percent}}% allocated","core-host-memory.other_kernel_help":"not represented by the categories above"}),
      es: Object.freeze({"core-host-memory.of_total":"de {{total}}","core-host-memory.monitoring_footprint":"Huella de monitorización","core-host-memory.collector_processes":"{{count, number}} procesos del árbol de recopilación","core-host-memory.available":"Disponible","core-host-memory.available_help":"la estimación del kernel de lo que queda","core-host-memory.cache_buffers":"Caché y búferes","core-host-memory.cache_help":"recuperable — no es un problema","core-host-memory.kernel_slab":"Slab del kernel","core-host-memory.unreclaimable":"{{amount}} no recuperable","core-host-memory.shared_tmpfs":"Memoria compartida del kernel / tmpfs","core-host-memory.in_dev_shm":"{{amount}} en /dev/shm","core-host-memory.swap":"Intercambio","core-host-memory.allocated":"{{percent}}% asignado","core-host-memory.other_kernel_help":"no representada por las categorías anteriores"}),
      hi: Object.freeze({"core-host-memory.of_total":"{{total}} में से","core-host-memory.monitoring_footprint":"निगरानी पदचिह्न","core-host-memory.collector_processes":"कलेक्टर-ट्री की {{count, number}} प्रक्रियाएं","core-host-memory.available":"उपलब्ध","core-host-memory.available_help":"कर्नेल का अनुमान कि कितना बचा है","core-host-memory.cache_buffers":"कैश और बफ़र","core-host-memory.cache_help":"पुनर्प्राप्त करने योग्य — समस्या नहीं","core-host-memory.kernel_slab":"कर्नेल स्लैब","core-host-memory.unreclaimable":"इसमें से {{amount}} पुनर्प्राप्त नहीं किया जा सकता","core-host-memory.shared_tmpfs":"कर्नेल साझा मेमोरी / tmpfs","core-host-memory.in_dev_shm":"/dev/shm में {{amount}}","core-host-memory.swap":"स्वैप","core-host-memory.allocated":"{{percent}}% आवंटित","core-host-memory.other_kernel_help":"ऊपर की श्रेणियों में प्रदर्शित नहीं"}),
      ar: Object.freeze({"core-host-memory.of_total":"من {{total}}","core-host-memory.monitoring_footprint":"بصمة المراقبة","core-host-memory.collector_processes":"{{count, number}} عملية لشجرة الجامع","core-host-memory.available":"متاح","core-host-memory.available_help":"تقدير النواة لما تبقى","core-host-memory.cache_buffers":"الذاكرة المخبأة والمخازن","core-host-memory.cache_help":"قابل للاسترداد — ليس مشكلة","core-host-memory.kernel_slab":"slab النواة","core-host-memory.unreclaimable":"{{amount}} منها غير قابل للاسترداد","core-host-memory.shared_tmpfs":"ذاكرة النواة المشتركة / tmpfs","core-host-memory.in_dev_shm":"{{amount}} في /dev/shm","core-host-memory.swap":"التبديل","core-host-memory.allocated":"{{percent}}% مخصص","core-host-memory.other_kernel_help":"غير ممثلة بالفئات أعلاه"}),
      zh: Object.freeze({"core-host-memory.of_total":"共 {{total}}","core-host-memory.monitoring_footprint":"监控占用量","core-host-memory.collector_processes":"{{count, number}} 个采集器树进程","core-host-memory.available":"可用","core-host-memory.available_help":"内核对剩余内存的估计","core-host-memory.cache_buffers":"缓存和缓冲区","core-host-memory.cache_help":"可回收 — 不成问题","core-host-memory.kernel_slab":"内核 slab","core-host-memory.unreclaimable":"其中 {{amount}} 不可回收","core-host-memory.shared_tmpfs":"内核共享内存 / tmpfs","core-host-memory.in_dev_shm":"/dev/shm 中有 {{amount}}","core-host-memory.swap":"交换空间","core-host-memory.allocated":"已分配 {{percent}}%","core-host-memory.other_kernel_help":"未包含在上述类别中"}),
    }),
  }),
  render(input, helpers) {
    const { sample, swap, swapUsed, swapPercent } = input;
    const { setCard, humanBytes, i18n } = helpers;
    const t = (key, values, fallback) => i18n.t(`core-host-memory.${key}`, values, fallback);
    setCard(i18n.t("host.memory_card_in_use", {}, "Memory in use"), humanBytes(sample.used_bytes),
      t("of_total", { total: humanBytes(sample.mem_total) }, "of {{total}}"));
    if (sample.monitoring_pss_bytes != null || sample.monitoring_rss_bytes != null) {
      const measured = sample.monitoring_pss_bytes != null
        ? humanBytes(sample.monitoring_pss_bytes) + " PSS"
        : humanBytes(sample.monitoring_rss_bytes) + " RSS";
      const processes = Number(sample.monitoring_processes || 0);
      setCard(t("monitoring_footprint", {}, "Monitoring footprint"), measured,
        t("collector_processes", { count: processes }, "{{count, number}} collector-tree processes"));
    }
    setCard(t("available", {}, "Available"), humanBytes(sample.mem_available), t("available_help", {}, "the kernel's estimate of what is left"));
    setCard(t("cache_buffers", {}, "Cache and buffers"), humanBytes(sample.cache_bytes), t("cache_help", {}, "reclaimable — not a problem"));
    setCard(t("kernel_slab", {}, "Kernel slab"), humanBytes(sample.slab),
      t("unreclaimable", { amount: humanBytes(sample.sunreclaim) }, "{{amount}} of it unreclaimable"));
    setCard(t("shared_tmpfs", {}, "Kernel shared memory / tmpfs"), humanBytes(sample.shmem),
      sample.devshm_used == null ? "" : t("in_dev_shm", { amount: humanBytes(sample.devshm_used) }, "{{amount}} in /dev/shm"));
    setCard(t("swap", {}, "Swap"), `${humanBytes(swapUsed)} / ${humanBytes(sample.swap_total)}`,
      swapPercent == null ? "" : t("allocated", { percent: swapPercent }, "{{percent}}% allocated"), swap.state === "pressure" ? "warn" : "", "pair");
    setCard(i18n.t("host.memory_card_other_kernel", {}, "Other kernel/system memory"), humanBytes(sample.unaccounted),
      t("other_kernel_help", {}, "not represented by the categories above"));
  },
};
