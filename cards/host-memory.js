/* Core card package: only declared dependencies are received from the shell. */
window.ZogographHostMemoryCard = {
  manifest: Object.freeze({
    id: "core-host-memory", locales: ["host."], helpers: ["setCard", "humanBytes", "i18n"],
  }),
  render(input, helpers) {
    const { sample, swap, swapUsed, swapPercent } = input;
    const { setCard, humanBytes, i18n } = helpers;
    const t = (key, values, fallback) => i18n.t(`host.memory_card_${key}`, values, fallback);
    setCard(t("in_use", {}, "Memory in use"), humanBytes(sample.used_bytes),
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
    setCard(t("other_kernel", {}, "Other kernel/system memory"), humanBytes(sample.unaccounted),
      t("other_kernel_help", {}, "not represented by the categories above"));
  },
};
