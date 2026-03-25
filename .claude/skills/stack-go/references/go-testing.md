# Go Testing Patterns

## Framework: Standard library (`testing`) + testify

## Table-Driven Tests (idiomatic Go)

```go
func TestUsersService_Create(t *testing.T) {
    tests := []struct {
        name    string
        input   CreateUserInput
        wantErr bool
        errType error
    }{
        // ✅ Happy Path
        {
            name:    "valid user",
            input:   CreateUserInput{Name: "John", Email: "john@example.com"},
            wantErr: false,
        },
        // ❌ Unhappy Path
        {
            name:    "invalid email",
            input:   CreateUserInput{Name: "John", Email: "invalid"},
            wantErr: true,
        },
        {
            name:    "empty name",
            input:   CreateUserInput{Name: "", Email: "john@example.com"},
            wantErr: true,
        },
    }

    for _, tt := range tests {
        t.Run(tt.name, func(t *testing.T) {
            repo := &MockUsersRepository{}
            service := NewUsersService(repo)

            result, err := service.Create(context.Background(), tt.input)

            if tt.wantErr {
                assert.Error(t, err)
                assert.Nil(t, result)
            } else {
                assert.NoError(t, err)
                assert.NotNil(t, result)
                assert.Equal(t, tt.input.Name, result.Name)
            }
        })
    }
}
```

## Mock Pattern

```go
// MockUsersRepository implements UsersRepository for tests
type MockUsersRepository struct {
    users   []User
    findErr error
    createErr error
}

func (m *MockUsersRepository) FindMany(ctx context.Context) ([]User, error) {
    if m.findErr != nil {
        return nil, m.findErr
    }
    return m.users, nil
}

func (m *MockUsersRepository) Create(ctx context.Context, input CreateUserInput) (*User, error) {
    if m.createErr != nil {
        return nil, m.createErr
    }
    user := &User{ID: "test-id", Name: input.Name, Email: input.Email}
    m.users = append(m.users, *user)
    return user, nil
}
```

## HTTP Handler Tests

```go
func TestUsersHandler_List(t *testing.T) {
    // ✅ Happy Path
    t.Run("returns users", func(t *testing.T) {
        service := NewUsersService(&MockUsersRepository{
            users: []User{{ID: "1", Name: "John"}},
        })
        handler := NewUsersHandler(service)

        req := httptest.NewRequest(http.MethodGet, "/api/users", nil)
        rec := httptest.NewRecorder()

        handler.List(rec, req)

        assert.Equal(t, http.StatusOK, rec.Code)
    })

    // 🔒 Security Path
    t.Run("does not expose password hashes", func(t *testing.T) {
        service := NewUsersService(&MockUsersRepository{
            users: []User{{ID: "1", Name: "John", PasswordHash: "secret"}},
        })
        handler := NewUsersHandler(service)

        req := httptest.NewRequest(http.MethodGet, "/api/users", nil)
        rec := httptest.NewRecorder()

        handler.List(rec, req)

        body := rec.Body.String()
        assert.NotContains(t, body, "secret")
        assert.NotContains(t, body, "password_hash")
    })
}
```

## Integration Test with Test Database

```go
func TestUsersRepository_Integration(t *testing.T) {
    if testing.Short() {
        t.Skip("skipping integration test in short mode")
    }

    db := setupTestDB(t)
    t.Cleanup(func() { db.Close() })

    repo := NewUsersRepository(db)

    t.Run("create and find user", func(t *testing.T) {
        user, err := repo.Create(context.Background(), CreateUserInput{
            Name: "John", Email: "john@example.com",
        })
        require.NoError(t, err)

        found, err := repo.FindByID(context.Background(), user.ID)
        require.NoError(t, err)
        assert.Equal(t, "John", found.Name)
    })
}
```
