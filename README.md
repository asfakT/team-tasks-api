# Team Tasks API

An API-only Laravel application where users create teams and manage tasks inside them. There is no browser UI. All endpoints are tested and documented with Postman.

This is a learning project for Laravel API development: Sanctum authentication, custom policies, the action pattern, API resources and form requests.

## Tech Stack

- PHP 8.2+
- Laravel 12
- Laravel Sanctum (API tokens)
- MySQL (development), SQLite in memory (tests)
- Postman (testing and documentation)

## Features

- Register, login, logout and current user with Sanctum tokens
- Teams: create, list, show, update, delete
- The user who creates a team becomes its owner
- Owner can add or remove team members
- Tasks inside a team with title, description, status, assignee and due date
- Task status: `todo`, `doing`, `done`
- Only the assignee can mark a task as `done`
- Paginated list endpoints
- Rate limiting on register and login (6 requests per minute)
- Tokens expire after 7 days

## Authorization Rules

| Action | Who can do it |
|---|---|
| View a team and its tasks | Team members only |
| Update or delete a team | Owner only |
| Add or remove members | Owner only |
| Create a task | Any team member |
| Edit or delete a task | Task creator or team owner |
| Mark a task as done | Assignee only |

## How It Is Built

| Part | Where | Purpose |
|---|---|---|
| Policies | `app/Policies` | `TeamPolicy` and `TaskPolicy`, registered by hand with `Gate::policy()` in `AppServiceProvider`. Custom methods: `manageMembers()`, `complete()` |
| Actions | `app/Actions` | One class per write: `CreateTeamAction`, `AddMemberAction`, `RemoveMemberAction`, `CreateTaskAction`, `CompleteTaskAction`, `RegisterUserAction`, `LoginUserAction` |
| Form Requests | `app/Http/Requests` | Validation only |
| API Resources | `app/Http/Resources` | `UserResource`, `TeamResource`, `TaskResource`. Models are never returned directly |
| Controllers | `app/Http/Controllers/Api` | Thin: validate, authorize, call the action, return the resource |
| Enum | `app/Enums/TaskStatus.php` | Allowed task status values |

## Installation

```bash
git clone https://github.com/asfakT/team-tasks-api.git
cd team-tasks-api
composer install
cp .env.example .env
php artisan key:generate
```

Create a MySQL database named `team_tasks`, then update `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=team_tasks
DB_USERNAME=root
DB_PASSWORD=
```

Run the migrations and start the server:

```bash
php artisan migrate
php artisan serve
```

The API is available at `http://127.0.0.1:8000/api/v1`.

## API Endpoints

All endpoints use the prefix `/api/v1`. Send the header `Accept: application/json` with every request. Protected endpoints also need `Authorization: Bearer {token}`.

### Auth

| Method | Endpoint | Access | Description |
|---|---|---|---|
| POST | `/register` | Public | Create an account and get a token |
| POST | `/login` | Public | Log in and get a token |
| GET | `/me` | Token | Current user |
| POST | `/logout` | Token | Delete the current token |

### Teams

| Method | Endpoint | Access | Description |
|---|---|---|---|
| GET | `/teams` | Token | List my teams (paginated) |
| POST | `/teams` | Token | Create a team |
| GET | `/teams/{team}` | Member | Show a team |
| PUT | `/teams/{team}` | Owner | Update a team |
| DELETE | `/teams/{team}` | Owner | Delete a team |

### Members

| Method | Endpoint | Access | Description |
|---|---|---|---|
| GET | `/teams/{team}/members` | Member | List members (paginated) |
| POST | `/teams/{team}/members` | Owner | Add a member by email |
| DELETE | `/teams/{team}/members/{user}` | Owner | Remove a member |

### Tasks

| Method | Endpoint | Access | Description |
|---|---|---|---|
| GET | `/teams/{team}/tasks` | Member | List tasks (paginated) |
| POST | `/teams/{team}/tasks` | Member | Create a task |
| GET | `/teams/{team}/tasks/{task}` | Member | Show a task |
| PUT | `/teams/{team}/tasks/{task}` | Creator or owner | Update a task |
| DELETE | `/teams/{team}/tasks/{task}` | Creator or owner | Delete a task |
| POST | `/teams/{team}/tasks/{task}/complete` | Assignee | Mark a task as done |

Task routes use scoped bindings, so a task that belongs to another team returns 404.

## Request and Response Examples

### Register

```
POST /api/v1/register
```

```json
{
    "name": "Asfak",
    "email": "asfak@test.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

Response `201`:

```json
{
    "data": {
        "user": {
            "id": 1,
            "name": "Asfak",
            "email": "asfak@test.com",
            "created_at": "2026-10-31T04:06:32.000000Z"
        },
        "token": "1|abc..."
    }
}
```

### Create Task

```
POST /api/v1/teams/1/tasks
```

```json
{
    "title": "Write API docs",
    "description": "Postman collection",
    "assignee_id": 2,
    "due_date": "2026-11-10"
}
```

Response `201`:

```json
{
    "data": {
        "id": 1,
        "team_id": 1,
        "title": "Write API docs",
        "description": "Postman collection",
        "status": "todo",
        "creator_id": 1,
        "assignee_id": 2,
        "due_date": "2026-11-10",
        "created_at": "2026-10-31T09:30:23.000000Z"
    }
}
```

### Error responses

Validation error `422`:

```json
{
    "message": "The email field is required.",
    "errors": {
        "email": ["The email field is required."]
    }
}
```

Not allowed `403`:

```json
{
    "message": "This action is unauthorized."
}
```

## Status Codes

| Code | Meaning |
|---|---|
| 200 | OK |
| 201 | Created |
| 204 | Deleted, no content |
| 401 | Missing or invalid token |
| 403 | Not allowed by policy |
| 404 | Not found, or not part of this team |
| 422 | Validation failed |
| 429 | Too many requests |

## Validation Rules

| Field | Rules |
|---|---|
| Register `name` | required, string, max 255 |
| Register `email` | required, lowercase, valid email, unique |
| Register `password` | required, confirmed, min 8 |
| Team `name` | required, string, max 255 |
| Member `email` | required, must belong to an existing user |
| Task `title` | required on create, string, max 255 |
| Task `status` | `todo` or `doing` only (`done` uses the complete endpoint) |
| Task `assignee_id` | optional, must be a member of the team |
| Task `due_date` | optional, valid date |

## Database Design

```
users
  id
  name
  email (unique)
  password
  timestamps

teams
  id
  name
  owner_id  -> users.id  (cascade on delete)
  timestamps

team_user  (pivot: team members)
  id
  team_id   -> teams.id  (cascade on delete)
  user_id   -> users.id  (cascade on delete)
  timestamps
  unique (team_id, user_id)

tasks
  id
  team_id      -> teams.id  (cascade on delete)
  creator_id   -> users.id  (cascade on delete)
  assignee_id  -> users.id  (nullable, set null on delete)
  title
  description  (nullable)
  status       (todo | doing | done, default todo)
  due_date     (nullable)
  timestamps
  index (team_id, status)

personal_access_tokens  (Sanctum tokens)
```

### Relationships

```
User 1 ---- * Team          (owner_id: a user owns many teams)
User * ---- * Team          (team_user: a user belongs to many teams)
Team 1 ---- * Task          (a team has many tasks)
User 1 ---- * Task          (creator_id: a user creates many tasks)
User 1 ---- * Task          (assignee_id: a user is assigned many tasks)
```

## Postman

The collection and environment are in the `postman/` folder.

1. Open Postman and click Import.
2. Import the collection file and the environment file from `postman/`.
3. Select the `Team Tasks Local` environment.
4. Run `Auth > login`. The token is saved to the environment automatically.
5. All other requests use the token through the collection's Bearer auth.

Environment variables:

| Variable | Value |
|---|---|
| `base_url` | `http://127.0.0.1:8000/api/v1` |
| `token` | Set automatically by login |
| `team_id` | Set automatically by Create Team |
| `task_id` | Set automatically by Create Task |

Every request has saved examples for success and error cases (401, 403, 404, 422).

## Running Tests

```bash
php artisan test
```

Tests run on an in-memory SQLite database, so the development database is never touched. The `pdo_sqlite` PHP extension must be enabled.

Current tests cover the auth endpoints (register, login, me, logout).

## To Do

- Feature tests for each `TeamPolicy` and `TaskPolicy` rule
- Seeder with owner, member and stranger users for quick manual testing
