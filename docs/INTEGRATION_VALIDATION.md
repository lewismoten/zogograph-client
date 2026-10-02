# Deployed validation checklist

> [Project README](../README.md) · [Documentation](README.md) · [Notes](../notes/README.md)

Run this only against a disposable HTTPS deployment with independent test
accounts and transfer credentials. Do not place production passwords, private
keys, recovery codes, or customer databases in a release tree or test log.

1. Deploy two distinct build identities. Load `setup.php`, then replace the
   release with the second build. Return focus to the existing Setup tab and
   verify its update notice identifies the new build and reloads safely.
2. Create a confidential OIDC client in Setup. With an independent OIDC client,
   fetch discovery and JWKS over HTTPS, run Authorization Code plus PKCE, and
   verify the callback, token, and nonce/state checks succeed.
3. Enroll TOTP using the displayed QR code in a separate authenticator. Confirm
   a generated code before saving, record the recovery codes, reload, and
   verify that recovery codes are not displayed again. Verify one recovery code
   works once and cannot be reused.
4. For each configured SSH/SFTP/FTPS profile, run the Setup target test, deploy
   only the selected core packages to a disposable release root, verify no
   credentials or private configuration are public, then confirm the remote
   controller preflight and selected update policy.

Record the deployment URL, build IDs, transfer method, test timestamp, and
redacted outcomes in the release audit. A failed check must not be treated as a
successful deployment.
