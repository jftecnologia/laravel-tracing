# Browser QA Tester - Heuristics

When evaluating the user interface and user experience of the target application, the browser subagent MUST check against the following heuristics:

## 1. Visual Hierarchy and Layout
- **Alignment:** Are elements properly aligned to a grid or logical structure? 
- **Spacing (Whitspace):** Is there enough breathing room between distinct components to prevent clutter?
- **Hierarchy:** Are primary actions (e.g., "Save", "Submit") visually distinct from secondary actions (e.g., "Cancel", "Back")?
- **Consistency:** Do buttons, inputs, and typography match the overall application design system?

## 2. Interaction and Feedback
- **States:** Do interactive elements (buttons, links) have clear hover, focus, and active states?
- **Feedback:** Does the system provide immediate visual feedback upon user action (e.g., loading spinners, success toasts, error messages)?
- **Clarity:** Is the language used in the interface clear, concise, and free of technical jargon?

## 3. Responsiveness and Adaptation
- **Scaling:** Does the layout adapt gracefully when the browser window is resized (simulate mobile/tablet viewports if possible)?
- **Overflow:** Do tables, long text blocks, or images break out of their containers?

## 4. Technical Health
- **Console Errors:** Are there any JavaScript errors, failed network requests, or warnings in the browser console?
- **Broken Links:** Do all interactive elements correctly route to the expected destination without returning 404s?
- **Performance:** Does the page load in a reasonable amount of time without heavy stuttering during animations?

## 5. Accessibility (a11y)
- **Contrast:** Is there sufficient contrast between text and its background?
- **Keyboard Navigation:** Can the core flow be completed using only the keyboard (Tab, Enter, Space)?
- **Labels:** Do form inputs and icon buttons have clear labels or ARIA tags?

## 6. Multi-Tenant Security Boundaries (Critical)
- **Data Isolation:** Can the current user see data that belongs to a different tenant context?
- **Action Scoping:** If the user attempts to predict and modify ID parameters in URLs to access cross-tenant resources, does the UI properly handle and block the request (e.g., showing a 403 or 404 gracefully)?
- **Selector Integrity:** Are tenant selectors correctly restricted to the data the user is explicitly authorized to see?
