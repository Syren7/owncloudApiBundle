<?php

ini_set('display_errors', '1');

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\VarDumper\VarDumper;

include 'Service/OwncloudCalendar.php';

$str = 'VERSION:2.0
		CALSCALE:GREGORIAN
		BEGIN:VEVENT
		UID:132456-34365
		SUMMARY:Weekly meeting
		DTSTART:20160228T120000
		DURATION:PT1H
		RRULE:FREQ=WEEKLY
		END:VEVENT
		END:VCALENDAR';
echo "Start!<br/>";

try {
	$cale = new \Syren7\OwncloudApiBundle\Service\OwncloudCalendar('https://cloud.srv0.org', 'Konsti', 'test1234567');
	foreach($cale->getCalendars() as $cal) {
		/**@var \Syren7\OwncloudApiBundle\Model\Calendar $cal*/
		echo $cal->getName()."<br/>";
		VarDumper::dump($cale->getEvents($cal));
		//$test = Sabre\VObject\Reader::read($str);
		//VarDumper::dump($cale->createEvent($cal, 's'));
	}
}
catch(\Exception $e) {
	VarDumper::dump($e);
}