# Changelog

## 7.0.0 (unreleased)

This is the first release of Kotchasan as a Composer package on its own. The
framework code is the same as the `Kotchasan/` folder of adminframework.

- `composer require goragod/kotchasan` installs only the framework. The old
  repository also carried a demo site, CKEditor, a PDF library and skins.
- Composer loads the classes from a classmap. The old `"psr-4": {"": "Kotchasan/"}`
  mapping could not find any class, so only `Kotchasan/load.php` loaded them.
  The bundled `Psr\Log` interfaces and PHPMailer 5 are left out of the classmap,
  so they never replace the `psr/log` package or a global `PHPMailer` class of
  an application. `Kotchasan\Email` still includes its own PHPMailer copy.
- `load.php` finds the project folder when the framework is installed in
  `vendor/`. `ROOT_PATH` and `WEB_URL` used to point at
  `vendor/goragod/kotchasan/` unless the application defined `ROOT_PATH`.
- Including `load.php` a second time does nothing. Composer's `vendor/autoload.php`
  already includes it, and an application that also included it by path used
  to stop with "Cannot redeclare function".
- PHP 7.4 or newer is required.
- `Request::isValidReferer()` (and `isReferer()`) compares the port as well as
  the host. On a port other than 80 or 443, such as `php -S localhost:8000`,
  it was always false, so `Kotchasan\Login` could not log anyone in. A Referer
  from another port of the same host is no longer accepted.
- `InputItem::email()` keeps `+`. `name+shop@example.com` used to become
  `nameshop@example.com`, which is another mailbox.
- A URL that names a class or method that does not exist is answered with 404.
  The router still throws, so the error is logged as before.
- An uncaught exception is answered with 500 instead of 200 and an empty page,
  unless an error status was already set.
- `Template::pregReplace()` reads the `e` of `'/{LNG_([^}]+)}/e'` only as a
  modifier after the last `/`. A key such as `'/{name}/'` whose value read like
  `Class::method` used to call that method.
- `projects/` holds eight examples that load `Kotchasan/` directly, with no
  Composer and no setup. `php -S localhost:8000`, then open `/projects/`.
