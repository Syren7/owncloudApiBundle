<?php

namespace Syren7\OwncloudApiBundle\Model;

class FsObject {

	const TYPE_FILE = 'file';
	const TYPE_DIR = 'dir';

	private $filename = '';
	/**
	 * @var \DateTime $date
	 */
	private $date;

	private $size = 0;

	private $path = '';

	private $type = null;

	private $contentType = '';

	public function __construct($path) {
		//basename returns always the filename or the name of the last folder if no filename is given
		$this->filename 	= basename($path);
		$this->path 		= $path;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @param null|string $type
	 *
	 * @return FsObject
	 */
	public function setType($type) {
		$this->type = $type;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getSize() {
		return $this->size;
	}

	/**
	 * @param int $size
	 *
	 * @return FsObject
	 */
	public function setSize($size) {
		$this->size = $size;

		return $this;
	}

	/**
	 * @return \DateTime
	 */
	public function getDate() {
		return $this->date;
	}

	/**
	 * @param \DateTime $date
	 *
	 * @return FsObject
	 */
	public function setDate(\DateTime $date) {
		$this->date = $date;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getFilename() {
		return $this->filename;
	}

	/**
	 * @param string $filename
	 *
	 * @return FsObject
	 */
	public function setFilename($filename) {
		$this->filename = $filename;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getContentType() {
		return $this->contentType;
	}

	/**
	 * @param string $contentType
	 *
	 * @return FsObject
	 */
	public function setContentType($contentType) {
		$this->contentType = $contentType;

		return $this;
	}

	public function __toString() {
		return $this->getFilename();
	}
}

