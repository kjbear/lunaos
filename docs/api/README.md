# Task Management API Documentation

Unified REST API for task management in LunaOS, supporting multiple view modes (list, board, executive).

---

## Quick Start

**Base URL:** `/api`  
**Version:** 1.0.0  
**Authentication:** Currently disabled for internal use (optional API key in `Authorization` header)

### Available Formats

- **OpenAPI 3.0 YAML:** `TASKS-API-OPENAPI.yaml`
- **OpenAPI 3.0 JSON:** `TASKS-API-OPENAPI.json`
- **Postman Collection:** `TASKS-API.postman_collection.json`

---

## Endpoints

### Core CRUD Operations

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/tasks` | List all tasks (paginated, with filters) |
| POST | `/tasks` | Create a new task |
| GET | `/tasks/{id}` | Get a single task |
| PUT | `/tasks/{id}` | Update a task |
| DELETE | `/tasks/{id}` | Delete a task |

### View-Specific Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/tasks/view-modes/{mode}` | Get tasks for a specific view (list/board/executive) |

### Statistics & Filters

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/tasks/stats` | Get aggregated task statistics |
| GET | `/tasks/filters` | Get available filter options |

### Bulk Operations

| Method | Endpoint | Description |
|--------|----------|-------------|
| PUT | `/tasks/bulk` | Update multiple tasks at once |
| DELETE | `/tasks/bulk` | Delete multiple tasks at once |

---

## Query Parameters (List Endpoint)

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `status` | string | Comma-separated statuses | `pending,in_progress` |
| `assigned_to` | string | Filter by agent name | `dave` |
| `priority` | string | Comma-separated priorities | `high,critical` |
| `task_type` | string | Filter by type | `feature` |
| `step` | string | Filter by workflow step | `develop` |
| `view_mode` | string | Filter by view mode | `board` |
| `repository_id` | integer | Filter by repo ID | `5` |
| `created_after` | date | ISO 8601 date | `2026-03-01` |
| `created_before` | date | ISO 8601 date | `2026-03-31` |
| `search` | string | Search title/description | `authentication` |
| `sort` | string | Sort field | `created_at` |
| `direction` | string | Sort direction | `asc`, `desc` |
| `per_page` | integer | Items per page (max 100) | `20` |
| `page` | integer | Page number | `1` |

**Allowed sort fields:** `id`, `title`, `created_at`, `updated_at`, `started_at`, `completed_at`, `status`, `priority`, `step`, `task_type`

---

## Enumerated Values

### Status
- `pending` - Waiting to be started
- `in_progress` - Actively being worked on
- `complete` - Completed
- `failed` - Failed during execution
- `blocked` - Blocked by external dependency

### Priority
- `low` - Can be deferred
- `medium` - Normal priority (default)
- `high` - Should be addressed soon
- `critical` - Immediate attention required

### Task Type
- `feature` - New feature development
- `bug` - Bug fix
- `chore` - Maintenance task
- `hotfix` - Urgent production fix
- `refactor` - Code refactoring

### Workflow Step
- `develop` - Development phase
- `qa` - Quality assurance testing
- `security` - Security review
- `staging` - Staging deployment
- `production` - Production deployment

### View Mode
- `list` - Traditional list view
- `board` - Kanban board view
- `executive` - Executive/strategic view

---

## Request/Response Examples

### Create Task

**Request:**
```bash
curl -X POST http://localhost:8000/api/tasks \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Fix login bug",
    "description": "Users cannot login with OAuth",
    "assigned_to": "dave",
    "priority": "critical",
    "task_type": "bug",
    "view_mode": "board"
  }'
```

**Response (201 Created):**
```json
{
  "data": {
    "id": 42,
    "title": "Fix login bug",
    "assigned_to": "dave",
    "priority": "critical",
    "task_type": "bug",
    "view_mode": "board",
    "status": "pending",
    "created_at": "2026-03-01T12:00:00Z",
    ...
  }
}
```

### List Tasks with Filters

**Request:**
```bash
curl -X GET "http://localhost:8000/api/tasks?status=pending&priority=high,critical&sort=created_at&direction=desc"
```

**Response (200 OK):**
```json
{
  "data": [...],
  "links": {
    "first": "/api/tasks?page=1",
    "last": "/api/tasks?page=5",
    "prev": null,
    "next": "/api/tasks?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "path": "/api/tasks",
    "per_page": 20,
    "to": 20,
    "total": 100
  }
}
```

---

## Error Handling

All errors follow RFC 7807 pattern:

```json
{
  "message": "Human-readable error message",
  "errors": {
    "field_name": ["Validation error 1", "Validation error 2"]
  }
}
```

### HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | Success (GET/PUT) |
| 201 | Created (POST) |
| 204 | No Content (DELETE) |
| 400 | Bad Request (invalid parameters) |
| 404 | Not Found |
| 422 | Validation Error |
| 500 | Server Error |

---

## Testing with Postman

1. Import `TASKS-API.postman_collection.json` into Postman
2. Set the `baseUrl` variable (default: `http://localhost:8000/api`)
3. Optionally configure API key authentication
4. Use the pre-populated requests to test endpoints

---

## OpenAPI Spec

View the full OpenAPI specification in:
- YAML format: `TASKS-API-OPENAPI.yaml`
- JSON format: `TASKS-API-OPENAPI.json`

You can load these files into:
- Swagger UI
- Stoplight Studio
- Redoc
- Any OpenAPI-compatible tool

---

## Frontend Integration

**Maya (Frontend Team):**

Use the view-specific endpoints for each task view:
- List view: `GET /api/tasks/view-modes/list`
- Board view: `GET /api/tasks/view-modes/board`
- Executive view: `GET /api/tasks/view-modes/executive`

Use `GET /api/tasks/filters` to populate filter dropdowns.
Use `GET /api/tasks/stats` for dashboard widgets.

---

**See also:**
- Phase 2A Task Spec: `../PHASE-2A-TASKS.md`
- Task API Spec: `../TASK-API-SPEC.md`
