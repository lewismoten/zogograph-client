# Core AI tool modules

Each file in this directory owns exactly one reviewed AI-tool declaration and
returns it as a one-entry catalogue map. Keep the stable tool name, parameter
schema, permission flag, and group unchanged when extracting a tool.

These are core modules today, not externally executable packages: the core
catalogue remains the policy boundary and packages must not introduce database
queries, browser code, or arbitrary handlers. The one-tool-per-file shape is
the migration boundary for future signed tool packages and their namespaced
locale catalogs. A tool's browser-visible strings belong in
`locales/core-ai-<tool-name>/<locale>.json` below this directory. Each catalog
is inert JSON and may contain only keys beginning with that tool's stable
`core-ai-<tool-name>.` package namespace.
