# API wire contract

`api.php` is a same-origin JSON API. Its current contract version is **1.0**,
sent in `X-HostScope-API-Version`. Additive fields are compatible; changing a
field's meaning, unit, or type requires a new major version and a client
transition.

## Numbers and time

- IDs, counts, byte totals, timestamps, and durations are JSON integers.
- Byte fields are bytes. Durations state their unit in the field name, such as
  `duration_ms`; Unix times use seconds in `*_at` fields.
- Measurements are JSON numbers rounded to at most five decimal places at the
  response boundary. `null` means unavailable or not measured, while `0` means
  measured as zero.
- Do not use a tiny float when its precision matters below `0.00001`. Send an
  integer-scaled field instead, such as `rate_micros`, and document the scale.

## Encoding and delivery

- JSON is encoded with exceptions enabled and invalid UTF-8 is replaced with
  the Unicode replacement character. One malformed collected string cannot
  invalidate an entire response.
- API responses are `application/json; charset=utf-8`, `no-store`, and
  `nosniff`. Apache compresses JSON when `mod_deflate` is available.
- Arrays that can grow are bounded by endpoint-specific limits or paging. New
  endpoints must state their limit/cursor behavior before they are exposed.

## Security

Signed-out responses must contain only data already approved for public view;
paths, credentials, raw command output, and internal errors remain server-side.
Do not use API error text as a debugging channel. The response reference maps a
failure to the protected problem log instead.
