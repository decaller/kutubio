# Graph Database Guide V2

Target database:

- Namespace: `insan_taqwa`
- Database: `school_graph_v1`
- Goal: rebuild the current graph database so table-to-table traversal works like the store example in `docs/example/store/sandbox-2026-04-24.surql`.

## Current Finding

The live `school_graph_v1` has entity records, but it is not a real graph yet.

Observed counts:

- `student`: 668
- `person`: 1871
- `household`: 591
- `class_group`: 25
- `enrollment`: 668
- `attendance_event`: 9476

Observed graph issue:

- Relation tables such as `is_student`, `belongs_to_household`, `has_parent`, `has_enrollment`, `in_class`, `for_student`, `for_session`, and `from_source` do not exist.
- `INFO FOR DB` shows entity tables as `TYPE ANY SCHEMALESS PERMISSIONS NONE`, which means schema application did not succeed before rows were inserted.
- Traversal queries such as `student->has_enrollment->enrollment` and `attendance_event->for_student->student` return empty arrays.

This means the current implementation stores denormalized string codes on rows, but the SurrealDB edge tables were not created and populated.

## V2 Design Rule

Use the same pattern as the store example:

```surql
DEFINE TABLE wishlist TYPE RELATION FROM person TO product SCHEMAFULL;
DEFINE TABLE cart TYPE RELATION FROM person TO product SCHEMAFULL;
DEFINE TABLE order TYPE RELATION FROM person TO product SCHEMAFULL;
DEFINE TABLE review TYPE RELATION FROM person TO product SCHEMAFULL;
```

For the school graph, every relationship table must be declared as `TYPE RELATION IN ... OUT ...` before any data is loaded.

Entity records store their own attributes. Edge records store relationships and relationship metadata.

## V2 Load Order

The migration must run in this order:

1. Remove or rebuild `school_graph_v1`.
2. Apply schema and fail immediately if any statement returns `ERR`.
3. Upsert base entity tables.
4. Upsert transactional/fact tables.
5. Create graph edges with `RELATE` or `INSERT RELATION`.
6. Run verification queries and fail if expected relation counts are missing.

Do not create entity rows before schema verification.

## V2 Entity Tables

Use `TYPE NORMAL SCHEMAFULL` for entities:

- `school`
- `campus`
- `academic_term`
- `grade_level`
- `person`
- `student`
- `parent`
- `teacher`
- `employee`
- `household`
- `class_group`
- `enrollment`
- `class_session`
- `attendance_event`
- `source_file`
- `ingest_run`
- future tables: `subject`, `lesson`, `assessment`, `score_entry`, `report_card`, `fee_plan`, `invoice`, `payment_transaction`, `homework`, `homework_submission`

Entity records may keep code fields such as `student_code` and `class_group_code` for import traceability, but those fields are not the join mechanism. Graph traversal must use edge tables.

## V2 Relation Tables

Define typed relation tables:

```surql
DEFINE TABLE is_student TYPE RELATION IN person OUT student ENFORCED SCHEMAFULL;
DEFINE TABLE is_parent TYPE RELATION IN person OUT parent ENFORCED SCHEMAFULL;
DEFINE TABLE is_teacher TYPE RELATION IN person OUT teacher ENFORCED SCHEMAFULL;

DEFINE TABLE belongs_to_household TYPE RELATION IN student OUT household ENFORCED SCHEMAFULL;
DEFINE TABLE parent_in_household TYPE RELATION IN parent OUT household ENFORCED SCHEMAFULL;
DEFINE TABLE has_parent TYPE RELATION IN student OUT parent ENFORCED SCHEMAFULL;

DEFINE TABLE has_enrollment TYPE RELATION IN student OUT enrollment ENFORCED SCHEMAFULL;
DEFINE TABLE in_class TYPE RELATION IN enrollment OUT class_group ENFORCED SCHEMAFULL;
DEFINE TABLE in_grade TYPE RELATION IN class_group OUT grade_level ENFORCED SCHEMAFULL;
DEFINE TABLE for_term TYPE RELATION IN enrollment OUT academic_term ENFORCED SCHEMAFULL;

DEFINE TABLE homeroom_for TYPE RELATION IN teacher OUT class_group ENFORCED SCHEMAFULL;
DEFINE TABLE scheduled_session TYPE RELATION IN class_group OUT class_session ENFORCED SCHEMAFULL;

DEFINE TABLE for_student TYPE RELATION IN attendance_event OUT student ENFORCED SCHEMAFULL;
DEFINE TABLE for_session TYPE RELATION IN attendance_event OUT class_session ENFORCED SCHEMAFULL;
DEFINE TABLE from_source TYPE RELATION IN attendance_event OUT source_file ENFORCED SCHEMAFULL;
```

Add the standard fields and indexes to every relation table:

```surql
DEFINE FIELD in ON TABLE has_parent TYPE record<student>;
DEFINE FIELD out ON TABLE has_parent TYPE record<parent>;
DEFINE FIELD created_at ON TABLE has_parent TYPE datetime DEFAULT time::now();
DEFINE FIELD relationship_type ON TABLE has_parent TYPE string ASSERT $value IN ['father', 'mother', 'guardian'];
DEFINE INDEX has_parent_unique ON TABLE has_parent COLUMNS in, out, relationship_type UNIQUE;
```

For relation tables without extra metadata:

```surql
DEFINE FIELD in ON TABLE has_enrollment TYPE record<student>;
DEFINE FIELD out ON TABLE has_enrollment TYPE record<enrollment>;
DEFINE FIELD created_at ON TABLE has_enrollment TYPE datetime DEFAULT time::now();
DEFINE INDEX has_enrollment_unique ON TABLE has_enrollment COLUMNS in, out UNIQUE;
```

## V2 Insert Strategy

Use deterministic record IDs for entities:

```surql
UPSERT student:`student-202101001` CONTENT {
    student_code: 'student-202101001',
    nipd: '252601001',
    nisn: '...',
    active_status: 'active',
    updated_at: time::now()
};
```

Then relate existing records:

```surql
RELATE person:`person-student-202101001`->is_student->student:`student-202101001`
    SET created_at = time::now();

RELATE student:`student-202101001`->belongs_to_household->household:`household-317...`
    SET created_at = time::now();

RELATE student:`student-202101001`->has_parent->parent:`parent-317...-father`
    SET relationship_type = 'father', created_at = time::now();

RELATE student:`student-202101001`->has_enrollment->enrollment:`enrollment-202101001-2025-2026-genap`
    SET created_at = time::now();

RELATE enrollment:`enrollment-202101001-2025-2026-genap`->in_class->class_group:`class-kelas-1-a-2025-2026-genap`
    SET created_at = time::now();

RELATE enrollment:`enrollment-202101001-2025-2026-genap`->for_term->academic_term:`2025-2026-genap`
    SET created_at = time::now();
```

For attendance:

```surql
UPSERT attendance_event:`attendance-kelas-1-a-252601001-2026-04-01` CONTENT {
    attendance_event_code: 'attendance-kelas-1-a-252601001-2026-04-01',
    attendance_key: 'kelas-1-a-252601001-2026-04-01',
    attendance_date: d'2026-04-01T00:00:00Z',
    attendance_code: 'H',
    attendance_label: 'Hadir',
    updated_at: time::now()
};

RELATE attendance_event:`attendance-kelas-1-a-252601001-2026-04-01`->for_student->student:`student-202101001`
    SET created_at = time::now();

RELATE attendance_event:`attendance-kelas-1-a-252601001-2026-04-01`->for_session->class_session:`session-kelas-1-a-2026-04-01`
    SET created_at = time::now();

RELATE attendance_event:`attendance-kelas-1-a-252601001-2026-04-01`->from_source->source_file:`source-attendance-april-2026`
    SET created_at = time::now();
```

## V2 Verification Queries

These must pass after migration:

```surql
INFO FOR DB;

SELECT count() AS count FROM student GROUP ALL;
SELECT count() AS count FROM enrollment GROUP ALL;
SELECT count() AS count FROM attendance_event GROUP ALL;

SELECT count() AS count FROM is_student GROUP ALL;
SELECT count() AS count FROM belongs_to_household GROUP ALL;
SELECT count() AS count FROM has_parent GROUP ALL;
SELECT count() AS count FROM has_enrollment GROUP ALL;
SELECT count() AS count FROM in_class GROUP ALL;
SELECT count() AS count FROM for_student GROUP ALL;
SELECT count() AS count FROM for_session GROUP ALL;
SELECT count() AS count FROM from_source GROUP ALL;
```

Expected minimum relation counts for the current dataset:

- `is_student`: 668
- `belongs_to_household`: 668
- `has_enrollment`: 668
- `in_class`: 668
- `for_term`: 668
- `for_student`: equal to `attendance_event`
- `for_session`: equal to `attendance_event`
- `from_source`: equal to `attendance_event`

`has_parent` depends on source completeness, but should be greater than student count because most students have father and mother rows.

## Sample Graph Queries

### Student profile with household, parents, class, and term

```surql
SELECT
    id,
    <-is_student<-person.full_name AS name,
    ->belongs_to_household->household.* AS household,
    ->has_parent->parent.{
        id,
        parent_role,
        person: <-is_parent<-person.{ full_name, nik, gender }
    } AS parents,
    ->has_enrollment->enrollment.{
        id,
        enrollment_status,
        class: ->in_class->class_group.{ class_name, homeroom_teacher_name },
        term: ->for_term->academic_term.{ school_year, semester }
    } AS enrollments
FROM student:`student-202101001`;
```

### Class roster

```surql
SELECT
    id,
    class_name,
    <-in_class<-enrollment<-has_enrollment<-student.{
        id,
        nipd,
        nisn,
        person: <-is_student<-person.{ full_name, gender }
    } AS students
FROM class_group:`class-kelas-1-a-2025-2026-genap`;
```

### Household siblings

```surql
SELECT
    id,
    family_card_number,
    <-belongs_to_household<-student.{
        id,
        nipd,
        person: <-is_student<-person.full_name
    } AS students
FROM household
WHERE count(<-belongs_to_household<-student) > 1
LIMIT 20;
```

### Attendance timeline for one student

```surql
SELECT
    attendance_date,
    attendance_code,
    attendance_label,
    ->for_session->class_session.{
        session_date,
        class: <-scheduled_session<-class_group.class_name
    } AS session
FROM attendance_event
WHERE ->for_student->student[0] = student:`student-202101001`
ORDER BY attendance_date ASC;
```

### Attendance summary by class

```surql
SELECT
    ->for_session->class_session<-scheduled_session<-class_group[0].class_name AS class_name,
    attendance_code,
    count() AS total
FROM attendance_event
GROUP BY class_name, attendance_code
ORDER BY class_name, attendance_code;
```

### Homeroom teacher to students

```surql
SELECT
    <-is_teacher<-person.full_name AS teacher_name,
    ->homeroom_for->class_group.{
        class_name,
        students: <-in_class<-enrollment<-has_enrollment<-student.{
            id,
            person: <-is_student<-person.full_name
        }
    } AS classes
FROM teacher;
```

## Implementation Checklist

- Define all entity tables as `TYPE NORMAL SCHEMAFULL`.
- Define all edge tables as `TYPE RELATION IN ... OUT ... SCHEMAFULL ENFORCED`.
- Add `in`, `out`, `created_at`, and unique `(in, out)` indexes to all edge tables.
- Add edge-specific fields only where the relationship has metadata, such as `relationship_type`.
- Validate every schema statement result and stop on the first `ERR`.
- Insert records first, then create `RELATE` edges.
- Validate relation table counts after migration.
- Keep denormalized code fields only for import lineage and debugging, not for joins.
