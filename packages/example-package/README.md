## Example signed package

This is an inert authoring template, not an installed package. Copy it outside
the client tree, provide the reviewed runtime files, enumerate every file in
the integrity manifest, then sign the final manifest with a trusted publisher
key.

The example demonstrates the current safe package surface:

- an enabled package contributes a dashboard page below its own section through
  `parent`, `parent_title`, and `parent_title_key`;
- it contributes only the allowlisted `overview-facts` card shape—the shell
  supplies all HTML, CSS, data access, and rendering;
- it declares a separate Setup panel and a JSON settings schema;
- English is the authoring fallback; `locales/es.json` is an optional
  namespaced language pack.

Package manifests must never contain browser JavaScript, HTML, stylesheets,
SQL, or arbitrary HTTP endpoints. See the modularity checklist for the pending
lifecycle, capability, and navigation-ordering work.
