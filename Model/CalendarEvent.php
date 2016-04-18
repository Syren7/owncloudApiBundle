<?php

namespace Syren7\OwncloudApiBundle\Model;

use Sabre\VObject;
use Sabre\VObject\Property;

class CalendarEventException extends \Exception {};

class CalendarEvent {
	/**
	 * ETag is the "changeID" of an CalDav entry
	 *
	 * @var string $eTag
	 */
	private $eTag = '';
	/**
	 * @var array $fields
	 */
	private $fields = array();
	/**
	 * CalendarEvent constructor.
	 *
	 * @param VObject\Component\VEvent $event
	 * @param string $calETag eTag for Event
	 *
	 */
	public function __construct(VObject\Component\VEvent $event, $calETag) {
		//safe E-Tag for later removal or updates
		$this->eTag = $calETag;
		//set head information
		$this->fields = $this->parseFields($event->children());
	}
	/**
	 * @return string
	 */
	public function getETag() {
		return $this->eTag;
	}
	/**
	 * @param $fieldName
	 *
	 * @return string|\DateTime
	 * @throws CalendarEventException
	 */
	public function getField($fieldName) {
		if(array_key_exists($fieldName, $this->fields)) {
			return $this->fields[$fieldName];
		}
		throw new CalendarEventException("VEVENT field not found");
	}

	/**
	 * Returns an key => value of all children found in VEVENT
	 *
	 * @param array $children
	 *
	 * @return array
	 */
	private function parseFields(array $children) {
		$return = [];
		//go through every child of VEVENT
		foreach($children as $child) {
			//there should always be a name and value attribute
			/** @var Property $child*/
			//switch case for special types like TimeStamps
			switch($child->name) {
				case 'DTSTART':
				case 'DTEND':
				case 'CREATED':
				case 'DTSTAMP':
				case 'LAST-MODIFIED':
						$value = new \DateTime($child->getValue());
					break;
				default:
					$value = $child->getValue();
			}

			$return[$child->name] = $value;
		}
		//return the array
		return $return;
	}
	#########################################
	########## Custom field getter ##########
	#########################################
	/**
	 * @return string
	 * @throws CalendarEventException
	 */
	public function getLocation() {
		return $this->getField('LOCATION');
	}
	/**
	 * @return string
	 * @throws CalendarEventException
	 */
	public function getSummary() {
		return $this->getField('SUMMARY');
	}
	/**
	 * @return string
	 * @throws CalendarEventException
	 */
	public function getDescription() {
		return $this->getField('DESCRIPTION');
	}
	/**
	 * @return \DateTime
	 * @throws CalendarEventException
	 */
	public function getStart() {
		return $this->getField('DTSTART');
	}
	/**
	 * @return \DateTime
	 * @throws CalendarEventException
	 */
	public function getEnd() {
		return $this->getField('DTEND');
	}
	/**
	 * @return string
	 * @throws CalendarEventException
	 */
	public function getUID() {
		return $this->getField('UID');
	}
	/**
	 * @return bool
	 * @throws CalendarEventException
	 */
	public function isAllDay() {
		return $this->getStart()->format('H:i:s') == '00:00:00' && $this->getEnd()->format('H:i:s') == '00:00:00';
	}
}