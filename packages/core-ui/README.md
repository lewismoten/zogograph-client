## Core UI package

This package holds small reviewed UI cards shared across features: gauge and
live-state labels, navigation badges, and common action, chart, and table copy. They
are registered through the shared card registry rather than feature-specific
browser globals. This is product code—not an installable executable package—
and keeps English beside each renderer while optional locale files remain
separate.

Shared DOM primitives belong in `ui/card-kit.js`; feature packages receive
those primitives through declared card helpers instead of copying rendering
boilerplate.
