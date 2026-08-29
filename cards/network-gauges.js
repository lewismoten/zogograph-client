/* Core card package: compact network capacity and exposure gauges. */
window.ZogographNetworkGaugesCard = {
  manifest: Object.freeze({
    id: "core-network-gauges", locales: ["core-network-gauges."],
    helpers: ["target", "buildGauge", "humanBytes", "humanCount", "i18n"],
    catalogs: Object.freeze({
      en: Object.freeze({"core-network-gauges.rate_on_interface":"{{label}} on {{interface}}","core-network-gauges.needs_two_readings":"needs two readings","core-network-gauges.rate_of_link":"{{rate}} of a {{speed}} Mbit link","core-network-gauges.connections_busiest":"{{connections}} open, busiest peer holds {{busiest}}","core-network-gauges.none_recorded":"none recorded","core-network-gauges.listening_ports":"Listening ports","core-network-gauges.ports_reachable_summary":"{{exposed}} of {{open}} reachable from outside"}),
      es: Object.freeze({"core-network-gauges.rate_on_interface":"{{label}} en {{interface}}","core-network-gauges.needs_two_readings":"se necesitan dos lecturas","core-network-gauges.rate_of_link":"{{rate}} de un enlace de {{speed}} Mbit","core-network-gauges.connections_busiest":"{{connections}} abiertas; el par más ocupado tiene {{busiest}}","core-network-gauges.none_recorded":"sin registros","core-network-gauges.listening_ports":"Puertos en escucha","core-network-gauges.ports_reachable_summary":"{{exposed}} de {{open}} accesibles desde fuera"}),
      hi: Object.freeze({"core-network-gauges.rate_on_interface":"{{interface}} पर {{label}}","core-network-gauges.needs_two_readings":"दो रीडिंग चाहिए","core-network-gauges.rate_of_link":"{{speed}} Mbit लिंक का {{rate}}","core-network-gauges.connections_busiest":"{{connections}} खुले हैं, सबसे व्यस्त पीयर के पास {{busiest}} हैं","core-network-gauges.none_recorded":"कोई रिकॉर्ड नहीं","core-network-gauges.listening_ports":"सुनने वाले पोर्ट","core-network-gauges.ports_reachable_summary":"{{open}} में से {{exposed}} बाहर से पहुंच योग्य"}),
      ar: Object.freeze({"core-network-gauges.rate_on_interface":"{{label}} على {{interface}}","core-network-gauges.needs_two_readings":"يتطلب قراءتين","core-network-gauges.rate_of_link":"{{rate}} من وصلة {{speed}} مِبت","core-network-gauges.connections_busiest":"{{connections}} مفتوحة، ويحتفظ أكثر الأقران انشغالاً بـ {{busiest}}","core-network-gauges.none_recorded":"لا سجلات","core-network-gauges.listening_ports":"منافذ الاستماع","core-network-gauges.ports_reachable_summary":"{{exposed}} من {{open}} يمكن الوصول إليها من الخارج"}),
      zh: Object.freeze({"core-network-gauges.rate_on_interface":"{{interface}} 上的{{label}}","core-network-gauges.needs_two_readings":"需要两次读数","core-network-gauges.rate_of_link":"{{speed}} Mbit 链路中的 {{rate}}","core-network-gauges.connections_busiest":"{{connections}} 个打开连接，最繁忙的对端占 {{busiest}} 个","core-network-gauges.none_recorded":"未记录","core-network-gauges.listening_ports":"监听端口","core-network-gauges.ports_reachable_summary":"{{open}} 个中有 {{exposed}} 个可从外部访问"}),
    }),
  }),
  render(data, helpers) {
    const { target, buildGauge, humanBytes, humanCount, i18n } = helpers;
    const t = (key, values = {}) => i18n.t(`core-network-gauges.${key}`, values);
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
          label: t("rate_on_interface", { label, interface: busiest.name }),
          value: rate == null ? null : (rate / capacity) * 100, warn: 60, crit: 85,
          sub: rate == null
            ? t("needs_two_readings")
            : t("rate_of_link", { rate: `${humanBytes(rate)}/s`, speed: busiest.speed_mbit }),
        });
      }
    }

    const peers = (data.peers || []).reduce((sum, peer) => sum + Number(peer.connections || 0), 0);
    const top = (data.peers || [])[0];
    dials.push({
      label: i18n.t("network.connections"), value: null, warn: 0, crit: 0,
      sub: peers
        ? t("connections_busiest", { connections: humanCount(peers), busiest: humanCount(top.connections) })
        : t("none_recorded"),
      plain: peers ? humanCount(peers) : "—",
    });

    const open = (data.ports || []).length;
    const exposed = (data.ports || []).filter(port => port.public).length;
    dials.push({
      label: t("listening_ports"), value: null, warn: 0, crit: 0,
      sub: t("ports_reachable_summary", { exposed, open }), plain: String(open),
    });
    target.replaceChildren(...dials.map(buildGauge));
  },
};
