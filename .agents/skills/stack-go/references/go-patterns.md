# Go Patterns

## Handler Pattern (net/http)

```go
type UsersHandler struct {
    service *UsersService
}

func NewUsersHandler(s *UsersService) *UsersHandler {
    return &UsersHandler{service: s}
}

func (h *UsersHandler) List(w http.ResponseWriter, r *http.Request) {
    users, err := h.service.FindAll(r.Context())
    if err != nil {
        httputil.Error(w, err)
        return
    }
    httputil.JSON(w, http.StatusOK, users)
}

func (h *UsersHandler) Create(w http.ResponseWriter, r *http.Request) {
    var input CreateUserInput
    if err := json.NewDecoder(r.Body).Decode(&input); err != nil {
        httputil.Error(w, NewValidationError("invalid request body"))
        return
    }

    user, err := h.service.Create(r.Context(), input)
    if err != nil {
        httputil.Error(w, err)
        return
    }
    httputil.JSON(w, http.StatusCreated, user)
}
```

## Service Pattern

```go
type UsersService struct {
    repo UsersRepository
}

func NewUsersService(repo UsersRepository) *UsersService {
    return &UsersService{repo: repo}
}

func (s *UsersService) FindAll(ctx context.Context) ([]User, error) {
    return s.repo.FindMany(ctx)
}

func (s *UsersService) Create(ctx context.Context, input CreateUserInput) (*User, error) {
    if err := input.Validate(); err != nil {
        return nil, fmt.Errorf("validation failed: %w", err)
    }
    return s.repo.Create(ctx, input)
}
```

## Interface Pattern

```go
// Small interfaces — accept interfaces, return structs
type UsersRepository interface {
    FindMany(ctx context.Context) ([]User, error)
    FindByID(ctx context.Context, id string) (*User, error)
    Create(ctx context.Context, input CreateUserInput) (*User, error)
}
```

## Error Handling

```go
// Custom errors with types
type AppError struct {
    Message    string `json:"message"`
    StatusCode int    `json:"-"`
    Code       string `json:"code"`
}

func (e *AppError) Error() string { return e.Message }

func NewNotFoundError(resource, id string) *AppError {
    return &AppError{
        Message:    fmt.Sprintf("%s with id %s not found", resource, id),
        StatusCode: http.StatusNotFound,
        Code:       "NOT_FOUND",
    }
}

// Error wrapping
func (s *UsersService) FindByID(ctx context.Context, id string) (*User, error) {
    user, err := s.repo.FindByID(ctx, id)
    if err != nil {
        return nil, fmt.Errorf("finding user %s: %w", id, err)
    }
    if user == nil {
        return nil, NewNotFoundError("user", id)
    }
    return user, nil
}
```

## Configuration

```go
type Config struct {
    Port        int    `env:"PORT" envDefault:"8080"`
    DatabaseURL string `env:"DATABASE_URL,required"`
    JWTSecret   string `env:"JWT_SECRET,required"`
    Debug       bool   `env:"DEBUG" envDefault:"false"`
}

func LoadConfig() (*Config, error) {
    var cfg Config
    if err := env.Parse(&cfg); err != nil {
        return nil, fmt.Errorf("parsing config: %w", err)
    }
    return &cfg, nil
}
```
