# Student Finance and Enrollment Clearance

The production finance flow uses a student account ledger. The old `/api/finances` resource remains for compatibility, but enrollment clearance is now based on charges, adjustments, payments, allocations and financial holds.

## Core concepts

- `financial_concepts`: billable concepts such as enrollment fee, tuition, lab fee, scholarship, discount or penalty.
- `student_charges`: money the student owes for a course, enrollment or concept.
- `financial_adjustments`: scholarships, discounts, waivers or penalties applied to a charge.
- `student_payments`: confirmed payment transactions.
- `payment_allocations`: how a payment is applied to one or more charges.
- `payment_receipts`: receipt metadata for a payment.
- `financial_holds`: financial blocks that prevent enrollment even if some charges are paid.

## Enrollment clearance rule

A student can enroll only when all conditions are true:

1. Every active concept marked `is_required_for_enrollment = true` has a charge for the student/course.
2. Required charges have `balance_amount = 0`.
3. The student has no previous open debt.
4. The student has no active financial hold.

Endpoint:

```text
GET /api/students/{student}/financial-clearance?course_id=1
GET /api/students/{student}/payment-status?course_id=1
```

Example response:

```json
{
  "student_id": 1,
  "course_id": 1,
  "can_enroll": true,
  "has_active_hold": false,
  "missing_required_concepts": [],
  "required_balance": 0,
  "previous_debt": 0,
  "required_charges": []
}
```

## Financial concepts

```text
GET    /api/financial-concepts
POST   /api/financial-concepts
GET    /api/financial-concepts/{financialConcept}
PATCH  /api/financial-concepts/{financialConcept}
DELETE /api/financial-concepts/{financialConcept}
```

Create enrollment fee:

```json
{
  "code": "ENROLLMENT_FEE",
  "name": "Derecho de matricula",
  "type": "enrollment",
  "default_amount": 250,
  "currency": "USD",
  "is_required_for_enrollment": true,
  "is_active": true,
  "description": "Cargo obligatorio para habilitar la matricula"
}
```

Suggested types:

```text
enrollment
tuition
monthly_fee
lab_fee
certificate
scholarship
discount
penalty
other
```

## Student charges

```text
GET    /api/student-charges
POST   /api/student-charges
GET    /api/student-charges/{studentCharge}
PATCH  /api/student-charges/{studentCharge}
DELETE /api/student-charges/{studentCharge}
```

Create charge:

```json
{
  "student_id": 1,
  "course_id": 1,
  "enrollment_id": null,
  "financial_concept_id": 1,
  "original_amount": 250,
  "currency": "USD",
  "issue_date": "2026-08-01",
  "due_date": "2026-08-30",
  "notes": "Matricula curso 2026-2027"
}
```

The backend calculates:

```text
adjustment_amount
paid_amount
balance_amount
status
```

Charge statuses:

```text
pending
partial
paid
overdue
void
```

## Discounts, scholarships and waivers

```text
POST /api/student-charges/{studentCharge}/adjustments
```

Apply scholarship or discount:

```json
{
  "type": "scholarship",
  "amount": 100,
  "status": "approved",
  "reason": "Beca institucional por rendimiento academico"
}
```

Suggested adjustment types:

```text
scholarship
discount
waiver
penalty
manual_correction
```

When approved, the charge balance is recalculated.

## Student payments

```text
GET    /api/student-payments
POST   /api/student-payments
GET    /api/student-payments/{studentPayment}
PATCH  /api/student-payments/{studentPayment}
DELETE /api/student-payments/{studentPayment}
```

Register payment and manually allocate it:

```json
{
  "student_id": 1,
  "amount": 150,
  "currency": "USD",
  "payment_method": "cash",
  "payment_reference": "PAY-2026-0001",
  "paid_at": "2026-08-20",
  "receipt_number": "RCPT-2026-0001",
  "allocations": [
    { "student_charge_id": 1, "amount": 150 }
  ]
}
```

If `allocations` is omitted, the backend auto-applies the payment to the oldest open charges in the same currency.

Payment statuses:

```text
confirmed
cancelled
refunded
reversed
```

## Partial payments

If a charge is 250 and the student pays 100:

```text
paid_amount = 100
balance_amount = 150
status = partial
```

The student is not cleared for enrollment until required charges reach:

```text
balance_amount = 0
```

## Financial holds

```text
GET    /api/financial-holds
POST   /api/financial-holds
GET    /api/financial-holds/{financialHold}
PATCH  /api/financial-holds/{financialHold}
DELETE /api/financial-holds/{financialHold}
```

Create hold:

```json
{
  "student_id": 1,
  "course_id": 1,
  "amount": 150,
  "currency": "USD",
  "reason": "Saldo vencido de matricula",
  "status": "active"
}
```

Release hold:

```json
{
  "status": "released",
  "released_at": "2026-08-29",
  "release_reason": "Pago validado"
}
```

Active holds block enrollment.

## Recommended frontend flow

1. Create required financial concepts.
2. Generate charges for the student/course.
3. Apply scholarships or discounts if needed.
4. Register one or many payments.
5. Let backend allocate the payment or send explicit allocations.
6. Query financial clearance.
7. Create enrollment only if `can_enroll = true`.
