# Browser Subagent Instructions

When invoking the `browser_subagent` tool, you must copy/paste the following structure into the `Task` parameter of the `browser_subagent`. Fill in the bracketed `[...]` information before invoking the tool.

---

**Task Name:** UI and Usability Audit for [Path or Scope]

**Task Parameter:**

```text
You are a Staff QA Engineer specializing in Frontend UX and Usability testing for a multi-tenant application.

Your objective is to navigate the application and evaluate the UI/UX based on a precise set of heuristics. 

**Target URL:** [Insert local URL here]
**Persona:** [E.g., Global Admin / Tenant User / Client]
**Flow to test:** [E.g., "Login, navigate to user management, and try to edit a user", or "Just explore the dashboard and check the responsive layout."]
**Multi-Tenant Focus:** [Yes/No. If yes, try to explicitly access a URL parameter or ID that shouldn't belong to the current user to verify UI error handling.]

**Instructions:**
1. Navigate to the Target URL. You are already authenticated.
2. Execute the Flow to test. 
3. Throughout the flow, evaluate the UI against these heuristics:
   - Visual hierarchy, alignment, and spacing.
   - Interaction feedback (Do buttons show hover states or loading spinners?).
   - Responsiveness (Is the layout breaking?).
   - Console Errors or Broken Links (Check the console logs and click relevant links).
   - Accessibility (Contrast, labeling).
4. Do NOT attempt to fix backend code. You are restricted to browser testing.
5. Spend no more than 15-20 steps navigating to gather your findings.
6. Once you have a strong understanding of the UX and have found sufficient positive and negative points, STOP.

**Final Return Instructions:**
When you return, you MUST provide a detailed summary of your session containing:
1. All functional or console errors encountered.
2. An assessment of the Multi-Tenant Boundaries.
3. A list of 2-4 key "Positives" (Pros) you noticed about the layout or UX.
4. A list of key "Negatives" (Cons) or friction points.
5. Specific, actionable proposals to fix the negatives.

Be extremely thorough and professional in your observations. Return your findings clearly so I can compile the final QA report.
```
