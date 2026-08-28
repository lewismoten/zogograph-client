# Security policy

## Reporting a vulnerability

Do not open a public issue for a suspected vulnerability, exposed credential,
or customer-data disclosure. Report it privately to the address configured in
`security.contact` on the affected installation. If that field is blank, use
the installation owner's established private support channel.

Include the Zogograph version or commit, deployment type, affected URL or
feature, a minimal reproduction, impact, and any relevant log reference. Do
not send passwords, API keys, raw databases, full request captures, or private
host paths unless the owner requests an encrypted transfer.

## Response expectations

The installation owner should acknowledge a report promptly, preserve relevant
audit evidence, contain exposed credentials first, and document the fix and
recovery steps. Revoke remote pairing credentials, OIDC client secrets, and AI
provider keys when they may have been exposed.

## Supported reporting surface

Security-sensitive changes should have server-side authorization, CSRF or
request authentication as appropriate, audit evidence without secrets, and a
repeatable local check under `tests/`. See `README.md` for the current checks.
