# LunaOS Project API Documentation

Complete API reference for the LunaOS Project Management endpoints.

## Table of Contents

- [Authentication](#authentication)
- [Base URL](#base-url)
- [Response Format](#response-format)
- [Pagination](#pagination)
- [Error Handling](#error-handling)
- [Rate Limiting](#rate-limiting)
- [Endpoints](#endpoints)
  - [List Projects](#list-projects)
  - [Get Project Statistics](#get-project-statistics)
  - [Get Filter Options](#get-filter-options)
  - [Create Project](#create-project)
  - [Get Project](#get-project)
  - [Update Project](#update-project)
  - [Archive Project](#archive-project)
  - [Restore Project](#restore-project)
  - [Permanently Delete Project](#permanently-delete-project)
  - [Assign Agent to Project](#assign-agent-to-project)
  - [Remove Agent from Project](#remove-agent-from-project)

---

## Authentication

The API uses HTTP Basic authentication.

```
Username: kyle
Password: changeme
```

```bash
# Example with curl
curl -u kyle:changeme http://lunaos.test/api/projects
```

---

## Base URL

All API endpoints are prefixed with `/api`:

```
http://lunaos.test/api
```

---

## Response Format

All responses follow a consistent JSON format:

### Success Response

```json
{
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "name": "LunaOS Main",
    "description": "Main project repository",
    "status": "active",
    "health": "healthy",
    "...": "..."
  }
}
```

### Paginated Response

```json
{
  "data": [...],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 15,
    "total": 42,
    "from": 1,
    "to": 15
  },
  "links": {
    "first": "http://lunaos.test/api/projects?page=1",
    "last": "http://lunaos.test/api/projects?page=3",
    "prev": null,
    "next": "http://lunaos.test/api/projects?page=2"
  }
}
```

---

## Pagination

List endpoints support pagination:

| Parameter | Type | Default | Max | Description |
|-----------|------|---------|-----|-------------|
| `per_page` | int | 15 | 100 | Items per page |
| `page` | int | 1 | - | Page number |

**Example:**

```bash
curl "http://lunaos.test/api/projects?per_page=25&page=2"
```

---

## Error Handling

### HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | OK - Request succeeded |
| 201 | Created - Resource created |
| 204 | No Content - Resource deleted |
| 400 | Bad Request - Invalid parameters |
| 401 | Unauthorized - Authentication required |
| 404 | Not Found - Resource not found |
| 409 | Conflict - Resource already exists |
| 422 | Unprocessable Entity - Validation error |
| 500 | Internal Server Error |

### Validation Error Response

```json
{
  "message": "The name field is required.",
  "errors": {
    "name": ["The name field is required."],
    "status": ["The selected status is invalid."]
  }
}
```

---

## Rate Limiting

Rate limits apply to all API endpoints:

| User Type | Limit |
|-----------|-------|
| Authenticated | 60 requests/minute |
| Unauthenticated | 30 requests/minute |

Rate limit headers are included in responses:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
```

---

## Endpoints

### List Projects

Get a paginated list of projects with optional filtering and sorting.

```http
GET /api/projects
```

#### Query Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `status` | string | Filter by status (comma-separated). Values: `planning`, `active`, `completed`, `archived` |
| `health` | string | Filter by health. Values: `healthy`, `at_risk`, `blocked` |
| `architecture_type` | string | Filter by architecture type |
| `repository_id` | string | Filter by repository UUID |
| `project_manager_id` | string | Filter by project manager UUID |
| `search` | string | Search by name or description |
| `created_after` | date | Filter projects created after date (YYYY-MM-DD) |
| `created_before` | date | Filter projects created before date (YYYY-MM-DD) |
| `sort` | string | Sort field. Values: `id`, `name`, `status`, `health`, `progress`, `created_at`, `updated_at` |
| `direction` | string | Sort direction. Values: `asc`, `desc` |
| `per_page` | int | Items per page (default: 15, max: 100) |
| `page` | int | Page number (default: 1) |
| `with_trashed` | bool | Include soft-deleted projects |

#### Examples

```bash
# List all projects
curl "http://lunaos.test/api/projects"

# Filter by status
curl "http://lunaos.test/api/projects?status=active"

# Filter multiple statuses
curl "http://lunaos.test/api/projects?status=active,planning"

# Search by name
curl "http://lunaos.test/api/projects?search=LunaOS"

# Sort by progress ascending
curl "http://lunaos.test/api/projects?sort=progress&direction=asc"

# Include archived projects
curl "http://lunaos.test/api/projects?with_trashed=1"

# Combined filters
curl "http://lunaos.test/api/projects?status=active&health=healthy&sort=name&direction=asc&per_page=50"
```

#### Response

```json
{
  "data": [
    {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "name": "LunaOS Main",
      "description": "Main project repository",
      "repo_url": "https://github.com/kjbear/lunaos",
      "status": "active",
      "health": "healthy",
      "progress": 75,
      "percent_complete": 75.50,
      "architecture_type": "microservices",
      "technologies": ["Laravel", "Vue", "PostgreSQL"],
      "repository_id": null,
      "project_manager_id": null,
      "created_at": "2026-03-01T10:00:00Z",
      "updated_at": "2026-03-07T15:30:00Z",
      "deleted_at": null
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 1,
    "from": 1,
    "to": 1
  },
  "links": {
    "first": "http://lunaos.test/api/projects?page=1",
    "last": "http://lunaos.test/api/projects?page=1",
    "prev": null,
    "next": null
  }
}
```

---

### Get Project Statistics

Get project counts grouped by status and health.

```http
GET /api/projects/stats
```

#### Example

```bash
curl "http://lunaos.test/api/projects/stats"
```

#### Response

```json
{
  "data": {
    "total": 10,
    "by_status": {
      "planning": 2,
      "active": 5,
      "completed": 2,
      "archived": 1
    },
    "by_health": {
      "healthy": 7,
      "at_risk": 2,
      "blocked": 1
    },
    "trashed": 3
  }
}
```

---

### Get Filter Options

Get available filter options for projects.

```http
GET /api/projects/filters
```

#### Example

```bash
curl "http://lunaos.test/api/projects/filters"
```

#### Response

```json
{
  "data": {
    "statuses": ["planning", "active", "completed", "archived"],
    "health_states": ["healthy", "at_risk", "blocked"],
    "architecture_types": ["monolith", "microservices", "serverless", "hybrid"]
  }
}
```

---

### Create Project

Create a new project.

```http
POST /api/projects
```

#### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | string | Yes | Project name (max: 255) |
| `description` | string | No | Project description |
| `repo_url` | url | No | Repository URL (max: 2048) |
| `repository_id` | uuid | No | Repository UUID |
| `status` | enum | No | Status. Values: `planning` (default), `active`, `completed`, `archived` |
| `health` | enum | No | Health. Values: `healthy` (default), `at_risk`, `blocked` |
| `architecture_type` | string | No | Architecture type (max: 100) |
| `technologies` | array | No | Technology stack |
| `project_manager_id` | uuid | No | Project manager agent UUID |
| `progress` | int | No | Progress percentage (0-100) |

#### Examples

```bash
# Minimal project
curl -X POST "http://lunaos.test/api/projects" \
  -H "Content-Type: application/json" \
  -d '{"name": "My Project"}'

# Full project details
curl -X POST "http://lunaos.test/api/projects" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "E-Commerce Platform",
    "description": "A modern e-commerce platform built with Laravel and Vue",
    "repo_url": "https://github.com/company/ecommerce",
    "status": "planning",
    "health": "healthy",
    "architecture_type": "microservices",
    "technologies": ["Laravel", "Vue", "PostgreSQL", "Redis"],
    "progress": 0
  }'
```

#### Response (201 Created)

```json
{
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "name": "E-Commerce Platform",
    "description": "A modern e-commerce platform built with Laravel and Vue",
    "repo_url": "https://github.com/company/ecommerce",
    "status": "planning",
    "health": "healthy",
    "progress": 0,
    "percent_complete": 0,
    "architecture_type": "microservices",
    "technologies": ["Laravel", "Vue", "PostgreSQL", "Redis"],
    "...": "..."
  }
}
```

---

### Get Project

Get a single project by ID with relationships.

```http
GET /api/projects/{project_id}
```

#### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `project_id` | uuid | Project UUID |

#### Example

```bash
curl "http://lunaos.test/api/projects/550e8400-e29b-41d4-a716-446655440000"
```

#### Response

```json
{
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "name": "LunaOS Main",
    "description": "Main project repository",
    "status": "active",
    "health": "healthy",
    "progress": 75,
    "...": "...",
    "repository": { ... },
    "project_manager": { ... },
    "agents": [
      {
        "agent": {
          "id": "...",
          "name": "dev-agent",
          "role": "developer"
        },
        "role": "developer"
      }
    ],
    "tasks": [ ... ],
    "issues": [ ... ]
  }
}
```

---

### Update Project

Update a project's details.

```http
PUT /api/projects/{project_id}
```

#### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `project_id` | uuid | Project UUID |

#### Request Body

All fields are optional. Only include fields you want to update.

| Field | Type | Description |
|-------|------|-------------|
| `name` | string | Project name (required if provided) |
| `description` | string | Project description |
| `repo_url` | url | Repository URL |
| `status` | enum | Status. Values: `planning`, `active`, `completed`, `archived` |
| `health` | enum | Health. Values: `healthy`, `at_risk`, `blocked` |
| `architecture_type` | string | Architecture type |
| `technologies` | array | Technology stack |
| `project_manager_id` | uuid | Project manager agent UUID |
| `progress` | int | Progress percentage (0-100) |

#### Examples

```bash
# Update status
curl -X PUT "http://lunaos.test/api/projects/550e8400-e29b-41d4-a716-446655440000" \
  -H "Content-Type: application/json" \
  -d '{"status": "active", "health": "healthy"}'

# Update progress
curl -X PUT "http://lunaos.test/api/projects/550e8400-e29b-41d4-a716-446655440000" \
  -H "Content-Type: application/json" \
  -d '{"progress": 75}'

# Update technologies
curl -X PUT "http://lunaos.test/api/projects/550e8400-e29b-41d4-a716-446655440000" \
  -H "Content-Type: application/json" \
  -d '{"technologies": ["Laravel", "Vue", "PostgreSQL", "Redis", "Docker"]}'
```

#### Response

```json
{
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "name": "LunaOS Main",
    "status": "active",
    "health": "healthy",
    "...": "..."
  }
}
```

---

### Archive Project

Soft delete (archive) a project. Cascades to tasks and agent assignments.

```http
DELETE /api/projects/{project_id}
```

#### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `project_id` | uuid | Project UUID |

#### Example

```bash
curl -X DELETE "http://lunaos.test/api/projects/550e8400-e29b-41d4-a716-446655440000"
```

#### Response

```json
{
  "message": "Project archived successfully",
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "deleted_at": "2026-03-07T16:00:00Z"
  }
}
```

---

### Restore Project

Restore a soft-deleted project.

```http
POST /api/projects/{project_id}/restore
```

#### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `project_id` | uuid | Project UUID |

#### Example

```bash
curl -X POST "http://lunaos.test/api/projects/550e8400-e29b-41d4-a716-446655440000/restore"
```

#### Response

```json
{
  "message": "Project restored successfully",
  "data": { ... }
}
```

---

### Permanently Delete Project

Permanently delete a project. **Cannot be undone.**

```http
DELETE /api/projects/{project_id}/force
```

#### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `project_id` | uuid | Project UUID |

#### Example

```bash
curl -X DELETE "http://lunaos.test/api/projects/550e8400-e29b-41d4-a716-446655440000/force"
```

#### Response

```
HTTP 204 No Content
```

---

### Assign Agent to Project

Assign an AI agent to a project with a specific role.

```http
POST /api/projects/{project_id}/agents
```

#### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `project_id` | uuid | Project UUID |

#### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `agent_id` | uuid | Yes | Agent UUID |
| `role` | enum | Yes | Role. Values: `project_manager`, `architect`, `developer`, `qa`, `reviewer` |

#### Example

```bash
curl -X POST "http://lunaos.test/api/projects/550e8400-e29b-41d4-a716-446655440000/agents" \
  -H "Content-Type: application/json" \
  -d '{
    "agent_id": "agent-uuid-here",
    "role": "developer"
  }'
```

#### Response (201 Created)

```json
{
  "message": "Agent assigned successfully",
  "data": {
    "project_id": "550e8400-e29b-41d4-a716-446655440000",
    "agent_id": "agent-uuid-here",
    "role": "developer",
    "assigned_at": "2026-03-07T10:00:00Z",
    "agent": {
      "id": "agent-uuid-here",
      "name": "dev-agent",
      "role": "developer",
      "status": "online"
    }
  }
}
```

---

### Remove Agent from Project

Remove an agent assignment from a project.

```http
DELETE /api/projects/{project_id}/agents/{agent_id}
```

#### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `project_id` | uuid | Project UUID |
| `agent_id` | uuid | Agent UUID |

#### Example

```bash
curl -X DELETE "http://lunaos.test/api/projects/550e8400-e29b-41d4-a716-446655440000/agents/agent-uuid-here"
```

#### Response

```
HTTP 204 No Content
```

---

## Postman Collection

Import the Postman collection from:

```
docs/api/lunaos-api.postman_collection.json
```

## OpenAPI Specification

The full OpenAPI 3.0.3 specification is available at:

```
docs/api/openapi.yaml
```

## Testing

Run the integration test suite:

```bash
php artisan test --filter=ProjectApiIntegrationTest
```

## Changelog

### v1.0.0 (2026-03-07)

- Initial release
- Full CRUD operations for projects
- Statistics endpoint
- Filter options endpoint
- Agent assignment management
- Soft delete with cascade
- Restore functionality
- Permanent delete