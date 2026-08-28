# Zogograph Client guidance

This repository is the self-hosted Zogograph client: host collection,
dashboard, alerts, local storage, authentication, agents, and plugins.

Work on `develop`. Commit small cohesive batches, run `npm test` and
`npm run build` before a stable milestone, then push it. Preserve persisted
data, browser keys, plugin IDs, and protocol compatibility unless a tested
migration explicitly changes them.
