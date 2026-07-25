# Matomo PHP Tracker Changelog

This is the Developer Changelog for Matomo PHP Tracker. All breaking changes or new features are listed below.

## Matomo PHP Tracker 4.0.0

Attention: this is a major release with breaking changes.

### Removed
- Support for PHP versions lower than 8.1. The tracker now requires PHP 8.1 or newer.
- The `#[AllowDynamicProperties]` attribute. All properties are now declared explicitly, so setting undeclared dynamic properties on a tracker instance is no longer supported (extend `MatomoTracker` and declare the property instead).

### Changed
- `declare(strict_types=1)` is now enabled and every method has proper parameter and return type hints aligned with how Matomo core handles the corresponding tracking parameters. **Passing a mismatched scalar type now throws a `TypeError` instead of being silently coerced.**
- Optional "unset" parameters and their corresponding properties and getters now use `null` instead of the previous `false` sentinel. For example `getUserId()`, `getUserAgent()`, `getIp()` and `getPageviewId()` now return `null` (not `false`) when no value is set, and `doTrackEvent()`/`getUrlTrackEvent()` default the event name and value to `null`.
- All public properties are now natively typed. Assigning a legacy sentinel value such as `false` to e.g. `$tracker->userAgent` now throws a `TypeError`; the `attributionInfo` property defaults to an empty array instead of `false`. Subclasses overriding methods with the old untyped signatures may need to be updated to the new signatures.
- `setUserId()` now accepts `null` to de-assign a previously set User ID, as the method documentation always promised (previously the `string` type hint made that impossible).
- `setUrlReferrer()` (and the deprecated `setUrlReferer()`) accept `null` to unset the referrer.
- `setCustomTrackingParameter()` accepts an array value again (serialized via `http_build_query`, as the JS tracker does); this restores the pre-4.0 behavior for multi-value parameters.
- `setLatitude()` / `setLongitude()` values of `0.0` (equator / prime meridian) are now sent to Matomo. Previously coordinates of exactly zero were silently dropped.
- Goal and Ecommerce revenue amounts now distinguish "not set" from an explicit `0`. `doTrackGoal()` / `getUrlTrackGoal()` (and the `Matomo_`/`Piwik_` goal helpers) take `?float $revenue = null`: `null` omits `revenue` (so Matomo uses the goal's configured revenue) while `0.0` now sends `revenue=0`. Likewise the optional Ecommerce amounts (`$subTotal`, `$tax`, `$shipping`, `$discount` of `doTrackEcommerceOrder()` etc.) are `?float = null` and only sent when provided, and the required Ecommerce grand total is now always sent (a `0.0` order/cart sends `revenue=0`). Previously an explicit `0`/`0.0` was silently omitted for all of these.
- The `do*` tracking methods now declare a `string|bool` return type. In bulk mode they return boolean `true` (previously the value was coerced to the string `"1"`).
- `doTrackSiteSearch()` / `getUrlTrackSiteSearch()` accept `?int $countResults` and only send `&search_count` when a count is provided (previously `&search_count=0` was always sent).
- When the stream fallback is used and the request fails, `doBulkTrack()` / the `do*` methods now return `false` instead of an empty string.
- Lowered the default request timeouts from 600s/300s to 5s/2s so a slow or unreachable Matomo can no longer block the calling page for minutes (#88). Raise them again via `setRequestTimeout()` / `setRequestConnectTimeout()` if needed.
- Bumped the test suite to PHPUnit 10.5.

### Fixed
- Event and content tracking requests now send `&ca=1` (custom action), so Matomo no longer falls back to recording them as page views if the handling plugin is disabled (#80).
- The `cip` (override IP) tracking parameter is now URL-encoded like every other value (#151).
- No longer calls the deprecated `curl_close()` (it was already a no-op on the supported PHP versions) (#149).

### Added
- PHPStan static analysis at max level (`phpstan.neon.dist`) and the Matomo coding standard via PHP_CodeSniffer (`phpcs.xml.dist`), both enforced for every pull request through GitHub Actions.
- A greatly expanded unit test suite covering all tracking parameters, cookie handling and request preparation, with code coverage reported in CI.
- `setDebugTrackingParameter()` (`@internal` test helper) to append a raw, unvalidated tracking parameter that overrides any built-in parameter of the same name, so integration tests can verify server-side handling of malformed values.
- `setCurlOptions(array)` to pass additional cURL options (e.g. `CURLOPT_IPRESOLVE`, `CURLOPT_HTTP_VERSION`) for the tracking requests; they are applied after the built-in options (#92).

## Matomo PHP Tracker 3.4.0
### Changed

- Fixed PHP 8.5 deprecation notice
- static `$URL` is deprecated
- a lot of arguments of `MatomoTracker` methods have explicitly types
- a lot of `MatomoTracker` method return types have strict types

### Added
- new private property `apiUrl` for storing API URL

## Matomo PHP Tracker 3.3.2
### Changed
- Support for formFactors client hint parameter, supported as of Matomo 5.2.0

## Matomo PHP Tracker 3.3.1
### Fixed
- closed curl connection

## Matomo PHP Tracker 3.3.0
### Removed
- support for PHP versions lower than 7.2
### Changed
- all `MatomoTracker` class constants are now explicitly public
- all `MatomoTracker` dynamic properties are now explicitly public

## Matomo PHP Tracker 3.0.0

Attention: This version of Matomo PHP Tracker is no longer compatible with Matomo 3.x or earlier

- Support for new page performance metrics (added in Matomo 4) has been added. You can use `setPerformanceTimings()` to set them for page views.
- Setting page generation time using `setGenerationTime()` has been discontinued. The method still exists to not break applications still using it, but it does not have any effect. Please use new page performance metrics as replacement.
- Sending requests using cURL will now throw an exception if an error occurs in a request.
- Matomo does not longer support tracking of these browser plugins: Gears, Director. Therefor the signature of `setPlugins()` changed.
- Implementation of ecommerce views changed from custom variables to raw parameters
- It is now possible to configure cookie options for Secure, HTTPOnly and SameSite.
- Add method setRequestMethodNonBulk() to allow (non bulk) POST requests.
