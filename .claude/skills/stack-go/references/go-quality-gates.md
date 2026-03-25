# Go Quality Gates

## Lint — golangci-lint

```yaml
# .golangci.yml
run:
  timeout: 5m

linters:
  enable:
    - errcheck
    - gosimple
    - govet
    - ineffassign
    - staticcheck
    - unused
    - gocritic
    - gofmt
    - goimports
    - misspell
    - revive
    - unconvert
    - unparam

linters-settings:
  gocritic:
    enabled-tags:
      - diagnostic
      - style
      - performance
  revive:
    rules:
      - name: exported
        severity: warning
```

### Commands

```bash
golangci-lint run         # Full lint
golangci-lint run --fix   # Auto-fix where possible
```

## Static Analysis — go vet

```bash
go vet ./...              # Built-in static analysis
```

## Security

```bash
govulncheck ./...         # Check for known vulnerabilities
gosec ./...               # Security-focused static analysis
```

## Quality Gate Sequence

```bash
# Before commit
go vet ./... && golangci-lint run

# Before PR
go vet ./... && golangci-lint run && go test ./... -race

# Before deploy
go vet ./... && golangci-lint run && go test ./... -race -cover && govulncheck ./... && go build ./...
```
