/* Core card package: only declared dependencies are received from the shell. */
window.ZogographOverviewVitalsCards = {
  render(overview, helpers) {
    const { setCard, humanBytes, humanCount, humanDuration, projectedDate, i18n } = helpers;
    const t = (key, values, fallback) => i18n.t(`overview.${key}`, values, fallback);
    const v = overview.vitals;
    if (!v) { setCard("cardDisk", "—", t("nothing_collected", {}, "nothing collected yet")); return; }
    const free = v.disk_total_bytes ? v.disk_free_bytes / v.disk_total_bytes * 100 : null;
    setCard("cardDisk", humanBytes(v.disk_free_bytes), free === null ? "" : t("free_of", { percent: free.toFixed(1), total: humanBytes(v.disk_total_bytes) }, `${free.toFixed(1)}% free of ${humanBytes(v.disk_total_bytes)}`), free === null ? "" : free < 5 ? "crit" : free < 12 ? "warn" : "");
    const trend = overview.trend?.disk;
    if (!trend) setCard("cardTrend", "—", t("not_enough_history", {}, "not enough history yet"));
    else if (trend.spike) setCard("cardTrend", t("one_off", {}, "one-off"), t("went_at_once", { amount: humanBytes(-trend.overall * (trend.span_seconds || 1)) }, `${humanBytes(-trend.overall * (trend.span_seconds || 1))} went at once, not a trend`));
    else if (!trend.sustained || trend.seconds_left == null) setCard("cardTrend", t("steady", {}, "steady"), t("free_space_steady", {}, "free space is not falling"));
    else setCard("cardTrend", projectedDate(trend.seconds_left), t("losing_rate", { rate: humanBytes(-trend.rate * 3600), window: humanDuration(trend.window_seconds || 86400) }, `losing ${humanBytes(-trend.rate * 3600)}/h, measured over ${humanDuration(trend.window_seconds || 86400)}`), trend.seconds_left < 86400 ? "crit" : trend.seconds_left < 604800 ? "warn" : "");
    const inode = v.inodes_total ? v.inodes_free / v.inodes_total * 100 : null;
    setCard("cardInodes", v.inodes_free == null ? t("not_available", {}, "n/a") : humanCount(v.inodes_free), inode === null ? t("not_reported", {}, "not reported") : t("percent_free", { percent: inode.toFixed(1) }, `${inode.toFixed(1)}% free`), inode === null ? "" : inode < 5 ? "crit" : inode < 12 ? "warn" : "");
    setCard("cardCpu", v.cpu_percent == null ? t("not_available", {}, "n/a") : `${Number(v.cpu_percent).toFixed(0)}%`, v.cpu_percent == null ? t("no_proc_stat", {}, "no /proc/stat here") : t("since_previous_reading", {}, "since the previous reading"), v.cpu_percent > 90 ? "crit" : v.cpu_percent > 70 ? "warn" : "");
    setCard("cardMem", v.mem_used_percent == null ? t("not_available", {}, "n/a") : `${Number(v.mem_used_percent).toFixed(0)}%`, v.mem_total_bytes ? t("available", { amount: humanBytes(v.mem_available_bytes) }, `${humanBytes(v.mem_available_bytes)} available`) : t("no_proc_meminfo", {}, "no /proc/meminfo here"), v.mem_used_percent > 92 ? "crit" : v.mem_used_percent > 80 ? "warn" : "");
    const cores = v.cpu_cores ? Number(v.cpu_cores) : null; const perCore = v.load1 != null && cores ? Number(v.load1) / cores : null;
    setCard("cardLoad", v.load1 == null ? t("not_available", {}, "n/a") : Number(v.load1).toFixed(2), v.load1 == null ? "" : (cores ? t("percent_of_cores", { percent: (perCore * 100).toFixed(0), cores }, `${(perCore * 100).toFixed(0)}% of ${cores} cores · `) : "") + t("load_at", { five: Number(v.load5).toFixed(2), fifteen: Number(v.load15).toFixed(2) }, `${Number(v.load5).toFixed(2)} / ${Number(v.load15).toFixed(2)} at 5 and 15 min`), perCore == null ? "" : perCore > 1.5 ? "crit" : perCore > 1 ? "warn" : "");
    const rx = v.net_rx_rate == null ? null : Number(v.net_rx_rate), tx = v.net_tx_rate == null ? null : Number(v.net_tx_rate);
    setCard("cardNet", rx == null ? t("not_available", {}, "n/a") : `${humanBytes(rx, 1)}/s`, rx == null ? t("no_proc_net_dev", {}, "no /proc/net/dev here") : t("network_in_out", { incoming: humanBytes(rx, 1), outgoing: humanBytes(tx, 1) }, `in · ${humanBytes(tx, 1)}/s out`));
  },
};
