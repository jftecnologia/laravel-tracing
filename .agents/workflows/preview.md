---
description: Start a local dev server and preview the application in the browser. Use when you want to quickly view the app running locally, validate UI changes, or run browser QA.
---

# Preview Application

## Steps

1. Detect project stack and find the dev server command:
   - **Laravel**: `php artisan serve` (backend) + `npm run dev` (frontend assets)
   - **Node.js (Vite/Next)**: `npm run dev`
   - **Python (Django)**: `python manage.py runserver`
   - **Python (FastAPI)**: `uvicorn main:app --reload`
   - **Go**: `go run .` or check `Makefile` for dev target

2. Check if a dev server is already running:
   // turbo
   - Check common ports (8000, 3000, 5173, 8080)
   - If already running, skip to step 4

3. Start the dev server:
   - Run the detected command in background
   - Wait for the server to be ready (check for "started" output or port availability)

4. Get the app URL:
   - **Laravel**: use `get-absolute-url` MCP tool or default `http://localhost:8000`
   - **Others**: use detected port

5. Open browser to preview:
   - Navigate to the app URL using browser subagent
   - Take a screenshot for the user

6. Optional — run browser QA:
   - Ask the user: "Want me to run a quick UX/UI check with `browser-qa-tester`?"
   - If yes, invoke the `browser-qa-tester` skill on the current page
