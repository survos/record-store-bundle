# Record Store Bundle demo

This directory is a provider-development lab: an isolated, local-only Grist server plus a minimal
Symfony console wired directly to `survos/record-store-bundle`. It does not add another application
repository and it is not a production Grist deployment.

The demo uses port `8485` by default so it can coexist with the shared Grist instance in
`~/sites/docker` on port `8484`.

## 1. Start Grist

```bash
docker compose up -d
docker compose ps
```

Open <http://localhost:8485>. Create a document named **Record Store Demo**, then create a table
with table ID `People` and text columns with column IDs `Name` and `Email`. Add a couple of rows.

Schema provisioning is manual for this first vertical slice. That keeps the demo honest about the
bundle's current contract: schema discovery, record query, insert, and upsert are implemented;
portable schema reconciliation is not.

## 2. Get a development API key

The Compose service is deliberately single-user and bound to loopback. Its login endpoint creates
a session for `demo@example.test`; use that session to request an API key without printing it:

```bash
curl -fsS -c /tmp/record-store-grist.jar -L http://localhost:8485/login -o /dev/null
grist_demo_key="$(curl -fsS -b /tmp/record-store-grist.jar \
  http://localhost:8485/api/profile/apikey | tr -d '\"')"
if [ -z "$grist_demo_key" ] || [ "$grist_demo_key" = null ]; then
  grist_demo_key="$(curl -fsS -b /tmp/record-store-grist.jar -X POST \
    -H 'Content-Type: application/json' -d '{}' \
    http://localhost:8485/api/profile/apikey | tr -d '\"')"
fi
export GRIST_API_KEY="$grist_demo_key"
unset grist_demo_key
rm -f /tmp/record-store-grist.jar
```

Copy the document ID from its URL and export it. Grist document URLs use the segment immediately
after `/doc/` as the ID.

```bash
export GRIST_DEMO_DOC_ID='your-document-id'
```

## 3. Exercise the bundle

From this directory:

```bash
bin/console record-store:applications
bin/console record-store:schema demo
bin/console record-store:query demo.people --select=name,email --json
```

The console uses the mono checkout's Composer dependencies when run in mono and the package's own
`vendor/` directory in a standalone checkout. Set `GRIST_BASE_URI` to point it at another Grist
instance.

## Stop or reset

```bash
docker compose down
docker compose down --volumes # also deletes this demo's Grist documents
```

The second command is intentionally destructive and should only be used when the demo data is
disposable.
