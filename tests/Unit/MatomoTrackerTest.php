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

use Exception;
use PHPUnit\Framework\TestCase;

class MatomoTrackerTest extends TestCase
{
    public const TEST_URL = 'http://mymatomo.com';

    protected function setUp(): void
    {
        parent::setUp();

        \MatomoTracker::$URL = '';
        \MatomoTracker::$DEBUG_LAST_REQUESTED_URL = false;
        $_COOKIE = [];
    }

    private function createTracker(): TestableMatomoTracker
    {
        $tracker = new TestableMatomoTracker(1, self::TEST_URL);
        $tracker->setUrl('http://somesite.com');

        return $tracker;
    }

    /**
     * @return array<string, array<mixed>|string>
     */
    private static function parseQueryParams(string $url): array
    {
        $queryStr = parse_url($url, PHP_URL_QUERY);
        self::assertIsString($queryStr);
        parse_str($queryStr, $query);

        /** @var array<string, array<mixed>|string> $query */
        return $query;
    }

    public function testTrackingWithCookieSetsCorrectUrl(): void
    {
        $testVisitorId = substr(md5('testuuid'), 0, 16);
        $this->assertEquals(16, strlen($testVisitorId));

        $createTs = strtotime('2020-03-04 03:04:05');

        $cookieName = '_pk_id_1_f609';
        $_COOKIE[$cookieName] = $testVisitorId . '.' . $createTs;

        $tracker = new \MatomoTracker(1, self::TEST_URL);
        $tracker->setUrl('http://somesite.com');
        $url = $tracker->getUrlTrackPageView('test title');
        $url = (string) preg_replace('/&r=\d+/', "", $url);

        $query = self::parseQueryParams($url);

        $this->assertEquals($testVisitorId, $query['_id']);
        $this->assertEquals($createTs, $query['_idts']);

        $expected = 'http://mymatomo.com/matomo.php?idsite=1&rec=1&apiv=1&_idts=1583291045&_id=0958f111f2588a1b&url=http%3A%2F%2Fsomesite.com&urlref=&action_name=test+title';
        $this->assertEquals($expected, $url);
    }

    public function testTrackingWithPreMatomo4CookieSetsCorrectUrl(): void
    {
        $testVisitorId = substr(md5('testother'), 0, 16);
        $this->assertEquals(16, strlen($testVisitorId));

        $createTs = strtotime('2020-03-04 05:04:05');
        $currentTs = strtotime('2020-03-05 05:04:05');
        $lastVisitTs = strtotime('2020-03-06 05:04:05');
        $ecommerceLastOrderTs =  strtotime('2020-03-06 06:04:05');

        $cookieName = '_pk_id_1_f609';
        $_COOKIE[$cookieName] = $testVisitorId . '.' . $createTs . '.5.' . $currentTs . '.' . $lastVisitTs . '.' . $ecommerceLastOrderTs;

        $tracker = new \MatomoTracker(1, self::TEST_URL);
        $tracker->setUrl('http://somesite.com');
        $url = $tracker->getUrlTrackPageView('test title');
        $url = (string) preg_replace('/&r=\d+/', "", $url);

        $query = self::parseQueryParams($url);

        $this->assertEquals($testVisitorId, $query['_id']);
        $this->assertEquals($createTs, $query['_idts']);

        $expected = 'http://mymatomo.com/matomo.php?idsite=1&rec=1&apiv=1&_idts=1583298245&_id=b446c233274f79f0&url=http%3A%2F%2Fsomesite.com&urlref=&action_name=test+title';
        $this->assertEquals($expected, $url);
    }

    public function testTrackingWithNumericCookieNameDoesNotFail(): void
    {
        // numeric cookie names are exposed as integer keys in $_COOKIE
        $_COOKIE[12345] = 'some-value';
        $_COOKIE['_pk_cvar_1_f609'] = '{"1":["name","value"]}';

        $tracker = new \MatomoTracker(1, self::TEST_URL);

        $this->assertSame(['name', 'value'], $tracker->getCustomVariable(1));
    }

    public function testSetApiUrl(): void
    {
        $newApiUrl = 'https://NEW-API-URL.com';
        $tracker = new \MatomoTracker(1, self::TEST_URL);
        $tracker->setApiUrl($newApiUrl);
        $url = $tracker->getUrlTrackPageView('test title');

        $this->assertSame(substr($url, 0, strlen($newApiUrl)), $newApiUrl);
    }

    public function testUsageApiUrl(): void
    {
        $newApiUrl = 'https://NEW-API-URL.com';
        $tracker = new \MatomoTracker(1, $newApiUrl);
        $url = $tracker->getUrlTrackPageView('test title');

        $this->assertSame(substr($url, 0, strlen($newApiUrl)), $newApiUrl);
    }

    public function testGetBaseUrlThrowsWhenNoUrlConfigured(): void
    {
        $tracker = new TestableMatomoTracker(1);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('You must first set the Matomo Tracker URL');

        $tracker->callGetBaseUrl();
    }

    public function testGetBaseUrlAppendsMatomoPhp(): void
    {
        $tracker = new TestableMatomoTracker(1, 'http://example.org/matomo/');
        $this->assertSame('http://example.org/matomo/matomo.php', $tracker->callGetBaseUrl());

        $tracker = new TestableMatomoTracker(1, 'http://example.org/matomo.php');
        $this->assertSame('http://example.org/matomo.php', $tracker->callGetBaseUrl());

        $tracker = new TestableMatomoTracker(1, 'http://example.org/proxy-matomo.php');
        $this->assertSame('http://example.org/proxy-matomo.php', $tracker->callGetBaseUrl());
    }

    /**
     * @dataProvider getTestDataForIsUserAgentAIBot
     */
    public function testIsUserAgentAIBot(string $userAgent, bool $expected): void
    {
        $this->assertSame($expected, \MatomoTracker::isUserAgentAIBot($userAgent));
    }

    /**
     * @return list<array{string, bool}>
     */
    public static function getTestDataForIsUserAgentAIBot(): array
    {
        return [
            ['', false],

            ['Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.3', false],
            ['Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Mobile Safari/537.3', false],

            ['Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko); compatible; ChatGPT-User/1.0; +https://openai.com/bot', true],
            ['Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko); compatible; GPTBot/1.1; +https://openai.com/gptbot', false],
            ['Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; MistralAI-User/1.0; +https://docs.mistral.ai/robots)', true],
            ['Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Gemini-Deep-Research; +https://gemini.google/overview/deep-research/) Chrome/135.0.0.0 Safari/537.36', true],
            ['Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Claude-User/1.0; +Claude-User@anthropic.com)', true],
            ['Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Perplexity-User/1.0; +https://perplexity.ai/perplexity-user)', true],
            ['Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 (compatible; Google-GeminiNotebook; +https://developers.google.com/crawling/docs/crawlers-fetchers/google-gemininotebook)', true],
            ['Google-NotebookLM/1.0', true],
            ['Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36; Devin/1.0; +devin.ai', false],
        ];
    }

    public function testIsUserAgentAIBotWithNull(): void
    {
        $this->assertFalse(\MatomoTracker::isUserAgentAIBot(null));
    }

    /**
     * @dataProvider getTestDataForGetUrlTrackAIBot
     */
    public function testGetUrlTrackAIBot(?int $httpStatus, ?int $responseSizeBytes, ?int $serverTimeMs, ?string $source, string $expected): void
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko); compatible; ChatGPT-User/1.0; +https://openai.com/bot';

        $tracker = new \MatomoTracker(1, self::TEST_URL);
        $tracker->setUrl('https://example.com/page');
        $tracker->setVisitorId('abcdef01234517ab');

        $actual = $tracker->getUrlTrackAIBot($httpStatus, $responseSizeBytes, $serverTimeMs, $source);
        $actual = $this->normalizeTrackingUrl($actual);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return list<array{?int, ?int, ?int, ?string, string}>
     */
    public static function getTestDataForGetUrlTrackAIBot(): array
    {
        return [
            [
                200,
                34567,
                123,
                'wordpress',
                'http://mymatomo.com/matomo.php?idsite=1&rec=1&apiv=1&r=&r=&cid=abcdef01234517ab&url=https%3A%2F%2Fexample.com%2Fpage&urlref=&recMode=1&http_status=200&bw_bytes=34567&pf_srv=123&source=wordpress',
            ],

            [
                null,
                34567,
                null,
                'something else',
                'http://mymatomo.com/matomo.php?idsite=1&rec=1&apiv=1&r=&r=&cid=abcdef01234517ab&url=https%3A%2F%2Fexample.com%2Fpage&urlref=&recMode=1&bw_bytes=34567&source=something%20else',
            ],

            [
                null,
                null,
                null,
                null,
                'http://mymatomo.com/matomo.php?idsite=1&rec=1&apiv=1&r=&r=&cid=abcdef01234517ab&url=https%3A%2F%2Fexample.com%2Fpage&urlref=&recMode=1',
            ],
        ];
    }

    public function testDoTrackPageViewIfAIBotWithRegularUserAgentReturnsNull(): void
    {
        $tracker = $this->createTracker();
        $tracker->setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64)');

        $this->assertNull($tracker->doTrackPageViewIfAIBot(200));
        $this->assertSame([], $tracker->capturedRequests);
    }

    public function testDoTrackPageViewIfAIBotWithBotUserAgentTracks(): void
    {
        $tracker = $this->createTracker();
        $tracker->setUserAgent('compatible; ChatGPT-User/1.0; +https://openai.com/bot');

        $response = $tracker->doTrackPageViewIfAIBot(200, 1024, 55, 'wordpress');

        $this->assertSame('mock-response', $response);
        $query = self::parseQueryParams($tracker->lastRequestUrl());
        $this->assertSame('1', $query['recMode']);
        $this->assertSame('200', $query['http_status']);
        $this->assertSame('1024', $query['bw_bytes']);
        $this->assertSame('55', $query['pf_srv']);
        $this->assertSame('wordpress', $query['source']);
    }

    private function normalizeTrackingUrl(string $url): string
    {
        $nonDeterministicParams = [
            'r',
            '_idts',
        ];

        foreach ($nonDeterministicParams as $param) {
            $url = (string) preg_replace('/&' . preg_quote($param, '/') . '=[^&]+/', '&r=', $url);
        }

        return $url;
    }

    public function testDoTrackPageViewGeneratesNewPageviewId(): void
    {
        $tracker = $this->createTracker();
        $tracker->doTrackPageView('page one');
        $firstId = $tracker->getPageviewId();

        $tracker->doTrackPageView('page two');
        $secondId = $tracker->getPageviewId();

        $this->assertNotNull($firstId);
        $this->assertNotNull($secondId);
        $this->assertSame(6, strlen($firstId));
        $this->assertNotSame($firstId, $secondId);

        $query = self::parseQueryParams($tracker->lastRequestUrl());
        $this->assertSame($secondId, $query['pv_id']);
        $this->assertSame('page two', $query['action_name']);
    }

    public function testSetPageviewIdIsKeptAcrossPageViews(): void
    {
        $tracker = $this->createTracker();
        $tracker->setPageviewId('custom');
        $tracker->doTrackPageView('page one');
        $tracker->doTrackPageView('page two');

        $this->assertSame('custom', $tracker->getPageviewId());
    }

    public function testGetUrlTrackPageViewWithoutTitle(): void
    {
        $tracker = $this->createTracker();
        $url = $tracker->getUrlTrackPageView();

        $this->assertStringNotContainsString('action_name', $url);
    }

    public function testGetUrlTrackEventRequiresCategoryAndAction(): void
    {
        $tracker = $this->createTracker();

        try {
            $tracker->getUrlTrackEvent('', 'action');
            $this->fail('Expected exception for empty category');
        } catch (Exception $e) {
            $this->assertStringContainsString('Category', $e->getMessage());
        }

        $this->expectException(Exception::class);
        $tracker->getUrlTrackEvent('category', '');
    }

    public function testGetUrlTrackEventDefaultsOmitNameAndValue(): void
    {
        $tracker = $this->createTracker();
        $url = $tracker->getUrlTrackEvent('cat', 'act');

        $this->assertStringContainsString('&e_c=cat', $url);
        $this->assertStringContainsString('&e_a=act', $url);
        $this->assertStringContainsString('&ca=1', $url);
        $this->assertStringNotContainsString('&e_n=', $url);
        $this->assertStringNotContainsString('&e_v=', $url);

        // a plain page view is not a custom action
        $this->assertStringNotContainsString('&ca=1', $tracker->getUrlTrackPageView('title'));
    }

    public function testGetUrlTrackEventWithNameAndValues(): void
    {
        $tracker = $this->createTracker();

        $url = $tracker->getUrlTrackEvent('cat', 'act', 'name', 0);
        $this->assertStringContainsString('&e_n=name', $url);
        $this->assertStringContainsString('&e_v=0', $url);

        $url = $tracker->getUrlTrackEvent('cat', 'act', 'name', 3.5);
        $this->assertStringContainsString('&e_v=3.5', $url);

        // an empty name is not sent
        $url = $tracker->getUrlTrackEvent('cat', 'act', '', 1);
        $this->assertStringNotContainsString('&e_n=', $url);
    }

    public function testDoTrackEventSendsRequest(): void
    {
        $tracker = $this->createTracker();
        $response = $tracker->doTrackEvent('cat', 'act', 'name', 2);

        $this->assertSame('mock-response', $response);
        $this->assertCount(1, $tracker->capturedRequests);
    }

    public function testGetUrlTrackContentImpression(): void
    {
        $tracker = $this->createTracker();

        $url = $tracker->getUrlTrackContentImpression('name', 'piece', 'http://target.example');
        $query = self::parseQueryParams($url);
        $this->assertSame('name', $query['c_n']);
        $this->assertSame('piece', $query['c_p']);
        $this->assertSame('http://target.example', $query['c_t']);
        $this->assertSame('1', $query['ca']);

        $url = $tracker->getUrlTrackContentImpression('name', '', null);
        $this->assertStringNotContainsString('&c_p=', $url);
        $this->assertStringNotContainsString('&c_t=', $url);

        $this->expectException(Exception::class);
        $tracker->getUrlTrackContentImpression('', 'piece', null);
    }

    public function testGetUrlTrackContentInteraction(): void
    {
        $tracker = $this->createTracker();

        $url = $tracker->getUrlTrackContentInteraction('click', 'name', 'piece', 'http://target.example');
        $query = self::parseQueryParams($url);
        $this->assertSame('click', $query['c_i']);
        $this->assertSame('name', $query['c_n']);
        $this->assertSame('piece', $query['c_p']);
        $this->assertSame('http://target.example', $query['c_t']);
        $this->assertSame('1', $query['ca']);

        $url = $tracker->getUrlTrackContentInteraction('click', 'name', '', null);
        $this->assertStringNotContainsString('&c_p=', $url);
        $this->assertStringNotContainsString('&c_t=', $url);
    }

    public function testGetUrlTrackContentInteractionRequiresInteractionAndName(): void
    {
        $tracker = $this->createTracker();

        try {
            $tracker->getUrlTrackContentInteraction('', 'name', 'piece', null);
            $this->fail('Expected exception for empty interaction');
        } catch (Exception $e) {
            $this->assertStringContainsString('interaction', $e->getMessage());
        }

        $this->expectException(Exception::class);
        $tracker->getUrlTrackContentInteraction('click', '', 'piece', null);
    }

    public function testDoTrackContentImpressionAndInteractionSendRequests(): void
    {
        $tracker = $this->createTracker();
        $tracker->doTrackContentImpression('name');
        $tracker->doTrackContentInteraction('click', 'name');

        $this->assertCount(2, $tracker->capturedRequests);
    }

    public function testGetUrlTrackSiteSearchOmitsCountByDefault(): void
    {
        $tracker = $this->createTracker();

        $url = $tracker->getUrlTrackSiteSearch('keyword', '');
        $this->assertStringContainsString('&search=keyword', $url);
        $this->assertStringNotContainsString('&search_cat=', $url);
        $this->assertStringNotContainsString('&search_count=', $url);
    }

    public function testGetUrlTrackSiteSearchWithCategoryAndZeroCount(): void
    {
        $tracker = $this->createTracker();

        $url = $tracker->getUrlTrackSiteSearch('keyword', 'category', 0);
        $this->assertStringContainsString('&search_cat=category', $url);
        $this->assertStringContainsString('&search_count=0', $url);

        $url = $tracker->getUrlTrackSiteSearch('keyword', '', 12);
        $this->assertStringContainsString('&search_count=12', $url);
    }

    public function testDoTrackSiteSearchSendsRequest(): void
    {
        $tracker = $this->createTracker();
        $tracker->doTrackSiteSearch('keyword');

        $this->assertStringContainsString('&search=keyword', $tracker->lastRequestUrl());
    }

    public function testGetUrlTrackGoal(): void
    {
        $tracker = $this->createTracker();

        $url = $tracker->getUrlTrackGoal(42);
        $this->assertStringContainsString('&idgoal=42', $url);
        $this->assertStringNotContainsString('&revenue=', $url);

        $url = $tracker->getUrlTrackGoal(42, 3.5);
        $this->assertStringContainsString('&revenue=3.5', $url);
    }

    public function testDoTrackGoalSendsRequest(): void
    {
        $tracker = $this->createTracker();
        $tracker->doTrackGoal(7, 1.25);

        $this->assertStringContainsString('&idgoal=7', $tracker->lastRequestUrl());
    }

    public function testGetUrlTrackActionAndDoTrackAction(): void
    {
        $tracker = $this->createTracker();

        $url = $tracker->getUrlTrackAction('http://example.org/file.zip', 'download');
        $this->assertStringContainsString('&download=' . urlencode('http://example.org/file.zip'), $url);

        $tracker->doTrackAction('http://example.org/out', 'link');
        $this->assertStringContainsString('&link=' . urlencode('http://example.org/out'), $tracker->lastRequestUrl());
    }

    public function testGetUrlTrackActionEncodesTheActionType(): void
    {
        $tracker = $this->createTracker();

        // A crafted action type must be URL-encoded into a single parameter name and must not be
        // able to inject an additional query-string parameter of its own.
        $url = $tracker->getUrlTrackAction('http://example.org/file.zip', 'download&extra=1');

        $this->assertStringContainsString('&' . urlencode('download&extra=1') . '=', $url);
        $this->assertStringNotContainsString('&extra=1', $url);
    }

    /**
     * @return \MatomoTracker a tracker that always uses the stream transport (no cURL)
     */
    private function createStreamTracker(string $apiUrl): \MatomoTracker
    {
        $tracker = new class (1, $apiUrl) extends \MatomoTracker {
            protected function hasCurlSupport(): bool
            {
                return false;
            }
        };
        $tracker->setUrl('http://somesite.com');

        return $tracker;
    }

    public function testStreamTransportThrowsHostOnlyMessageOnFailure(): void
    {
        // Port 1 on loopback refuses the connection immediately, so the stream transport fails fast.
        $tracker = $this->createStreamTracker('http://127.0.0.1:1/matomo.php');
        $tracker->setTokenAuth(str_repeat('a', 32));

        try {
            $tracker->doTrackPageView('secret title');
            $this->fail('Expected a RuntimeException from the failing stream request.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('127.0.0.1', $e->getMessage());
            // The query string (which carries token_auth and other PII) must never leak into the message.
            $this->assertStringNotContainsString('token_auth', $e->getMessage());
            $this->assertStringNotContainsString('action_name', $e->getMessage());
        }
    }

    public function testStreamTransportFailSafeReturnsFalseWhenExceptionsDisabled(): void
    {
        $tracker = $this->createStreamTracker('http://127.0.0.1:1/matomo.php');
        $tracker->setExceptionsEnabled(false);

        $this->assertFalse($tracker->doTrackPageView('some title'));
    }

    public function testGetUrlTrackCrash(): void
    {
        $tracker = $this->createTracker();

        $url = $tracker->getUrlTrackCrash('message', 'TypeError', 'category', 'stack', 'http://loc.example', 10, 20);
        $query = self::parseQueryParams($url);
        $this->assertSame('1', $query['ca']);
        $this->assertSame('message', $query['cra']);
        $this->assertSame('TypeError', $query['cra_tp']);
        $this->assertSame('category', $query['cra_ct']);
        $this->assertSame('stack', $query['cra_st']);
        $this->assertSame('http://loc.example', $query['cra_ru']);
        $this->assertSame('10', $query['cra_rl']);
        $this->assertSame('20', $query['cra_rc']);

        $url = $tracker->getUrlTrackCrash('message');
        $this->assertStringNotContainsString('&cra_tp=', $url);
        $this->assertStringNotContainsString('&cra_ct=', $url);
        $this->assertStringNotContainsString('&cra_st=', $url);
        $this->assertStringNotContainsString('&cra_ru=', $url);
        $this->assertStringNotContainsString('&cra_rl=', $url);
        $this->assertStringNotContainsString('&cra_rc=', $url);
    }

    public function testDoTrackCrashAndPhpThrowable(): void
    {
        $tracker = $this->createTracker();

        $tracker->doTrackCrash('crashed');
        $this->assertStringContainsString('&cra=crashed', $tracker->lastRequestUrl());

        $throwable = new \RuntimeException('something broke');
        $tracker->doTrackPhpThrowable($throwable, 'category');

        $query = self::parseQueryParams($tracker->lastRequestUrl());
        $this->assertSame('something broke', $query['cra']);
        $this->assertSame('RuntimeException', $query['cra_tp']);
        $this->assertSame('category', $query['cra_ct']);
        $this->assertSame(__FILE__, $query['cra_ru']);
    }

    public function testDoPing(): void
    {
        $tracker = $this->createTracker();
        $tracker->doPing();

        $this->assertStringContainsString('&ping=1', $tracker->lastRequestUrl());
    }

    public function testAddEcommerceItemRequiresSku(): void
    {
        $tracker = $this->createTracker();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('You must specify a SKU');

        $tracker->addEcommerceItem('');
    }

    public function testEcommerceOrderWithItems(): void
    {
        $tracker = $this->createTracker();
        $tracker->addEcommerceItem('SKU1', 'Product 1', ['cat1', 'cat2'], '9,99', 2);
        $tracker->addEcommerceItem('SKU2');

        $tracker->doTrackEcommerceOrder('order-1', 20.5, 18.0, 1.5, 0.5, 0.25);

        $query = self::parseQueryParams($tracker->lastRequestUrl());
        $this->assertSame('0', $query['idgoal']);
        $this->assertSame('order-1', $query['ec_id']);
        $this->assertSame('20.5', $query['revenue']);
        $this->assertSame('18', $query['ec_st']);
        $this->assertSame('1.5', $query['ec_tx']);
        $this->assertSame('0.5', $query['ec_sh']);
        $this->assertSame('0.25', $query['ec_dt']);

        $this->assertIsString($query['ec_items']);
        $items = json_decode($query['ec_items'], true);
        $this->assertSame([
            ['SKU1', 'Product 1', ['cat1', 'cat2'], '9.99', 2],
            ['SKU2', '', '', '0', 1],
        ], $items);

        // items are cleared after the order was tracked
        $this->assertSame([], $tracker->ecommerceItems);
    }

    public function testGetUrlTrackEcommerceOrderRequiresOrderId(): void
    {
        $tracker = $this->createTracker();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('orderId');

        $tracker->getUrlTrackEcommerceOrder('', 10.0);
    }

    public function testEcommerceOrderAcceptsIntegerOrderId(): void
    {
        $tracker = $this->createTracker();
        $url = $tracker->getUrlTrackEcommerceOrder(12345, 10.0);

        $this->assertStringContainsString('&ec_id=12345', $url);
    }

    public function testDoTrackEcommerceCartUpdate(): void
    {
        $tracker = $this->createTracker();
        $tracker->addEcommerceItem('SKU1');
        $tracker->doTrackEcommerceCartUpdate(10.0);

        $url = $tracker->lastRequestUrl();
        $this->assertStringContainsString('&idgoal=0', $url);
        $this->assertStringContainsString('&revenue=10', $url);
        $this->assertStringNotContainsString('&ec_id=', $url);
    }

    public function testGetUrlTrackEcommerceCartUpdateWithZeroTotal(): void
    {
        $tracker = $this->createTracker();
        $url = $tracker->getUrlTrackEcommerceCartUpdate(0.0);

        // grandTotal is required, so an explicit zero total is sent as revenue=0
        $this->assertStringContainsString('&idgoal=0', $url);
        $this->assertStringContainsString('&revenue=0', $url);
    }

    public function testGoalRevenueOmittedByDefaultButZeroIsSent(): void
    {
        $tracker = $this->createTracker();

        // no revenue argument -> revenue omitted (Matomo uses the goal's configured revenue)
        $this->assertStringNotContainsString('&revenue=', $tracker->getUrlTrackGoal(1));

        // explicit 0.0 -> revenue=0 is sent (distinct from "unset")
        $this->assertStringContainsString('&revenue=0', $tracker->getUrlTrackGoal(1, 0.0));

        // a real value is sent as-is
        $this->assertStringContainsString('&revenue=12.5', $tracker->getUrlTrackGoal(1, 12.5));
    }

    public function testEcommerceOptionalAmountsOmittedByDefaultButZeroIsSent(): void
    {
        $tracker = $this->createTracker();

        // subtotal/tax/shipping/discount omitted when not provided
        $url = $tracker->getUrlTrackEcommerceOrder('order-1', 10.0);
        $this->assertStringNotContainsString('&ec_st=', $url);
        $this->assertStringNotContainsString('&ec_tx=', $url);

        // explicit zeros are sent
        $url = $tracker->getUrlTrackEcommerceOrder('order-2', 10.0, 0.0, 0.0, 0.0, 0.0);
        $this->assertStringContainsString('&ec_st=0', $url);
        $this->assertStringContainsString('&ec_tx=0', $url);
        $this->assertStringContainsString('&ec_sh=0', $url);
        $this->assertStringContainsString('&ec_dt=0', $url);
    }

    public function testSetEcommerceView(): void
    {
        $tracker = $this->createTracker();

        $tracker->setEcommerceView('SKU1', 'Product', 'category', 9.99);
        $this->assertSame(
            ['_pkc' => 'category', '_pkp' => '9.99', '_pks' => 'SKU1', '_pkn' => 'Product'],
            $tracker->ecommerceView
        );

        $tracker->setEcommerceView('SKU1', 'Product', ['cat1', 'cat2']);
        $this->assertSame('["cat1","cat2"]', $tracker->ecommerceView['_pkc']);

        // category-only page: product sku/name are not recorded
        $tracker->setEcommerceView('', '', 'category');
        $this->assertSame(['_pkc' => 'category'], $tracker->ecommerceView);

        // ecommerce view parameters end up in the tracking URL and are reset afterwards
        $tracker->setEcommerceView('SKU1', 'Product', 'category');
        $url = $tracker->getUrlTrackPageView('title');
        $this->assertStringContainsString('&_pkc=category', $url);
        $this->assertStringContainsString('&_pks=SKU1', $url);
        $this->assertStringContainsString('&_pkn=Product', $url);
        $this->assertSame([], $tracker->ecommerceView);
    }

    public function testSetAttributionInfo(): void
    {
        $tracker = $this->createTracker();
        $tracker->setAttributionInfo('["campaign","keyword",1234,"http://referrer.example"]');

        $this->assertSame('["campaign","keyword",1234,"http:\/\/referrer.example"]', $tracker->getAttributionInfo());

        $query = self::parseQueryParams($tracker->getUrlTrackPageView('title'));
        $this->assertSame('campaign', $query['_rcn']);
        $this->assertSame('keyword', $query['_rck']);
        $this->assertSame('1234', $query['_refts']);
        $this->assertSame('http://referrer.example', $query['_ref']);
    }

    public function testUrlValuesAreEncodedAgainstInjection(): void
    {
        $tracker = $this->createTracker();

        // _refts comes from (attacker-controlled) attribution JSON and must be encoded
        $tracker->setAttributionInfo('["c","k","1&new_visit=1&cid=deadbeefdeadbeef","r"]');
        $tracker->customData = 'x&idsite=999';
        $tracker->setPageCharset('utf-8&foo=bar');

        $url = $tracker->getUrlTrackPageView('title');
        $query = self::parseQueryParams($url);

        // injected params must land inside the encoded value, not as separate parameters
        $this->assertSame('1&new_visit=1&cid=deadbeefdeadbeef', $query['_refts']);
        $this->assertSame('x&idsite=999', $query['data']);
        $this->assertSame('utf-8&foo=bar', $query['cs']);
        $this->assertArrayNotHasKey('new_visit', $query);
        $this->assertSame('1', $query['idsite']); // built-in idsite is untouched
        $this->assertArrayNotHasKey('foo', $query);
    }

    public function testSetAttributionInfoThrowsOnInvalidJsonWithoutLeakingPayload(): void
    {
        $tracker = $this->createTracker();
        $payload = 'not-json-with-secret@example.com';

        try {
            $tracker->setAttributionInfo($payload);
            $this->fail('Expected an exception');
        } catch (Exception $e) {
            $this->assertStringContainsString('JSON encoded string', $e->getMessage());
            // the (potentially PII-bearing) payload must not appear in the message
            $this->assertStringNotContainsString($payload, $e->getMessage());
        }
    }

    public function testGetAttributionInfoFromCookie(): void
    {
        $_COOKIE['_pk_ref_1_f609'] = '["campaign","keyword"]';

        $tracker = $this->createTracker();
        $this->assertSame('["campaign","keyword"]', $tracker->getAttributionInfo());
    }

    public function testGetAttributionInfoWithoutCookieReturnsFalse(): void
    {
        $tracker = $this->createTracker();
        $this->assertFalse($tracker->getAttributionInfo());
    }

    public function testCustomVariables(): void
    {
        $tracker = $this->createTracker();

        $tracker->setCustomVariable(1, 'visit-name', 'visit-value');
        $tracker->setCustomVariable(1, 'page-name', 'page-value', 'page');
        $tracker->setCustomVariable(1, 'event-name', 'event-value', 'event');

        $this->assertSame(['visit-name', 'visit-value'], $tracker->getCustomVariable(1));
        $this->assertSame(['page-name', 'page-value'], $tracker->getCustomVariable(1, 'page'));
        $this->assertSame(['event-name', 'event-value'], $tracker->getCustomVariable(1, 'event'));
        $this->assertFalse($tracker->getCustomVariable(2, 'page'));
        $this->assertFalse($tracker->getCustomVariable(2, 'event'));
        $this->assertFalse($tracker->getCustomVariable(2));

        $query = self::parseQueryParams($tracker->getUrlTrackPageView('title'));
        $this->assertSame('{"1":["visit-name","visit-value"]}', $query['_cvar']);
        $this->assertSame('{"1":["page-name","page-value"]}', $query['cvar']);
        $this->assertSame('{"1":["event-name","event-value"]}', $query['e_cvar']);

        // page and event scoped variables are reset after the request, visit scope is kept
        $url = $tracker->getUrlTrackPageView('title');
        $this->assertStringNotContainsString('&cvar=', $url);
        $this->assertStringNotContainsString('&e_cvar=', $url);
        $this->assertStringContainsString('&_cvar=', $url);

        $tracker->clearCustomVariables();
        $this->assertFalse($tracker->getCustomVariable(1));
    }

    public function testSetCustomVariableThrowsOnInvalidScope(): void
    {
        $tracker = $this->createTracker();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Invalid 'scope' parameter value");

        $tracker->setCustomVariable(1, 'name', 'value', 'invalid');
    }

    public function testGetCustomVariableThrowsOnInvalidScope(): void
    {
        $tracker = $this->createTracker();

        $this->expectException(Exception::class);

        $tracker->getCustomVariable(1, 'invalid');
    }

    public function testGetCustomVariableFromCookie(): void
    {
        $_COOKIE['_pk_cvar_1_f609'] = '{"2":["cookie-name","cookie-value"]}';

        $tracker = $this->createTracker();
        $this->assertSame(['cookie-name', 'cookie-value'], $tracker->getCustomVariable(2));
        $this->assertFalse($tracker->getCustomVariable(3));
    }

    /**
     * @dataProvider getTestDataForCustomVariablesFromCookie
     * @param array<int, array{0: string, 1: string}> $expected
     */
    public function testGetCustomVariablesFromCookieFiltersInvalidData(string $cookieValue, array $expected): void
    {
        $_COOKIE['_pk_cvar_1_f609'] = $cookieValue;

        $tracker = $this->createTracker();
        $this->assertSame($expected, $tracker->callGetCustomVariablesFromCookie());
    }

    /**
     * @return list<array{string, array<int, array{0: string, 1: string}>}>
     */
    public static function getTestDataForCustomVariablesFromCookie(): array
    {
        return [
            ['', []],
            ['not-json', []],
            ['"a string"', []],
            ['{"1":"not-a-pair"}', []],
            ['{"1":["only-one"]}', []],
            ['{"1":["name","value"],"2":"broken"}', [1 => ['name', 'value']]],
            ['{"1":["name",5]}', [1 => ['name', '5']]],
        ];
    }

    public function testCustomDimensions(): void
    {
        $tracker = $this->createTracker();
        $tracker->setCustomDimension(2, 'value');

        $this->assertSame('value', $tracker->getCustomDimension(2));
        $this->assertNull($tracker->getCustomDimension(3));

        $url = $tracker->getUrlTrackPageView('title');
        $this->assertStringContainsString('&dimension2=value', $url);

        // dimensions are reset after a request
        $this->assertNull($tracker->getCustomDimension(2));

        $tracker->setCustomDimension(2, 'value');
        $tracker->clearCustomDimensions();
        $this->assertNull($tracker->getCustomDimension(2));
    }

    public function testCustomTrackingParameters(): void
    {
        $tracker = $this->createTracker();
        $tracker->setCustomTrackingParameter('bw_bytes', '1024');

        $url = $tracker->getUrlTrackPageView('title');
        $this->assertStringContainsString('&bw_bytes=1024', $url);

        // custom parameters are reset after a request
        $url = $tracker->getUrlTrackPageView('title');
        $this->assertStringNotContainsString('&bw_bytes=', $url);

        // dimensionX parameters are mapped to custom dimensions
        $tracker->setCustomTrackingParameter('dimension3', 'dim-value');
        $this->assertSame('dim-value', $tracker->getCustomDimension(3));

        $tracker->setCustomTrackingParameter('bw_bytes', '1024');
        $tracker->clearCustomTrackingParameters();
        $url = $tracker->getUrlTrackPageView('title');
        $this->assertStringNotContainsString('&bw_bytes=', $url);
    }

    public function testCustomTrackingParameterAcceptsArrayValue(): void
    {
        $tracker = $this->createTracker();
        // array values are serialized like the JS tracker does, via http_build_query
        $tracker->setCustomTrackingParameter('forms', [['name' => 'a'], ['name' => 'b']]);

        $url = $tracker->getUrlTrackPageView('title');
        $this->assertStringContainsString('forms%5B0%5D%5Bname%5D=a', $url);
        $this->assertStringContainsString('forms%5B1%5D%5Bname%5D=b', $url);
    }

    public function testSetDebugTrackingParameterOverridesBuiltInParameter(): void
    {
        $tracker = $this->createTracker();
        // inject an intentionally invalid idsite to exercise server-side validation
        $tracker->setDebugTrackingParameter('idsite', 'not-a-number');
        $tracker->setDebugTrackingParameter('_cvar', '{"1":[["bad"],"v"]}');

        $url = $tracker->getUrlTrackPageView('title');
        // appended last so it wins over the built-in idsite=1
        $this->assertStringContainsString('&idsite=not-a-number', $url);
        $this->assertStringContainsString('&_cvar=' . urlencode('{"1":[["bad"],"v"]}'), $url);
        $query = self::parseQueryParams($url);
        $this->assertSame('not-a-number', $query['idsite']);

        // debug parameters are cleared after a request
        $this->assertStringNotContainsString('not-a-number', $tracker->getUrlTrackPageView('title'));
    }

    public function testVisitorIdHandling(): void
    {
        $tracker = $this->createTracker();

        $randomId = $tracker->getVisitorId();
        $this->assertSame(16, strlen($randomId));

        $tracker->setVisitorId('abcdef0123456789');
        $this->assertSame('abcdef0123456789', $tracker->getVisitorId());

        $tracker->setNewVisitorId();
        $newId = $tracker->getVisitorId();
        $this->assertSame(16, strlen($newId));
        $this->assertNotSame('abcdef0123456789', $newId);
    }

    public function testSetVisitorIdThrowsOnInvalidValue(): void
    {
        $tracker = $this->createTracker();

        try {
            $tracker->setVisitorId('too-short');
            $this->fail('Expected exception for invalid length');
        } catch (Exception $e) {
            $this->assertStringContainsString('16', $e->getMessage());
        }

        $this->expectException(Exception::class);
        $tracker->setVisitorId('zzzzzzzzzzzzzzzz');
    }

    public function testForcedVisitorIdIsUsedAsCid(): void
    {
        $tracker = $this->createTracker();
        $tracker->setVisitorId('abcdef0123456789');

        $query = self::parseQueryParams($tracker->getUrlTrackPageView('title'));
        $this->assertSame('abcdef0123456789', $query['cid']);
        $this->assertArrayNotHasKey('_id', $query);
    }

    public function testLoadVisitorIdCookie(): void
    {
        $tracker = $this->createTracker();
        $this->assertFalse($tracker->callLoadVisitorIdCookie());

        $_COOKIE['_pk_id_1_f609'] = 'too-short.123';
        $this->assertFalse($tracker->callLoadVisitorIdCookie());

        // a 16-char but non-hex id (e.g. containing injection chars) is rejected
        $_COOKIE['_pk_id_1_f609'] = '&x=1&y=2&z=3&w=4.1';
        $this->assertFalse($tracker->callLoadVisitorIdCookie());

        $_COOKIE['_pk_id_1_f609'] = 'abcdef0123456789.1583291045';
        $this->assertTrue($tracker->callLoadVisitorIdCookie());
        $this->assertSame('abcdef0123456789', $tracker->getVisitorId());
        $this->assertSame(1583291045, $tracker->createTs);
    }

    public function testLoadVisitorIdCookieWithoutCreationTsKeepsCurrentOne(): void
    {
        $tracker = $this->createTracker();
        $createTsBefore = $tracker->createTs;

        $_COOKIE['_pk_id_1_f609'] = 'abcdef0123456789';
        $this->assertTrue($tracker->callLoadVisitorIdCookie());
        $this->assertSame($createTsBefore, $tracker->createTs);
    }

    public function testUserIdHandling(): void
    {
        $tracker = $this->createTracker();
        $this->assertNull($tracker->getUserId());

        $tracker->setUserId('user@example.org');
        $this->assertSame('user@example.org', $tracker->getUserId());

        $query = self::parseQueryParams($tracker->getUrlTrackPageView('title'));
        $this->assertSame('user@example.org', $query['uid']);

        // null de-assigns a previously set user id
        $tracker->setUserId(null);
        $this->assertNull($tracker->getUserId());
        $url = $tracker->getUrlTrackPageView('title');
        $this->assertStringNotContainsString('&uid=', $url);
    }

    public function testSetUserIdThrowsOnEmptyString(): void
    {
        $tracker = $this->createTracker();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User ID cannot be empty');

        $tracker->setUserId('');
    }

    public function testGetUserIdHashed(): void
    {
        $this->assertSame(substr(sha1('user@example.org'), 0, 16), \MatomoTracker::getUserIdHashed('user@example.org'));
    }

    public function testUserAgentAndBrowserLanguage(): void
    {
        $tracker = $this->createTracker();
        $this->assertNull($tracker->getUserAgent());

        $tracker->setUserAgent('My Agent');
        $this->assertSame('My Agent', $tracker->getUserAgent());

        $tracker->setBrowserLanguage('de-de');

        $options = $tracker->callPrepareCurlOptions('http://example.org', 'GET', null, false);
        $this->assertSame('My Agent', $options[CURLOPT_USERAGENT]);
        $this->assertSame(['Accept-Language: de-de'], $options[CURLOPT_HTTPHEADER]);
    }

    public function testIpHandling(): void
    {
        $tracker = $this->createTracker();
        $this->assertNull($tracker->getIp());

        $tracker->setIp('130.54.2.1');
        $this->assertSame('130.54.2.1', $tracker->getIp());

        // cip is only added when a token_auth is set
        $url = $tracker->getUrlTrackPageView('title');
        $this->assertStringNotContainsString('&cip=', $url);

        $tracker->setTokenAuth('0123456789abcdef0123456789abcdef');
        $url = $tracker->getUrlTrackPageView('title');
        $this->assertStringContainsString('&cip=130.54.2.1', $url);
    }

    public function testGeoLocationParameters(): void
    {
        $tracker = $this->createTracker();
        $tracker->setCountry('de');
        $tracker->setRegion('Hessen');
        $tracker->setCity('Frankfurt');
        $tracker->setLatitude(50.11);
        $tracker->setLongitude(8.68);

        $query = self::parseQueryParams($tracker->getUrlTrackPageView('title'));
        $this->assertSame('de', $query['country']);
        $this->assertSame('Hessen', $query['region']);
        $this->assertSame('Frankfurt', $query['city']);
        $this->assertSame('50.11', $query['lat']);
        $this->assertSame('8.68', $query['long']);
    }

    public function testZeroCoordinatesAreSent(): void
    {
        $tracker = $this->createTracker();
        $tracker->setLatitude(0.0);
        $tracker->setLongitude(0.0);

        $query = self::parseQueryParams($tracker->getUrlTrackPageView('title'));
        $this->assertSame('0', $query['lat']);
        $this->assertSame('0', $query['long']);
    }

    public function testBrowserAttributes(): void
    {
        $tracker = $this->createTracker();
        $tracker->setResolution(1920, 1080);
        $tracker->setBrowserHasCookies(true);
        $tracker->setLocalTime('04:05:06');
        $tracker->setPlugins(true, false, true);

        $query = self::parseQueryParams($tracker->getUrlTrackPageView('title'));
        $this->assertSame('1920x1080', $query['res']);
        $this->assertSame('1', $query['cookie']);
        $this->assertSame('4', $query['h']);
        $this->assertSame('5', $query['m']);
        $this->assertSame('6', $query['s']);
        $this->assertSame('1', $query['fla']);
        $this->assertSame('0', $query['java']);
        $this->assertSame('1', $query['qt']);
        $this->assertSame('0', $query['realp']);
        $this->assertSame('0', $query['pdf']);
        $this->assertSame('0', $query['wma']);
        $this->assertSame('0', $query['ag']);
    }

    public function testPageCharset(): void
    {
        $tracker = $this->createTracker();

        $url = $tracker->getUrlTrackPageView('title');
        $this->assertStringNotContainsString('&cs=', $url);

        $tracker->setPageCharset('iso-8859-1');
        $url = $tracker->getUrlTrackPageView('title');
        $this->assertStringContainsString('&cs=iso-8859-1', $url);

        $tracker->setPageCharset();
        $url = $tracker->getUrlTrackPageView('title');
        $this->assertStringNotContainsString('&cs=', $url);
    }

    public function testUrlReferrer(): void
    {
        $tracker = $this->createTracker();
        $tracker->setUrlReferrer('http://referrer.example');

        $query = self::parseQueryParams($tracker->getUrlTrackPageView('title'));
        $this->assertSame('http://referrer.example', $query['urlref']);

        // the deprecated setUrlReferer() forwards to setUrlReferrer()
        $tracker->setUrlReferer('http://other.example');
        $query = self::parseQueryParams($tracker->getUrlTrackPageView('title'));
        $this->assertSame('http://other.example', $query['urlref']);

        // null unsets the referrer (renders as an empty urlref)
        $tracker->setUrlReferrer(null);
        $query = self::parseQueryParams($tracker->getUrlTrackPageView('title'));
        $this->assertSame('', $query['urlref']);
    }

    public function testSetGenerationTimeIsANoOp(): void
    {
        $tracker = $this->createTracker();
        $this->assertSame($tracker, $tracker->setGenerationTime(500));
    }

    public function testPerformanceTimings(): void
    {
        $tracker = $this->createTracker();

        // without a pageview id no performance timings are added
        $tracker->setPerformanceTimings(1, 2, 3, 4, 5, 6);
        $url = $tracker->getUrlTrackPageView('title');
        $this->assertStringNotContainsString('&pf_net=', $url);

        $tracker->setPageviewId('abc123');
        $url = $tracker->getUrlTrackPageView('title');
        $this->assertStringContainsString('&pf_net=1', $url);
        $this->assertStringContainsString('&pf_srv=2', $url);
        $this->assertStringContainsString('&pf_tfr=3', $url);
        $this->assertStringContainsString('&pf_dm1=4', $url);
        $this->assertStringContainsString('&pf_dm2=5', $url);
        $this->assertStringContainsString('&pf_onl=6', $url);

        // timings are cleared after they were tracked once
        $url = $tracker->getUrlTrackPageView('title');
        $this->assertStringNotContainsString('&pf_net=', $url);

        $tracker->setPerformanceTimings(1, 2, 3, 4, 5, 6);
        $tracker->clearPerformanceTimings();
        $url = $tracker->getUrlTrackPageView('title');
        $this->assertStringNotContainsString('&pf_net=', $url);
    }

    public function testForceVisitDateTime(): void
    {
        $tracker = $this->createTracker();
        $tracker->setForceVisitDateTime('2020-01-02 03:04:05');

        $query = self::parseQueryParams($tracker->getUrlTrackPageView('title'));
        $this->assertSame('2020-01-02 03:04:05', $query['cdt']);

        $this->assertSame(strtotime('2020-01-02 03:04:05'), $tracker->callGetTimestamp());
    }

    public function testGetTimestampFallsBackToCurrentTimeOnInvalidDateTime(): void
    {
        $tracker = $this->createTracker();
        $tracker->setForceVisitDateTime('not a datetime');

        $this->assertEqualsWithDelta(time(), $tracker->callGetTimestamp(), 5);

        $tracker = $this->createTracker();
        $this->assertEqualsWithDelta(time(), $tracker->callGetTimestamp(), 5);
    }

    public function testForceNewVisitIsOnlySentOnce(): void
    {
        $tracker = $this->createTracker();
        $tracker->setForceNewVisit();

        $url = $tracker->getUrlTrackPageView('title');
        $this->assertStringContainsString('&new_visit=1', $url);

        $url = $tracker->getUrlTrackPageView('title');
        $this->assertStringNotContainsString('&new_visit=1', $url);
    }

    public function testSetIdSite(): void
    {
        $tracker = $this->createTracker();
        $tracker->setIdSite(42);

        $this->assertStringContainsString('idsite=42', $tracker->getUrlTrackPageView('title'));
    }

    public function testDebugStringAppend(): void
    {
        $tracker = $this->createTracker();
        $tracker->setDebugStringAppend('debug=1');

        $this->assertStringContainsString('&debug=1', $tracker->getUrlTrackPageView('title'));
    }

    public function testDisableSendImageResponse(): void
    {
        $tracker = $this->createTracker();

        $url = $tracker->getUrlTrackPageView('title');
        $this->assertStringNotContainsString('&send_image=0', $url);

        $tracker->disableSendImageResponse();
        $url = $tracker->getUrlTrackPageView('title');
        $this->assertStringContainsString('&send_image=0', $url);
    }

    public function testClientHintsFromStrings(): void
    {
        $tracker = $this->createTracker();
        $tracker->setClientHints(
            'model',
            'Windows',
            '14.0.0',
            '"Chromium"; v="110.0.1", "Google Chrome"; v="110.0.2"',
            '110.0.1',
            '"Desktop", "XR"'
        );

        $query = self::parseQueryParams($tracker->getUrlTrackPageView('title'));
        $this->assertIsString($query['uadata']);
        $this->assertSame(
            [
                'model' => 'model',
                'platform' => 'Windows',
                'platformVersion' => '14.0.0',
                'uaFullVersion' => '110.0.1',
                'fullVersionList' => [
                    ['brand' => 'Chromium', 'version' => '110.0.1'],
                    ['brand' => 'Google Chrome', 'version' => '110.0.2'],
                ],
                'formFactors' => ['Desktop', 'XR'],
            ],
            json_decode($query['uadata'], true)
        );
    }

    public function testClientHintsFromArrays(): void
    {
        $tracker = $this->createTracker();
        $fullVersionList = [['brand' => 'Chromium', 'version' => '110.0.1']];
        $tracker->setClientHints('', 'Linux', '', $fullVersionList, '', ['Desktop']);

        $query = self::parseQueryParams($tracker->getUrlTrackPageView('title'));
        $this->assertIsString($query['uadata']);
        $this->assertSame(
            [
                'platform' => 'Linux',
                'fullVersionList' => $fullVersionList,
                'formFactors' => ['Desktop'],
            ],
            json_decode($query['uadata'], true)
        );
    }

    public function testEmptyClientHintsAreNotSent(): void
    {
        $tracker = $this->createTracker();
        $tracker->setClientHints();

        $this->assertStringNotContainsString('&uadata=', $tracker->getUrlTrackPageView('title'));
    }

    public function testClientHintsFromServerVariables(): void
    {
        $_SERVER['HTTP_SEC_CH_UA_PLATFORM'] = '"macOS"';
        $_SERVER['HTTP_SEC_CH_UA_PLATFORM_VERSION'] = '"13.1.0"';

        $tracker = $this->createTracker();

        $query = self::parseQueryParams($tracker->getUrlTrackPageView('title'));
        $this->assertIsString($query['uadata']);
        $this->assertSame(
            ['platform' => '"macOS"', 'platformVersion' => '"13.1.0"'],
            json_decode($query['uadata'], true)
        );
    }

    public function testConstructorReadsServerVariables(): void
    {
        $_SERVER['HTTP_REFERER'] = 'http://referrer.example';
        $_SERVER['REMOTE_ADDR'] = '10.11.12.13';
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr-fr';
        $_SERVER['HTTP_USER_AGENT'] = 'Test Agent';

        $tracker = new TestableMatomoTracker(1, self::TEST_URL);

        $this->assertSame('10.11.12.13', $tracker->getIp());
        $this->assertSame('fr-fr', $tracker->acceptLanguage);
        $this->assertSame('Test Agent', $tracker->getUserAgent());

        $query = self::parseQueryParams($tracker->getUrlTrackPageView('title'));
        $this->assertSame('http://referrer.example', $query['urlref']);
    }

    public function testBulkTrackingStoresRequestsAndResetsState(): void
    {
        $tracker = $this->createTracker();
        $tracker->enableBulkTracking();
        $tracker->setUserAgent('Bulk Agent');
        $tracker->setBrowserLanguage('en-us');

        $this->assertTrue($tracker->doTrackPageView('title'));
        $this->assertCount(1, $tracker->storedTrackingActions);
        $this->assertStringContainsString('&ua=' . urlencode('Bulk Agent'), $tracker->storedTrackingActions[0]);
        $this->assertStringContainsString('&lang=' . urlencode('en-us'), $tracker->storedTrackingActions[0]);

        // user agent, language and client hints are reset after storing a bulk request
        $this->assertNull($tracker->getUserAgent());
        $this->assertNull($tracker->acceptLanguage);
        $this->assertSame([], $tracker->clientHints);

        $this->assertTrue($tracker->doTrackEvent('cat', 'act'));
        $this->assertCount(2, $tracker->storedTrackingActions);
    }

    public function testDoBulkTrackSendsAllStoredRequests(): void
    {
        $tracker = $this->createTracker();
        $tracker->enableBulkTracking();
        $tracker->setTokenAuth('0123456789abcdef0123456789abcdef');
        $tracker->doTrackPageView('page one');
        $tracker->doTrackPageView('page two');

        $response = $tracker->doBulkTrack();

        $this->assertSame('mock-response', $response);
        $this->assertSame([], $tracker->storedTrackingActions);

        $this->assertCount(1, $tracker->capturedRequests);
        $request = $tracker->capturedRequests[0];
        $this->assertSame('http://mymatomo.com/matomo.php', $request['url']);
        $this->assertSame('POST', $request['method']);
        $this->assertTrue($request['force']);
        // bulk requests use the more generous bulk timeout
        $this->assertGreaterThanOrEqual(\MatomoTracker::DEFAULT_BULK_REQUEST_TIMEOUT, $request['timeout']);

        $this->assertIsString($request['data']);
        $data = json_decode($request['data'], true);
        $this->assertIsArray($data);
        $this->assertSame('0123456789abcdef0123456789abcdef', $data['token_auth']);
        $this->assertIsArray($data['requests']);
        $this->assertCount(2, $data['requests']);
    }

    public function testDoBulkTrackRetainsBatchOnFailureAndRestoresTimeout(): void
    {
        $tracker = $this->createTracker();
        $tracker->mockResponse = false; // simulate a failed send
        $tracker->enableBulkTracking();
        $tracker->doTrackPageView('page');
        $originalTimeout = $tracker->getRequestTimeout();

        $this->assertFalse($tracker->doBulkTrack());
        // the batch is kept so the caller can retry, and the (temporarily raised) timeout is restored
        $this->assertCount(1, $tracker->storedTrackingActions);
        $this->assertSame($originalTimeout, $tracker->getRequestTimeout());
    }

    public function testTokenAuthRequestIsSentAsPost(): void
    {
        // capture the transport method after sendRequest() has applied its token/method handling
        $captured = new class (1, 'http://matomo.example/matomo.php') extends \MatomoTracker {
            public string $capturedMethod = '';

            protected function prepareCurlOptions(string $url, string $method, ?string $data, bool $forcePostUrlEncoded): array
            {
                $this->capturedMethod = $method;
                throw new \RuntimeException('stop-before-network');
            }
        };
        $captured->disableCookieSupport();
        $captured->setTokenAuth('0123456789abcdef0123456789abcdef');

        try {
            $captured->doTrackPageView('page');
            $this->fail('expected the network short-circuit');
        } catch (\RuntimeException $e) {
            $this->assertSame('stop-before-network', $e->getMessage());
        }

        // with a token and no explicit request method, the request must be POSTed so Matomo
        // reads token_auth from the body instead of ignoring a GET body
        $this->assertSame('POST', $captured->capturedMethod);
    }

    /**
     * The URL/body carry token_auth and PII, so they must be redacted from stack traces not only
     * in sendRequest() but also in the transport option builders they are forwarded to (otherwise
     * a throw one frame down would put them straight back into the trace).
     *
     * @return array<int, array{0: string, 1: string}>
     */
    public static function sensitiveParameterProvider(): array
    {
        return [
            ['sendRequest', 'url'],
            ['sendRequest', 'data'],
            ['prepareCurlOptions', 'url'],
            ['prepareCurlOptions', 'data'],
            ['prepareStreamOptions', 'data'],
        ];
    }

    /**
     * @dataProvider sensitiveParameterProvider
     */
    public function testRequestUrlAndBodyAreMarkedSensitive(string $method, string $param): void
    {
        $reflection = new \ReflectionMethod(\MatomoTracker::class, $method);
        foreach ($reflection->getParameters() as $p) {
            if ($p->getName() === $param) {
                $this->assertNotEmpty(
                    $p->getAttributes(\SensitiveParameter::class),
                    "$method(\$$param) must be marked #[\\SensitiveParameter]"
                );
                return;
            }
        }
        $this->fail("Parameter \$$param not found on $method()");
    }

    public function testSetCurlOptionsMergesHttpHeadersInsteadOfReplacingThem(): void
    {
        $tracker = $this->createTracker();
        $tracker->setCurlOptions([CURLOPT_HTTPHEADER => ['X-Custom: 1']]);

        // A POST/bulk-style request whose Content-Type must survive the caller's extra header.
        $options = $tracker->callPrepareCurlOptions('http://example.org/', 'POST', 'foo=bar', true);
        $headers = $options[CURLOPT_HTTPHEADER];

        $this->assertIsArray($headers);
        $this->assertContains('X-Custom: 1', $headers);
        $this->assertContains('Content-Type: application/x-www-form-urlencoded', $headers);
        $this->assertContains('Accept-Language: ', $headers);
    }

    public function testStreamOptionsIgnoreHttpErrors(): void
    {
        $tracker = $this->createTracker();
        $options = $tracker->callPrepareStreamOptions('GET', null, false);
        $this->assertTrue($options['http']['ignore_errors']);
    }

    public function testDoBulkTrackThrowsWithoutStoredRequests(): void
    {
        $tracker = $this->createTracker();

        $this->expectException(Exception::class);

        $tracker->doBulkTrack();
    }

    public function testDisableBulkTracking(): void
    {
        $tracker = $this->createTracker();
        $tracker->enableBulkTracking();
        $tracker->disableBulkTracking();

        $this->assertSame('mock-response', $tracker->doTrackPageView('title'));
        $this->assertSame([], $tracker->storedTrackingActions);
    }

    public function testRequestTimeoutAccessors(): void
    {
        $tracker = $this->createTracker();

        $this->assertSame(5, $tracker->getRequestTimeout());
        $tracker->setRequestTimeout(10);
        $this->assertSame(10, $tracker->getRequestTimeout());

        $this->assertSame(2, $tracker->getRequestConnectTimeout());
        $tracker->setRequestConnectTimeout(5);
        $this->assertSame(5, $tracker->getRequestConnectTimeout());
    }

    public function testRequestTimeoutThrowsOnNegativeValue(): void
    {
        $tracker = $this->createTracker();

        $this->expectException(Exception::class);
        $tracker->setRequestTimeout(-1);
    }

    public function testRequestConnectTimeoutThrowsOnNegativeValue(): void
    {
        $tracker = $this->createTracker();

        $this->expectException(Exception::class);
        $tracker->setRequestConnectTimeout(-1);
    }

    public function testPrepareCurlOptions(): void
    {
        $tracker = $this->createTracker();

        $options = $tracker->callPrepareCurlOptions('http://example.org', 'GET', null, false);
        $this->assertSame('http://example.org', $options[CURLOPT_URL]);
        $this->assertSame('', $options[CURLOPT_USERAGENT]);
        $this->assertTrue($options[CURLOPT_FOLLOWLOCATION]);
        $this->assertArrayNotHasKey(CURLOPT_POST, $options);

        $options = $tracker->callPrepareCurlOptions('http://example.org', 'POST', null, false);
        $this->assertTrue($options[CURLOPT_POST]);
        $this->assertArrayNotHasKey(CURLOPT_FOLLOWLOCATION, $options);

        // url encoded post data
        $options = $tracker->callPrepareCurlOptions('http://example.org', 'POST', 'a=b', true);
        $this->assertSame('a=b', $options[CURLOPT_POSTFIELDS]);
        $this->assertIsArray($options[CURLOPT_HTTPHEADER]);
        $this->assertContains('Content-Type: application/x-www-form-urlencoded', $options[CURLOPT_HTTPHEADER]);

        // json post data
        $options = $tracker->callPrepareCurlOptions('http://example.org', 'POST', '{"requests":[]}', false);
        $this->assertSame('{"requests":[]}', $options[CURLOPT_POSTFIELDS]);
        $this->assertIsArray($options[CURLOPT_HTTPHEADER]);
        $this->assertContains('Content-Type: application/json', $options[CURLOPT_HTTPHEADER]);
        $this->assertContains('Expect:', $options[CURLOPT_HTTPHEADER]);
    }

    public function testPrepareCurlOptionsWithProxyAndCookies(): void
    {
        $tracker = $this->createTracker();
        $tracker->setProxy('proxy.example', 3128);
        $tracker->setOutgoingTrackerCookie('name', 'value');

        $options = $tracker->callPrepareCurlOptions('http://example.org', 'GET', null, false);
        $this->assertSame('proxy.example:3128', $options[CURLOPT_PROXY]);
        $this->assertSame('name=value', $options[CURLOPT_COOKIE]);

        // outgoing cookies are cleared once they were added to a request
        $options = $tracker->callPrepareCurlOptions('http://example.org', 'GET', null, false);
        $this->assertArrayNotHasKey(CURLOPT_COOKIE, $options);
    }

    public function testSetCurlOptionsExtendAndOverrideDefaults(): void
    {
        $tracker = $this->createTracker();
        $tracker->setCurlOptions([
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4, // extends the defaults
            CURLOPT_TIMEOUT => 1,                   // overrides the built-in timeout
        ]);

        $options = $tracker->callPrepareCurlOptions('http://example.org', 'GET', null, false);
        $this->assertSame(CURL_IPRESOLVE_V4, $options[CURLOPT_IPRESOLVE]);
        $this->assertSame(1, $options[CURLOPT_TIMEOUT]);
    }

    private function makeFailingTracker(): \MatomoTracker
    {
        // A closed local port gives a fast, deterministic connection failure without external I/O.
        $tracker = new \MatomoTracker(1, 'http://127.0.0.1:1/matomo.php');
        $tracker->disableCookieSupport();
        $tracker->setRequestConnectTimeout(1);
        $tracker->setRequestTimeout(1);

        return $tracker;
    }

    public function testFailedRequestThrowsByDefault(): void
    {
        $tracker = $this->makeFailingTracker();

        $this->expectException(\RuntimeException::class);
        $tracker->doTrackPageView('title');
    }

    public function testFailedRequestReturnsFalseWhenExceptionsDisabled(): void
    {
        $tracker = $this->makeFailingTracker();
        $tracker->setExceptionsEnabled(false);

        $this->assertFalse($tracker->doTrackPageView('title'));
    }

    public function testPrepareStreamOptions(): void
    {
        $tracker = $this->createTracker();
        $tracker->setUserAgent('Stream Agent');
        $tracker->setBrowserLanguage('en-gb');

        $options = $tracker->callPrepareStreamOptions('GET', null, false);
        $this->assertSame('GET', $options['http']['method']);
        $this->assertSame('Stream Agent', $options['http']['user_agent']);
        $this->assertSame("Accept-Language: en-gb\r\n", $options['http']['header']);

        $options = $tracker->callPrepareStreamOptions('POST', 'a=b', true);
        $this->assertIsString($options['http']['header']);
        $this->assertStringContainsString('Content-Type: application/x-www-form-urlencoded', $options['http']['header']);
        $this->assertSame('a=b', $options['http']['content']);

        $options = $tracker->callPrepareStreamOptions('POST', '{"requests":[]}', false);
        $this->assertIsString($options['http']['header']);
        $this->assertStringContainsString('Content-Type: application/json', $options['http']['header']);
        $this->assertSame('{"requests":[]}', $options['http']['content']);
    }

    public function testPrepareStreamOptionsWithProxyAndCookies(): void
    {
        $tracker = $this->createTracker();
        $tracker->setProxy('proxy.example');
        $tracker->setOutgoingTrackerCookie('name', 'value');

        $options = $tracker->callPrepareStreamOptions('GET', null, false);
        $this->assertSame('proxy.example:80', $options['http']['proxy']);
        $this->assertIsString($options['http']['header']);
        $this->assertStringContainsString('Cookie: name=value', $options['http']['header']);
    }

    public function testOutgoingTrackerCookieCanBeRemoved(): void
    {
        $tracker = $this->createTracker();
        $tracker->setOutgoingTrackerCookie('name', 'value');
        $tracker->setOutgoingTrackerCookie('name', null);

        $this->assertSame([], $tracker->outgoingTrackerCookies);
    }

    public function testOutgoingCookiesAreJoinedWithSemicolon(): void
    {
        $tracker = $this->createTracker();
        $tracker->setOutgoingTrackerCookie('a', '1');
        $tracker->setOutgoingTrackerCookie('b', '2');

        $options = $tracker->callPrepareCurlOptions('http://example.org', 'GET', null, false);
        $this->assertSame('a=1; b=2', $options[CURLOPT_COOKIE]);
    }

    public function testParseIncomingCookies(): void
    {
        $tracker = $this->createTracker();

        $tracker->callParseIncomingCookies([
            'Content-Type: text/plain',
            'Set-Cookie: first=value1; path=/; HttpOnly',
            'Set-Cookie: second=value2; path=/',
            12345,
        ]);

        // multiple Set-Cookie headers all accumulate (previously only the last survived)
        $this->assertSame('value1', $tracker->getIncomingTrackerCookie('first'));
        $this->assertSame('value2', $tracker->getIncomingTrackerCookie('second'));
        $this->assertFalse($tracker->getIncomingTrackerCookie('missing'));

        $tracker->callParseIncomingCookies([]);
        $this->assertFalse($tracker->getIncomingTrackerCookie('first'));
    }

    public function testFirstPartyCookiesAreSet(): void
    {
        $tracker = $this->createTracker();
        $tracker->setCustomVariable(1, 'name', 'value');
        $tracker->setAttributionInfo('["campaign","keyword"]');
        $tracker->callSetFirstPartyCookies();

        $cookieNames = array_column($tracker->capturedCookies, 'name');
        $this->assertSame(['ref', 'ses', 'id', 'cvar'], $cookieNames);

        $this->assertSame('["campaign","keyword"]', $tracker->capturedCookies[0]['value']);
        $this->assertSame('*', $tracker->capturedCookies[1]['value']);
        $this->assertStringContainsString($tracker->getVisitorId() . '.', $tracker->capturedCookies[2]['value']);
        $this->assertSame('{"1":["name","value"]}', $tracker->capturedCookies[3]['value']);
    }

    public function testDisableCookieSupport(): void
    {
        $_COOKIE['_pk_id_1_f609'] = 'abcdef0123456789.1583291045';

        $tracker = $this->createTracker();
        $tracker->disableCookieSupport();

        $this->assertFalse($tracker->callGetCookieMatchingName('id'));

        $tracker->callSetFirstPartyCookies();
        $this->assertSame([], $tracker->capturedCookies);
    }

    public function testDeleteCookies(): void
    {
        $tracker = $this->createTracker();
        $tracker->deleteCookies();

        $this->assertCount(4, $tracker->capturedCookies);
        $this->assertSame(['id', 'ses', 'cvar', 'ref'], array_column($tracker->capturedCookies, 'name'));
        foreach ($tracker->capturedCookies as $cookie) {
            $this->assertSame('', $cookie['value']);
            $this->assertSame(-86400, $cookie['ttl']);
        }
    }

    public function testSetCookieBuildsHeader(): void
    {
        $tracker = $this->createTracker();
        $tracker->captureCookies = false;
        $tracker->enableCookies('example.com', '/path', true, true, 'Lax');

        // in a CLI environment headers can not actually be sent, this only must not fail
        $tracker->deleteCookies();

        $this->assertSame([], $tracker->capturedCookies);
    }

    public function testEnableCookiesInfluencesCookieName(): void
    {
        $tracker = $this->createTracker();
        $defaultName = $tracker->callGetCookieName('id');
        $this->assertMatchesRegularExpression('/^_pk_id\.1\.[0-9a-f]{4}$/', $defaultName);

        $tracker->enableCookies('example.com', '/path');
        $nameWithDomain = $tracker->callGetCookieName('id');
        $this->assertMatchesRegularExpression('/^_pk_id\.1\.[0-9a-f]{4}$/', $nameWithDomain);
        $this->assertNotSame($defaultName, $nameWithDomain);
    }

    /**
     * @dataProvider getTestDataForDomainFixup
     */
    public function testDomainFixup(string $domain, string $expected): void
    {
        $this->assertSame($expected, TestableMatomoTracker::callDomainFixup($domain));
    }

    /**
     * @return list<array{string, string}>
     */
    public static function getTestDataForDomainFixup(): array
    {
        return [
            ['', ''],
            ['example.com', 'example.com'],
            ['example.com.', 'example.com'],
            ['*.example.com', '.example.com'],
        ];
    }

    /**
     * @dataProvider getTestDataForToStringValue
     */
    public function testToStringValue(mixed $value, string $expected): void
    {
        $this->assertSame($expected, TestableMatomoTracker::callToStringValue($value));
    }

    /**
     * @return list<array{mixed, string}>
     */
    public static function getTestDataForToStringValue(): array
    {
        return [
            ['string', 'string'],
            [5, '5'],
            [1.5, '1.5'],
            [true, '1'],
            [false, ''],
            [null, ''],
            [['array'], ''],
            [new \stdClass(), ''],
        ];
    }

    public function testGetCookieMatchingNameReturnsFalseWhenNotFound(): void
    {
        $tracker = $this->createTracker();
        $this->assertFalse($tracker->callGetCookieMatchingName('id'));
    }

    public function testGetCurrentScheme(): void
    {
        unset($_SERVER['HTTPS']);
        $this->assertSame('http', TestableMatomoTracker::callGetCurrentScheme());

        $_SERVER['HTTPS'] = 'on';
        $this->assertSame('https', TestableMatomoTracker::callGetCurrentScheme());
    }

    public function testGetCurrentHost(): void
    {
        unset($_SERVER['HTTP_HOST']);
        $this->assertSame('unknown', TestableMatomoTracker::callGetCurrentHost());

        $_SERVER['HTTP_HOST'] = 'matomo.example';
        $this->assertSame('matomo.example', TestableMatomoTracker::callGetCurrentHost());
    }

    public function testGetCurrentScriptName(): void
    {
        unset($_SERVER['PATH_INFO'], $_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME']);
        $this->assertSame('/', TestableMatomoTracker::callGetCurrentScriptName());

        // SCRIPT_NAME is only the fallback when REQUEST_URI is unavailable.
        $_SERVER['SCRIPT_NAME'] = 'script.php';
        $this->assertSame('/script.php', TestableMatomoTracker::callGetCurrentScriptName());

        // REQUEST_URI is the primary source; the query string is stripped.
        $_SERVER['REQUEST_URI'] = '/dir/page.php?query=1';
        $this->assertSame('/dir/page.php', TestableMatomoTracker::callGetCurrentScriptName());

        $_SERVER['REQUEST_URI'] = '/dir/other.php';
        $this->assertSame('/dir/other.php', TestableMatomoTracker::callGetCurrentScriptName());

        // Front-controller / path-info routing: with a request for /dir1/page handled by
        // dir1/index.php, PATH_INFO is only "/page". The full requested path must still be tracked,
        // so REQUEST_URI wins and PATH_INFO is ignored (previously it truncated the URL to "/page").
        $_SERVER['REQUEST_URI'] = '/dir1/page';
        $_SERVER['PATH_INFO'] = '/page';
        $_SERVER['SCRIPT_NAME'] = '/dir1/index.php';
        $this->assertSame('/dir1/page', TestableMatomoTracker::callGetCurrentScriptName());
    }

    public function testGetCurrentQueryStringAndUrl(): void
    {
        unset($_SERVER['QUERY_STRING']);
        $this->assertSame('', TestableMatomoTracker::callGetCurrentQueryString());

        $_SERVER['QUERY_STRING'] = 'a=b&c=d';
        $this->assertSame('?a=b&c=d', TestableMatomoTracker::callGetCurrentQueryString());

        $_SERVER['HTTPS'] = 'on';
        $_SERVER['HTTP_HOST'] = 'matomo.example';
        unset($_SERVER['PATH_INFO']);
        $_SERVER['REQUEST_URI'] = '/page';
        $this->assertSame('https://matomo.example/page?a=b&c=d', TestableMatomoTracker::callGetCurrentUrl());
    }

    public function testHelperFunctions(): void
    {
        \MatomoTracker::$URL = self::TEST_URL;

        $url = \Matomo_getUrlTrackPageView(5, 'my title');
        $this->assertStringContainsString('idsite=5', $url);
        $this->assertStringContainsString('&action_name=my+title', $url);

        $url = \Matomo_getUrlTrackGoal(5, 3, 1.5);
        $this->assertStringContainsString('idsite=5', $url);
        $this->assertStringContainsString('&idgoal=3', $url);
        $this->assertStringContainsString('&revenue=1.5', $url);
    }

    public function testPiwikCompatibilityShim(): void
    {
        \MatomoTracker::$URL = self::TEST_URL;

        $tracker = new \PiwikTracker(1, self::TEST_URL);
        $this->assertInstanceOf(\MatomoTracker::class, $tracker);

        $url = \Piwik_getUrlTrackPageView(5, 'my title');
        $this->assertStringContainsString('idsite=5', $url);

        $url = \Piwik_getUrlTrackGoal(5, 3, 1.5);
        $this->assertStringContainsString('&idgoal=3', $url);
    }
}
