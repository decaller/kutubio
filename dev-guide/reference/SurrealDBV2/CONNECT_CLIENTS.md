# Connect V2 Clients

Target graph database:

- Endpoint: `http://100.76.245.13:8000`
- Namespace: `insan_taqwa`
- Database: `school_graph_v1`
- Server version: `surrealdb-3.0.5`

Use the database app user from `.env`:

- `SURREALDB_ENDPOINT`
- `SURREALDB_NAMESPACE`
- `SURREALDB_GRAPH_DATABASE`
- `SURREALDB_APP_USER`
- `SURREALDB_APP_PASS`
- `SURREALDB_APP_ROLE`
- `SURREALDB_TIMEOUT`

Do not hardcode credentials in client code. Load them from environment variables or a secret manager.

## REST API

SurrealDB exposes HTTP endpoints on the same server:

- `GET /status`
- `GET /health`
- `GET /version`
- `POST /signin`
- `POST /sql`
- `POST /rpc`

### Health check

```bash
curl -i http://100.76.245.13:8000/status
curl -i http://100.76.245.13:8000/health
curl -sS http://100.76.245.13:8000/version
```

### Sign in and run a query

From this repository:

```bash
set -a
. ./.env
set +a

TOKEN=$(curl -sS -X POST \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d "{
    \"ns\": \"${SURREALDB_NAMESPACE}\",
    \"db\": \"${SURREALDB_GRAPH_DATABASE}\",
    \"user\": \"${SURREALDB_APP_USER}\",
    \"pass\": \"${SURREALDB_APP_PASS}\"
  }" \
  "${SURREALDB_ENDPOINT}/signin" \
  | python3 -c 'import json,sys; print(json.load(sys.stdin)["token"])')

curl -sS -X POST \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Surreal-NS: ${SURREALDB_NAMESPACE}" \
  -H "Surreal-DB: ${SURREALDB_GRAPH_DATABASE}" \
  -d "SELECT count() AS count FROM student GROUP ALL;" \
  "${SURREALDB_ENDPOINT}/sql"
```

### Run a graph expansion query

```bash
curl -sS -X POST \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Surreal-NS: ${SURREALDB_NAMESPACE}" \
  -H "Surreal-DB: ${SURREALDB_GRAPH_DATABASE}" \
  -d "
    SELECT
      *,
      <-is_student<-person AS identity,
      ->belongs_to_household->household AS households,
      ->has_parent->parent AS parents,
      ->has_enrollment->enrollment AS enrollments,
      ->has_enrollment->enrollment->in_class->class_group AS classes
    FROM student
    LIMIT 3;
  " \
  "${SURREALDB_ENDPOINT}/sql"
```

### Query with URL parameters

Use parameters for dynamic values instead of string-building SurrealQL.

```bash
curl -sS -G -X POST \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Surreal-NS: ${SURREALDB_NAMESPACE}" \
  -H "Surreal-DB: ${SURREALDB_GRAPH_DATABASE}" \
  --data-urlencode "student=student:student-202101001" \
  --data-urlencode "query=
    SELECT
      id,
      student_code,
      <-is_student<-person[0].full_name AS full_name,
      ->has_enrollment->enrollment[0]->in_class->class_group[0].class_name AS class_name
    FROM \$student;
  " \
  "${SURREALDB_ENDPOINT}/sql"
```

If your HTTP client does not support query parameters cleanly for `/sql`, build a small server-side allowlist of known query templates and bind only scalar inputs.

## Laravel

Laravel should connect to SurrealDB through a small service class using Laravel's HTTP client. Keep credentials in `.env` and expose only application-specific endpoints from Laravel.

### Environment

Add these to the Laravel app `.env`:

```dotenv
SURREALDB_ENDPOINT=http://100.76.245.13:8000
SURREALDB_NAMESPACE=insan_taqwa
SURREALDB_GRAPH_DATABASE=school_graph_v1
SURREALDB_APP_USER=school_app
SURREALDB_APP_PASS=replace-with-secret
SURREALDB_APP_ROLE=app
SURREALDB_TIMEOUT=15
```

### Config file

Create `config/surrealdb.php`:

```php
<?php

declare(strict_types=1);

return [
    'endpoint' => env('SURREALDB_ENDPOINT', 'http://127.0.0.1:8000'),
    'namespace' => env('SURREALDB_NAMESPACE', 'insan_taqwa'),
    'database' => env('SURREALDB_GRAPH_DATABASE', 'school_graph_v1'),
    'username' => env('SURREALDB_APP_USER', 'school_app'),
    'password' => env('SURREALDB_APP_PASS'),
    'role' => env('SURREALDB_APP_ROLE', 'app'),
    'timeout' => (int) env('SURREALDB_TIMEOUT', 15),
];
```

### Service class

Create `app/Services/SurrealDbClient.php`:

```php
<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class SurrealDbClient
{
    public function query(string $surql, array $params = []): array
    {
        $response = $this->http()
            ->withToken($this->token())
            ->withHeaders([
                'Surreal-NS' => config('surrealdb.namespace'),
                'Surreal-DB' => config('surrealdb.database'),
            ])
            ->withQueryParameters($params)
            ->withBody($surql, 'text/plain')
            ->post('/sql');

        if ($response->failed()) {
            throw new RuntimeException('SurrealDB query failed: '.$response->body());
        }

        return $response->json('result', []);
    }

    private function token(): string
    {
        return Cache::remember('surrealdb.token', now()->addMinutes(50), function (): string {
            $response = $this->http()->post('/signin', [
                'ns' => config('surrealdb.namespace'),
                'db' => config('surrealdb.database'),
                'user' => config('surrealdb.username'),
                'pass' => config('surrealdb.password'),
            ]);

            if ($response->failed()) {
                throw new RuntimeException('SurrealDB signin failed: '.$response->body());
            }

            $token = $response->json('token');

            if (! is_string($token) || $token === '') {
                throw new RuntimeException('SurrealDB signin did not return a token.');
            }

            return $token;
        });
    }

    private function http(): PendingRequest
    {
        return Http::baseUrl(config('surrealdb.endpoint'))
            ->acceptJson()
            ->timeout(config('surrealdb.timeout'));
    }
}
```

### Controller example

Create `app/Http/Controllers/SchoolGraphController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\SurrealDbClient;
use Illuminate\Http\JsonResponse;

final class SchoolGraphController
{
    public function classes(SurrealDbClient $db): JsonResponse
    {
        $result = $db->query(<<<'SURQL'
            SELECT
                class_name,
                homeroom_teacher_name,
                count(<-in_class<-enrollment<-has_enrollment<-student) AS student_count
            FROM class_group
            ORDER BY class_name;
        SURQL);

        return response()->json($result[0]['result'] ?? []);
    }

    public function student(string $studentCode, SurrealDbClient $db): JsonResponse
    {
        $recordId = 'student:'.$studentCode;

        $result = $db->query(<<<'SURQL'
            SELECT
                *,
                <-is_student<-person AS identity,
                ->belongs_to_household->household AS households,
                ->has_parent->parent AS parents,
                ->has_enrollment->enrollment AS enrollments,
                ->has_enrollment->enrollment->in_class->class_group AS classes
            FROM $student;
        SURQL, [
            'student' => $recordId,
        ]);

        return response()->json($result[0]['result'] ?? []);
    }
}
```

### Routes

Add to `routes/api.php`:

```php
<?php

use App\Http\Controllers\SchoolGraphController;
use Illuminate\Support\Facades\Route;

Route::get('/school/classes', [SchoolGraphController::class, 'classes']);
Route::get('/school/students/{studentCode}', [SchoolGraphController::class, 'student']);
```

Example requests:

```bash
curl http://your-laravel-app.test/api/school/classes
curl http://your-laravel-app.test/api/school/students/student-202101001
```

### Laravel notes

- Keep SurrealDB credentials server-side only.
- Do not expose a generic "run any SurrealQL" endpoint to browsers.
- Create Laravel API endpoints for specific use cases.
- Cache the `/signin` token for less than its token lifetime.
- Keep heavy graph aggregations as dedicated, reviewed queries.

## Surrealist

Use Surrealist when you want to inspect records, run graph queries manually, or view relation tables.

### Connection setup

1. Open Surrealist Desktop or `https://app.surrealdb.com`.
2. Create a new remote connection.
3. Set endpoint to `http://100.76.245.13:8000`.
4. Set namespace to `insan_taqwa`.
5. Set database to `school_graph_v1`.
6. Use database user authentication:
    - Username: `school_app`
    - Password: value of `SURREALDB_APP_PASS` from this repo `.env`
7. Test the connection and save.

### First queries to run

```surql
INFO FOR DB;

SELECT count() AS count FROM student GROUP ALL;
SELECT count() AS count FROM attendance_event GROUP ALL;
SELECT count() AS count FROM has_enrollment GROUP ALL;
SELECT count() AS count FROM for_student GROUP ALL;
```

### Graph expansion query

```surql
SELECT
    *,
    <-is_student<-person AS identity,
    ->belongs_to_household->household AS households,
    ->has_parent->parent AS parents,
    ->has_enrollment->enrollment AS enrollments,
    ->has_enrollment->enrollment->in_class->class_group AS classes
FROM student
LIMIT 10;
```

### Relation table checks

```surql
SELECT * FROM has_enrollment LIMIT 10;
SELECT * FROM in_class LIMIT 10;
SELECT * FROM for_student LIMIT 10;
SELECT * FROM for_session LIMIT 10;
```

### Designer view

In Surrealist Designer, relation tables should appear as graph edges:

- `person -> is_student -> student`
- `student -> has_enrollment -> enrollment`
- `enrollment -> in_class -> class_group`
- `attendance_event -> for_student -> student`
- `attendance_event -> for_session -> class_session`
- `attendance_event -> from_source -> source_file`

If relation tables show up as normal schemaless tables, rerun the V2 migration and confirm `INFO FOR DB` contains `TYPE RELATION ... ENFORCED SCHEMAFULL` for the edge tables.
