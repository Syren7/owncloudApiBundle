<?php

namespace Syren7\OwncloudApiBundle\Service;

use Syren7\OwncloudApiBundle\lib\CalDavClient;
use Syren7\OwncloudApiBundle\Model\Calendar;

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
			'baseUri' => $ocHost.'/remote.php/caldav/calendars/'.$ocUser.'/',
			'userName' => $ocUser,
			'password' => $ocPass,
		);
		//create required connection objects
		$this->cal = new CalDavClient($settings);
	}

	public function getEvents(Calendar $calendar) {
		return $this->cal->getEvents($calendar);
	}

	public function createEvent(Calendar $cal, $object) {
		//return $this->cal->createEvent($cal, $object);
	}

	public function getCalendars() {
		return $this->cal->getCalendars();
	}
}