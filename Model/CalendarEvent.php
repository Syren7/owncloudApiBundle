<?php

namespace Syren7\OwncloudApiBundle\Model;

use Sabre\VObject;
use Sabre\VObject\Property;

class CalendarEventException extends \Exception {};

class CalendarEvent {
	/**
	 * @var VObject\Component\VCalendar $VCAL
	 */
	private $VCAL = null;
	/**
	 * ETag is the "changeID" of an CalDav entry
	 *
	 * @var string $eTag
	 */
	private $eTag = '';
	/**
	 * @var string $prodId
	 */
	private $prodId = '';
	/**
	 * @var string $version
	 */
	private $version = '';
	/**
	 * @var string $calscale
	 */
	private $calscale = '';
	/**
	 * @var VObject\Component\VEvent $calscale
	 */
	private $vevent = null;
	/**
	 * @var array $fields
	 */
	private $fields = array();

	/**
	 * CalendarEvent constructor.
	 *
	 * @param string $calData Data for this event
	 * @param string $calETag eTag for Event
	 */
	public function __construct($calData, $calETag) {
		//Read VCalendar Object from calData
		$this->VCAL = VObject\Reader::read($calData);
		//safe E-Tag for later removal or updates
		$this->eTag = $calETag;
		//set head information
		$this->version = $this->VCAL->VERSION->getValue();
		$this->prodId = $this->VCAL->PRODID->getValue();
		$this->calscale = $this->VCAL->CALSCALE->getValue();
		$this->vevent = $this->VCAL->VEVENT;
		$this->fields = $this->parseFields($this->vevent->children());
	}

	/**
	 * @return string
	 */
	public function getETag() {
		return $this->eTag;
	}
	
	/**
	 * @return string
	 */
	public function getVersion() {
		return $this->version;
	}
	/**
	 * @return string
	 */
	public function getCalscale() {
		return $this->calscale;
	}
	/**
	 * @return string
	 */
	public function getProdId() {
		return $this->prodId;
	}
	/**
	 * @return VObject\Component\VEvent
	 */
	public function getVEvent() {
		return $this->vevent;
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
}