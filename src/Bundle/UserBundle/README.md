Integrated User Bundle
=====

This bundle provides user authentication, profile/group/scope management, password reset and website login flows.

### Required Project Config

For the security hardening in this bundle (login throttle + password reset throttle + HMAC reset links), make sure the host project has:

1. A valid `kernel.secret` (`APP_SECRET`) in every environment.
2. A working app cache pool (`cache.app`) so throttling state can be stored.
3. Login templates that render `warning` flashes (already covered by Integrated default templates).

No extra Symfony package is required for throttling in this implementation.

### Example Security Config

Use separate firewalls for admin and website login flows (or equivalent routes in your project):

```yaml
security:
    password_hashers:
        Integrated\Bundle\UserBundle\Model\User: auto

    providers:
        integrated_user:
            id: integrated_user.security.provider
        integrated_user_scope:
            id: Integrated\Bundle\UserBundle\Security\UserScopeProvider

    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt|configurator)|css|images|js)/
            security: false

        default:
            provider: integrated_user
            pattern: ^/admin
            form_login:
                login_path: integrated_user_login
                check_path: integrated_user_check
                enable_csrf: true
            logout:
                path: integrated_user_logout
                target: /
            remember_me:
                secret: '%kernel.secret%'
                lifetime: 2592000
                path: /

        frontend:
            pattern: ^/
            lazy: true
            provider: integrated_user_scope
            form_login:
                login_path: integrated_user_website_security_login
                check_path: integrated_user_website_security_check
                enable_csrf: true
            logout:
                path: integrated_user_logout
                target: /
            remember_me:
                secret: '%kernel.secret%'
                lifetime: 2592000
                path: /

    access_control:
        - { path: ^/admin/login, roles: PUBLIC_ACCESS }
        - { path: ^/login, roles: PUBLIC_ACCESS }
        - { path: ^/admin, roles: IS_AUTHENTICATED_REMEMBERED }
```

### Translation Keys

The bundle now uses these user-facing throttle messages:

- `Too many login attempts. Please try again later.`
- `Too many password reset attempts. Please wait a few minutes and try again.`

Override them in your project translation files if needed.
