# Bundled marketplace manifests

`hostscope-path-hygiene.json` is a data-only package manifest for the local
marketplace. It authorizes a small, core-owned check; it does not load or run
package code.

To try it after deploying this release, sign in, open **System → Marketplace**,
choose **Install Path-name hygiene**, then enable **Path-name hygiene**. Run the
normal account disk scan. Its non-notifying result appears in **What is wrong**
only when it finds a name needing review, and its panel appears on
**Storage**. Searching navigation for `hygiene`, `shell`, or `filename` finds
that page. The manual installer remains for
third-party manifests; do not paste this README into it.

Packages can declare `view_contributions` to attach a core-owned capability to
an approved existing view. Each entry names its `capability`, `view`, optional
navigation `keywords`, `priority` (`high`, `medium`, or `low`), and optional
package-ID `before`/`after` lists. These are ordering hints for compatible
core panels, not permission to supply HTML, JavaScript, or PHP.

For a harmless demonstration in a throwaway directory inside the configured
scan root, create a file with a trailing space:

```sh
touch 'Zogograph path hygiene demo '
```

Run `php cron.php --scan`, refresh **What is wrong**, then delete the test file
and scan again. The plugin treats control characters, leading or trailing
whitespace, a leading dash, and shell/wildcard characters as review-worthy.
Ordinary interior spaces and Unicode are not findings.
