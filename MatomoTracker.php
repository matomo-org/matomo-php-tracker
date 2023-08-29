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

/**
 * MatomoTracker implements the Matomo Tracking Web API.
 *
 * For more information, see: https://github.com/matomo-org/matomo-php-tracker/
 *
 * @package MatomoTracker
 * @api
 */
class MatomoTracker
{
	/**
	 * Matomo base URL, for example http://example.tld/matomo/
	 *
	 * Must be set via the constructor:
	 * 	$yourMatomoObject = new MatomoTracker(1, 'http://example.tld/matomo/');
	 * or via the setApiURL function:
	 * 	$yourMatomoObject->setApiUrl('http://example.tld/matomo/');
	 *
	 * @var string
	 */
	public $URL = '';

	/**
	 * API Version
	 *
	 * @var int
	 */
	public const VERSION = 1;

	/**
	 * @var string
	 */
	public $DEBUG_APPEND_URL = '';

	/**
	 * Visitor ID length
	 *
	 * @var int
	 */
	public const LENGTH_VISITOR_ID = 16;

	/**
	 * Charset
	 *
	 * @see setPageCharset
	 * @var string
	 */
	public const DEFAULT_CHARSET_PARAMETER_VALUES = 'utf-8';

	/**
	 * See matomo.js
	 *
	 * @var string
	 */
	public const FIRST_PARTY_COOKIES_PREFIX = '_pk_';

	/**
	 * Defines how many categories can be used max when calling addEcommerceItem().
	 *
	 * @var int
	 */
	public const MAX_NUM_ECOMMERCE_ITEM_CATEGORIES = 5;

	/**
	 * Default cookie domain path
	 *
	 * @var string
	 */
	public const DEFAULT_COOKIE_PATH = '/';

	/**
	 * Request method
	 *
	 * @var ?string
	 */
	private $requestMethod = null;

	/**
	 * @var array
	 */
	public $ecommerceItems = [];

	/**
	 * @var array
	 */
	public $attributionInfo = [];

	/**
	 * @var array
	 */
	public $eventCustomVar = [];

	/**
	 * @var string
	 */
	public $forcedDatetime = '';

	/**
	 * @var bool
	 */
	public $forcedNewVisit = false;

	/**
	 * @var int|false
	 */
	public $networkTime = false;

	/**
	 * @var int|false
	 */
	public $serverTime = false;

	/**
	 * @var int|false
	 */
	public $transferTime = false;

	/**
	 * @var int|false
	 */
	public $domProcessingTime = false;

	/**
	 * @var int|false
	 */
	public $domCompletionTime = false;

	/**
	 * @var int|false
	 */
	public $onLoadTime = false;

	/**
	 * @var array
	 */
	public $pageCustomVar = [];

	/**
	 * @var array
	 */
	public $ecommerceView = [];

	/**
	 * @var array
	 */
	public $customParameters = [];

	/**
	 * @var array
	 */
	public $customDimensions = [];

	/**
	 * @var bool
	 */
	public $customData = false;

	/**
	 * @var bool
	 */
	public $hasCookies = false;

	/**
	 * @var string
	 */
	public $token_auth = '';

	/**
	 * @var string|false
	 */
	public $userAgent = false;

	/**
	 * @var string
	 */
	public $country = '';

	/**
	 * @var string
	 */
	public $region = '';

	/**
	 * @var string
	 */
	public $city = '';

	/**
	 * @var string
	 */
	public $lat = '';

	/**
	 * @var string
	 */
	public $long = '';

	/**
	 * @var string
	 */
	public $width = '';

	/**
	 * @var string
	 */
	public $height = '';

	/**
	 * @var string
	 */
	public $plugins = '';

	/**
	 * @var int|false
	 */
	public $localHour = false;

	/**
	 * @var int|false
	 */
	public $localMinute = false;

	/**
	 * @var int|false
	 */
	public $localSecond = false;

	/**
	 * @var string
	 */
	public $idPageview = '';

	/**
	 * Id site to be tracked
	 *
	 * @var int
	 */
	public $idSite = 0;

	/**
	 * @var string|null
	 */
	public $urlReferrer = null;

	/**
	 * @var string
	 */
	public $pageCharset = '';

	/**
	 * @var string|null
	 */
	public $pageUrl = null;

	/**
	 * @var string|false
	 */
	public $ip = false;

	/**
	 * @var string|false
	 */
	public $acceptLanguage = false;

	/**
	 * @var array
	 */
	public $clientHints = [];

	/**
	 * Life of the visitor cookie (in sec)
	 *
	 * @var int
	 */
	public $configVisitorCookieTimeout = 33955200; // 13 months (365 + 28 days)

	/**
	 * Life of the session cookie (in sec)
	 *
	 * @var int
	 */
	public $configSessionCookieTimeout = 1800; // 30 minutes

	/**
	 * Life of the session cookie (in sec)
	 *
	 * @var int
	 */
	public $configReferralCookieTimeout = 15768000; // 6 months

	/**
	 * @var string|false
	 */
	public $userId = false;

	/**
	 * @var string|false
	 */
	public $forcedVisitorId = false;

	/**
	 * @var string|false
	 */
	public $cookieVisitorId = false;

	/**
	 * @var string|false
	 */
	public $randomVisitorId = false;

	/**
	 * @var bool
	 */
	public $configCookiesDisabled = false;

	/**
	 * @var string
	 */
	public $configCookiePath = self::DEFAULT_COOKIE_PATH;

	/**
	 * @var string
	 */
	public $configCookieDomain = '';

	/**
	 * @var string
	 */
	public $configCookieSameSite = '';

	/**
	 * @var bool
	 */
	public $configCookieSecure = false;

	/**
	 * @var bool
	 */
	public $configCookieHTTPOnly = false;

	/**
	 * @var ?int
	 */
	public $currentTs = null;

	/**
	 * @var ?int
	 */
	public $createTs = null;

	/**
	 * @var int
	 */
	public $requestTimeout = 600;

	/**
	 * @var int
	 */
	public $requestConnectTimeout = 300;

	/**
	 * @var bool
	 */
	public $doBulkRequests = false;

	/**
	 * @var array
	 */
	public $storedTrackingActions = [];

	/**
	 * @var bool
	 */
	public $sendImageResponse = true;

	/**
	 * @var array
	 */
	public $visitorCustomVar = [];

	/**
	 * @var array
	 */
	public $outgoingTrackerCookies = [];

	/**
	 * @var array
	 */
	public $incomingTrackerCookies = [];

	/**
	 * @var string
	 */
	public $proxy = '';

	/**
	 * @var int
	 */
	public $proxyPort = 80;

	/**
	 * Used in tests to output useful error messages.
	 *
	 * @ignore
	 * @var string|false
	 */
	public static $DEBUG_LAST_REQUESTED_URL = false;

	/**
	 * Builds a MatomoTracker object, used to track visits, pages and Goal conversions
	 * for a specific website, by using the Matomo Tracking API.
	 *
	 * @param int $idSite Id Site to be tracked
	 * @param string $apiUrl "http://example.org/matomo/" or "http://matomo.example.org/", if not set use the setApiUrl() function
	 */
	public function __construct($idSite, $apiUrl = '')
	{
		$this->idSite = $idSite;
		$this->urlReferrer = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
		$this->pageCharset = self::DEFAULT_CHARSET_PARAMETER_VALUES;
		$this->pageUrl = $this->getCurrentUrl();
		$this->ip = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
		$this->acceptLanguage = !empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : false;
		$this->userAgent = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : false;
		$this->setClientHints(
			!empty($_SERVER['HTTP_SEC_CH_UA_MODEL']) ? $_SERVER['HTTP_SEC_CH_UA_MODEL'] : '',
			!empty($_SERVER['HTTP_SEC_CH_UA_PLATFORM']) ? $_SERVER['HTTP_SEC_CH_UA_PLATFORM'] : '',
			!empty($_SERVER['HTTP_SEC_CH_UA_PLATFORM_VERSION']) ? $_SERVER['HTTP_SEC_CH_UA_PLATFORM_VERSION'] : '',
			!empty($_SERVER['HTTP_SEC_CH_UA_FULL_VERSION_LIST']) ? $_SERVER['HTTP_SEC_CH_UA_FULL_VERSION_LIST'] : '',
			!empty($_SERVER['HTTP_SEC_CH_UA_FULL_VERSION']) ? $_SERVER['HTTP_SEC_CH_UA_FULL_VERSION'] : ''
		);
		if (!empty($apiUrl)) {
			$this->URL = $apiUrl;
		}

		$this->setNewVisitorId();

		$this->currentTs = time();
		$this->createTs = $this->currentTs;

		$this->visitorCustomVar = $this->getCustomVariablesFromCookie();
	}

	/**
	 * @param string $url
	 * @return void
	 */
	public function setApiUrl(string $url)
	{
		$this->URL = $url;
	}

	/**
	 * By default, Matomo expects utf-8 encoded values, for example
	 * for the page URL parameter values, Page Title, etc.
	 * It is recommended to only send UTF-8 data to Matomo.
	 * If required though, you can also specify another charset using this function.
	 *
	 * @param string $charset
	 * @return $this
	 */
	public function setPageCharset($charset = '')
	{
		$this->pageCharset = $charset;
		return $this;
	}

	/**
	 * Sets the current URL being tracked
	 *
	 * @param string $url Raw URL (not URL encoded)
	 * @return $this
	 */
	public function setUrl($url)
	{
		$this->pageUrl = $url;
		return $this;
	}

	/**
	 * Sets the URL referrer used to track Referrers details for new visits.
	 *
	 * @param string $url Raw URL (not URL encoded)
	 * @return $this
	 */
	public function setUrlReferrer($url)
	{
		$this->urlReferrer = $url;
		return $this;
	}

	/**
	 * This method is deprecated and does nothing. It used to set the time that it took to generate the document on the server side.
	 *
	 * @param int $timeMs Generation time in ms
	 * @return $this
	 *
	 * @deprecated this metric is deprecated please use performance timings instead
	 * @see setPerformanceTimings
	 */
	public function setGenerationTime($timeMs)
	{
		return $this;
	}

	/**
	 * Sets timings for various browser performance metrics.
	 * @see https://developer.mozilla.org/en-US/docs/Web/API/PerformanceTiming
	 *
	 * @param int|false $network Network time in ms (connectEnd – fetchStart)
	 * @param int|false $server Server time in ms (responseStart – requestStart)
	 * @param int|false $transfer Transfer time in ms (responseEnd – responseStart)
	 * @param int|false $domProcessing DOM Processing to Interactive time in ms (domInteractive – domLoading)
	 * @param int|false $domCompletion DOM Interactive to Complete time in ms (domComplete – domInteractive)
	 * @param int|false $onload Onload time in ms (loadEventEnd – loadEventStart)
	 * @return $this
	 */
	public function setPerformanceTimings($network = false, $server = false, $transfer = false, $domProcessing = false, $domCompletion = false, $onload = false)
	{
		$this->networkTime = $network;
		$this->serverTime = $server;
		$this->transferTime = $transfer;
		$this->domProcessingTime = $domProcessing;
		$this->domCompletionTime = $domCompletion;
		$this->onLoadTime = $onload;
		return $this;
	}

	/**
	 * Clear / reset all previously set performance metrics.
	 * @return void
	 */
	public function clearPerformanceTimings()
	{
		$this->networkTime = false;
		$this->serverTime = false;
		$this->transferTime = false;
		$this->domProcessingTime = false;
		$this->domCompletionTime = false;
		$this->onLoadTime = false;
	}

	/**
	 * @deprecated
	 * @ignore
	 * @param string $url Raw URL (not URL encoded)
	 * @return $this
	 */
	public function setUrlReferer($url)
	{
		$this->setUrlReferrer($url);
		return $this;
	}

	/**
	 * Sets the attribution information to the visit, so that subsequent Goal conversions are
	 * properly attributed to the right Referrer URL, timestamp, Campaign Name & Keyword.
	 *
	 * This must be a JSON encoded string that would typically be fetched from the JS API:
	 * matomoTracker.getAttributionInfo() and that you have JSON encoded via JSON2.stringify()
	 *
	 * If you call enableCookies() then these referral attribution values will be set
	 * to the 'ref' first party cookie storing referral information.
	 *
	 * @param string $jsonEncoded JSON encoded array containing Attribution info
	 * @return $this
	 * @throws Exception
	 * @see function getAttributionInfo() in https://github.com/matomo-org/matomo/blob/master/js/matomo.js
	 */
	public function setAttributionInfo($jsonEncoded)
	{
		$decoded = json_decode($jsonEncoded, $assoc = true);
		if (!is_array($decoded)) {
			throw new Exception("setAttributionInfo() is expecting a JSON encoded string, $jsonEncoded given");
		}
		$this->attributionInfo = $decoded;
		return $this;
	}

	/**
	 * Sets Visit Custom Variable.
	 * See https://matomo.org/docs/custom-variables/
	 *
	 * @param int $id Custom variable slot ID from 1-5
	 * @param string $name Custom variable name
	 * @param string $value Custom variable value
	 * @param string $scope Custom variable scope. Possible values: visit, page, event
	 * @return $this
	 * @throws Exception
	 */
	public function setCustomVariable($id, $name, $value, $scope = 'visit')
	{
		if (!is_int($id)) {
			throw new Exception("Parameter id to setCustomVariable should be an integer");
		}
		if ($scope == 'page') {
			$this->pageCustomVar[$id] = array($name, $value);
		} elseif ($scope == 'event') {
			$this->eventCustomVar[$id] = array($name, $value);
		} elseif ($scope == 'visit') {
			$this->visitorCustomVar[$id] = array($name, $value);
		} else {
			throw new Exception("Invalid 'scope' parameter value");
		}
		return $this;
	}

	/**
	 * Returns the currently assigned Custom Variable.
	 *
	 * If scope is 'visit', it will attempt to read the value set in the first party cookie created by Matomo Tracker
	 *  ($_COOKIE array).
	 *
	 * @param int $id Custom Variable integer index to fetch from cookie. Should be a value from 1 to 5
	 * @param string $scope Custom variable scope. Possible values: visit, page, event
	 *
	 * @throws Exception
	 * @return mixed An array with this format: array( 0 => CustomVariableName, 1 => CustomVariableValue ) or false
	 * @see matomo.js getCustomVariable()
	 */
	public function getCustomVariable($id, $scope = 'visit')
	{
		if ($scope == 'page') {
			return isset($this->pageCustomVar[$id]) ? $this->pageCustomVar[$id] : false;
		} elseif ($scope == 'event') {
			return isset($this->eventCustomVar[$id]) ? $this->eventCustomVar[$id] : false;
		} else {
			if ($scope != 'visit') {
				throw new Exception("Invalid 'scope' parameter value");
			}
		}
		if (!empty($this->visitorCustomVar[$id])) {
			return $this->visitorCustomVar[$id];
		}
		$cookieDecoded = $this->getCustomVariablesFromCookie();
		if (!is_int($id)) {
			throw new Exception("Parameter to getCustomVariable should be an integer");
		}
		if (
			!is_array($cookieDecoded)
			|| !isset($cookieDecoded[$id])
			|| !is_array($cookieDecoded[$id])
			|| count($cookieDecoded[$id]) != 2
		) {
			return false;
		}

		return $cookieDecoded[$id];
	}

	/**
	 * Clears any Custom Variable that may be have been set.
	 *
	 * This can be useful when you have enabled bulk requests,
	 * and you wish to clear Custom Variables of 'visit' scope.
	 * @return void
	 */
	public function clearCustomVariables()
	{
		$this->visitorCustomVar = [];
		$this->pageCustomVar = [];
		$this->eventCustomVar = [];
	}

	/**
	 * Sets a specific custom dimension
	 *
	 * @param int $id id of custom dimension
	 * @param string $value value for custom dimension
	 * @return $this
	 */
	public function setCustomDimension($id, $value)
	{
		$this->customDimensions['dimension' . $id] = $value;
		return $this;
	}

	/**
	 * Clears all previously set custom dimensions
	 * @return void
	 */
	public function clearCustomDimensions()
	{
		$this->customDimensions = [];
	}

	/**
	 * Returns the value of the custom dimension with the given id
	 *
	 * @param int $id id of custom dimension
	 * @return string|null
	 */
	public function getCustomDimension($id)
	{
		return $this->customDimensions['dimension' . $id] ?? null;
	}

	/**
	 * Sets a custom tracking parameter. This is useful if you need to send any tracking parameters for a 3rd party
	 * plugin that is not shipped with Matomo itself. Please note that custom parameters are cleared after each
	 * tracking request.
	 *
	 * @param string $trackingApiParameter The name of the tracking API parameter, eg 'bw_bytes'
	 * @param string $value Tracking parameter value that shall be sent for this tracking parameter.
	 * @return $this
	 * @throws Exception
	 */
	public function setCustomTrackingParameter($trackingApiParameter, $value)
	{
		$matches = [];

		if (preg_match('/^dimension([0-9]+)$/', $trackingApiParameter, $matches)) {
			$this->setCustomDimension(intval($matches[1]), $value);
			return $this;
		}

		$this->customParameters[$trackingApiParameter] = $value;
		return $this;
	}

	/**
	 * Clear / reset all previously set custom tracking parameters.
	 * @return void
	 */
	public function clearCustomTrackingParameters()
	{
		$this->customParameters = array();
	}

	/**
	 * Sets the current visitor ID to a random new one.
	 * @return void
	 */
	public function setNewVisitorId()
	{
		$this->randomVisitorId = substr(md5(uniqid(strval(rand()), true)), 0, self::LENGTH_VISITOR_ID);
		$this->forcedVisitorId = false;
		$this->cookieVisitorId = false;
	}

	/**
	 * Sets the current site ID.
	 *
	 * @param int $idSite
	 * @return $this
	 */
	public function setIdSite($idSite)
	{
		$this->idSite = $idSite;
		return $this;
	}

	/**
	 * Sets the Browser language. Used to guess visitor countries when GeoIP is not enabled
	 *
	 * @param string $acceptLanguage For example "fr-fr"
	 * @return $this
	 */
	public function setBrowserLanguage($acceptLanguage)
	{
		$this->acceptLanguage = $acceptLanguage;
		return $this;
	}

	/**
	 * Sets the user agent, used to detect OS and browser.
	 * If this function is not called, the User Agent will default to the current user agent.
	 *
	 * @param string $userAgent
	 * @return $this
	 */
	public function setUserAgent($userAgent)
	{
		$this->userAgent = $userAgent;
		return $this;
	}

	/**
	 * Sets the client hints, used to detect OS and browser.
	 * If this function is not called, the client hints sent with the current request will be used.
	 *
	 * Supported as of Matomo 4.12.0
	 *
	 * @param string $model Value of the header 'HTTP_SEC_CH_UA_MODEL'
	 * @param string $platform Value of the header 'HTTP_SEC_CH_UA_PLATFORM'
	 * @param string $platformVersion Value of the header 'HTTP_SEC_CH_UA_PLATFORM_VERSION'
	 * @param string|array $fullVersionList Value of header 'HTTP_SEC_CH_UA_FULL_VERSION_LIST' or an array containing
	 *                                      all brands with the structure
	 *                                      [['brand' => 'Chrome', 'version' => '10.0.2'], ['brand' => '...]
	 * @param string $uaFullVersion Value of the header 'HTTP_SEC_CH_UA_FULL_VERSION'
	 *
	 * @return void
	 */
	public function setClientHints($model = '', $platform = '', $platformVersion = '', $fullVersionList = '', $uaFullVersion = '')
	{
		if (is_string($fullVersionList)) {
			$reg = '/^"([^"]+)"; ?v="([^"]+)"(?:, )?/';
			$list = [];

			while (\preg_match($reg, $fullVersionList, $matches)) {
				$list[] = ['brand' => $matches[1], 'version' => $matches[2]];
				$fullVersionList = \substr($fullVersionList, \strlen($matches[0]));
			}

			$fullVersionList = $list;
		} elseif (!is_array($fullVersionList)) {
			$fullVersionList = [];
		}

		$this->clientHints = array_filter([
			'model' => $model,
			'platform' => $platform,
			'platformVersion' => $platformVersion,
			'uaFullVersion' => $uaFullVersion,
			'fullVersionList' => $fullVersionList,
		]);
	}

	/**
	 * Sets the country of the visitor. If not used, Matomo will try to find the country
	 * using either the visitor's IP address or language.
	 *
	 * Allowed only for Admin/Super User, must be used along with setTokenAuth().
	 * @param string $country
	 * @return $this
	 */
	public function setCountry($country)
	{
		$this->country = $country;
		return $this;
	}

	/**
	 * Sets the region of the visitor. If not used, Matomo may try to find the region
	 * using the visitor's IP address (if configured to do so).
	 *
	 * Allowed only for Admin/Super User, must be used along with setTokenAuth().
	 * @param string $region
	 * @return $this
	 */
	public function setRegion($region)
	{
		$this->region = $region;
		return $this;
	}

	/**
	 * Sets the city of the visitor. If not used, Matomo may try to find the city
	 * using the visitor's IP address (if configured to do so).
	 *
	 * Allowed only for Admin/Super User, must be used along with setTokenAuth().
	 * @param string $city
	 * @return $this
	 */
	public function setCity($city)
	{
		$this->city = $city;
		return $this;
	}

	/**
	 * Sets the latitude of the visitor. If not used, Matomo may try to find the visitor's
	 * latitude using the visitor's IP address (if configured to do so).
	 *
	 * Allowed only for Admin/Super User, must be used along with setTokenAuth().
	 * @param string $lat
	 * @return $this
	 */
	public function setLatitude($lat)
	{
		$this->lat = $lat;
		return $this;
	}

	/**
	 * Sets the longitude of the visitor. If not used, Matomo may try to find the visitor's
	 * longitude using the visitor's IP address (if configured to do so).
	 *
	 * Allowed only for Admin/Super User, must be used along with setTokenAuth().
	 * @param string $long
	 * @return $this
	 */
	public function setLongitude($long)
	{
		$this->long = $long;
		return $this;
	}

	/**
	 * Enables the bulk request feature. When used, each tracking action is stored until the
	 * doBulkTrack method is called. This method will send all tracking data at once.
	 *
	 * @return void
	 */
	public function enableBulkTracking()
	{
		$this->doBulkRequests = true;
	}

	/**
	 * Disables the bulk request feature. Make sure to call `doBulkTrack()` before disabling it if you have stored
	 * tracking actions previously as this method won't be sending any previously stored actions before disabling it.
	 *
	 * @return void
	 */
	public function disableBulkTracking()
	{
		$this->doBulkRequests = false;
	}

	/**
	 * Enable Cookie Creation - this will cause a first party VisitorId cookie to be set when the VisitorId is set or reset
	 *
	 * @param string $domain (optional) Set first-party cookie domain.
	 *  Accepted values: example.com, *.example.com (same as .example.com) or subdomain.example.com
	 * @param string $path (optional) Set first-party cookie path
	 * @param bool $secure (optional) Set secure flag for cookies
	 * @param bool $httpOnly (optional) Set HTTPOnly flag for cookies
	 * @param string $sameSite (optional) Set SameSite flag for cookies
	 * @return void
	 */
	public function enableCookies($domain = '', $path = '/', $secure = false, $httpOnly = false, $sameSite = '')
	{
		$this->configCookiesDisabled = false;
		$this->configCookieDomain = $this->domainFixup($domain);
		$this->configCookiePath = $path;
		$this->configCookieSecure = $secure;
		$this->configCookieHTTPOnly = $httpOnly;
		$this->configCookieSameSite = $sameSite;
	}

	/**
	 * If image response is disabled Matomo will respond with a HTTP 204 header instead of responding with a gif.
	 * @return void
	 */
	public function disableSendImageResponse()
	{
		$this->sendImageResponse = false;
	}

	/**
	 * Fix-up domain
	 * @param string $domain
	 * @return string
	 */
	protected function domainFixup($domain)
	{
		if (strlen($domain) > 0) {
			$dl = strlen($domain) - 1;
			// remove trailing '.'
			if ($domain[$dl] === '.') {
				$domain = substr($domain, 0, $dl);
			}
			// remove leading '*'
			if (substr($domain, 0, 2) === '*.') {
				$domain = substr($domain, 1);
			}
		}

		return $domain;
	}

	/**
	 * Get cookie name with prefix and domain hash
	 * @param string $cookieName
	 * @return string
	 */
	protected function getCookieName($cookieName)
	{
		// NOTE: If the cookie name is changed, we must also update the method in matomo.js with the same name.
		$hash = substr(
			sha1(
				($this->configCookieDomain == '' ? $this->getCurrentHost() : $this->configCookieDomain) . $this->configCookiePath
			),
			0,
			4
		);

		return self::FIRST_PARTY_COOKIES_PREFIX . $cookieName . '.' . $this->idSite . '.' . $hash;
	}

	/**
	 * Tracks a page view
	 *
	 * @param string $documentTitle Page title as it will appear in the Actions > Page titles report
	 * @return mixed Response string or true if using bulk requests.
	 */
	public function doTrackPageView($documentTitle)
	{
		$this->generateNewPageviewId();

		$url = $this->getUrlTrackPageView($documentTitle);

		return $this->sendRequest($url);
	}

	/**
	 * @return void
	 */
	private function generateNewPageviewId()
	{
		$this->idPageview = substr(md5(uniqid(strval(rand()), true)), 0, 6);
	}

	/**
	 * Tracks an event
	 *
	 * @param string $category The Event Category (Videos, Music, Games...)
	 * @param string $action The Event's Action (Play, Pause, Duration, Add Playlist, Downloaded, Clicked...)
	 * @param string|false $name (optional) The Event's object Name (a particular Movie name, or Song name, or File name...)
	 * @param float|false $value (optional) The Event's value
	 * @return mixed Response string or true if using bulk requests.
	 */
	public function doTrackEvent($category, $action, $name = false, $value = false)
	{
		$url = $this->getUrlTrackEvent($category, $action, $name, $value);

		return $this->sendRequest($url);
	}

	/**
	 * Tracks a content impression
	 *
	 * @param string $contentName The name of the content. For instance 'Ad Foo Bar'
	 * @param string $contentPiece The actual content. For instance the path to an image, video, audio, any text
	 * @param string|false $contentTarget (optional) The target of the content. For instance the URL of a landing page.
	 * @return mixed Response string or true if using bulk requests.
	 */
	public function doTrackContentImpression($contentName, $contentPiece = 'Unknown', $contentTarget = false)
	{
		$url = $this->getUrlTrackContentImpression($contentName, $contentPiece, $contentTarget);

		return $this->sendRequest($url);
	}

	/**
	 * Tracks a content interaction. Make sure you have tracked a content impression using the same content name and
	 * content piece, otherwise it will not count. To do so you should call the method doTrackContentImpression();
	 *
	 * @param string $interaction The name of the interaction with the content. For instance a 'click'
	 * @param string $contentName The name of the content. For instance 'Ad Foo Bar'
	 * @param string $contentPiece The actual content. For instance the path to an image, video, audio, any text
	 * @param string|false $contentTarget (optional) The target the content leading to when an interaction occurs. For instance the URL of a landing page.
	 * @return mixed Response string or true if using bulk requests.
	 */
	public function doTrackContentInteraction(
		$interaction,
		$contentName,
		$contentPiece = 'Unknown',
		$contentTarget = false
	) {
		$url = $this->getUrlTrackContentInteraction($interaction, $contentName, $contentPiece, $contentTarget);

		return $this->sendRequest($url);
	}

	/**
	 * Tracks an internal Site Search query, and optionally tracks the Search Category, and Search results Count.
	 * These are used to populate reports in Actions > Site Search.
	 *
	 * @param string $keyword Searched query on the site
	 * @param string $category (optional) Search engine category if applicable
	 * @param int|false $countResults (optional) results displayed on the search result page. Used to track "zero result" keywords.
	 *
	 * @return mixed Response or true if using bulk requests.
	 */
	public function doTrackSiteSearch($keyword, $category = '', $countResults = false)
	{
		$url = $this->getUrlTrackSiteSearch($keyword, $category, $countResults);

		return $this->sendRequest($url);
	}

	/**
	 * Records a Goal conversion
	 *
	 * @param int $idGoal Id Goal to record a conversion
	 * @param float $revenue Revenue for this conversion
	 * @return mixed Response or true if using bulk request
	 */
	public function doTrackGoal($idGoal, $revenue = 0.0)
	{
		$url = $this->getUrlTrackGoal($idGoal, $revenue);

		return $this->sendRequest($url);
	}

	/**
	 * Tracks a download or outlink
	 *
	 * @param string $actionUrl URL of the download or outlink
	 * @param string $actionType Type of the action: 'download' or 'link'
	 * @return mixed Response or true if using bulk request
	 */
	public function doTrackAction($actionUrl, $actionType)
	{
		// Referrer could be udpated to be the current URL temporarily (to mimic JS behavior)
		$url = $this->getUrlTrackAction($actionUrl, $actionType);

		return $this->sendRequest($url);
	}

	/**
	 * Adds an item in the Ecommerce order.
	 *
	 * This should be called before doTrackEcommerceOrder(), or before doTrackEcommerceCartUpdate().
	 * This function can be called for all individual products in the cart (or order).
	 * SKU parameter is mandatory. Other parameters are optional (set to false if value not known).
	 * Ecommerce items added via this function are automatically cleared when doTrackEcommerceOrder() or getUrlTrackEcommerceOrder() is called.
	 *
	 * @param string $sku (required) SKU, Product identifier
	 * @param string $name (optional) Product name
	 * @param string|array $category (optional) Product category, or array of product categories (up to 5 categories can be specified for a given product)
	 * @param float|int $price (optional) Individual product price (supports integer and decimal prices)
	 * @param int $quantity (optional) Product quantity. If not specified, will default to 1 in the Reports
	 * @throws Exception
	 * @return $this
	 */
	public function addEcommerceItem($sku, $name = '', $category = '', $price = 0.0, $quantity = 1)
	{
		if (empty($sku)) {
			throw new Exception("You must specify a SKU for the Ecommerce item");
		}

		$price = $this->forceDotAsSeparatorForDecimalPoint($price);

		$this->ecommerceItems[] = array($sku, $name, $category, $price, $quantity);
		return $this;
	}

	/**
	 * Tracks a Cart Update (add item, remove item, update item).
	 *
	 * On every Cart update, you must call addEcommerceItem() for each item (product) in the cart,
	 * including the items that haven't been updated since the last cart update.
	 * Items which were in the previous cart and are not sent in later Cart updates will be deleted from the cart (in the database).
	 *
	 * @param float $grandTotal Cart grandTotal (typically the sum of all items' prices)
	 * @return mixed Response or true if using bulk request
	 */
	public function doTrackEcommerceCartUpdate($grandTotal)
	{
		$url = $this->getUrlTrackEcommerceCartUpdate($grandTotal);

		return $this->sendRequest($url);
	}

	/**
	 * Sends all stored tracking actions at once. Only has an effect if bulk tracking is enabled.
	 *
	 * To enable bulk tracking, call enableBulkTracking().
	 *
	 * @throws Exception
	 * @return mixed
	 */
	public function doBulkTrack()
	{
		if (empty($this->storedTrackingActions)) {
			throw new Exception(
				"Error: You must call the function doTrackPageView or doTrackGoal from this class, before calling this method doBulkTrack()"
			);
		}

		$data = array('requests' => $this->storedTrackingActions);

		// token_auth is not required by default, except if bulk_requests_require_authentication=1
		if (!empty($this->token_auth)) {
			$data['token_auth'] = $this->token_auth;
		}

		$postData = json_encode($data);
		if($postData === false)
		$postData = '';

		$response = $this->sendRequest($this->getBaseUrl(), 'POST', $postData, $force = true);

		$this->storedTrackingActions = array();

		return $response;
	}

	/**
	 * Tracks an Ecommerce order.
	 *
	 * If the Ecommerce order contains items (products), you must call first the addEcommerceItem() for each item in the order.
	 * All revenues (grandTotal, subTotal, tax, shipping, discount) will be individually summed and reported in Matomo reports.
	 * Only the parameters $orderId and $grandTotal are required.
	 *
	 * @param string|int $orderId (required) Unique Order ID.
	 *                This will be used to count this order only once in the event the order page is reloaded several times.
	 *                orderId must be unique for each transaction, even on different days, or the transaction will not be recorded by Matomo.
	 * @param float $grandTotal (required) Grand Total revenue of the transaction (including tax, shipping, etc.)
	 * @param float $subTotal (optional) Sub total amount, typically the sum of items prices for all items in this order (before Tax and Shipping costs are applied)
	 * @param float $tax (optional) Tax amount for this order
	 * @param float $shipping (optional) Shipping amount for this order
	 * @param float $discount (optional) Discounted amount in this order
	 * @return mixed Response or true if using bulk request
	 */
	public function doTrackEcommerceOrder(
		$orderId,
		$grandTotal,
		$subTotal = 0.0,
		$tax = 0.0,
		$shipping = 0.0,
		$discount = 0.0
	) {
		$url = $this->getUrlTrackEcommerceOrder($orderId, $grandTotal, $subTotal, $tax, $shipping, $discount);

		return $this->sendRequest($url);
	}

	/**
	 * Tracks a PHP Throwable a crash (requires CrashAnalytics to be enabled in the target Matomo)
	 *
	 * @param Throwable $ex (required) the throwable to track. The message, stack trace, file location and line number
	 *                      of the crash are deduced from this parameter. The crash type is set to the class name of
	 *                      the Throwable.
	 * @param string|null $category (optional) a category value for this crash. This can be any information you want
	 *                              to attach to the crash.
	 * @return mixed Response or true if using bulk request
	 */
	public function doTrackPhpThrowable(\Throwable $ex, $category = null)
	{
		$message = $ex->getMessage();
		$stack = $ex->getTraceAsString();
		$type = get_class($ex);
		$location = $ex->getFile();
		$line = $ex->getLine();

		return $this->doTrackCrash($message, $type, $category, $stack, $location, $line);
	}

	/**
	 * Track a crash (requires CrashAnalytics to be enabled in the target Matomo)
	 *
	 * @param string $message (required) the error message.
	 * @param string|null $type (optional) the error type, such as the class name of an Exception.
	 * @param string|null $category (optional) a category value for this crash. This can be any information you want
	 *                              to attach to the crash.
	 * @param string|null $stack (optional) the stack trace of the crash.
	 * @param string|null $location (optional) the source file URI where the crash originated.
	 * @param int|null $line (optional) the source file line where the crash originated.
	 * @param int|null $column (optional) the source file column where the crash originated.
	 * @return mixed Response or true if using bulk request
	 */
	public function doTrackCrash($message, $type = null, $category = null, $stack = null, $location = null, $line = null, $column = null)
	{
		$url = $this->getUrlTrackCrash($message, $type, $category, $stack, $location, $line, $column);

		return $this->sendRequest($url);
	}

	/**
	 * Sends a ping request.
	 *
	 * Ping requests do not track new actions. If they are sent within the standard visit length (see global.ini.php),
	 * they will extend the existing visit and the current last action for the visit. If after the standard visit length,
	 * ping requests will create a new visit using the last action in the last known visit.
	 *
	 * @return mixed Response or true if using bulk request
	 */
	public function doPing()
	{
		$url = $this->getRequest($this->idSite);
		$url .= '&ping=1';

		return $this->sendRequest($url);
	}

	/**
	 * Sets the current page view as an item (product) page view, or an Ecommerce Category page view.
	 *
	 * This must be called before doTrackPageView() on this product/category page.
	 *
	 * On a category page, you may set the parameter $category only and set the other parameters to false.
	 *
	 * Tracking Product/Category page views will allow Matomo to report on Product & Categories
	 * conversion rates (Conversion rate = Ecommerce orders containing this product or category / Visits to the product or category)
	 *
	 * @param string $sku Product SKU being viewed
	 * @param string $name Product Name being viewed
	 * @param string|array $category Category being viewed. On a Product page, this is the product's category.
	 *                                You can also specify an array of up to 5 categories for a given page view.
	 * @param float $price Specify the price at which the item was displayed
	 * @return $this
	 */
	public function setEcommerceView($sku = '', $name = '', $category = '', $price = 0.0)
	{
		$this->ecommerceView = [];

		if (!empty($category)) {
			if (is_array($category)) {
				$category = json_encode($category);
			}
		} else {
			$category = "";
		}
		$this->ecommerceView['_pkc'] = $category;

		if (!empty($price)) {
			$price = (float)$price;
			$price = $this->forceDotAsSeparatorForDecimalPoint($price);
			$this->ecommerceView['_pkp'] = $price;
		}

		// On a category page, do not record "Product name not defined"
		if (empty($sku) && empty($name)) {
			return $this;
		}
		if (!empty($sku)) {
			$this->ecommerceView['_pks'] = $sku;
		}
		if (empty($name)) {
			$name = "";
		}
		$this->ecommerceView['_pkn'] = $name;
		return $this;
	}

	/**
	 * Force the separator for decimal point to be a dot. See https://github.com/matomo-org/matomo/issues/6435
	 * If for instance a German locale is used it would be a comma otherwise.
	 *
	 * @param float|string $value
	 * @return string
	 */
	private function forceDotAsSeparatorForDecimalPoint($value)
	{
		if (null === $value || false === $value) {
			return $value;
		}

		return str_replace(',', '.', strval($value));
	}

	/**
	 * Returns URL used to track Ecommerce Cart updates
	 * Calling this function will reinitializes the property ecommerceItems to empty array
	 * so items will have to be added again via addEcommerceItem()
	 * @ignore
	 * @param float $grandTotal Cart grandTotal (typically the sum of all items' prices)
	 * @return string
	 */
	public function getUrlTrackEcommerceCartUpdate($grandTotal)
	{
		$url = $this->getUrlTrackEcommerce($grandTotal);

		return $url;
	}

	/**
	 * Returns URL used to track Ecommerce Orders
	 * Calling this function will reinitializes the property ecommerceItems to empty array
	 * so items will have to be added again via addEcommerceItem()
	 * @ignore
	 * @param string|int $orderId (required) Unique Order ID.
	 * @param float $grandTotal (required) Grand Total revenue of the transaction (including tax, shipping, etc.)
	 * @param float $subTotal (optional) Sub total amount, typically the sum of items prices for all items in this order (before Tax and Shipping costs are applied)
	 * @param float $tax (optional) Tax amount for this order
	 * @param float $shipping (optional) Shipping amount for this order
	 * @param float $discount (optional) Discounted amount in this order
	 * @return string
	 */
	public function getUrlTrackEcommerceOrder(
		$orderId,
		$grandTotal,
		$subTotal = 0.0,
		$tax = 0.0,
		$shipping = 0.0,
		$discount = 0.0
	) {
		if (empty($orderId)) {
			throw new Exception("You must specifiy an orderId for the Ecommerce order");
		}
		$url = $this->getUrlTrackEcommerce($grandTotal, $subTotal, $tax, $shipping, $discount);
		$url .= '&ec_id=' . urlencode(strval($orderId));

		return $url;
	}

	/**
	 * Returns URL used to track Ecommerce orders
	 *
	 * Calling this function will reinitializes the property ecommerceItems to empty array
	 * so items will have to be added again via addEcommerceItem()
	 *
	 * @ignore
	 * @param float $grandTotal (required) Grand Total revenue of the transaction (including tax, shipping, etc.)
	 * @param float $subTotal (optional) Sub total amount, typically the sum of items prices for all items in this order (before Tax and Shipping costs are applied)
	 * @param float $tax (optional) Tax amount for this order
	 * @param float $shipping (optional) Shipping amount for this order
	 * @param float $discount (optional) Discounted amount in this order
	 * @return string
	 */
	protected function getUrlTrackEcommerce(
		$grandTotal,
		$subTotal = 0.0,
		$tax = 0.0,
		$shipping = 0.0,
		$discount = 0.0
	) {
		if (!is_numeric($grandTotal)) {
			throw new Exception("You must specifiy a grandTotal for the Ecommerce order (or Cart update)");
		}

		$url = $this->getRequest($this->idSite);
		$url .= '&idgoal=0';
		if (!empty($grandTotal)) {
			$grandTotal = $this->forceDotAsSeparatorForDecimalPoint($grandTotal);
			$url .= '&revenue=' . $grandTotal;
		}
		if (!empty($subTotal)) {
			$subTotal = $this->forceDotAsSeparatorForDecimalPoint($subTotal);
			$url .= '&ec_st=' . $subTotal;
		}
		if (!empty($tax)) {
			$tax = $this->forceDotAsSeparatorForDecimalPoint($tax);
			$url .= '&ec_tx=' . $tax;
		}
		if (!empty($shipping)) {
			$shipping = $this->forceDotAsSeparatorForDecimalPoint($shipping);
			$url .= '&ec_sh=' . $shipping;
		}
		if (!empty($discount)) {
			$discount = $this->forceDotAsSeparatorForDecimalPoint($discount);
			$url .= '&ec_dt=' . $discount;
		}
		if (!empty($this->ecommerceItems)) {
			$url .= '&ec_items=' . urlencode(strval(json_encode($this->ecommerceItems)));
		}
		$this->ecommerceItems = array();

		return $url;
	}

	/**
	 * Builds URL to track a page view.
	 *
	 * @see doTrackPageView()
	 * @param string $documentTitle Page view name as it will appear in Matomo reports
	 * @return string URL to matomo.php with all parameters set to track the pageview
	 */
	public function getUrlTrackPageView($documentTitle = '')
	{
		$url = $this->getRequest($this->idSite);
		if (strlen($documentTitle) > 0) {
			$url .= '&action_name=' . urlencode($documentTitle);
		}

		return $url;
	}

	/**
	 * Builds URL to track a custom event.
	 *
	 * @see doTrackEvent()
	 * @param string $category The Event Category (Videos, Music, Games...)
	 * @param string $action The Event's Action (Play, Pause, Duration, Add Playlist, Downloaded, Clicked...)
	 * @param string|false $name (optional) The Event's object Name (a particular Movie name, or Song name, or File name...)
	 * @param float|false $value (optional) The Event's value
	 * @return string URL to matomo.php with all parameters set to track the pageview
	 * @throws Exception
	 */
	public function getUrlTrackEvent($category, $action, $name = false, $value = false)
	{
		$url = $this->getRequest($this->idSite);
		if (strlen($category) == 0) {
			throw new Exception("You must specify an Event Category name (Music, Videos, Games...).");
		}
		if (strlen($action) == 0) {
			throw new Exception("You must specify an Event action (click, view, add...).");
		}

		$url .= '&e_c=' . urlencode($category);
		$url .= '&e_a=' . urlencode($action);

		if($name !== false && strlen($name) > 0) {
			$url .= '&e_n=' . urlencode($name);
		}
		if($value !== false && strlen(strval($value)) > 0) {
			$value = $this->forceDotAsSeparatorForDecimalPoint($value);
			$url .= '&e_v=' . $value;
		}

		return $url;
	}

	/**
	 * Builds URL to track a content impression.
	 *
	 * @see doTrackContentImpression()
	 * @param string $contentName The name of the content. For instance 'Ad Foo Bar'
	 * @param string $contentPiece The actual content. For instance the path to an image, video, audio, any text
	 * @param string|false $contentTarget (optional) The target of the content. For instance the URL of a landing page.
	 * @throws Exception In case $contentName is empty
	 * @return string URL to matomo.php with all parameters set to track the pageview
	 */
	public function getUrlTrackContentImpression($contentName, $contentPiece, $contentTarget = false)
	{
		$url = $this->getRequest($this->idSite);

		if (strlen($contentName) == 0) {
			throw new Exception("You must specify a content name");
		}

		$url .= '&c_n=' . urlencode($contentName);

		if (!empty($contentPiece) && strlen($contentPiece) > 0) {
			$url .= '&c_p=' . urlencode($contentPiece);
		}
		if ($contentTarget !== false && strlen($contentTarget) > 0) {
			$url .= '&c_t=' . urlencode($contentTarget);
		}

		return $url;
	}

	/**
	 * Builds URL to track a content impression.
	 *
	 * @see doTrackContentInteraction()
	 * @param string $interaction The name of the interaction with the content. For instance a 'click'
	 * @param string $contentName The name of the content. For instance 'Ad Foo Bar'
	 * @param string $contentPiece The actual content. For instance the path to an image, video, audio, any text
	 * @param string|false $contentTarget (optional) The target the content leading to when an interaction occurs. For instance the URL of a landing page.
	 * @throws Exception In case $interaction or $contentName is empty
	 * @return string URL to matomo.php with all parameters set to track the pageview
	 */
	public function getUrlTrackContentInteraction($interaction, $contentName, $contentPiece, $contentTarget = false)
	{
		$url = $this->getRequest($this->idSite);

		if (strlen($interaction) == 0) {
			throw new Exception("You must specify a name for the interaction");
		}

		if (strlen($contentName) == 0) {
			throw new Exception("You must specify a content name");
		}

		$url .= '&c_i=' . urlencode($interaction);
		$url .= '&c_n=' . urlencode($contentName);

		if (!empty($contentPiece) && strlen($contentPiece) > 0) {
			$url .= '&c_p=' . urlencode($contentPiece);
		}
		if ($contentTarget !== false && strlen($contentTarget) > 0) {
			$url .= '&c_t=' . urlencode($contentTarget);
		}

		return $url;
	}

	/**
	 * Builds URL to track a site search.
	 *
	 * @see doTrackSiteSearch()
	 * @param string $keyword
	 * @param string $category
	 * @param int|false $countResults
	 * @return string
	 */
	public function getUrlTrackSiteSearch($keyword, $category, $countResults = false)
	{
		$url = $this->getRequest($this->idSite);
		$url .= '&search=' . urlencode($keyword);
		if (strlen($category) > 0) {
			$url .= '&search_cat=' . urlencode($category);
		}
		if ($countResults !== false) {
			$url .= '&search_count=' . (int)$countResults;
		}

		return $url;
	}

	/**
	 * Builds URL to track a goal with idGoal and revenue.
	 *
	 * @see doTrackGoal()
	 * @param int $idGoal Id Goal to record a conversion
	 * @param float $revenue Revenue for this conversion
	 * @return string URL to matomo.php with all parameters set to track the goal conversion
	 */
	public function getUrlTrackGoal($idGoal, $revenue = 0.0)
	{
		$url = $this->getRequest($this->idSite);
		$url .= '&idgoal=' . $idGoal;
		if (!empty($revenue)) {
			$revenue = $this->forceDotAsSeparatorForDecimalPoint($revenue);
			$url .= '&revenue=' . $revenue;
		}

		return $url;
	}

	/**
	 * Builds URL to track a new action.
	 *
	 * @see doTrackAction()
	 * @param string $actionUrl URL of the download or outlink
	 * @param string $actionType Type of the action: 'download' or 'link'
	 * @return string URL to matomo.php with all parameters set to track an action
	 */
	public function getUrlTrackAction($actionUrl, $actionType)
	{
		$url = $this->getRequest($this->idSite);
		$url .= '&' . $actionType . '=' . urlencode($actionUrl);

		return $url;
	}

	/**
	 * Builds URL to track a crash.
	 *
	 * @see doTrackCrash()
	 * @param string $message (required) the error message.
	 * @param string|null $type (optional) the error type, such as the class name of an Exception.
	 * @param string|null $category (optional) a category value for this crash. This can be any information you want
	 *                              to attach to the crash.
	 * @param string|null $stack (optional) the stack trace of the crash.
	 * @param string|null $location (optional) the source file URI where the crash originated.
	 * @param int|null $line (optional) the source file line where the crash originated.
	 * @param int|null $column (optional) the source file column where the crash originated.
	 * @return string URL to matomo.php with all parameters set to track an action
	 */
	public function getUrlTrackCrash(
		$message,
		$type = null,
		$category = null,
		$stack = null,
		$location = null,
		$line = null,
		$column = null
	) {
		$url = $this->getRequest($this->idSite);
		$url .= '&ca=1&cra=' . urlencode($message);
		if($type !== null)
			$url .= '&cra_tp=' . urlencode($type);

		if($category !== null)
			$url .= '&cra_ct=' . urlencode($category);

		if($stack !== null)
			$url .= '&cra_st=' . urlencode($stack);

		if($location !== null)
			$url .= '&cra_ru=' . urlencode($location);

		if($line !== null)
			$url .= '&cra_rl=' . urlencode(strval($line));

		if($column !== null)
			$url .= '&cra_rc=' . urlencode(strval($column));

		return $url;
	}

	/**
	 * Overrides server date and time for the tracking requests.
	 * By default Matomo will track requests for the "current datetime" but this function allows you
	 * to track visits in the past. All times are in UTC.
	 *
	 * Allowed only for Admin/Super User, must be used along with setTokenAuth()
	 * @see setTokenAuth()
	 * @param string $dateTime Date with the format 'Y-m-d H:i:s', or a UNIX timestamp.
	 *               If the datetime is older than one day (default value for tracking_requests_require_authentication_when_custom_timestamp_newer_than), then you must call setTokenAuth() with a valid Admin/Super user token.
	 * @return $this
	 */
	public function setForceVisitDateTime($dateTime)
	{
		$this->forcedDatetime = $dateTime;
		return $this;
	}

	/**
	 * Forces Matomo to create a new visit for the tracking request.
	 *
	 * By default, Matomo will create a new visit if the last request by this user was more than 30 minutes ago.
	 * If you call setForceNewVisit() before calling doTrack*, then a new visit will be created for this request.
	 * @return $this
	 */
	public function setForceNewVisit()
	{
		$this->forcedNewVisit = true;
		return $this;
	}

	/**
	 * Overrides IP address
	 *
	 * Allowed only for Admin/Super User, must be used along with setTokenAuth()
	 * @see setTokenAuth()
	 * @param string $ip IP string, eg. 130.54.2.1
	 * @return $this
	 */
	public function setIp($ip)
	{
		$this->ip = $ip;
		return $this;
	}

	/**
	 * Force the action to be recorded for a specific User. The User ID is a string representing a given user in your system.
	 *
	 * A User ID can be a username, UUID or an email address, or any number or string that uniquely identifies a user or client.
	 *
	 * @param string|false $userId Any user ID string (eg. email address, ID, username). Must be non empty. Set to false to de-assign a user id previously set.
	 * @return $this
	 * @throws Exception
	 */
	public function setUserId($userId)
	{
		if ($userId === '') {
			throw new Exception("User ID cannot be empty.");
		}
		$this->userId = $userId;
		return $this;
	}

	/**
	 * Hash function used internally by Matomo to hash a User ID into the Visitor ID.
	 *
	 * Note: matches implementation of Tracker\Request->getUserIdHashed()
	 *
	 * @param string|int $id
	 * @return string
	 */
	public function getUserIdHashed($id)
	{
		return substr(sha1(strval($id)), 0, 16);
	}

	/**
	 * Forces the requests to be recorded for the specified Visitor ID.
	 *
	 * Rather than letting Matomo attribute the user with a heuristic based on IP and other user fingeprinting attributes,
	 * force the action to be recorded for a particular visitor.
	 *
	 * If not set, the visitor ID will be fetched from the 1st party cookie, or will be set to a random UUID.
	 *
	 * @param string $visitorId 16 hexadecimal characters visitor ID, eg. "33c31e01394bdc63"
	 * @return $this
	 * @throws Exception
	 */
	public function setVisitorId($visitorId)
	{
		$hexChars = '01234567890abcdefABCDEF';
		if (
			strlen($visitorId) != self::LENGTH_VISITOR_ID
			|| strspn($visitorId, $hexChars) !== strlen($visitorId)
		) {
			throw new Exception(
				"setVisitorId() expects a "
				. self::LENGTH_VISITOR_ID
				. " characters hexadecimal string (containing only the following: "
				. $hexChars
				. ")"
			);
		}
		$this->forcedVisitorId = $visitorId;
		return $this;
	}

	/**
	 * If the user initiating the request has the Matomo first party cookie,
	 * this function will try and return the ID parsed from this first party cookie (found in $_COOKIE).
	 *
	 * If you call this function from a server, where the call is triggered by a cron or script
	 * not initiated by the actual visitor being tracked, then it will return
	 * the random Visitor ID that was assigned to this visit object.
	 *
	 * This can be used if you wish to record more visits, actions or goals for this visitor ID later on.
	 *
	 * @return string 16 hex chars visitor ID string
	 */
	public function getVisitorId()
	{
		if($this->forcedVisitorId !== false)
			return $this->forcedVisitorId;

		if($this->loadVisitorIdCookie() && $this->cookieVisitorId !== false)
			return $this->cookieVisitorId;

		return strval($this->randomVisitorId);
	}

	/**
	 * Returns the currently set user agent.
	 * @return string
	 */
	public function getUserAgent()
	{
		if($this->userAgent === false)
				return '';
		else
				return $this->userAgent;
	}

	/**
	 * Returns the currently set IP address.
	 * @return string
	 */
	public function getIp()
	{
		if($this->ip === false)
				return '';
		else
				return $this->ip;
	}

	/**
	 * Returns the User ID string, which may have been set via:
	 *     $v->setUserId('username@example.org');
	 *
	 * @return string|false
	 */
	public function getUserId()
	{
		return $this->userId;
	}

	/**
	 * Loads values from the VisitorId Cookie
	 *
	 * @return bool True if cookie exists and is valid, False otherwise
	 */
	protected function loadVisitorIdCookie()
	{
		$idCookie = $this->getCookieMatchingName('id');
		if ($idCookie === false) {
			return false;
		}
		$parts = explode('.', $idCookie);
		if (strlen($parts[0]) != self::LENGTH_VISITOR_ID) {
			return false;
		}

		/* $this->cookieVisitorId provides backward compatibility since getVisitorId()
didn't change any existing VisitorId value */
		$this->cookieVisitorId = $parts[0];
		$this->createTs = intval($parts[1]);

		return true;
	}

	/**
	 * Deletes all first party cookies from the client
	 * @return void
	 */
	public function deleteCookies()
	{
		$cookies = array('id', 'ses', 'cvar', 'ref');
		foreach ($cookies as $cookie) {
			$this->setCookie($cookie, '', -86400);
		}
	}

	/**
	 * Returns the currently assigned Attribution Information stored in a first party cookie.
	 *
	 * This function will only work if the user is initiating the current request, and his cookies
	 * can be read by PHP from the $_COOKIE array.
	 *
	 * @return string|false JSON Encoded string containing the Referrer information for Goal conversion attribution.
	 *                Will return false if the cookie could not be found
	 * @see matomo.js getAttributionInfo()
	 */
	public function getAttributionInfo()
	{
		if(!empty($this->attributionInfo)) {
			$json = json_encode($this->attributionInfo);
			if($json !== false)
				return $json;
		}

		return $this->getCookieMatchingName('ref');
	}

	/**
	 * Some Tracking API functionality requires express authentication, using either the
	 * Super User token_auth, or a user with 'admin' access to the website.
	 *
	 * The following features require access:
	 * - force the visitor IP
	 * - force the date & time of the tracking requests rather than track for the current datetime
	 *
	 * @param string $token_auth token_auth 32 chars token_auth string
	 * @return $this
	 */
	public function setTokenAuth($token_auth)
	{
		$this->token_auth = $token_auth;
		return $this;
	}

	/**
	 * Sets local visitor time
	 *
	 * @param string $time HH:MM:SS format
	 * @return $this
	 */
	public function setLocalTime($time)
	{
		list($hour, $minute, $second) = explode(':', $time);
		$this->localHour = (int)$hour;
		$this->localMinute = (int)$minute;
		$this->localSecond = (int)$second;
		return $this;
	}

	/**
	 * Sets user resolution width and height.
	 *
	 * @param int $width
	 * @param int $height
	 * @return $this
	 */
	public function setResolution($width, $height)
	{
		$this->width = strval($width);
		$this->height = strval($height);
		return $this;
	}

	/**
	 * Sets if the browser supports cookies
	 * This is reported in "List of plugins" report in Matomo.
	 *
	 * @param bool $bool
	 * @return $this
	 */
	public function setBrowserHasCookies($bool)
	{
		$this->hasCookies = $bool;
		return $this;
	}

	/**
	 * Will append a custom string at the end of the Tracking request.
	 * @param string $string
	 * @return $this
	 */
	public function setDebugStringAppend($string)
	{
		$this->DEBUG_APPEND_URL = '&' . $string;
		return $this;
	}

	/**
	 * Sets visitor browser supported plugins
	 *
	 * @param bool $flash
	 * @param bool $java
	 * @param bool $quickTime
	 * @param bool $realPlayer
	 * @param bool $pdf
	 * @param bool $windowsMedia
	 * @param bool $silverlight
	 * @return $this
	 */
	public function setPlugins(
		$flash = false,
		$java = false,
		$quickTime = false,
		$realPlayer = false,
		$pdf = false,
		$windowsMedia = false,
		$silverlight = false
	) {
		$this->plugins =
			'&fla=' . (int)$flash .
			'&java=' . (int)$java .
			'&qt=' . (int)$quickTime .
			'&realp=' . (int)$realPlayer .
			'&pdf=' . (int)$pdf .
			'&wma=' . (int)$windowsMedia .
			'&ag=' . (int)$silverlight;
		return $this;
	}

	/**
	 * By default, MatomoTracker will read first party cookies
	 * from the request and write updated cookies in the response (using setrawcookie).
	 * This can be disabled by calling this function.
	 * @return void
	 */
	public function disableCookieSupport()
	{
		$this->configCookiesDisabled = true;
	}

	/**
	 * Returns the maximum number of seconds the tracker will spend waiting for a response
	 * from Matomo. Defaults to 600 seconds.
	 * @return int
	 */
	public function getRequestTimeout()
	{
		return $this->requestTimeout;
	}

	/**
	 * Sets the maximum number of seconds that the tracker will spend waiting for a response
	 * from Matomo.
	 *
	 * @param int $timeout
	 * @return $this
	 * @throws Exception
	 */
	public function setRequestTimeout($timeout)
	{
		if (!is_int($timeout) || $timeout < 0) {
			throw new Exception("Invalid value supplied for request timeout: $timeout");
		}

		$this->requestTimeout = $timeout;
		return $this;
	}

	/**
	 * Returns the maximum number of seconds the tracker will spend trying to connect to Matomo.
	 * Defaults to 300 seconds.
	 * @return int
	 */
	public function getRequestConnectTimeout()
	{
		return $this->requestConnectTimeout;
	}

	/**
	 * Sets the maximum number of seconds that the tracker will spend tryint to connect to Matomo.
	 *
	 * @param int $timeout
	 * @return $this
	 * @throws Exception
	 */
	public function setRequestConnectTimeout($timeout)
	{
		if (!is_int($timeout) || $timeout < 0) {
			throw new Exception("Invalid value supplied for request connect timeout: $timeout");
		}

		$this->requestConnectTimeout = $timeout;
		return $this;
	}

	/**
	 * Sets the request method to POST, which is recommended when using setTokenAuth()
	 * to prevent the token from being recorded in server logs. Avoid using redirects
	 * when using POST to prevent the loss of POST values. When using Log Analytics,
	 * be aware that POST requests are not parseable/replayable.
	 *
	 * @param string $method Either 'POST' or 'GET'
	 * @return $this
	 */
	public function setRequestMethodNonBulk($method)
	{
		$this->requestMethod = strtoupper($method) === 'POST' ? 'POST' : 'GET';
		return $this;
	}

	/**
	 * If a proxy is needed to look up the address of the Matomo site, set it with this
	 * @param string $proxy IP as string, for example "173.234.92.107"
	 * @param int $proxyPort
	 * @return void
	 */
	public function setProxy($proxy, $proxyPort = 80)
	{
		$this->proxy = $proxy;
		$this->proxyPort = $proxyPort;
	}

	/**
	 * If the proxy IP and the proxy port have been set, with the setProxy() function
	 * returns a string, like "173.234.92.107:80"
	 * @return string|null
	 */
	private function getProxy()
	{
		if (!empty($this->proxy) && !empty($this->proxyPort)) {
			return $this->proxy . ":" . $this->proxyPort;
		}
		return null;
	}

	/**
	 * Returns array of curl options for request
	 * @param string $url
	 * @param string $method
	 * @param string $data
	 * @param bool $forcePostUrlEncoded
	 * @return array
	 */
	protected function prepareCurlOptions($url, $method, $data, $forcePostUrlEncoded)
	{
		$options = array(
			CURLOPT_URL => $url,
			CURLOPT_USERAGENT => $this->userAgent,
			CURLOPT_HEADER => true,
			CURLOPT_TIMEOUT => $this->requestTimeout,
			CURLOPT_CONNECTTIMEOUT => $this->requestConnectTimeout,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => array(
				'Accept-Language: ' . $this->acceptLanguage,
			),
		);

		if ($method === 'GET') {
			$options[CURLOPT_FOLLOWLOCATION] = true;
		}

		if (defined('PATH_TO_CERTIFICATES_FILE')) {
			$options[CURLOPT_CAINFO] = PATH_TO_CERTIFICATES_FILE;
		}

		$proxy = $this->getProxy();
		if(!empty($proxy)) {
			$options[CURLOPT_PROXY] = $proxy;
		}

		switch ($method) {
			case 'POST':
				$options[CURLOPT_POST] = true;
				break;
			default:
				break;
		}

		// only supports JSON data
		if (!empty($data) && $forcePostUrlEncoded) {
			$options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/x-www-form-urlencoded';
			$options[CURLOPT_POSTFIELDS] = $data;
			$options[CURLOPT_POST] = true;
			if (defined('CURL_REDIR_POST_ALL')) {
				$options[CURLOPT_POSTREDIR] = CURL_REDIR_POST_ALL;
				$options[CURLOPT_FOLLOWLOCATION] = true;
			}
		} elseif (!empty($data)) {
			$options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
			$options[CURLOPT_HTTPHEADER][] = 'Expect:';
			$options[CURLOPT_POSTFIELDS] = $data;
		}

		if (!empty($this->outgoingTrackerCookies)) {
			$options[CURLOPT_COOKIE] = http_build_query($this->outgoingTrackerCookies);
			$this->outgoingTrackerCookies = array();
		}

		return $options;
	}

	/**
	 * Returns array of stream options for request
	 * @param string $method
	 * @param string $data
	 * @param bool $forcePostUrlEncoded
	 * @return array
	 */
	protected function prepareStreamOptions($method, $data, $forcePostUrlEncoded)
	{
		$stream_options = array(
			'http' => array(
				'method' => $method,
				'user_agent' => $this->userAgent,
				'header' => "Accept-Language: " . $this->acceptLanguage . "\r\n",
				'timeout' => $this->requestTimeout,
			),
		);

		$proxy = $this->getProxy();
		if(!empty($proxy)) {
			$stream_options['http']['proxy'] = $proxy;
		}

		// only supports JSON data
		if (!empty($data) && $forcePostUrlEncoded) {
			$stream_options['http']['header'] .= "Content-Type: application/x-www-form-urlencoded \r\n";
			$stream_options['http']['content'] = $data;
		} elseif (!empty($data)) {
			$stream_options['http']['header'] .= "Content-Type: application/json \r\n";
			$stream_options['http']['content'] = $data;
		}

		if (!empty($this->outgoingTrackerCookies)) {
			$stream_options['http']['header'] .= 'Cookie: ' . http_build_query($this->outgoingTrackerCookies) . "\r\n";
			$this->outgoingTrackerCookies = array();
		}

		return $stream_options;
	}

	/**
	 * @param string $url
	 * @param string $method
	 * @param string $data
	 * @param bool $force
	 * @return mixed
	 * @ignore
	 */
	protected function sendRequest($url, $method = 'GET', $data = '', $force = false)
	{
		self::$DEBUG_LAST_REQUESTED_URL = $url;
		$content = '';

		// if doing a bulk request, store the url
		if ($this->doBulkRequests && !$force) {
			$this->storedTrackingActions[]
				= $url
				. (!empty($this->userAgent) ? ('&ua=' . urlencode($this->userAgent)) : '')
				. (!empty($this->acceptLanguage) ? ('&lang=' . urlencode($this->acceptLanguage)) : '');

			// Clear custom variables & dimensions so they don't get copied over to other users in the bulk request
			$this->clearCustomVariables();
			$this->clearCustomDimensions();
			$this->clearCustomTrackingParameters();
			$this->userAgent = false;
			$this->clientHints = [];
			$this->acceptLanguage = false;

			return true;
		}

		$forcePostUrlEncoded = false;
		if (!$this->doBulkRequests) {
			if (!empty($this->requestMethod) && strtoupper($this->requestMethod) === 'POST') {
				// POST ALL parameters and have no GET parameters
				$urlParts = explode('?', $url);

				$url = $urlParts[0];
				$data = $urlParts[1];
				$forcePostUrlEncoded = true;

				$method = 'POST';
			}

			if (!empty($this->token_auth)) {
				$appendTokenString = '&token_auth=' . urlencode($this->token_auth);

				if (empty($this->requestMethod) || $method === 'POST') {
					// Only post token_auth but use GET URL parameters for everything else
					$forcePostUrlEncoded = true;
					if($data === null || empty($data)) {
						$data = '';
					}
					$data .= $appendTokenString;
					$data = ltrim($data, '&'); // when no request method set we don't want it to start with '&'
				} elseif (!empty($this->token_auth)) {
					// Use GET for all URL parameters
					$url .= $appendTokenString;
				}
			}
		}

		if (function_exists('curl_init') && function_exists('curl_exec')) {
			$options = $this->prepareCurlOptions($url, $method, $data, $forcePostUrlEncoded);

			$ch = curl_init();
			curl_setopt_array($ch, $options);
			ob_start();
			$response = @curl_exec($ch);
			ob_end_clean();

			$header = '';

			if ($response === false)
				return false;

			if (is_string($response) && !empty($response)) {
				// extract header
				$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
				$header = substr($response, 0, $headerSize);

				// extract content
				$content = substr($response, $headerSize);
			}

			$this->parseIncomingCookies(explode("\r\n", $header));

		} elseif (function_exists('stream_context_create')) {
			$stream_options = $this->prepareStreamOptions($method, $data, $forcePostUrlEncoded);

			$ctx = stream_context_create($stream_options);
			$response = file_get_contents($url, false, $ctx);
			$content = $response;

			$this->parseIncomingCookies($http_response_header);
		}

		return $content;
	}

	/**
	 * Returns current timestamp, or forced timestamp/datetime if it was set
	 * @return string|int|false
	 */
	protected function getTimestamp()
	{
		return !empty($this->forcedDatetime)
			? strtotime($this->forcedDatetime)
			: time();
	}

	/**
	 * Returns the base URL for the Matomo server.
	 * @return string
	 */
	protected function getBaseUrl()
	{
		if (empty($this->URL))
			throw new Exception('You must set the Matomo Tracker URL, via the second parameter of the class constructor or via the setApiURL() function');

		if (
			strpos($this->URL, '/matomo.php') === false
			&& strpos($this->URL, '/proxy-matomo.php') === false
		) {
			$this->URL = rtrim($this->URL, '/');
			$this->URL .= '/matomo.php';
		}

		return $this->URL;
	}

	/**
	 * @param int $idSite
	 * @return string
	 * @ignore
	 */
	protected function getRequest($idSite)
	{
		$this->setFirstPartyCookies();

		$customFields = '';
		if (!empty($this->customParameters)) {
			$customFields = '&' . http_build_query($this->customParameters, '', '&');
		}

		$customDimensions = '';
		if (!empty($this->customDimensions)) {
			$customDimensions = '&' . http_build_query($this->customDimensions, '', '&');
		}

		$baseUrl = $this->getBaseUrl();
		$start = '?';
		if (strpos($baseUrl, '?') !== false) {
			$start = '&';
		}

		$url = $baseUrl . $start .
			'idsite=' . $idSite .
			'&rec=1' .
			'&apiv=' . self::VERSION .
			'&r=' . substr(strval(mt_rand()), 2, 6) .

			// XDEBUG_SESSIONS_START and KEY are related to the PHP Debugger, this can be ignored in other languages
			(!empty($_GET['XDEBUG_SESSION_START']) ?
				'&XDEBUG_SESSION_START=' . @urlencode($_GET['XDEBUG_SESSION_START']) : '') .
			(!empty($_GET['KEY']) ? '&KEY=' . @urlencode($_GET['KEY']) : '') .

			// Only allowed for Admin/Super User, token_auth required,
			((!empty($this->ip) && !empty($this->token_auth)) ? '&cip=' . $this->ip : '') .
			(!empty($this->userId) ? '&uid=' . urlencode($this->userId) : '') .
			(!empty($this->forcedDatetime) ? '&cdt=' . urlencode($this->forcedDatetime) : '') .
			(!empty($this->forcedNewVisit) ? '&new_visit=1' : '') .

			// Values collected from cookie
			'&_idts=' . $this->createTs .

			// These parameters are set by the JS, but optional when using API
			(!empty($this->plugins) ? $this->plugins : '') .
			(($this->localHour !== false && $this->localMinute !== false && $this->localSecond !== false) ?
				'&h=' . $this->localHour . '&m=' . $this->localMinute . '&s=' . $this->localSecond : '') .
			(!empty($this->width) && !empty($this->height) ? '&res=' . $this->width . 'x' . $this->height : '') .
			(!empty($this->hasCookies) ? '&cookie=' . $this->hasCookies : '') .

			// Various important attributes
			(!empty($this->customData) ? '&data=' . $this->customData : '') .
			(!empty($this->visitorCustomVar) ? '&_cvar=' . urlencode(strval(json_encode($this->visitorCustomVar))) : '') .
			(!empty($this->pageCustomVar) ? '&cvar=' . urlencode(strval(json_encode($this->pageCustomVar))) : '') .
			(!empty($this->eventCustomVar) ? '&e_cvar=' . urlencode(strval(json_encode($this->eventCustomVar))) : '') .
			(!empty($this->forcedVisitorId) ? '&cid=' . $this->forcedVisitorId : '&_id=' . $this->getVisitorId()) .

			// URL parameters
			'&url=' . urlencode($this->pageUrl ?? '') .
			'&urlref=' . urlencode($this->urlReferrer ?? '') .
			((!empty($this->pageCharset) && $this->pageCharset != self::DEFAULT_CHARSET_PARAMETER_VALUES) ?
				'&cs=' . $this->pageCharset : '') .

			// unique pageview id
			(!empty($this->idPageview) ? '&pv_id=' . urlencode($this->idPageview) : '') .

			// Attribution information, so that Goal conversions are attributed to the right referrer or campaign
			// Campaign name
			(!empty($this->attributionInfo[0]) ? '&_rcn=' . urlencode($this->attributionInfo[0]) : '') .
			// Campaign keyword
			(!empty($this->attributionInfo[1]) ? '&_rck=' . urlencode($this->attributionInfo[1]) : '') .
			// Timestamp at which the referrer was set
			(!empty($this->attributionInfo[2]) ? '&_refts=' . $this->attributionInfo[2] : '') .
			// Referrer URL
			(!empty($this->attributionInfo[3]) ? '&_ref=' . urlencode($this->attributionInfo[3]) : '') .

			// custom location info
			(!empty($this->country) ? '&country=' . urlencode($this->country) : '') .
			(!empty($this->region) ? '&region=' . urlencode($this->region) : '') .
			(!empty($this->city) ? '&city=' . urlencode($this->city) : '') .
			(!empty($this->lat) ? '&lat=' . urlencode($this->lat) : '') .
			(!empty($this->long) ? '&long=' . urlencode($this->long) : '') .
			$customFields . $customDimensions .
			(!$this->sendImageResponse ? '&send_image=0' : '') .

			// client hints
			(!empty($this->clientHints) ? ('&uadata=' . urlencode(strval(json_encode($this->clientHints)))) : '') .

			// DEBUG
			$this->DEBUG_APPEND_URL;

		if (!empty($this->idPageview)) {
			$url .=
				($this->networkTime !== false ? '&pf_net=' . ((int)$this->networkTime) : '') .
				($this->serverTime !== false ? '&pf_srv=' . ((int)$this->serverTime) : '') .
				($this->transferTime !== false ? '&pf_tfr=' . ((int)$this->transferTime) : '') .
				($this->domProcessingTime !== false ? '&pf_dm1=' . ((int)$this->domProcessingTime) : '') .
				($this->domCompletionTime !== false ? '&pf_dm2=' . ((int)$this->domCompletionTime) : '') .
				($this->onLoadTime !== false ? '&pf_onl=' . ((int)$this->onLoadTime) : '');
			$this->clearPerformanceTimings();
		}

		foreach ($this->ecommerceView as $param => $value) {
			$url .= '&' . $param . '=' . urlencode($value);
		}

		// Reset page level custom variables after this page view
		$this->ecommerceView = array();
		$this->pageCustomVar = array();
		$this->eventCustomVar = array();
		$this->clearCustomDimensions();
		$this->clearCustomTrackingParameters();

		// force new visit only once, user must call again setForceNewVisit()
		$this->forcedNewVisit = false;

		return $url;
	}


	/**
	 * Returns a first party cookie which name contains $name
	 *
	 * @param string $name
	 * @return string|false String value of cookie, or false if not found
	 * @ignore
	 */
	protected function getCookieMatchingName($name)
	{
		if ($this->configCookiesDisabled) {
			return false;
		}
		if (!is_array($_COOKIE)) {
			return false;
		}
		$name = $this->getCookieName($name);

		// Matomo cookie names use dots separators in matomo.js,
		// but PHP Replaces . with _ http://www.php.net/manual/en/language.variables.predefined.php#72571
		$name = str_replace('.', '_', $name);
		foreach ($_COOKIE as $cookieName => $cookieValue) {
			if (strpos($cookieName, $name) !== false) {
				return $cookieValue;
			}
		}

		return false;
	}

	/**
	 * If current URL is "http://example.org/dir1/dir2/index.php?param1=value1&param2=value2"
	 * will return "/dir1/dir2/index.php"
	 *
	 * @return string
	 * @ignore
	 */
	protected function getCurrentScriptName()
	{
		$url = '';
		if (!empty($_SERVER['PATH_INFO'])) {
			$url = $_SERVER['PATH_INFO'];
		} else {
			if (!empty($_SERVER['REQUEST_URI'])) {
				if (($pos = strpos($_SERVER['REQUEST_URI'], '?')) !== false) {
					$url = substr($_SERVER['REQUEST_URI'], 0, $pos);
				} else {
					$url = $_SERVER['REQUEST_URI'];
				}
			}
		}
		if (empty($url) && isset($_SERVER['SCRIPT_NAME'])) {
			$url = $_SERVER['SCRIPT_NAME'];
		} elseif (empty($url)) {
			$url = '/';
		}

		if (!empty($url) && $url[0] !== '/') {
			$url = '/' . $url;
		}

		return $url;
	}

	/**
	 * If the current URL is 'http://example.org/dir1/dir2/index.php?param1=value1&param2=value2"
	 * will return 'http'
	 *
	 * @return string 'https' or 'http'
	 * @ignore
	 */
	protected function getCurrentScheme()
	{
		if (
			isset($_SERVER['HTTPS'])
			&& ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] === true)
		) {
			return 'https';
		}

		return 'http';
	}

	/**
	 * If current URL is "http://example.org/dir1/dir2/index.php?param1=value1&param2=value2"
	 * will return "http://example.org"
	 *
	 * @return string
	 * @ignore
	 */
	protected function getCurrentHost()
	{
		if (isset($_SERVER['HTTP_HOST'])) {
			return $_SERVER['HTTP_HOST'];
		}

		return 'unknown';
	}

	/**
	 * If current URL is "http://example.org/dir1/dir2/index.php?param1=value1&param2=value2"
	 * will return "?param1=value1&param2=value2"
	 *
	 * @return string
	 * @ignore
	 */
	protected function getCurrentQueryString()
	{
		$url = '';
		if (
			isset($_SERVER['QUERY_STRING'])
			&& !empty($_SERVER['QUERY_STRING'])
		) {
			$url .= '?' . $_SERVER['QUERY_STRING'];
		}

		return $url;
	}

	/**
	 * Returns the current full URL (scheme, host, path and query string.
	 *
	 * @return string
	 * @ignore
	 */
	protected function getCurrentUrl()
	{
		return $this->getCurrentScheme() . '://'
		. $this->getCurrentHost()
		. $this->getCurrentScriptName()
		. $this->getCurrentQueryString();
	}

	/**
	 * Sets the first party cookies as would the matomo.js
	 * All cookies are supported: 'id' and 'ses' and 'ref' and 'cvar' cookies.
	 * @return $this
	 */
	protected function setFirstPartyCookies()
	{
		if ($this->configCookiesDisabled) {
			return $this;
		}

		if (empty($this->cookieVisitorId)) {
			$this->loadVisitorIdCookie();
		}

		// Set the 'ref' cookie
		$attributionInfo = $this->getAttributionInfo();
		if($attributionInfo !== false) {
			$this->setCookie('ref', $attributionInfo, $this->configReferralCookieTimeout);
		}

		// Set the 'ses' cookie
		$this->setCookie('ses', '*', $this->configSessionCookieTimeout);

		// Set the 'id' cookie
		$cookieValue = $this->getVisitorId() . '.' . $this->createTs;
		$this->setCookie('id', $cookieValue, $this->configVisitorCookieTimeout);

		// Set the 'cvar' cookie
		$this->setCookie('cvar', json_encode($this->visitorCustomVar), $this->configSessionCookieTimeout);
		return $this;
	}

	/**
	 * Sets a first party cookie to the client to improve dual JS-PHP tracking.
	 *
	 * This replicates the matomo.js tracker algorithms for consistency and better accuracy.
	 *
	 * @param string $cookieName
	 * @param mixed $cookieValue
	 * @param int $cookieTTL
	 * @return $this
	 */
	protected function setCookie($cookieName, $cookieValue, $cookieTTL)
	{
		$cookieExpire = $this->currentTs + $cookieTTL;
		if (!headers_sent()) {
			$header = 'Set-Cookie: ' . rawurlencode($this->getCookieName($cookieName)) . '=' . rawurlencode($cookieValue)
				. (empty($cookieExpire) ? '' : '; expires=' . gmdate('D, d-M-Y H:i:s', $cookieExpire) . ' GMT')
				. (empty($this->configCookiePath) ? '' : '; path=' . $this->configCookiePath)
				. (empty($this->configCookieDomain) ? '' : '; domain=' . rawurlencode($this->configCookieDomain))
				. (!$this->configCookieSecure ? '' : '; secure')
				. (!$this->configCookieHTTPOnly ? '' : '; HttpOnly')
				. (!$this->configCookieSameSite ? '' : '; SameSite=' . rawurlencode($this->configCookieSameSite));

			header($header, false);
		}
		return $this;
	}

	/**
	 * @return array
	 */
	protected function getCustomVariablesFromCookie()
	{
		$cookie = $this->getCookieMatchingName('cvar');
		if (!$cookie) {
			return [];
		}

		return json_decode($cookie, $assoc = true);
	}

	/**
	 * Sets a cookie to be sent to the tracking server.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function setOutgoingTrackerCookie($name, $value)
	{
		if($value === null)
			unset($this->outgoingTrackerCookies[$name]);
		else
			$this->outgoingTrackerCookies[$name] = $value;
	}

	/**
	 * Gets a cookie which was set by the tracking server.
	 *
	 * @param $name
	 *
	 * @param string $name
	 * @return bool|string
	 */
	public function getIncomingTrackerCookie($name)
	{
		if (isset($this->incomingTrackerCookies[$name])) {
			return $this->incomingTrackerCookies[$name];
		}

		return false;
	}

	/**
	 * Reads incoming tracking server cookies.
	 *
	 * @param array $headers Array with HTTP response headers as values
	 * @return void
	 */
	protected function parseIncomingCookies($headers)
	{
		$this->incomingTrackerCookies = array();

		if (!empty($headers)) {
			$headerName = 'set-cookie:';
			$headerNameLength = strlen($headerName);

			foreach($headers as $header) {
				if (strpos(strtolower($header), $headerName) !== 0) {
					continue;
				}
				$cookies = trim(substr($header, $headerNameLength));
				$posEnd = strpos($cookies, ';');
				if ($posEnd !== false) {
					$cookies = substr($cookies, 0, $posEnd);
				}
				parse_str($cookies, $this->incomingTrackerCookies);
			}
		}
	}
}

/**
 * Helper function to quickly generate the URL to track a page view.
 *
 * @param int $idSite
 * @param string $documentTitle
 * @return string
 */
function Matomo_getUrlTrackPageView($idSite, $documentTitle = '')
{
	$tracker = new MatomoTracker($idSite);

	return $tracker->getUrlTrackPageView($documentTitle);
}

/**
 * Helper function to quickly generate the URL to track a goal.
 *
 * @param int $idSite
 * @param int $idGoal
 * @param float $revenue
 * @return string
 */
function Matomo_getUrlTrackGoal($idSite, $idGoal, $revenue = 0.0)
{
	$tracker = new MatomoTracker($idSite);

	return $tracker->getUrlTrackGoal($idGoal, $revenue);
}

/**
 * Ensure PiwikTracker class is available as well
 *
 * @deprecated
 */
if(
	!class_exists('\PiwikTracker') &&
	is_file('PiwikTracker.php')
) {
	include_once('PiwikTracker.php');
}

