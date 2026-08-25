# Survos Record Store Bundle

Provider-neutral Symfony access to application-backed tables. The portable API addresses logical applications, tables, and fields; adapters translate them to provider-native resources.

The initial bundle includes a native Grist REST client and adapter. `survos/quickbase-bundle` provides the Quickbase adapter while retaining its complete provider-specific client.

## Configuration

```yaml
survos_record_store:
    connections:
        internal:
            driver: grist
            options:
                base_uri: '%env(GRIST_BASE_URI)%'
                token: '%env(GRIST_API_KEY)%'

    applications:
        contacts:
            connection: internal
            id: '%env(GRIST_CONTACTS_DOC_ID)%'
            tables:
                contacts:
                    id: Contacts
                    fields:
                        name: Name
                        email: Email
```

Quickbase connections use `driver: quickbase`; realm and token configuration remains in `survos_quickbase` for backward compatibility.

## Read-only commands

```bash
bin/console record-store:applications
bin/console record-store:schema contacts
bin/console record-store:query contacts.contacts --select=name,email --json
bin/console record-store:query contacts.contacts --filter='{"status":["Active"]}'
```

The generic query surface intentionally supports only equality-list filters, sorting, limits, and provider-supported offsets. Use `QuickbaseClientInterface` for native Quickbase expressions and `GristClientInterface` for Grist-native operations such as SQL.

Schema mutation, declarative blueprints, Contacts integration, and admin UI are deliberately deferred until the two-adapter contract is proven.

## Local Grist demo

The `demo/` directory contains an isolated Grist Compose stack and a minimal Symfony console that
boots this bundle directly from the mono checkout or a standalone package checkout. It is intended
for adapter development, not deployment.

See `demo/README.md` for setup and the schema/query commands.
