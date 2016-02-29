<?php
/**
 * User: Konstantin Tümmler
 * Date: 02.02.2016
 * Time: 20:49
 */

namespace Syren7\OwncloudApiBundle\Model;

class Calendar {
	/**
	 * Absolute URL
	 * @var string uri
	 */
	private $uri;
	/**
	 * Name of this calendar
	 * @var string $name
	 */
	private $name;
	/**
	 * Calendar constructor.
	 *
	 * @param string $uri
	 * @param string $name
	 */
	public function __construct($uri, $name) {
		$this->setUri($uri);
		$this->setName($name);
	}
	/**
	 * @return string
	 */
	public function getUri() {
		return $this->uri;
	}
	/**
	 * @param string $uri
	 *
	 * @return Calendar
	 */
	public function setUri($uri) {
		$this->uri = $uri;

		return $this;
	}
	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	/**
	 * @param string $name
	 *
	 * @return Calendar
	 */
	public function setName($name) {
		$this->name = $name;

		return $this;
	}
}