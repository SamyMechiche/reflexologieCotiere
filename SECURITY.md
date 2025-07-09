# Security Overview for Réflexologie Côtière

This document summarizes the key security measures and best practices implemented in this project. It is intended to help you present and defend the security of your application during certification or professional review.

---

## 1. Symfony Security Features
- **Authentication:**
  - User authentication is handled by Symfony’s security system.
  - Passwords are hashed using modern algorithms (bcrypt/argon2i) and never stored in plain text.
  - Password upgrades are supported for future-proofing.
- **Authorization:**
  - Access to sensitive routes (e.g., admin CRUD) is restricted to users with appropriate roles (e.g., `ROLE_ADMIN`).
  - Controllers use `IsGranted` or are protected via `security.yaml` `access_control` rules.
- **CSRF Protection:**
  - All forms include CSRF tokens, which are validated server-side to prevent cross-site request forgery.
  - AJAX endpoints and form submissions are protected by Symfony’s CSRF system.
- **Session Management:**
  - Sessions are managed securely by Symfony, with proper cookie flags and session storage.

---

## 2. Output Escaping & XSS Prevention
- **Twig Auto-Escaping:**
  - All template output is auto-escaped by Twig, preventing XSS by default.
  - The `|raw` filter is only used for trusted, admin-controlled content (e.g., session descriptions), with clear comments and warnings in the code.
- **No Unescaped User Input:**
  - User-generated content is never rendered raw unless explicitly sanitized or restricted to trusted users.

---

## 3. Repository & Database Security
- **Doctrine ORM:**
  - All database access is handled via Doctrine ORM repositories.
  - All custom queries use parameter binding, preventing SQL injection.
  - No raw SQL or string interpolation is used in queries.
- **Entity Validation:**
  - Entities use Symfony validation constraints to ensure data integrity.
  - Only necessary fields are exposed in forms to prevent mass assignment.
- **Database Roles:**
  - Only the application has access to the database; users do not connect directly.
  - Sensitive operations (create/edit/delete) are restricted to authorized users.

---

## 4. General Security Practices
- **Input Validation:**
  - All user input is validated server-side using Symfony forms and validation constraints.
- **Output Sanitization:**
  - Output is auto-escaped, and any exceptions are clearly documented and justified.
- **Access Control:**
  - Sensitive routes and actions are protected by roles and access control rules.
- **CSRF & Session Security:**
  - CSRF tokens are used for all forms and AJAX requests.
  - Sessions are protected by secure cookies and proper configuration.
- **No Dangerous JS:**
  - No use of `eval`, `Function`, or direct DOM injection from user input in JavaScript.
- **No Sensitive Data in JS:**
  - No passwords or sensitive data are exposed to the frontend.

---

## 5. Security Documentation & Comments
- **Code Comments:**
  - Security-relevant code sections are clearly commented (repositories, controllers, templates).
  - Comments explain why certain practices (e.g., `|raw`) are safe in context.
- **Documentation:**
  - This file and the README.md summarize security practices for reviewers and maintainers.

---

## 6. Recommendations for Future Security
- **If you allow untrusted users to edit content rendered with `|raw`, add server-side HTML sanitization (e.g., HTMLPurifier or Symfony’s HtmlSanitizer).**
- **Regularly review and update dependencies for security patches.**
- **Add automated tests for security-critical features (authentication, authorization, CSRF, etc.).**
- **Consider adding a Content Security Policy (CSP) header for extra XSS protection.**

---

## 7. References
- [Symfony Security Documentation](https://symfony.com/doc/current/security.html)
- [OWASP Top 10 Security Risks](https://owasp.org/www-project-top-ten/)
- [Doctrine ORM Security](https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/security.html)

---

**If you have any questions about security in this project, refer to this file or the code comments, or contact the project maintainer.** 