# SmartSlot API

Shared EV charging slot coordination API

---

## 🔍 Context

Corporate parking lots are undergoing electrification, but charging stations remain limited. This creates new
coordination challenges for employees: usage conflicts, fair slot allocation, charging time constraints.

**SmartSlot** is a backend demonstrator that models the business logic involved in this context:

* managing charging intents,
* assigning available slots,
* queueing and releasing usage,
* enforcing fair usage principles.

No IoT integration or frontend is expected at this stage: only the REST backend is in scope.

---

## 🔊 Technical Objectives

* Deliver a clear, tested, and documented REST API
* Structure the project in a modular and scalable way
* Demonstrate understanding of business constraints (resource contention, fair scheduling, slot reuse logic)
* Justify implementation and architectural decisions

---

## 🧳 Minimum Scope (MVP)

### Models

* `ChargingRequest`: a user's expressed intent to charge
* `ChargingSlot`: a charging station (mocked, not physically connected)

### Business Rules

* A user can only have **one active request** at a time
* A request is automatically assigned to a free slot if available
* Otherwise, it enters a **queue** (FIFO)
* Ending a request frees up the slot for the next user

### Endpoints

| Method | URI                         | Description                    |
|--------|-----------------------------|--------------------------------|
| POST   | /charging-requests          | Create a new charging request  |
| GET    | /charging-requests/pending  | View pending requests          |
| POST   | /charging-requests/{id}/end | End an active charging session |

> ⚠️ **Note:** the HTTP integration layer is only partially completed. Core business logic is fully implemented and
> tested, but not all endpoints are wired yet.

---

## 🔧 Technical Choices

* Laravel 12 (latest stable)
* Modular structure inspired by Clean Architecture, **without dogma**
* Business logic encapsulated in **explicit services**
* Business logic injected via contracts and tested with Pest
* Extensive use of enum casting and VO to protect invariants
* Dynamic resolution of availability rules (see `SlotAvailability`)
* Occasional use of **Value Objects** to formalize domain concepts (e.g. `BatteryPercentage`)
* Clear and tested HTTP layer
* No external APIs or third-party services

---

## 🔍 Rationale

This project serves as a showcase of:

* my ability to structure a simple problem with future growth in mind
* my skill in isolating business rules from HTTP transport
* my preference for clarity, readability, and testable code

Rather than over-engineering, the goal is to show what a **clean, simple, yet extensible base** could look like for a
future service.

The logic is intentionally strict and test-driven: every rule has a purpose and is covered by dedicated scenarios.

---

## 🧭 Philosophy

This project deliberately favors code that’s explicit, testable, and aligned with business intent.

It's not meant to be "clever", but rather maintainable—even if extended by someone else in six months.

If any choices seem unusual or more structured than expected for such a scope, I’d be happy to discuss tradeoffs during
a review.

---

## 🛠️ Running the Project

```bash
# Installation
composer install
cp .env.example .env
php artisan key:generate

# Run database migrations
php artisan migrate

# Run the tests
php artisan test
```

---

## 🌐 API Testing

Postman or cURL examples will be added progressively.

> A small global middleware was added to ensure API routes always respond with JSON, even if the client forgets the
> appropriate headers. This avoids accidental HTML responses during testing or debugging.

---

## 🏛️ Future Ideas (if product evolves)

* Priority system based on departure time or battery percentage
* Notifications when a slot becomes free (SSE / Broadcast)
* Simulated slot sensor (connected/disconnected state)
* Admin Dashboard: usage statistics
* Emissions saved / energy consumed tracking

---

## ✅ Status

> First MVP delivered on May 22, covering all use cases, edge cases, and tested in-memory without external services.
> HTTP integration layer still in progress.

---

## 🧑‍💻 Dev Notes

### 🔒 Why `$stopOnFirstFailure = true` is not used in `Login` FormRequest

By default, Laravel allows you to short-circuit validation using `$stopOnFirstFailure = true`.
This improves performance in some cases and can simplify user feedback.

However, in this project, the `Login` FormRequest is tested via multiple assertions (using
`assertJsonValidationErrors([...])`).
Enabling `$stopOnFirstFailure` would prevent the detection of later validation failures in the same test.

> 💡 For clarity and completeness, `$stopOnFirstFailure` is intentionally **not used** in `Login` FormRequest to allow
> full validation coverage in tests.

---

### 🔐 Why no user registration is included

This module assumes that user provisioning is handled externally (e.g. admin creation, SSO, or internal processes).
As such, only token-based authentication is implemented here to expose a secured API.

The login flow was added intentionally to demonstrate:

* the ability to work with Laravel Sanctum (API token flow)
* a secure, testable authentication pipeline (FormRequest, validation, exception handling)
* and a complete end-to-end flow consumable via Postman or frontend clients

The authentication layer is self-contained and remains optional.
It can be bypassed with `actingAs()` in tests, or replaced by an external identity provider (IdP) in a real-world setup.

---

### 🔄 Token overwrite strategy

Each time a user logs in with valid credentials, any existing token matching the same `device_name` is deleted before
issuing a new one.

This choice ensures:

* consistent single-session behavior per device,
* no token accumulation in `personal_access_tokens`,
* minimal write overhead per login,
* a clean authentication lifecycle without storing stale tokens.

This decision favors clarity and simplicity over persistence or refresh mechanisms, which are considered out of scope
for this technical test.

---

### 🧠 Why business logic is decoupled from HTTP and storage

The charging logic is encapsulated in dedicated use cases and injected via contracts.
Repositories are interface-based, and availability is computed at runtime using a dynamic rule object.
This separation improves testability and makes the core logic portable across delivery modes (API, CLI, queue).
