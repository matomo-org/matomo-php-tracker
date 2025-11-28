<?php

declare(strict_types=1);

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

namespace Unit;

use PHPUnit\Framework\TestCase;

class MatomoTrackerTest extends TestCase
{
    const TEST_URL = 'http://mymatomo.com';

    public function test_trackingWithCookieSetsCorrectUrl()
    {
        $testVisitorId = substr(md5('testuuid'), 0, 16);
        $this->assertEquals(16, strlen($testVisitorId));

        $createTs = strtotime('2020-03-04 03:04:05');

        $cookieName = '_pk_id_1_f609';
        $_COOKIE[$cookieName] = $testVisitorId . '.' . $createTs;

        $tracker = new \MatomoTracker(1, $apiUrl = self::TEST_URL);
        $tracker->setUrl('http://somesite.com');
        $url = $tracker->getUrlTrackPageView('test title');
        $url = preg_replace('/&r=\d+/', "", $url);

        $queryStr = parse_url($url, PHP_URL_QUERY);
        parse_str($queryStr, $query);

        $this->assertEquals($testVisitorId, $query['_id']);
        $this->assertEquals($createTs, $query['_idts']);

        $expected = 'http://mymatomo.com/matomo.php?idsite=1&rec=1&apiv=1&_idts=1583291045&_id=0958f111f2588a1b&url=http%3A%2F%2Fsomesite.com&urlref=&action_name=test+title';
        $this->assertEquals($expected, $url);
    }

    public function test_trackingWithPreMatomo4CookieSetsCorrectUrl()
    {
        $testVisitorId = substr(md5('testother'), 0, 16);
        $this->assertEquals(16, strlen($testVisitorId));

        $createTs = strtotime('2020-03-04 05:04:05');
        $currentTs = strtotime('2020-03-05 05:04:05');
        $lastVisitTs = strtotime('2020-03-06 05:04:05');
        $ecommerceLastOrderTs =  strtotime('2020-03-06 06:04:05');

        $cookieName = '_pk_id_1_f609';
        $_COOKIE[$cookieName] = $testVisitorId . '.' . $createTs . '.5.' . $currentTs . '.' . $lastVisitTs . '.' . $ecommerceLastOrderTs;

        $tracker = new \MatomoTracker(1, $apiUrl = self::TEST_URL);
        $tracker->setUrl('http://somesite.com');
        $url = $tracker->getUrlTrackPageView('test title');
        $url = preg_replace('/&r=\d+/', "", $url);

        $queryStr = parse_url($url, PHP_URL_QUERY);
        parse_str($queryStr, $query);

        $this->assertEquals($testVisitorId, $query['_id']);
        $this->assertEquals($createTs, $query['_idts']);

        $expected = 'http://mymatomo.com/matomo.php?idsite=1&rec=1&apiv=1&_idts=1583298245&_id=b446c233274f79f0&url=http%3A%2F%2Fsomesite.com&urlref=&action_name=test+title';
        $this->assertEquals($expected, $url);
    }

    public function test_setApiUrl()
    {
        $newApiUrl = 'https://NEW-API-URL.com';
        $tracker = new \MatomoTracker(1, self::TEST_URL);
        $tracker->setApiUrl($newApiUrl);
        $url = $tracker->getUrlTrackPageView('test title');

        $this->assertSame(substr($url, 0, strlen($newApiUrl)), $newApiUrl);
    }

    /**
     * @dataProvider getTestDataForIsUserAgentAIBot
     */
    public function test_isUserAgentAIBot($userAgent, $expected)
    {
        $this->assertSame($expected, \MatomoTracker::isUserAgentAIBot($userAgent));
    }

    public function getTestDataForIsUserAgentAIBot(): array
    {
        return [
            ['', false],

            ['Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.3', false],
            ['Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Mobile Safari/537.3', false],

            ['Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko); compatible; ChatGPT-User/1.0; +https://openai.com/bot', true],
            ['Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko); compatible; GPTBot/1.1; +https://openai.com/gptbot', true],
            ['Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; MistralAI-User/1.0; +https://docs.mistral.ai/robots)', true],
            ['Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Gemini-Deep-Research; +https://gemini.google/overview/deep-research/) Chrome/135.0.0.0 Safari/537.36', true],
            ['Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Claude-User/1.0; +Claude-User@anthropic.com)', true],
            ['Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Perplexity-User/1.0; +https://perplexity.ai/perplexity-user)', true],
            ['Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko; compatible; GoogleAgent-Mariner; +https://developers.google[dot]com/search/docs/crawling-indexing/google-agent-mariner) Chrome/135.0.0.0 Safari/537.36', true],
            ['Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36; Devin/1.0; +devin.ai', true],
            ['test user agent NovaAct ...', true],
            ['Google-Extended', true],
            ['test Google-CloudVertexBot test', true],
        ];
    }

    /**
     * @dataProvider getTestDataForGetUrlTrackAIBot
     */
    public function test_getUrlTrackAIBot(?int $httpStatus, ?int $responseSizeBytes, ?int $serverTimeMs, ?string $source, string $expected)
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko); compatible; ChatGPT-User/1.0; +https://openai.com/bot';

        $tracker = new \MatomoTracker(1, $apiUrl = self::TEST_URL);
        $tracker->setVisitorId('abcdef01234517ab');

        $actual = $tracker->getUrlTrackAIBot($httpStatus, $responseSizeBytes, $serverTimeMs, $source);
        $actual = $this->normalizeTrackingUrl($actual);

        $this->assertEquals($expected, $actual);
    }

    public function getTestDataForGetUrlTrackAIBot(): array
    {
        return [
            [
                200,
                34567,
                123,
                'wordpress',
                'http://mymatomo.com/matomo.php?idsite=1&rec=1&apiv=1&r=&r=&cid=abcdef01234517ab&url=http%3A%2F%2Funknown%2Fvendor%2Fbin%2Fphpunit&urlref=&http_status=200&bw_bytes=34567&pf_srv=123&source=wordpress',
            ],

            [
                null,
                34567,
                null,
                'something else',
                'http://mymatomo.com/matomo.php?idsite=1&rec=1&apiv=1&r=&r=&cid=abcdef01234517ab&url=http%3A%2F%2Funknown%2Fvendor%2Fbin%2Fphpunit&urlref=&bw_bytes=34567&source=something%20else',
            ],

            [
                null,
                null,
                null,
                null,
                'http://mymatomo.com/matomo.php?idsite=1&rec=1&apiv=1&r=&r=&cid=abcdef01234517ab&url=http%3A%2F%2Funknown%2Fvendor%2Fbin%2Fphpunit&urlref=',
            ],
        ];
    }

    private function normalizeTrackingUrl(string $url)
    {
        $nonDeterministicParams = [
            'r',
            '_idts',
        ];

        foreach ($nonDeterministicParams as $param) {
            $url = preg_replace('/&' . preg_quote($param) . '=[^&]+/', '&r=', $url);
        }

        return $url;
    }
}