# Task Management API - OpenAPI Specification

**Version:** 1.0.0  
**Base URL:** `/api`  
**Description:** Unified Task Management API supporting multiple view modes (list, board, executive) with full CRUD operations, filtering, pagination, and sorting.

---

## Overview

This API provides a RESTful interface for managing tasks in the LunaOS platform. It supports:

- **CRUD Operations**: Create, read, update, and delete individual tasks
- **View Modes**: Tasks can be associated with different view modes (list, board, executive)
- **Filtering**: Filter tasks by status, priority, assignee, task type, step, dates, and more
- **Sorting**: Sort by various fields with configurable direction
- **Pagination**: Paginated responses with configurable page size
- **Bulk Operations**: Update or delete multiple tasks in a single request
- **Statistics**: Get aggregated statistics about tasks

---

## Authentication

Currently, API endpoints are unprotected for internal use. Authentication may be added in future versions.

---

## Endpoints

### Task Collection

#### List All Tasks

```http
GET /api/tasks
```

**Query Parameters:**

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `status` | string | No | - | Filter by status (comma-separated for multiple: `pending,in_progress`) |
| `assigned_to` | string | No | - | Filter by agent name |
| `priority` | string | No | - | Filter by priority (comma-separated) |
| `task_type` | string | No | - | Filter by task type |
| `step` | string | No | - | Filter by workflow step |
| `view_mode` | string | No | - | Filter by view mode |
| `repository_id` | integer | No | - | Filter by repository ID |
| `created_after` | date | No | - | Filter created after date (ISO 8601) |
| `created_before` | date | No | - | Filter created before date (ISO 8601) |
| `search` | string | No | - | Search in title and description |
| `sort` | string | No | `created_at` | Field to sort by |
| `direction` | string | No | `desc` | Sort direction (`asc` or `desc`) |
| `per_page` | integer | No | `20` | Items per page (max: 100) |
| `page` | integer | No | `1` | Page number |

**Allowed Sort Fields:** `id`, `title`, `created_at`, `updated_at`, `started_at`, `completed_at`, `status`, `priority`, `step`, `task_type`

**Response:** `200 OK`

```json
{
  "data": [
    {
      "id": 1,
      "title": "Implement user authentication",
      "description": "Add login/logout functionality",
      "assigned_to": "dave",
      "repository_id": 5,
      "status": "in_progress",
      "step": "develop",
      "priority": "high",
      "task_type": "feature",
      "view_mode": "board",
      "context": {},
      "branch_name": "feature/auth",
      "pr_url": "https://github.com/example/repo/pull/42",
      "artifacts": [],
      "failure_reason": null,
      "retry_count": 0,
      "started_at": "2026-03-01T10:00:00Z",
      "completed_at": null,
      "created_at": "2026-03-01T09:00:00Z",
      "updated_at": "2026-03-01T10:00:00Z",
      "progress_percentage": 20,
      "priority_badge_class": "bg-orange-500/20 text-orange-400 border-orange-500/30",
      "status_badge_class": "bg-blue-500/20 text-blue-400 border-blue-500/30",
      "agent_display_name": "Dave (Dev)",
      "created_at_human": "2 hours ago",
      "agent": {
        "id": 1,
        "name": "dave",
        "role": "developer",
        "avatar": "/avatars/dave.png",
        "status": "online"
      },
      "repository": {
        "id": 5,
        "name": "example/repo",
        "url": "https://github.com/example/repo",
        "provider": "github"
      },
      "activities": []
    }
  ],
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

#### Get Tasks by View Mode

```http
GET /api/tasks/view-modes/{viewMode}
```

**URL Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `viewMode` | string | Yes | View mode: `list`, `board`, or `executive` |

**Query Parameters:** Same as List All Tasks (excluding `view_mode`)

**Response:** `200 OK` - Same structure as List All Tasks

**Errors:**

- `400 Bad Request` - Invalid view mode

```json
{
  "error": "Invalid view mode",
  "allowed": ["list", "board", "executive"]
}
```

#### Get Task Statistics

```http
GET /api/tasks/stats
```

**Response:** `200 OK`

```json
{
  "total": 150,
  "by_status": {
    "pending": 25,
    "in_progress": 45,
    "complete": 70,
    "failed": 5,
    "blocked": 5
  },
  "by_priority": {
    "low": 30,
    "medium": 80,
    "high": 30,
    "critical": 10
  },
  "by_view_mode": {
    "list": 50,
    "board": 75,
    "executive": 25
  },
  "by_step": {
    "develop": 30,
    "qa": 25,
    "security": 20,
    "staging": 15,
    "production": 60
  },
  "completed_today": 12,
  "active_agents": 5
}
```

#### Get Filter Options

```http
GET /api/tasks/filters
```

**Response:** `200 OK`

```json
{
  "agents": [
    {
      "id": 1,
      "name": "dave",
      "role": "developer",
      "avatar": "/avatars/dave.png"
    },
    {
      "id": 2,
      "name": "sam",
      "role": "qa",
      "avatar": "/avatars/sam.png"
    }
  ],
  "statuses": ["pending", "in_progress", "complete", "failed", "blocked"],
  "priorities": ["low", "medium", "high", "critical"],
  "task_types": ["feature", "bug", "chore", "hotfix", "refactor"],
  "steps": ["develop", "qa", "security", "staging", "production"],
  "view_modes": ["list", "board", "executive"]
}
```

#### Create a Task

```http
POST /api/tasks
```

**Request Body:** `application/json`

```json
{
  "title": "Implement user authentication",
  "description": "Add login/logout functionality with OAuth support",
  "assigned_to": "dave",
  "repository_id": 5,
  "status": "pending",
  "step": "develop",
  "priority": "high",
  "task_type": "feature",
  "view_mode": "board",
  "context_json": {
    "requirements": ["OAuth2", "JWT tokens", "Session management"]
  },
  "branch_name": "feature/auth"
}
```

**Validation Rules:**

| Field | Required | Type | Constraints |
|-------|----------|------|-------------|
| `title` | Yes | string | max: 255 |
| `description` | No | string | - |
| `assigned_to` | No | string | Must exist in agents table (name) |
| `repository_id` | No | integer | Must exist in repositories table |
| `status` | No | string | `pending`, `in_progress`, `complete`, `failed`, `blocked` |
| `step` | No | string | `develop`, `qa`, `security`, `staging`, `production` |
| `priority` | No | string | `low`, `medium`, `high`, `critical` |
| `task_type` | No | string | `feature`, `bug`, `chore`, `hotfix`, `refactor` |
| `view_mode` | No | string | `list`, `board`, `executive` |
| `context_json` | No | array | - |
| `branch_name` | No | string | max: 255 |
| `pr_url` | No | string | Valid URL, max: 2048 |

**Response:** `201 Created`

```json
{
  "data": {
    "id": 42,
    "title": "Implement user authentication",
    "description": "Add login/logout functionality with OAuth support",
    "assigned_to": "dave",
    "repository_id": 5,
    "status": "pending",
    "step": "develop",
    "priority": "high",
    "task_type": "feature",
    "view_mode": "board",
    "context": {
      "requirements": ["OAuth2", "JWT tokens", "Session management"]
    },
    "branch_name": "feature/auth",
    "pr_url": null,
    "artifacts": [],
    "failure_reason": null,
    "retry_count": 0,
    "started_at": null,
    "completed_at": null,
    "created_at": "2026-03-01T12:00:00Z",
    "updated_at": "2026-03-01T12:00:00Z",
    "progress_percentage": 20,
    "priority_badge_class": "bg-orange-500/20 text-orange-400 border-orange-500/30",
    "status_badge_class": "bg-slate-500/20 text-slate-400 border-slate-500/30",
    "agent_display_name": "Dave (Dev)",
    "created_at_human": "just now"
  }
}
```

---

### Single Task Operations

#### Get a Task

```http
GET /api/tasks/{task}
```

**URL Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `task` | integer | Yes | Task ID |

**Response:** `200 OK` - Task resource with full details

**Errors:**

- `404 Not Found` - Task not found

#### Update a Task

```http
PUT /api/tasks/{task}
```

**URL Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `task` | integer | Yes | Task ID |

**Request Body:** `application/json`

Same fields as Create a Task (all optional for update)

**Response:** `200 OK` - Updated task resource

**Errors:**

- `404 Not Found` - Task not found
- `422 Unprocessable Entity` - Validation errors

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "title": ["The title field is required when updating."]
  }
}
```

#### Delete a Task

```http
DELETE /api/tasks/{task}
```

**URL Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `task` | integer | Yes | Task ID |

**Response:** `204 No Content`

**Errors:**

- `404 Not Found` - Task not found

---

### Bulk Operations

#### Bulk Update Tasks

```http
PUT /api/tasks/bulk
```

**Request Body:** `application/json`

```json
{
  "task_ids": [1, 2, 3],
  "updates": {
    "status": "in_progress",
    "assigned_to": "dave"
  }
}
```

**Validation Rules:**

| Field | Required | Type | Constraints |
|-------|----------|------|-------------|
| `task_ids` | Yes | array | min: 1, each item must be valid task ID |
| `updates` | Yes | object | Allowed fields: `status`, `step`, `priority`, `assigned_to`, `view_mode` |

**Response:** `200 OK`

```json
{
  "message": "Tasks updated successfully",
  "updated_count": 3
}
```

**Errors:**

- `422 Unprocessable Entity` - Validation errors

#### Bulk Delete Tasks

```http
DELETE /api/tasks/bulk
```

**Request Body:** `application/json`

```json
{
  "task_ids": [1, 2, 3]
}
```

**Validation Rules:**

| Field | Required | Type | Constraints |
|-------|----------|------|-------------|
| `task_ids` | Yes | array | min: 1, each item must be valid task ID |

**Response:** `200 OK`

```json
{
  "message": "Tasks deleted successfully",
  "deleted_count": 3
}
```

**Errors:**

- `422 Unprocessable Entity` - Validation errors

---

## Data Models

### Task Resource

```json
{
  "id": "integer",
  "title": "string",
  "description": "string|null",
  "assigned_to": "string|null",
  "repository_id": "integer|null",
  "status": "string",
  "step": "string",
  "priority": "string",
  "task_type": "string",
  "view_mode": "string",
  "context": "object|null",
  "branch_name": "string|null",
  "pr_url": "string|null",
  "artifacts": "array",
  "failure_reason": "string|null",
  "retry_count": "integer",
  "started_at": "datetime|null",
  "completed_at": "datetime|null",
  "created_at": "datetime",
  "updated_at": "datetime",
  "progress_percentage": "integer",
  "priority_badge_class": "string",
  "status_badge_class": "string",
  "agent_display_name": "string",
  "created_at_human": "string",
  "agent": "AgentResource|null",
  "repository": "RepositoryResource|null",
  "activities": "array of AgentActivityResource"
}
```

### Enumerated Values

**Status:**
- `pending` - Task is waiting to be started
- `in_progress` - Task is actively being worked on
- `complete` - Task has been completed
- `failed` - Task failed during execution
- `blocked` - Task is blocked by an external dependency

**Priority:**
- `low` - Low priority, can be deferred
- `medium` - Normal priority
- `high` - High priority, should be addressed soon
- `critical` - Critical priority, immediate attention required

**Task Type:**
- `feature` - New feature development
- `bug` - Bug fix
- `chore` - Maintenance task
- `hotfix` - Urgent production fix
- `refactor` - Code refactoring

**Step (Workflow Stage):**
- `develop` - Development phase
- `qa` - Quality assurance testing
- `security` - Security review
- `staging` - Staging deployment
- `production` - Production deployment

**View Mode:**
- `list` - Traditional list view
- `board` - Kanban board view
- `executive` - Executive/strategic view

---

## Error Handling

All errors follow RFC 7807 (Problem Details for HTTP APIs) pattern:

```json
{
  "message": "Human-readable error message",
  "errors": {
    "field_name": ["Validation error 1", "Validation error 2"]
  }
}
```

### HTTP Status Codes

| Code | Meaning | Usage |
|------|---------|-------|
| 200 | OK | Successful GET/PUT request |
| 201 | Created | Successful POST request |
| 204 | No Content | Successful DELETE request |
| 400 | Bad Request | Invalid parameters (e.g., invalid view mode) |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable Entity | Validation errors |
| 500 | Internal Server Error | Server error |

---

## Rate Limiting

Currently, no rate limiting is implemented. This may be added in future versions.

---

## Versioning

API version is included in the specification version. Breaking changes will result in a major version bump.

---

## Examples

### cURL Examples

#### List tasks with filtering

```bash
curl -X GET "http://localhost:8000/api/tasks?status=pending&priority=high,critical&sort=created_at&direction=desc"
```

#### Get board view tasks

```bash
curl -X GET "http://localhost:8000/api/tasks/view-modes/board"
```

#### Create a task

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

#### Bulk update tasks

```bash
curl -X PUT http://localhost:8000/api/tasks/bulk \
  -H "Content-Type: application/json" \
  -d '{
    "task_ids": [1, 2, 3],
    "updates": {
      "status": "complete"
    }
  }'
```

#### Get statistics

```bash
curl -X GET "http://localhost:8000/api/tasks/stats"
```

---

## Frontend Integration Notes

**Maya (Frontend):**
- Use `view-modes` endpoint to fetch tasks for specific views
- List view: `/api/tasks/view-modes/list`
- Board view: `/api/tasks/view-modes/board`
- Executive view: `/api/tasks/view-modes/executive`
- Use `filters` endpoint to populate filter dropdowns
- Use `stats` endpoint for dashboard widgets
 paginate
