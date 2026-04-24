# SurrealDB Connection Reference

This project uses the V2 SurrealDB graph reference as the current source for student and school operational data.

## Current Target

- Endpoint: `http://100.76.245.13:8000`
- Namespace: `insan_taqwa`
- Database: `school_graph_v1`
- Server version: `surrealdb-3.0.5`
- Reference docs: [SurrealDBV2 README](SurrealDBV2/README.md)

## Environment Variables

Use environment variables or a secret manager. Do not hardcode SurrealDB credentials in Laravel code, frontend code, tests, or docs.

```dotenv
SURREALDB_ENDPOINT=http://100.76.245.13:8000
SURREALDB_NAMESPACE=insan_taqwa
SURREALDB_GRAPH_DATABASE=school_graph_v1
SURREALDB_APP_USER=school_app
SURREALDB_APP_PASS=replace-with-secret
SURREALDB_APP_ROLE=app
SURREALDB_TIMEOUT=15
```

## Integration Rule

Laravel should treat SurrealDB as an external system-of-record input, not as a local relational join target.

- Keep credentials server-side.
- Expose Laravel endpoints for specific student-data use cases.
- Do not expose a generic browser-facing SurrealQL endpoint.
- Cache `/signin` JWTs for less than their token lifetime.
- Use bound parameters for dynamic values instead of building SurrealQL strings from request input.
- Keep library-core writes independent from SurrealDB availability.

## V2 Graph Model

The active V2 reference models school data as typed SurrealDB graph relations. Entity tables are `TYPE NORMAL SCHEMAFULL`; relationship tables are `TYPE RELATION ... ENFORCED SCHEMAFULL`.

Key relation examples:

- `person -> is_student -> student`
- `student -> has_enrollment -> enrollment`
- `enrollment -> in_class -> class_group`
- `attendance_event -> for_student -> student`
- `attendance_event -> for_session -> class_session`
- `attendance_event -> from_source -> source_file`

Use graph traversal for relationship lookups. Keep stored code fields such as `student_code` and `class_group_code` for import traceability, not as the primary join mechanism.

## Quick Checks

```bash
curl -i http://100.76.245.13:8000/status
curl -i http://100.76.245.13:8000/health
curl -sS http://100.76.245.13:8000/version
```

After signing in, these queries should return live V2 graph counts:

```surql
INFO FOR DB;

SELECT count() AS count FROM student GROUP ALL;
SELECT count() AS count FROM enrollment GROUP ALL;
SELECT count() AS count FROM attendance_event GROUP ALL;
SELECT count() AS count FROM has_enrollment GROUP ALL;
SELECT count() AS count FROM in_class GROUP ALL;
SELECT count() AS count FROM for_student GROUP ALL;
SELECT count() AS count FROM for_session GROUP ALL;
```

Expected current V2 counts are documented in [Graph Query Examples](SurrealDBV2/GRAPH_QUERY_EXAMPLES.md).

## Detailed References

- [Connect V2 Clients](SurrealDBV2/CONNECT_CLIENTS.md): REST, Laravel service, and Surrealist setup.
- [Graph Database Guide](SurrealDBV2/GRAPH_DATABASE_GUIDE.md): schema rules, relation tables, load order, and verification.
- [Graph Query Examples](SurrealDBV2/GRAPH_QUERY_EXAMPLES.md): student, class, household, parent, attendance, and data-quality queries.
