<?php

namespace Syren7\OwncloudApiBundle\lib;

use Doctrine\Common\Collections\ArrayCollection;
use Sabre\DAV\Client;
use Sabre\DAV\Exception;
use Sabre\DAV\Property;
use Sabre\DAV\XMLUtil;
use Sabre\HTTP\Request;
use Sabre\VObject;
use Syren7\OwncloudApiBundle\Model\Calendar;
use Syren7\OwncloudApiBundle\Model\CalendarEvent;

/**
 * This class extends the standard DAV client to an calDAV client
 *
 * See @url(http://sabre.io/dav/building-a-caldav-client/) for more information
 *
 * Class CalDavClient
 *
 * @extends Sabre\DAV\Client
 * @package Syren7\OwncloudApiBundle\lib
 */
class CalDavClient extends Client{

	const CALDAV_PROP_CALENDARETAG = '{DAV:}getetag';
	const CALDAV_PROP_CALENDARNAME = '{DAV:}displayname';
	const CALDAV_PROP_CALENDARDATA = '{urn:ietf:params:xml:ns:caldav}calendar-data';

	/**
	 * if there was an error you can retreive the last error with getLastException()
	 * @var Exception $lastException
	 */
	private $lastException = null;
	/**
	 * ocCalendarConnector constructor.
	 *
	 * @param array  $settings
	 */
	public function __construct($settings=array()) {
		parent::__construct($settings);
	}

	/**
	 * Reads all calendars from a calDAV URL
	 *
	 * @return ArrayCollection with Calendar objects
	 */
	public function getCalendars() {
		$calendars = new ArrayCollection();
		try {
			foreach($this->propFind($this->getAbsoluteUrl(), array(self::CALDAV_PROP_CALENDARNAME), 1) as $uri => $entry) {
				//owncloud returns two calendards which are not named
				//currently I don't know where they're from
				//so we don't add them to the calendar collection
				//ToDo: Find out where the empty calendards come from and possible reenable adding them to the collection
				if(isset($entry[self::CALDAV_PROP_CALENDARNAME])) {
					$calendars->add(new Calendar($uri, $entry[self::CALDAV_PROP_CALENDARNAME]));
				}
			}
		}
		//if there was an error
		catch(Exception $e) {
			$this->setLastException($e);
		}
		//return found calendars
		return $calendars;
	}

	/**
	 * Returns an ArrayCollection object with VObject elements as childs if there are some entries
	 *
	 * @param Calendar $calendar
	 *
	 * @return ArrayCollection
	 */
	public function getEvents(Calendar $calendar) {
		$entries = new ArrayCollection();
		try {
			foreach ($this->propReport($calendar->getUri(), array(
				self::CALDAV_PROP_CALENDARETAG,
				self::CALDAV_PROP_CALENDARDATA,
			), 1) as $uri => $entry) {
				/**@var VObject\Component\VCalendar $calendar */
				$entries->add(new CalendarEvent($entry[self::CALDAV_PROP_CALENDARDATA], $entry[self::CALDAV_PROP_CALENDARETAG]));
			};
		}
		catch(Exception $e) {
			$this->setLastException($e);
		}

		return $entries;
	}

	public function createEvent(Calendar $calendar, $text) {
		$url = $calendar->getUri().substr(bin2hex(random_bytes(32)), 0, 32).'.ics';

		return $this->request('PUT',
			  $url,
			  $text,
			  array(
				  'Content-Type:' => 'text/calendar; charset=utf-8',
			  )
		);
	}

	/**
	 * Executes a REPORT request to calDAV server
	 *
	 * @param       $url
	 * @param array $properties
	 * @param int   $depth
	 *
	 * @return array
	 * @throws Exception
	 * @throws \Sabre\HTTP\ClientException
	 * @throws \Sabre\HTTP\ClientHttpException
	 */
	public function propReport($url, array $properties, $depth = 0) {
		//create dom
		$dom = new \DOMDocument('1.0', 'UTF-8');
		$dom->formatOutput = true;
		$root = $dom->createElementNS('urn:ietf:params:xml:ns:caldav', 'c:calendar-query');
		$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:d', 'DAV:');
		$prop = $dom->createElement('d:prop');
		$filter = $dom->createElement('c:filter');
		$dat = $dom->createElement('c:comp-filter');
		$dat->setAttribute('name', 'VCALENDAR');
		$filter->appendChild($dat);

		foreach($properties as $property) {
			list(
				$namespace,
				$elementName
				) = XMLUtil::parseClarkNotation($property);

			if ($namespace === 'DAV:') {
				$element = $dom->createElement('d:'.$elementName);
			} else {
				$element = $dom->createElement('c:'.$elementName);
			}

			$prop->appendChild( $element );
		}

		$bla = $dom->appendChild($root);
		$bla->appendChild( $prop );
		$bla->appendChild( $filter );

		$body = $dom->saveXML();
		$url = $this->getAbsoluteUrl($url);

		$request = new Request('REPORT', $url, [
			'Depth' => $depth,
			'Content-Type' => 'application/xml'
		], $body);

		$response = $this->send($request);

		if ((int)$response->getStatus() >= 400) {
			throw new Exception('HTTP error: ' . $response->getStatus());
		}

		$result = $this->parseMultiStatus($response->getBodyAsString());

		// If depth was 0, we only return the top item
		if ($depth===0) {
			reset($result);
			$result = current($result);
			return isset($result[200])?$result[200]:[];
		}

		$newResult = [];
		foreach($result as $href => $statusList) {

			$newResult[$href] = isset($statusList[200])?$statusList[200]:[];

		}

		return $newResult;
	}
	###########################
	######### Helpers #########
	###########################
	/**
	 * Overwrite parent method so parameter $url is now optional
	 * @param string $url
	 *
	 * @return string
	 */
	public function getAbsoluteUrl($url='') {
		return parent::getAbsoluteUrl($url);
	}

	###########################
	### Getters and Setters ###
	###########################
	/**
	 * @return Exception
	 */
	public function getLastException() {
		return $this->lastException;
	}
	/**
	 * Returns true if there was an error while retreiving or sending data
	 * @return bool
	 */
	public function hasError() {
		return $this->lastException !== null;
	}
	/**
	 * @param Exception $e
	 *
	 * @return CalDavClient
	 */
	public function setLastException($e) {
		$this->lastException = $e;

		return $this;
	}
}