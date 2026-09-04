# Client plugin and card modularity checklist

## Outcome

Make dashboard and Setup features modular without treating bundled code as a
marketplace download. A built-in feature may be enabled or disabled; a signed
package may contribute to any declared navigation area; both use the same card
and localization contracts. Core security, shell, and recovery functions stay
non-disableable.

This is an implementation checklist, not an authorization to execute arbitrary
package browser or server code. The current package runtime is deliberately
data-only and its documented isolated execution runtime is still future work.

## Facts to preserve while changing it

- Core card scripts are currently loaded as browser globals and are invoked
  through `renderCoreCardPackage`. Their `window.Zogograph…Card` names leak
  into the ESLint globals list.
- The current signed runtime accepts named dashboard pages and an allowlisted
  `overview-facts` card renderer. It does not run a package-supplied renderer,
  query, HTML, CSS, or JavaScript.
- Package pages currently attach only to an existing `VIEWS` parent; an unknown
  parent falls back to the Packages page. That does not satisfy package-owned
  sections.
- The client already has server-authoritative visibility for selected core
  routes. That is a useful pattern, but a disabled feature must also stop
  background work, data requests, event listeners, and navigation exposure.

## 1. Write the package and feature model before moving code

- [ ] Define three distinct statuses in the product and API:
  `built_in`, `installed`, and `enabled`. Bundled code is never described as
  marketplace-installed.
- [ ] Create one registry record shape shared by built-in features and signed
  packages: stable ID, publisher/origin, version, compatibility, dependencies,
  grants, lifecycle state, navigation declarations, cards, settings, catalogs,
  migrations, and health/readiness state.
- [ ] Keep a package ID globally unique and reserve `core-*` for the product.
  All package-owned child IDs, route IDs, catalog keys, CSS data attributes,
  and storage names begin with the package ID.
- [ ] Define dependency behavior: a missing, disabled, incompatible, revoked,
  or integrity-failed dependency prevents enablement and gives a specific
  administrator-visible reason.
- [ ] Define lifecycle hooks and failure rules: `install`, `validate`,
  `enable`, `disable`, `upgrade`, `rollback`, and `uninstall`. Disable must be
  reversible and retain package state; uninstall requires an explicit retained
  data/migration policy.
- [ ] Keep the existing signature, integrity, compatibility, and grant checks
  on every install, enable, update, and launch decision.

## 2. Replace card globals with a registration boundary

- [ ] Define `window.Zogograph` as the only product namespace exposed to
  bundled scripts; expose `Zogograph.cards.register(definition)` and
  `Zogograph.cards.get(id)`. Remove direct `window.ZogographFooCard = …`
  assignments and their ESLint global declarations incrementally.
- [ ] Validate every definition at registration time: immutable ID, owner,
  declared UI slots, data contract, required grants, locale namespace,
  settings schema reference, declared shared UI primitives, and a render
  function (for trusted built-in code only).
- [ ] Replace direct global lookup/render calls with a registry lookup and a
  single `renderCard` lifecycle: resolve, check availability, register catalogs,
  create/update/destroy the container, then render or show a standard disabled
  state.
- [ ] Make rendering idempotent. A card receives a generic container and
  context; it never assumes a hard-coded page element, writes outside its
  container, or installs duplicate global listeners.
- [ ] Require cleanup for timers, subscriptions, observers, dialogs, and
  listeners. Disabling, navigating away, and rerendering must invoke cleanup.
- [ ] Keep a compatibility adapter only long enough to migrate existing cards;
  prevent new `window.Zogograph*Card` references with ESLint and a source audit.

## 3. Make navigation package-owned and area-aware

- [ ] Model navigation as a tree of declarative nodes, not direct `VIEWS`
  mutations. Each node declares `area`, `id`, `kind` (`section` or `page`),
  `parent`, title/summary/catalog keys, icon token, ordering constraints,
  keywords, route, access/grant requirements, and owner package.
- [ ] Define the two initial areas: `dashboard` (the signed-in front end) and
  `setup`. A package selects an area; it may declare a new top-level section
  within that area, so it is not limited to pre-existing core sections.
- [ ] Namespace package-created section, page, and route IDs. A package may
  parent a page under one of its own new sections or an explicitly public core
  insertion point; it may not silently replace or reparent another package's
  node.
- [ ] Define deterministic ordering: explicit `before`/`after` constraints,
  then priority, then owner+ID as the stable tie-breaker. Detect cycles and
  reject conflicting declarations at validation time.
- [ ] Define core insertion points as contracts, including whether third-party
  children are allowed and the required grants. Do not infer access from an ID
  string.
- [ ] Build the navigation tree after enabled registry records are loaded.
  Render the same tree for sidebar, setup navigation, route guards, search,
  breadcrumbs, titles, and accessibility skip links.
- [ ] Route ownership must be server-checked. A stale URL for a disabled or
  removed package redirects safely and never invokes its API/data provider.
- [ ] Include a generic empty state for a section with no visible children and
  preserve a usable core route when all optional children are disabled.

## 4. Separate trusted built-in cards from installable package UI

- [ ] Keep product-owned cards as reviewed code registered by the built asset.
  They can use the full internal card API but must still honor the generic card
  lifecycle and shared UI primitives.
- [ ] For current installable packages, expand only declarative card types
  backed by reviewed core renderers (facts, status, table, notice, action,
  chart) and validated data schemas. A manifest must never provide executable
  browser code, HTML, stylesheet URLs, SQL, or arbitrary API endpoints.
- [ ] For future executable packages, design a separate reviewed isolation
  boundary first: signed archive, content-addressed assets, CSP/sandbox,
  capability-scoped RPC, no ambient browser globals, server-side isolation,
  timeout/memory quotas, audit log, revocation, and upgrade rollback. Do not
  label that capability as available until it is implemented and tested.
- [ ] Use the same registry/navigation/catalog format for both paths so an
  approved declarative package can later gain a renderer without changing its
  identity or placement rules.

## 5. Create a small shared card UI kit (DRY)

- [ ] Define generic primitives: `Card`, `CardHeader`, `CardBody`, `CardFooter`,
  `CardNotice`, `CardEmpty`, `CardError`, `CardLoading`, `CardActions`,
  `CardTable`, `CardFacts`, `CardChart`, `CardFilters`, and `CardHelp`.
- [ ] Give every primitive consistent spacing, heading hierarchy, loading/error
  treatment, empty-state treatment, keyboard behavior, reduced-motion support,
  responsive table handling, and light/dark/theme tokens.
- [ ] Pass primitives through the card context rather than duplicating DOM
  construction, `replaceChildren`, table boilerplate, button states, notices,
  and source-marking in each card.
- [ ] Keep the card container generic: no feature-specific IDs or CSS selector
  dependence. Use owner-scoped `data-package` and `data-card` attributes only
  for diagnostics and styling hooks.
- [ ] Standardize safe rendering: text by default, controlled rich-text tokens
  where necessary, no package HTML injection, and a single escaping/sanitizing
  path.
- [ ] Add visual/accessibility fixtures for every primitive and require package
  examples to use primitives rather than copy their markup.

## 6. Simplify localization and complete the audit

- [ ] Publish `window.i18n` as the short global localization service; it owns
  catalog registration, `t`, rich-token rendering, date/number/list formatting,
  plural/select rules, and source attribution. New code uses a local `i18n`
  from card context or `window.i18n`, not `ZogographI18n`.
- [ ] Migrate `ZogographI18n` through a temporary alias, then remove it and add
  a lint/source check that prohibits new references.
- [ ] Require each package manifest to declare catalogs for every supported
  locale and use only its own `package-id.*` keys. Catalog registration must be
  independent of card rendering.
- [ ] Audit every visible string, including headings, table headers, labels,
  placeholders, empty/error/loading states, confirmation dialogs, tooltips,
  ARIA labels, navigation/search text, toast text, and browser document titles.
- [ ] Specifically audit retention UI: table headers; duration labels such as
  “7 days” and “forever”; clear/delete/action controls; interval names; zero,
  one, and plural values; and all fallback/error text.
- [ ] Replace concatenated presentation strings with formatter calls. For
  example, render `+83.4 KiB/yr` from a signed numeric value, localized byte
  formatter, localized sign, and a translated rate-period key—not a literal
  `yr`. Apply the same rule to units, dates, relative time, percentages,
  counts, ranges, and currency.
- [ ] Ensure each locale has the same key set, no unintended English fallback,
  no unsafe rich markup, and correct RTL behavior for Arabic and numbers/units.
- [ ] Add automated scans for hard-coded user-facing literals in JS/PHP/HTML,
  plus browser fixtures that render every supported locale and verify key UI
  states. Maintain a reviewed allowlist for identifiers, protocol text, and
  intentionally untranslated code samples.

## 7. Make built-in enable/disable safe

- [ ] Inventory cards/pages by dependency and classify them: mandatory shell,
  core-but-hideable, optional built-in module, declarative package feature, or
  future executable package feature.
- [ ] Start with leaf optional modules (for example specialized discovery,
  backup/vendor integrations, and secondary analysis pages). Do not begin with
  authentication, Setup, navigation, i18n, access control, diagnostics,
  overview shell, or recovery/protection controls.
- [ ] Store enablement server-side with actor, timestamp, reason, version, and
  audit history. Browser state is display-only.
- [ ] On disable: revoke routes/actions, cancel polling, detach cards, remove
  navigation, stop loading its catalogs only if no other enabled feature owns
  them, and preserve data unless the administrator separately chooses removal.
- [ ] On enable: validate dependencies/grants/compatibility/integrity before
  registering navigation, settings, catalogs, routes, and refresh work.
- [ ] Add an administrator recovery path for a broken optional module and a
  safe “disable all optional modules” mode.

## 8. Publish a plugin authoring example and contract tests

- [ ] Ship one minimal example that adds a new `dashboard` section and a child
  page, and one that adds a child to a public core insertion point. Include all
  catalogs, navigation metadata, a declarative card, settings, disabled state,
  accessibility labels, and tests.
- [ ] Document the manifest schema, ID/route/catalog namespaces, supported card
  types, allowed UI primitives, data schema limits, permissions, lifecycle,
  compatibility, signing, validation errors, and upgrade/rollback behavior.
- [ ] Provide a package validator/fixture command that checks schema, signature,
  integrity, locale parity, navigation cycles/collisions, route access, card
  schemas, accessibility fixtures, and no-executable-content policy.
- [ ] Add integration tests for install/enable/disable/upgrade/rollback/revoke,
  package-created sections in both areas, stale links, localization reload,
  multiple packages sharing an insertion point, and cleanup after disable.

## 9. Resolve the observed regressions and preserve them as tests

- [ ] Fix the uptime-event contract mismatch: its caller uses
  `ZogographUptimeEventsCard.text(...)`, but the card has no `text` method.
  The registry contract must make text/formatting capability explicit and test
  every public card API before a page invokes it.
- [ ] Fix the `core-ai-identity-settings` manifest mismatch. Its renderer is
  passed `aiPortraitAlt`, but that helper is absent from the manifest helper
  list, so normal helper-contract validation rejects the card. Add a test that
  compares every destructured renderer helper to its declared manifest helpers
  and every helper supplied by its caller.
- [ ] Fix CPU card accumulation. “Where the time goes” appends its state cards
  on every refresh while “Waiting to run” already clears its holder first;
  this produces repeated cards such as “Stolen by the host” and “Waiting for
  storage.” Require the shared card lifecycle to replace a card's owned
  container atomically, rather than append into an ambient holder, and add a
  repeated-refresh fixture asserting one of each expected CPU card.
- [ ] Treat the `VM… reportAllChanges/startTime` trace as unassigned until it
  reproduces in a clean browser profile with extensions disabled. `VM` source
  and `installHook.js` can be injected by browser tooling; record browser,
  extension state, reproduction route, and whether the error survives a clean
  profile before attributing it to client code. Do not suppress it blindly.
- [ ] Add a console-clean browser acceptance test for each route and locale:
  no uncaught exceptions, no invalid card manifests, no duplicate cards after
  initial render, polling refresh, locale change, route change, and return.

## Completion gates

- [ ] No feature-specific card globals remain outside the compatibility adapter;
  no growing ESLint globals list is needed for cards.
- [ ] A signed declarative package can add a new section in `dashboard` or
  `setup`, localize it, and be enabled/disabled without a client source edit.
- [ ] Existing built-in optional modules use the same registry and can be
  enabled/disabled without residual polling, navigation, or client errors.
- [ ] All card containers use the shared primitives and meet visual,
  keyboard, screen-reader, responsive, and RTL fixtures.
- [ ] Localization scans and all-locale UI fixtures pass, including retention
  controls and formatted rates/durations.
- [ ] Package validation, authorization, integrity, compatibility, and
  disable/recovery tests pass before any marketplace-facing rollout.

