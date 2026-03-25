# Python Testing Patterns

## Framework: pytest

### Configuration

```toml
# pyproject.toml
[tool.pytest.ini_options]
testpaths = ["tests"]
python_files = ["test_*.py"]
python_functions = ["test_*"]
asyncio_mode = "auto"
addopts = "-v --tb=short"
```

## Test Structure (Three Pillars)

```python
import pytest
from app.services.users import UsersService
from app.errors import NotFoundError, ValidationError
from tests.helpers import MockUser


class TestUsersService:
    """Tests for UsersService — Three Pillar Pattern."""

    # ✅ Happy Path
    async def test_create_user_with_valid_data(self, mock_repo):
        service = UsersService(repository=mock_repo)
        user = await service.create({"name": "John", "email": "john@example.com"})

        assert user.name == "John"
        mock_repo.create.assert_called_once()

    # ❌ Unhappy Path
    async def test_create_user_with_invalid_email_raises(self, mock_repo):
        service = UsersService(repository=mock_repo)

        with pytest.raises(ValidationError):
            await service.create({"name": "John", "email": "invalid"})

    async def test_find_by_id_raises_when_not_found(self, mock_repo):
        mock_repo.find_by_id.return_value = None
        service = UsersService(repository=mock_repo)

        with pytest.raises(NotFoundError):
            await service.find_by_id("nonexistent-id")

    # 🔒 Security Path
    async def test_password_hash_not_exposed(self, mock_repo):
        mock_repo.create.return_value = MockUser(password_hash="secret")
        service = UsersService(repository=mock_repo)

        result = await service.create({"name": "John", "email": "john@example.com"})
        assert not hasattr(result, "password_hash") or result.password_hash is None
```

## Fixtures

```python
# tests/conftest.py
import pytest
from unittest.mock import AsyncMock


@pytest.fixture
def mock_repo():
    repo = AsyncMock()
    repo.find_many.return_value = []
    repo.find_by_id.return_value = None
    return repo


@pytest.fixture
async def test_db():
    """Create a test database and run migrations."""
    db = await create_test_database()
    await db.migrate()
    yield db
    await db.drop()
```

## API Tests (FastAPI)

```python
from httpx import ASGITransport, AsyncClient
from app.main import app


@pytest.fixture
async def client():
    transport = ASGITransport(app=app)
    async with AsyncClient(transport=transport, base_url="http://test") as client:
        yield client


async def test_create_user_returns_201(client: AsyncClient):
    response = await client.post("/api/users", json={
        "name": "John",
        "email": "john@example.com",
    })
    assert response.status_code == 201
    assert response.json()["data"]["name"] == "John"
```
