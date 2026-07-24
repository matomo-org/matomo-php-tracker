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

if (!class_exists('\MatomoTracker')) {
    include_once('MatomoTracker.php');
}

/**
 * Helper function to quickly generate the URL to track a page view.
 *
 * @deprecated
 * @param int $idSite
 * @param string $documentTitle
 * @return string
 */
function Piwik_getUrlTrackPageView(int $idSite, string $documentTitle = ''): string
{
    return Matomo_getUrlTrackPageView($idSite, $documentTitle);
}

/**
 * Helper function to quickly generate the URL to track a goal.
 *
 * @deprecated
 * @param int $idSite
 * @param int $idGoal
 * @param float $revenue
 * @return string
 */
function Piwik_getUrlTrackGoal(int $idSite, int $idGoal, float $revenue = 0.0): string
{
    return Matomo_getUrlTrackGoal($idSite, $idGoal, $revenue);
}

/**
 * For BC only
 *
 * @deprecated use MatomoTracker instead
 */
class PiwikTracker extends MatomoTracker
{
}
