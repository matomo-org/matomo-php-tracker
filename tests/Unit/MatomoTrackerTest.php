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
}