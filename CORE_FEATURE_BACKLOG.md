# Remaining core feature conversion backlog

Each row is a cohesive core package, not a promise that hiding navigation is
enough. Before a row is marked complete, its routes must be declared in
`lib/core-features.php`, all matching API actions must be refused when disabled,
and any shared dashboard or assistant data path must be closed too.

| Core package | Remaining routes | Boundary work still required |
| --- | --- | --- |
| Monitoring overview | `overview` | Alert, uptime, and chart-history boundaries are complete. Any overview boundary must preserve active collection and setup guidance while closing only its shared presentation data. |
| Host and process observations | `host-disk`, `host-layout`, `cpu*`, `host`, `mem-*`, `processes`, `services`, `units`, `busy`, `capabilities`, `sys-installed`, `sys-changed` | Root-owned collection remains on the host schedule. Disable only the browser/API observations after tracing host-task requests and assistant tools. |
| Network tools | `network`, `net-*`, `domains`, `whois`, `calendar`, `whoami`, `dns-records` | Outbound fetch/DNS/WHOIS and stored URL/domain actions have separate access and SSRF controls; preserve those guards while introducing feature boundaries. |

Completed groups are documented in `GOAL_AUDIT.md`. This file is deliberately
kept as a conversion queue rather than a completion claim.
