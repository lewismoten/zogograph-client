# Third-party notices

> [Project README](../README.md) · [Documentation](README.md) · [Notes](../notes/README.md)

Zogograph currently ships without Composer, npm, or vendored runtime
dependencies. Its dashboard uses browser platform APIs and its server uses PHP
and SQLite supplied by the deployment host.

This inventory covers the source tree as released. A dependency added later
must be recorded here with its name, version, source location, license, and
required attribution before release; regenerate `SBOM.cdx.json` at the same
time.

Host-provided PHP extensions, operating-system commands, and services are not
bundled components. Their availability is detected per installation and is
reported by Zogograph rather than licensed or redistributed by it.
