<?php

ini_set('display_errors', '1');

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\VarDumper\VarDumper;

include 'Service/OwncloudCalendar.php';

try {

	$t = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:http://www.example.com/calendarapplication/
METHOD:PUBLISH
BEGIN:VEVENT
UID:461092315540@example.com
LOCATION:Somewhere
SUMMARY:Eine Kurzinfo
DESCRIPTION:Beschreibung des Termines
DTSTART:20160228T220000Z
DTEND:20160228T220000Z
DTSTAMP:20160228T220000Z
END:VEVENT
END:VCALENDAR';

	//$cale = new \Syren7\OwncloudApiBundle\Service\OwncloudCalendar('https://', '', '');
	//VarDumper::dump($cale->getCalendars());
	/**@var \Syren7\OwncloudApiBundle\Model\Calendar $cal*/
	//VarDumper::dump($cale->createEvent($cal, $t));

	/*foreach($cale->getCalendars() as $cal) {
		/**@var \Syren7\OwncloudApiBundle\Model\Calendar $cal*
		echo $cal->getName()."<br/>";
		VarDumper::dump($cale->getEvents($cal));
		//$test = Sabre\VObject\Reader::read($str);
		//VarDumper::dump($cale->createEvent($cal, 's'));
	}*/
}
catch(\Exception $e) {
	echo "Exception:<br/>";
	VarDumper::dump($e);
}