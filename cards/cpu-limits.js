/* Core card package: account CPU ceilings and CloudLinux throttling evidence. */
window.ZogographCpuLimitsCard = {
  manifest: Object.freeze({
    id: "core-cpu-limits", locales: ["core-cpu-limits."],
    helpers: ["renderLimits", "noteTarget", "lveWrap", "renderTable", "humanCount", "drawLineChart", "countAxis", "i18n"],
    catalogs: Object.freeze({
      en: Object.freeze({
        "core-cpu-limits.faults": "{{faults, number}} CPU fault(s) recorded at the last reading across {{accounts, number}} account(s). A fault is an account asking for more processor than its LVE allows and being held back — this is being throttled now, not being near a limit. A throttled account's own percentage sits calmly at its ceiling, which is why the fault count is the figure to watch and not the percentage.",
        "core-cpu-limits.throttled": "This account is at {{account, number}}% of its own allowance while the machine is {{host, number}}% busy. That is not a contradiction: the two percentages have different denominators. It is the ordinary shape of hitting a limit, and it is felt as a slow site even though the server has plenty of processor left.",
        "core-cpu-limits.comparison": "This account is using {{account, number}}% of what it is allowed; the machine as a whole is {{host, number}}% busy. Neither number is a share of the other, and dividing them produces nothing meaningful.",
        "core-cpu-limits.missing_side": "Both halves of this comparison need a reading — one from this account and one from the host collector running as root.",
      }),
    }),
  }),
  render(data, helpers) {
    const { renderLimits, noteTarget, lveWrap, renderTable, humanCount, drawLineChart, countAxis, i18n } = helpers;
    if (!noteTarget || !lveWrap) return;
    renderLimits(data.limits, "cpuLimitsTable", "cpuLimitsWhen");
    const mine = data.vitals?.cpu_percent == null ? null : Number(data.vitals.cpu_percent);
    const host = data.sample?.cpu_percent == null ? null : Number(data.sample.cpu_percent);
    const rows = data.lve ?? [];
    const faults = rows.reduce((sum, row) => sum + (Number(row.faults_cpu) || 0), 0);
    const t = (key, values, fallback) => i18n.t(`core-cpu-limits.${key}`, values, fallback);
    const cpu = (key, values, fallback) => i18n.t(`cpu.${key}`, values, fallback);
    noteTarget.textContent = faults > 0
      ? t("faults", { faults, accounts: rows.length }, "{{faults, number}} CPU fault(s) recorded at the last reading across {{accounts, number}} account(s). A fault is an account asking for more processor than its LVE allows and being held back — this is being throttled now, not being near a limit. A throttled account's own percentage sits calmly at its ceiling, which is why the fault count is the figure to watch and not the percentage.")
      : mine != null && host != null && mine >= 85 && host < 50
        ? t("throttled", { account: Math.round(mine), host: Math.round(host) }, "This account is at {{account, number}}% of its own allowance while the machine is {{host, number}}% busy. That is not a contradiction: the two percentages have different denominators. It is the ordinary shape of hitting a limit, and it is felt as a slow site even though the server has plenty of processor left.")
        : mine != null && host != null
          ? t("comparison", { account: Math.round(mine), host: Math.round(host) }, "This account is using {{account, number}}% of what it is allowed; the machine as a whole is {{host, number}}% busy. Neither number is a share of the other, and dividing them produces nothing meaningful.")
          : t("missing_side", {}, "Both halves of this comparison need a reading — one from this account and one from the host collector running as root.");
    lveWrap.hidden = rows.length === 0;
    if (!rows.length) return;
    renderTable("cpuLve", rows, [
      { label: cpu("account", {}, "Account"), render: row => row.user },
      { label: cpu("cpu_faults", {}, "CPU faults"), className: "size", render: row => humanCount(row.faults_cpu) },
      { label: cpu("memory_faults", {}, "Memory faults"), className: "size", render: row => humanCount(row.faults_mem) },
      { label: cpu("process_faults", {}, "Process faults"), className: "size", render: row => humanCount(row.faults_procs) },
      { label: cpu("cpu_in_use", {}, "CPU in use"), className: "size", render: row => row.cpu_percent == null ? "—" : `${Number(row.cpu_percent).toFixed(0)}%` },
    ]);
    drawLineChart(document.querySelector("#cpuLveChart"), data.lve_series ?? [], [
      { key: "faults_cpu", label: cpu("cpu_faults", {}, "CPU faults") },
      { key: "faults_mem", label: cpu("memory_faults", {}, "Memory faults") },
    ], { axis: countAxis, zeroBased: true, empty: cpu("no_lve_readings", {}, "No LVE readings in this range") });
  },
};
