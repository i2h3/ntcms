# AGENTS.md

This project is a Nextcloud server app.

## Technical Guidelines

### Backend

- You write PHP code according to Nextcloud standards.
- You use the backend of Nextcloud and no other third-party or external solutions.
- You use `OCP\IL10N` for all user-visible texts.
- You register the app via `Application.php`.
- Controllers use attributes like `#[NoAdminRequired]` or `#[NoCSRFRequired]` from `OCP\AppFramework\Http\Attribute`.
- The app must be compatible with the latest major release only. Older major releases do not have to be supported.
- You do not interact with autoloading, Nextcloud already takes care of that.
- Database tables are created through migrations. A `database.xml` is obsolete.
- You register navigation with `info.xml`.
- All routes are registered in `appinfo/routes.php`.
- Use database tables with 23 characters at most to remain compatible with all supported database management systems.
- The whole CRUD data flow is implemented from endpoint over controller, service, entity and mapper.
- Use `lowerCamelCase` for identifiers.
- Do not use underscores in identifiers.
- No getters and setters in entities, they are generated automatically.
- Entities implement `jsonSerialize`.

### Frontend

- VanillaJS only.
- You write EcmaScript6.
- Stick to the guidelines by Nextcloud.
- Do not use Webpack.
- You structure code with the module pattern and namespaces in submodules.
- You fetch data via AJAX.
- Implement own controller endpoints without OCS.
- You use the `t()` API for all user-visible texts.
- You do not create any language files.
- All AJAX requests must contain the header `OCS-APIREQUEST: true`.
- All AJAX requests must contain the header `requesttoken: OC.requestToken`.
- You do not use external composer packages.
- The DOM on the main page consists out of three elements:
    - `app-navigation`
    - `app-content`
    - `app-sidebar`
- Navigation items are displayed as lists under the app navigation.
- Use `lowerCamelCase` for identifiers.
- Do not use underscores in identifiers.