# School Graph Query Examples

Target:

- Namespace: `insan_taqwa`
- Database: `school_graph_v1`

This file is a practical query cookbook for the graph database loaded by `src/insan_taqwa_pipeline/graph_surreal.py`.

## Graph Expansion Queries

These follow the same pattern as:

```surql
SELECT
    *,
    ->relation_name->target_table AS alias
FROM source_table;
```

### Expand student graph

```surql
SELECT
    *,
    <-is_student<-person AS identity,
    ->belongs_to_household->household AS households,
    ->has_parent->parent AS parents,
    ->has_enrollment->enrollment AS enrollments
FROM student
LIMIT 10;
```

### Expand student graph with class and term

```surql
SELECT
    *,
    <-is_student<-person AS identity,
    ->belongs_to_household->household AS households,
    ->has_parent->parent AS parents,
    ->has_enrollment->enrollment AS enrollments,
    ->has_enrollment->enrollment->in_class->class_group AS classes,
    ->has_enrollment->enrollment->for_term->academic_term AS terms
FROM student
LIMIT 10;
```

### Expand attendance graph

```surql
SELECT
    *,
    ->for_student->student AS students,
    ->for_session->class_session AS sessions,
    ->from_source->source_file AS sources
FROM attendance_event
LIMIT 10;
```

### Expand class graph

```surql
SELECT
    *,
    <-in_class<-enrollment AS enrollments,
    <-in_class<-enrollment<-has_enrollment<-student AS students,
    <-homeroom_for<-teacher AS homeroom_teachers,
    ->scheduled_session->class_session AS sessions,
    ->in_grade->grade_level AS grades
FROM class_group
LIMIT 10;
```

## Quick Health Checks

```surql
INFO FOR DB;

SELECT count() AS count FROM student GROUP ALL;
SELECT count() AS count FROM enrollment GROUP ALL;
SELECT count() AS count FROM attendance_event GROUP ALL;

SELECT count() AS count FROM is_student GROUP ALL;
SELECT count() AS count FROM has_enrollment GROUP ALL;
SELECT count() AS count FROM in_class GROUP ALL;
SELECT count() AS count FROM for_student GROUP ALL;
SELECT count() AS count FROM for_session GROUP ALL;
```

Expected current counts:

- `student`: 673
- `enrollment`: 673
- `attendance_event`: 20190
- `class_session`: 750
- `has_enrollment`: 673
- `in_class`: 673
- `for_student`: 20190
- `for_session`: 20190

## Student Queries

### Student profile with identity, parents, household, and enrollment

```surql
SELECT
    id,
    student_code,
    nipd,
    nisn,
    active_status,
    <-is_student<-person[0].{
        full_name,
        gender,
        birth_place,
        birth_date,
        nik,
        religion
    } AS identity,
    ->belongs_to_household->household[0].{
        family_card_number,
        address_line,
        rt,
        rw,
        village,
        district,
        postal_code
    } AS household,
    ->has_parent->parent.{
        parent_role,
        relationship: <-has_parent[WHERE in = $parent.id][0].relationship_type,
        occupation,
        education_level,
        income_band,
        identity: <-is_parent<-person[0].{
            full_name,
            gender,
            nik
        }
    } AS parents,
    ->has_enrollment->enrollment.{
        enrollment_status,
        class: ->in_class->class_group[0].{
            class_name,
            homeroom_teacher_name
        },
        term: ->for_term->academic_term[0].{
            school_year,
            semester,
            term_name
        }
    } AS enrollments
FROM student
LIMIT 5;
```

### Find a student by name

```surql
SELECT
    id,
    full_name,
    ->is_student->student[0].{
        student_code,
        nipd,
        nisn,
        active_status
    } AS student
FROM person
WHERE full_name @@ 'Mikhayla'
LIMIT 10;
```

### Attendance-only students

These records exist because attendance has 5 student keys that were not present in the roster.

```surql
SELECT
    id,
    student_code,
    nipd,
    origin_school,
    active_status,
    <-is_student<-person[0].full_name AS display_name,
    ->has_enrollment->enrollment[0]->in_class->class_group[0].class_name AS class_name,
    count(<-for_student<-attendance_event) AS attendance_events
FROM student
WHERE active_status = 'attendance_only'
ORDER BY student_code;
```

## Class Queries

### Class roster

```surql
SELECT
    id,
    class_name,
    homeroom_teacher_name,
    <-in_class<-enrollment<-has_enrollment<-student.{
        id,
        student_code,
        nipd,
        nisn,
        active_status,
        full_name: <-is_student<-person[0].full_name,
        gender: <-is_student<-person[0].gender
    } AS students
FROM class_group:`class-kelas-1-a-2025-2026-genap`;
```

### Student count per class

```surql
SELECT
    class_name,
    homeroom_teacher_name,
    count(<-in_class<-enrollment<-has_enrollment<-student) AS student_count
FROM class_group
ORDER BY class_name;
```

### Classes by grade

```surql
SELECT
    display_name AS grade,
    <-in_grade<-class_group.{
        class_name,
        homeroom_teacher_name,
        student_count: count(<-in_class<-enrollment<-has_enrollment<-student)
    } AS classes
FROM grade_level
ORDER BY grade_number;
```

### Homeroom teacher classes

```surql
SELECT
    id,
    <-is_teacher<-person[0].full_name AS teacher_name,
    ->homeroom_for->class_group.{
        class_name,
        student_count: count(<-in_class<-enrollment<-has_enrollment<-student)
    } AS classes
FROM teacher
ORDER BY teacher_name;
```

## Household And Parent Queries

### Households with multiple students

```surql
SELECT
    id,
    family_card_number,
    address_line,
    village,
    district,
    count(<-belongs_to_household<-student) AS student_count,
    <-belongs_to_household<-student.{
        student_code,
        nipd,
        full_name: <-is_student<-person[0].full_name,
        class_name: ->has_enrollment->enrollment[0]->in_class->class_group[0].class_name
    } AS students
FROM household
WHERE count(<-belongs_to_household<-student) > 1
ORDER BY student_count DESC
LIMIT 25;
```

### Parent with all linked children

```surql
SELECT
    id,
    parent_role,
    <-is_parent<-person[0].{
        full_name,
        nik,
        gender
    } AS identity,
    <-has_parent<-student.{
        student_code,
        nipd,
        full_name: <-is_student<-person[0].full_name,
        class_name: ->has_enrollment->enrollment[0]->in_class->class_group[0].class_name
    } AS children,
    ->parent_in_household->household[0].{
        family_card_number,
        address_line,
        village,
        district
    } AS household
FROM parent
LIMIT 10;
```

### Parent occupation summary

```surql
SELECT
    parent_role,
    occupation,
    count() AS total
FROM parent
GROUP BY parent_role, occupation
ORDER BY parent_role, total DESC;
```

## Attendance Queries

For large attendance aggregations, prefer grouping on stored code fields such as `student_code`, `class_group_code`, and `attendance_date`. Use graph traversal for drill-down rows and relationship checks. Traversing multiple edges for every `attendance_event` during a full-table aggregate can be slower over the HTTP SDK.

### Attendance timeline for one student

```surql
SELECT
    attendance_date,
    attendance_code,
    attendance_label,
    ->for_session->class_session[0].{
        session_date,
        class_name: <-scheduled_session<-class_group[0].class_name
    } AS session
FROM attendance_event
WHERE ->for_student->student[0] = student:`student-252601001`
ORDER BY attendance_date ASC;
```

### Attendance summary by student

```surql
SELECT
    student_code,
    class_group_code,
    count() AS total_days,
    count(attendance_code = 'H') AS present_days
FROM attendance_event
GROUP BY student_code, class_group_code
ORDER BY total_days DESC
LIMIT 50;
```

### Attendance summary by class

```surql
SELECT
    class_group_code,
    attendance_code,
    attendance_label,
    count() AS total
FROM attendance_event
GROUP BY class_group_code, attendance_code, attendance_label
ORDER BY class_group_code, attendance_code;
```

### Daily attendance by class

```surql
SELECT
    attendance_date,
    class_group_code,
    attendance_code,
    count() AS total
FROM attendance_event
GROUP BY attendance_date, class_group_code, attendance_code
ORDER BY attendance_date, class_group_code, attendance_code;
```

### Students absent at least once

```surql
SELECT
    student_code,
    class_group_code,
    count() AS absent_days
FROM attendance_event
WHERE attendance_code = 'A'
GROUP BY student_code, class_group_code
ORDER BY absent_days DESC, class_group_code
LIMIT 50;
```

### Attendance events with source file

```surql
SELECT
    id,
    attendance_key,
    attendance_date,
    attendance_label,
    student: ->for_student->student[0].{
        student_code,
        full_name: <-is_student<-person[0].full_name
    },
    session: ->for_session->class_session[0].{
        session_date,
        class_name: <-scheduled_session<-class_group[0].class_name
    },
    source: ->from_source->source_file[0].{
        file_name,
        source_domain,
        loaded_at
    }
FROM attendance_event
LIMIT 10;
```

## Data Quality Queries

### Students without household relation

Expected: the 5 `attendance_only` placeholder students.

```surql
SELECT
    id,
    student_code,
    active_status,
    <-is_student<-person[0].full_name AS full_name
FROM student
WHERE count(->belongs_to_household->household) = 0
ORDER BY student_code;
```

### Students without parent relation

```surql
SELECT
    id,
    student_code,
    active_status,
    <-is_student<-person[0].full_name AS full_name,
    ->has_enrollment->enrollment[0]->in_class->class_group[0].class_name AS class_name
FROM student
WHERE count(->has_parent->parent) = 0
ORDER BY class_name, full_name;
```

### Attendance records missing student traversal

Expected: `0`.

```surql
SELECT count() AS missing_student_relation
FROM attendance_event
WHERE count(->for_student->student) = 0
GROUP ALL;
```

### Attendance records missing session traversal

Expected: `0`.

```surql
SELECT count() AS missing_session_relation
FROM attendance_event
WHERE count(->for_session->class_session) = 0
GROUP ALL;
```

### Duplicate parent links by relationship type

Expected: empty if the relation unique index is working.

```surql
SELECT
    in,
    out,
    relationship_type,
    count() AS total
FROM has_parent
GROUP BY in, out, relationship_type
HAVING total > 1;
```

## Operational Queries

### Last migration run

```surql
SELECT *
FROM ingest_run
ORDER BY started_at DESC
LIMIT 1;
```

### Source files

```surql
SELECT
    source_file_code,
    file_name,
    source_domain,
    loaded_at
FROM source_file
ORDER BY source_domain;
```

### Table shape check

Use this to confirm relation tables are real graph tables, not accidental schemaless normal tables.

```surql
INFO FOR DB;
```

In the `tables` output, relation tables should include text like:

```text
DEFINE TABLE has_enrollment TYPE RELATION IN student OUT enrollment ENFORCED SCHEMAFULL
DEFINE TABLE for_student TYPE RELATION IN attendance_event OUT student ENFORCED SCHEMAFULL
```
