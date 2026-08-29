/* Core card package: compact network capacity and exposure gauges. */
window.ZogographNetworkGaugesCard = {
  manifest: Object.freeze({
    id: "core-network-gauges", locales: ["network."],
    helpers: ["target", "buildGauge", "humanBytes", "humanCount", "i18n"],
  }),
  render(data, helpers) {
    const { target, buildGauge, humanBytes, humanCount, i18n } = helpers;
    if (!target) return;

    const real = (data.interfaces || []).filter(item => !item.loopback);
    const busiest = real.slice().sort(
      (left, right) => ((right.rx_rate || 0) + (right.tx_rate || 0)) - ((left.rx_rate || 0) + (left.tx_rate || 0))
    )[0];
    const dials = [];

    if (busiest && busiest.speed_mbit) {
      const capacity = busiest.speed_mbit * 125000;
      for (const [label, rate] of [[i18n.t("network.in"), busiest.rx_rate], [i18n.t("network.out"), busiest.tx_rate]]) {
        dials.push({
          label: i18n.t("network.rate_on_interface", { label, interface: busiest.name }),
          value: rate == null ? null : (rate / capacity) * 100, warn: 60, crit: 85,
          sub: rate == null
            ? i18n.t("network.needs_two_readings")
            : i18n.t("network.rate_of_link", { rate: `${humanBytes(rate)}/s`, speed: busiest.speed_mbit }),
        });
      }
    }

    const peers = (data.peers || []).reduce((sum, peer) => sum + Number(peer.connections || 0), 0);
    const top = (data.peers || [])[0];
    dials.push({
      label: i18n.t("network.connections"), value: null, warn: 0, crit: 0,
      sub: peers
        ? i18n.t("network.connections_busiest", { connections: humanCount(peers), busiest: humanCount(top.connections) })
        : i18n.t("network.none_recorded"),
      plain: peers ? humanCount(peers) : "—",
    });

    const open = (data.ports || []).length;
    const exposed = (data.ports || []).filter(port => port.public).length;
    dials.push({
      label: i18n.t("network.listening_ports"), value: null, warn: 0, crit: 0,
      sub: i18n.t("network.ports_reachable_summary", { exposed, open }), plain: String(open),
    });
    target.replaceChildren(...dials.map(buildGauge));
  },
};
