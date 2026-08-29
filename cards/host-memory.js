/* Core card package: only declared dependencies are received from the shell. */
window.ZogographHostMemoryCard = {
  manifest: Object.freeze({
    id: "core-host-memory", locales: [], helpers: ["setCard", "humanBytes"],
  }),
  render(input, helpers) {
    const { sample, swap, swapUsed, swapPercent } = input;
    const { setCard, humanBytes } = helpers;
    setCard("Memory in use", humanBytes(sample.used_bytes), `of ${humanBytes(sample.mem_total)}`);
    if (sample.monitoring_pss_bytes != null || sample.monitoring_rss_bytes != null) {
      const measured = sample.monitoring_pss_bytes != null
        ? humanBytes(sample.monitoring_pss_bytes) + " PSS"
        : humanBytes(sample.monitoring_rss_bytes) + " RSS";
      const processes = Number(sample.monitoring_processes || 0);
      setCard("Monitoring footprint", measured,
        `${processes} collector-tree process${processes === 1 ? "" : "es"}`);
    }
    setCard("Available", humanBytes(sample.mem_available), "the kernel's estimate of what is left");
    setCard("Cache and buffers", humanBytes(sample.cache_bytes), "reclaimable — not a problem");
    setCard("Kernel slab", humanBytes(sample.slab), `${humanBytes(sample.sunreclaim)} of it unreclaimable`);
    setCard("Kernel shared memory / tmpfs", humanBytes(sample.shmem),
      sample.devshm_used == null ? "" : `${humanBytes(sample.devshm_used)} in /dev/shm`);
    setCard("Swap", `${humanBytes(swapUsed)} / ${humanBytes(sample.swap_total)}`,
      swapPercent == null ? "" : `${swapPercent}% allocated`, swap.state === "pressure" ? "warn" : "", "pair");
    setCard("Other kernel/system memory", humanBytes(sample.unaccounted),
      "not represented by the categories above");
  },
};
