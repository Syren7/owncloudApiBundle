<?php

namespace Syren7\OwncloudApiBundle\Service;

use it\thecsea\simple_caldav_client\CalDAVCalendar;
use Syren7\OwncloudApiBundle\lib\CalDavClient;

class OwncloudCalendar {

	/**
	 * @var CalDavClient $client
	 */
	protected $client;

	/**
	 * OwncloudCalendar constructor.
	 *
	 * @param	string	$ocHost
	 * @param	string	$ocUser
	 * @param	string	$ocPass
	 */
	public function __construct($ocHost, $ocUser, $ocPass) {
		//Create settings array for curl connection
		$settings = array(
			//the webdav url ist created from your owncloud url + '/remote.php/webdav/' + your specific folder if wished
			'baseUri' => $ocHost.'/remote.php/caldav/',
			'userName' => $ocUser,
			'password' => $ocPass,
		);
		//create required connection objects
		$this->cal = new CalDavClient($settings);
	}

	public function getEvents(CalDAVCalendar $calendar) {
		return $this->cal->getEvents($calendar);
	}

	public function createEvent() {
		//ToDo: This feature is currently missing
		//return $this->cal->createEvent($cal, $text);
	}

	public function getCalendars() {
		return $this->cal->getCalendars();
	}
}