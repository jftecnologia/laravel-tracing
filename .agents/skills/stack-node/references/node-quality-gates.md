# Node.js Quality Gates

## Lint — ESLint

```javascript
// eslint.config.js (flat config — ESLint 9+)
import eslint from "@eslint/js";
import tseslint from "typescript-eslint";

export default tseslint.config(
  eslint.configs.recommended,
  ...tseslint.configs.recommended,
  {
    rules: {
      "@typescript-eslint/explicit-function-return-type": "warn",
      "@typescript-eslint/no-unused-vars": "error",
      "no-console": "warn",
    },
  },
);
```

### Commands

```bash
npx eslint . --fix          # Fix auto-fixable issues
npx eslint .                # Check only
```

## Format — Prettier

```json
// .prettierrc
{
  "semi": true,
  "singleQuote": true,
  "trailingComma": "all",
  "printWidth": 100,
  "tabWidth": 2
}
```

### Commands

```bash
npx prettier --write .      # Format all
npx prettier --check .      # Check only
```

## Type Check — TypeScript

```json
// tsconfig.json (strict mode)
{
  "compilerOptions": {
    "strict": true,
    "noUncheckedIndexedAccess": true,
    "noImplicitReturns": true,
    "noFallthroughCasesInSwitch": true,
    "forceConsistentCasingInFileNames": true,
    "esModuleInterop": true,
    "moduleResolution": "bundler",
    "module": "ESNext",
    "target": "ESNext"
  }
}
```

### Commands

```bash
npx tsc --noEmit            # Type check without emitting
```

## Security

```bash
npm audit                   # Check for known vulnerabilities
npm audit fix               # Auto-fix where possible
```

## Package Scripts

Recommended `package.json` scripts:

```json
{
  "scripts": {
    "dev": "tsx watch src/index.ts",
    "build": "tsc",
    "start": "node dist/index.js",
    "lint": "eslint .",
    "lint:fix": "eslint . --fix",
    "format": "prettier --write .",
    "types": "tsc --noEmit",
    "test": "vitest run",
    "test:watch": "vitest",
    "test:coverage": "vitest run --coverage"
  }
}
```

## Quality Gate Sequence

```bash
# Before commit
npm run lint && npm run types

# Before PR
npm run lint && npm run types && npm test

# Before deploy
npm run lint && npm run types && npm test && npm run build && npm audit
```
