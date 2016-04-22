<?php

namespace Syren7\OwncloudApiBundle\lib;

use Doctrine\Common\Collections\ArrayCollection;
use it\thecsea\simple_caldav_client\CalDAVCalendar;
use it\thecsea\simple_caldav_client\CalDAVException;
use it\thecsea\simple_caldav_client\CalDAVObject;
use Sabre\VObject;
use Syren7\OwncloudApiBundle\Model\Calendar;
use Syren7\OwncloudApiBundle\Model\CalendarEvent;
use it\thecsea\simple_caldav_client\SimpleCalDAVClient;

/**
 * Class CalDavClient
 *
 * @package Syren7\OwncloudApiBundle\lib
 */
class CalDavClient {
	/**
	 * if there was an error you can retreive the last error with getLastException()
	 * @var \Exception $lastException
	 */
	private $lastException = null;
	/**
	 * @var SimpleCalDAVClient $client
	 */
	private $client;
	/**
	 * CalDavClient constructor.
	 *
	 * @param array $settings
	 */
	public function __construct($settings=array()) {
		//ToDo: check settings for required keys
		//initiate CalDAVClient
		$this->client = new SimpleCalDAVClient();
		//connect to caldav server
		$this->client->connect($settings['baseUri'], $settings['userName'], $settings['password']);
	}
	/**
	 * Reads all calendars from a calDAV URL
	 *
	 * @return ArrayCollection with CalDAVCalendar objects
	 */
	public function getCalendars() {
		try {
			$calendars = new ArrayCollection($this->client->findCalendars());
			//return found calendars
			return $calendars;
		}
		//if there was an error
		catch(\Exception $e) {
			$this->setLastException($e);
			//else on error return empty array collection
			return new ArrayCollection();
		}
	}

	/**
	 * Returns an ArrayCollection object with VObject elements as childs if there are some entries
	 *
	 * @param CalDAVCalendar $calendar
	 *
	 * @return ArrayCollection
	 * @throws CalDAVException
	 */
	public function getEvents(CalDAVCalendar $calendar) {
		$entries = new ArrayCollection();
		try {
			//check calendar
			if(!$calendar instanceof CalDAVCalendar) {
				throw new CalDAVException("invalid calendar set", 1);
			}
			//set calendar
			$this->client->setCalendar($calendar);
			//go through every event and convert data
			foreach ($this->client->getEvents() as $event) {
				/**@var CalDAVObject $event*/
				//Read VCalendar Object from calData
				/** @var VObject\Component\VCalendar $cal */
				$cal = VObject\Reader::read($event->getData());
				//expand event if there are multiple events
				$cal->expand((new \DateTime())->modify('-2 years'),(new \DateTime())->modify('+2 years'));
				//go through all events
				foreach($cal->VEVENT as $ev) {
					/**@var VObject\Component\VEvent $ev*/
					$entries->add(new CalendarEvent($ev, $event->getEtag()));
				}
			};
		}
		catch(\Exception $e) {
			$this->setLastException($e);
		}

		return $entries;
	}

	public function createEvent(Calendar $calendar, $text) {
		//ToDo: implement
	}

	###########################
	### Getters and Setters ###
	###########################
	/**
	 * @return \Exception
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
	 * @param \Exception $e
	 *
	 * @return CalDavClient
	 */
	public function setLastException($e) {
		$this->lastException = $e;

		return $this;
	}
}