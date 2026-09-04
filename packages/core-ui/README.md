## Core UI package

This package holds small reviewed UI cards shared across features. Its first
card, gauge status, supplies localized gauge availability labels through the
card registry. It is product code—not an installable executable package—and
keeps English beside the renderer while optional locale files remain separate.

Shared DOM primitives belong in `ui/card-kit.js`; feature packages receive
those primitives through declared card helpers instead of copying rendering
boilerplate.
