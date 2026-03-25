# Python Patterns

## Service Pattern

```python
from dataclasses import dataclass
from app.repositories.users import UsersRepository
from app.models.user import User, CreateUserDto


@dataclass
class UsersService:
    repository: UsersRepository

    async def find_all(self, filters: dict | None = None) -> list[User]:
        return await self.repository.find_many(filters or {})

    async def create(self, data: CreateUserDto) -> User:
        # Business logic
        return await self.repository.create(data)

    async def find_by_id(self, user_id: str) -> User:
        user = await self.repository.find_by_id(user_id)
        if not user:
            raise NotFoundError("User", user_id)
        return user
```

## Error Handling

```python
class AppError(Exception):
    def __init__(self, message: str, status_code: int = 500, code: str = "INTERNAL_ERROR"):
        self.message = message
        self.status_code = status_code
        self.code = code
        super().__init__(message)


class NotFoundError(AppError):
    def __init__(self, resource: str, resource_id: str):
        super().__init__(f"{resource} with id {resource_id} not found", 404, "NOT_FOUND")


class ValidationError(AppError):
    def __init__(self, message: str, errors: dict[str, list[str]] | None = None):
        self.errors = errors or {}
        super().__init__(message, 422, "VALIDATION_ERROR")
```

## Configuration (Pydantic)

```python
from pydantic_settings import BaseSettings


class Settings(BaseSettings):
    app_name: str = "My App"
    debug: bool = False
    database_url: str
    secret_key: str
    allowed_origins: list[str] = ["http://localhost:3000"]

    model_config = {"env_file": ".env", "env_file_encoding": "utf-8"}


settings = Settings()
```

## API Route (FastAPI)

```python
from fastapi import APIRouter, Depends, HTTPException
from app.services.users import UsersService
from app.models.user import CreateUserDto, UserResponse

router = APIRouter(prefix="/api/users", tags=["users"])


def get_users_service() -> UsersService:
    return UsersService(repository=UsersRepository())


@router.get("/", response_model=list[UserResponse])
async def list_users(service: UsersService = Depends(get_users_service)):
    return await service.find_all()


@router.post("/", response_model=UserResponse, status_code=201)
async def create_user(data: CreateUserDto, service: UsersService = Depends(get_users_service)):
    return await service.create(data)
```
