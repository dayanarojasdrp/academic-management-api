# Academic Management API

API REST en Laravel para una aplicacion de gestion academica de una institucion superior.

## Modulos iniciales

- Estudiantes: codigo unico, nombres, apellidos, carnet/pasaporte, estado, grupo y matricula actual.
- Carreras: nombre, abreviatura y descripcion.
- Cursos: nombre, fechas de inicio/fin y estado.
- Matriculas: estudiante, curso de inicio, curso de fin opcional, fecha y estado.
- Finanzas: estudiante, matricula, monto, moneda, concepto, fechas y estado.
- Profesores: codigo unico, nombres, apellidos y asignatura principal.
- Planes de estudio: carrera, version, duracion y asignaturas requeridas por semestre.
- Grupos: curso, carrera, nombre, turno y estado.
- Calificaciones: estudiante, asignatura, profesor, valor, tipo de evaluacion y estado.
- Asignaturas: tabla de apoyo para profesores, planes de estudio y calificaciones.

## Instalacion

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

La API queda disponible en:

```text
http://127.0.0.1:8000/api
```

## Endpoints

Todos los recursos tienen operaciones REST: `GET`, `POST`, `GET /{id}`, `PUT/PATCH /{id}` y `DELETE /{id}`.

```text
/api/careers
/api/courses
/api/subjects
/api/curriculum-plans
/api/groups
/api/students
/api/enrollments
/api/finances
/api/professors
/api/subject-enrollments
/api/grades
```

La guia completa para frontend esta en [docs/API.md](docs/API.md).
Las decisiones de arquitectura y rendimiento estan en [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md).
La autenticacion, roles y permisos estan en [docs/AUTHORIZATION.md](docs/AUTHORIZATION.md).
El flujo financiero de produccion esta en [docs/FINANCE.md](docs/FINANCE.md).

## Ejemplo rapido

```bash
curl http://127.0.0.1:8000/api/students
```

```bash
curl -X POST http://127.0.0.1:8000/api/careers \
  -H "Content-Type: application/json" \
  -d '{"name":"Medicina","abbreviation":"MED","description":"Carrera de ciencias medicas"}'
```

## Nota de arquitectura

La primera version mantiene controladores REST simples, modelos Eloquent con relaciones y una migracion de dominio clara. Eso permite crecer luego hacia autenticacion, roles, servicios de negocio, filtros avanzados, auditoria, historiales academicos y reportes sin rehacer la base.
