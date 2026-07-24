<?php

/**
 * Matomo - free/libre analytics platform
 *
 * For more information, see README.md
 *
 * @license released under BSD License http://www.opensource.org/licenses/bsd-license.php
 * @link https://matomo.org/docs/tracking-api/
 *
 * @category Matomo
 * @package MatomoTracker
 */

declare(strict_types=1);

namespace Unit;

/**
 * Test double capturing outgoing requests and cookies instead of performing I/O.
 *
 * Bulk-mode requests are forwarded to the real implementation so the
 * request-storing branch of sendRequest() stays covered.
 */
class TestableMatomoTracker extends \MatomoTracker
{
    /**
     * @var list<array{url: string, method: string, data: string|null, force: bool}>
     */
    public array $capturedRequests = [];

    public string|bool $mockResponse = 'mock-response';

    /**
     * @var list<array{name: string, value: string, ttl: int}>
     */
    public array $capturedCookies = [];

    public bool $captureCookies = true;

    protected function sendRequest(string $url, string $method = 'GET', ?string $data = null, bool $force = false): string|bool
    {
        if ($this->doBulkRequests && !$force) {
            return parent::sendRequest($url, $method, $data, $force);
        }

        $this->capturedRequests[] = ['url' => $url, 'method' => $method, 'data' => $data, 'force' => $force];

        return $this->mockResponse;
    }

    protected function setCookie(string $cookieName, string $cookieValue, int $cookieTTL): self
    {
        if (!$this->captureCookies) {
            return parent::setCookie($cookieName, $cookieValue, $cookieTTL);
        }

        $this->capturedCookies[] = ['name' => $cookieName, 'value' => $cookieValue, 'ttl' => $cookieTTL];

        return $this;
    }

    public function lastRequestUrl(): string
    {
        $last = end($this->capturedRequests);

        return $last === false ? '' : $last['url'];
    }

    /**
     * @return array<int, mixed>
     */
    public function callPrepareCurlOptions(string $url, string $method, ?string $data, bool $forcePostUrlEncoded): array
    {
        return $this->prepareCurlOptions($url, $method, $data, $forcePostUrlEncoded);
    }

    /**
     * @return array{http: array<string, mixed>}
     */
    public function callPrepareStreamOptions(string $method, ?string $data, bool $forcePostUrlEncoded): array
    {
        return $this->prepareStreamOptions($method, $data, $forcePostUrlEncoded);
    }

    /**
     * @param array<array-key, mixed> $headers
     */
    public function callParseIncomingCookies(array $headers): void
    {
        $this->parseIncomingCookies($headers);
    }

    public function callGetTimestamp(): int
    {
        return $this->getTimestamp();
    }

    public function callGetBaseUrl(): string
    {
        return $this->getBaseUrl();
    }

    public function callGetRequest(int $idSite): string
    {
        return $this->getRequest($idSite);
    }

    public function callGetCookieMatchingName(string $name): string|false
    {
        return $this->getCookieMatchingName($name);
    }

    public function callGetCookieName(string $name): string
    {
        return $this->getCookieName($name);
    }

    public function callLoadVisitorIdCookie(): bool
    {
        return $this->loadVisitorIdCookie();
    }

    public function callSetFirstPartyCookies(): void
    {
        $this->setFirstPartyCookies();
    }

    /**
     * @return array<int, array{0: string, 1: string}>
     */
    public function callGetCustomVariablesFromCookie(): array
    {
        return $this->getCustomVariablesFromCookie();
    }

    public static function callDomainFixup(string $domain): string
    {
        return self::domainFixup($domain);
    }

    public static function callToStringValue(mixed $value): string
    {
        return self::toStringValue($value);
    }

    public static function callGetCurrentScheme(): string
    {
        return self::getCurrentScheme();
    }

    public static function callGetCurrentHost(): string
    {
        return self::getCurrentHost();
    }

    public static function callGetCurrentScriptName(): string
    {
        return self::getCurrentScriptName();
    }

    public static function callGetCurrentQueryString(): string
    {
        return self::getCurrentQueryString();
    }

    public static function callGetCurrentUrl(): string
    {
        return self::getCurrentUrl();
    }
}
