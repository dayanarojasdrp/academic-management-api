# Institutional Structure

The API now supports a multi-structure academic model instead of assuming a single institution.

## Hierarchy

```text
Institution
  Campus
  Faculty
    Department
      Career / Program
  Modality
```

The structure is intentionally flexible:

- a faculty can belong to one campus or be institution-wide;
- a department can belong to a faculty and optionally a campus;
- modalities can be institutional or global;
- careers, groups, professors and subject offerings can all be segmented by organizational ids.

## Main Endpoints

```text
/api/institutions
/api/campuses
/api/faculties
/api/departments
/api/modalities
```

Read access uses `catalogs.view` or `catalogs.manage`.
Write access uses `catalogs.manage`.

## Where The Fields Are Used

Careers:

```json
{
  "institution_id": 1,
  "faculty_id": 1,
  "department_id": 1,
  "modality_id": 1
}
```

Courses:

```json
{
  "institution_id": 1,
  "campus_id": 1
}
```

Groups:

```json
{
  "institution_id": 1,
  "campus_id": 1,
  "faculty_id": 1,
  "department_id": 1,
  "modality_id": 1
}
```

Professors:

```json
{
  "institution_id": 1,
  "campus_id": 1,
  "faculty_id": 1,
  "department_id": 1
}
```

Subject offerings:

```json
{
  "institution_id": 1,
  "campus_id": 1,
  "faculty_id": 1,
  "department_id": 1,
  "modality_id": 1
}
```

## Why This Matters

Latin American academic platforms commonly need segmentation by:

- institution or client;
- sede/campus;
- facultad;
- escuela/departamento;
- programa/carrera;
- turno;
- modalidad presencial, semipresencial, virtual or hybrid.

This design keeps those dimensions normalized so reporting, permissions, billing and academic planning can later filter by the same institutional keys.
