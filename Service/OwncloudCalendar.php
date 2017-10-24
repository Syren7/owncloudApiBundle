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

		//New in  1.3.4
		//fixes error, when accessing an URL with double slash (e.g. //) owncloud raises an error
		//so we are going to search for double slashes and replace them with one slash
		$settings['baseUri'] = $this->fixUrl($settings['baseUri']);

		//create required connection objects
		$this->cal = new CalDavClient($settings);
	}


	/**
	 * Fixes double slash issue in urls
	 * @param $checkUrl
	 *
	 * @return string
	 */
	private function fixUrl($checkUrl) {
		//1. filter http / https
		$proto = strpos($checkUrl, "https") !== false ? "https://" : "http://";
		//1.1 remove leading https:// or http://
		$url = str_replace($proto, "", $checkUrl);
		//1.2 if the first sign is still an / than there were three slashes after http: or https:
		if(substr($url, 0, 1) == "/") $url = substr($url, 1);
		//2. replace all // to /
		$url = preg_replace('/([^:])(\/{2,})/', "/", $url);
		//3. create url
		return $proto.$url;
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