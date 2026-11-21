# CSS style rules


- Apply these rules to authored CSS; do not hand-format vendor, dependency, or generated/minified assets.
- Put the opening brace after the selector, then start declarations on the next line. Never use one-line rule blocks.
- Put every declaration on its own line and the closing brace on its own line.
- Use four spaces per indentation level, including nested rules and media queries; never use tabs.
- Leave exactly one blank line between adjacent CSS rules, including rules nested inside at-rules.
- Group related rules into clearly named English section comments (for example, `/* Toast */` or `/* Card */`). Leave one blank line after each section comment.
- Keep selector order, specificity, declaration order, values, and responsive behavior intact during formatting-only refactors.
- Parse/check authored CSS after edits and run the frontend build and affected browser checks when available.

```css
/* Toast */

.sir-toast {
    display: flex;
    gap: 1rem;
}

.sir-toast-header {
    font-weight: 600;
}

/* Responsive toast */

@media (max-width: 40rem) {
    .sir-toast {
        flex-direction: column;
    }

    .sir-toast-header {
        width: 100%;
    }
}
```
