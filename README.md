# SmartSlot API

Shared EV charging slot coordination API

---

## 🔍 Context

Corporate parking lots are undergoing electrification, but charging stations remain limited. This creates new coordination challenges for employees, such as avoiding usage conflicts or incomplete charging sessions.

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
* Demonstrate understanding of business concerns (fairness, coordination, resource limitation)
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
| ------ | --------------------------- | ------------------------------ |
| POST   | /charging-requests          | Create a new charging request  |
| GET    | /charging-requests/pending  | View pending requests          |
| POST   | /charging-requests/{id}/end | End an active charging session |

---

## 🔧 Technical Choices

* Laravel 12 (latest stable)
* Modular structure inspired by Clean Architecture, **without dogma**
* Business logic encapsulated in **explicit services**
* Occasional use of **Value Objects** to formalize domain concepts (e.g. `BatteryPercentage`)
* Clear and tested HTTP layer
* No external APIs or third-party services

---

## 🔍 Rationale

This project serves as a showcase of:

* my ability to structure a simple problem with future growth in mind
* my skill in isolating business rules from HTTP transport
* my preference for clarity, readability, and testable code

Rather than over-engineering, the goal is to show what a **clean, simple, yet extensible base** could look like for a future service.

---

## 🛠️ Running the Project

```bash
# Installation
composer install
cp .env.example .env
php artisan key:generate

# Run the tests
php artisan test
```

---

## 🌐 API Testing

Postman or cURL examples will be added progressively.

---

## 🏛️ Future Ideas (if product evolves)

* Priority system based on departure time or battery percentage
* Notifications when a slot becomes free (SSE / Broadcast)
* Simulated slot sensor (connected/disconnected state)
* Admin Dashboard: usage statistics
* Emissions saved / energy consumed tracking

---

## ✅ Status

> In progress. First MVP targeted for delivery by the evening of May 22.
