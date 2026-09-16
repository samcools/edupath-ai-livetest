# EduPath AI v3.1.0 Source Snapshot

This directory preserves the complete current EduPath AI v3.1.0 source tree as a chunked base64-encoded `tar.gz` archive.

The snapshot is split into 19 files under `source-parts/` because the repository connector used during the hackathon build has a per-write payload limit. Concatenating the parts reproduces the exact original base64 file.

## Integrity

Base64 file SHA-256:

`07103689f80b3ca2e73c30e491a63f13eff4904abe6c65e5f49fb7f81306526e`

Decoded tar.gz SHA-256:

`6b2610b7f8aae88f86da020f8aa46fbc6f8910a0a71661d9e2d94e1497c28253`

## Restore

From the repository root:

```bash
bash scripts/restore-v3.1.0-source.sh /tmp/edupath-v3.1.0
```

The restored source contains:

- `wordpress-theme/edupath-ai-theme/`
- `wordpress-plugin/edupath-ai-platform/`
- `MASTER-BUILD-PROMPT.md`
- project README and docs
- v3.1 QA and Foundry setup notes
- build workflow source

No real API keys, passwords, OTP codes or private tokens are included.
