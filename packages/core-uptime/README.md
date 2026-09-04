## Core uptime package

This built-in dashboard package owns the uptime summary, collector-coverage,
and uptime-event cards. It is the first staged move out of the flat `cards/`
directory; its existing scripts retain the same stable card IDs while the
registry migration proceeds.

`manifest.json` is the package-owned inventory. Every card declares
`owner: "core-uptime"`; the card registry rejects a card whose ID does not
belong to that owner.
