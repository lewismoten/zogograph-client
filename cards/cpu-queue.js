/* Core card package: only declared dependencies are received from the shell. */
window.ZogographCpuQueueCard = {
  manifest: Object.freeze({
    id: "core-cpu-queue", locales: ["cpu."],
    helpers: ["setCard", "humanCount", "i18n"],
  }),
  render(input, helpers) {
    const { now, sample } = input;
    const { setCard, humanCount, i18n } = helpers;
    const t = (key, values, fallback) => i18n.t(`cpu.${key}`, values, fallback);
    const cores = Number(sample.cpu_cores) || 0;
    const running = now?.procs_running ?? null;
    const blocked = now?.procs_blocked ?? null;
    const load = sample.load1 == null ? null : Number(sample.load1);

    setCard(t("wanting_to_run", {}, "Wanting to run"), running == null ? "—" : String(running),
      cores ? t("across_cores", { count: cores }, "across {{count, number}} cores") : t("cores_unknown", {}, "cores unknown"),
      running != null && cores && running > cores * 2 ? "warn" : "");
    setCard(t("blocked_in_kernel", {}, "Blocked in the kernel"), blocked == null ? "—" : String(blocked),
      t("uninterruptible_storage", {}, "uninterruptible — waiting on storage"),
      blocked != null && blocked >= 4 ? "warn" : "");
    setCard(t("load_one_minute", {}, "Load, one minute"), load == null ? "—" : load.toFixed(2),
      t("queue_not_cpu", {}, "a queue length, not a CPU reading"));

    const pressure = now?.cpu_pressure_some ?? null;
    setCard(t("waiting_for_core", {}, "Waiting for a core"), pressure == null ? "—" : `${Number(pressure).toFixed(0)}%`,
      pressure == null ? t("no_pressure_stall", {}, "no pressure stall info on this kernel") : t("last_ten_seconds", {}, "share of the last ten seconds"),
      pressure != null && pressure >= 20 ? "warn" : "");
    setCard(t("context_switches", {}, "Context switches"), now?.ctxt_rate == null ? "—" : `${humanCount(now.ctxt_rate)}/s`,
      t("scheduler_change", {}, "the scheduler changing its mind"));
    setCard(t("interrupts", {}, "Interrupts"), now?.intr_rate == null ? "—" : `${humanCount(now.intr_rate)}/s`,
      t("devices_attention", {}, "devices asking for attention"));
    setCard(t("new_processes", {}, "New processes"), now?.fork_rate == null ? "—" : `${humanCount(now.fork_rate)}/s`,
      t("forks_execs_second", {}, "forks and execs per second"));
  },
};
