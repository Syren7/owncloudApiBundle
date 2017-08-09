<?php

namespace Syren7\OwncloudApiBundle\lib;

use SimpleXMLElement;

/**
 * Class ocs
 *
 * Gives you the ability to access any OCS API
 * Current development targets on ownCloud OCS
 *
 * @package ejt\FileBundle\lib
 */
class ocs {
	/**
	 * Per default is the api available for OwnCloud
	 * You can define another URL when loading the ocs-Class
	 * See: ocs::__contruct() for more details
	 */
	const OCS_OWNCLOUD = 'ocs/v1.php/cloud';
	/**
	 * Definition for Method types
	 */
	const HTTP_GET 		= 'GET';
	const HTTP_POST 	= 'POST';
	const HTTP_PUT 		= 'PUT';
	const HTTP_DELETE 	= 'DELETE';
	/**
	 * Contains the statusCode
	 * @var int $statusCode
	 */
	private $statusCode = 0;
	/**
	 * Contains the status as text given from ocs metadata
	 * @var string $status
	 */
	private $status = '';
	/**
	 * Contains the message of request (e.g. OK or UNAUTHORIZED)
	 * @var string $message
	 */
	private $message = '';
	/**
	 * Holds return data of OCS request if given (content of <data> tag)
	 * @var array $data
	 */
	private $data = array();
	/**
	 * Username for OC Server
	 * @var string $ocUser
	 */
	private $ocUser = '';
	/**
	 * Password for OC Server
	 * @var string $ocPass
	 */
	private $ocPass = '';
	/**
	 * Url for API Access on OC Server. For more information visit: @Link(https://doc.owncloud.org/server/8.0/admin_manual/configuration_user/user_provisioning_api.html)
	 * @var string $apiUrl
	 */
	private $apiUrl = '';

	/**
	 * ocs constructor.
	 *
	 * @param string $ocHost Url to your OwnCloud
	 * @param string $ocUser Your owncloud user
	 * @param string $ocPass Your owncloud password
	 * @param string $ocURL relative URL from $ocHost where the API is accessable
	 */
	public function __construct($ocHost, $ocUser, $ocPass, $ocURL=self::OCS_OWNCLOUD) {
		$this->ocUser = $ocUser;
		$this->ocPass = $ocPass;
		$this->apiUrl = $ocHost.$ocURL;
	}

	/**
	 * Performs a request to an ocs API resource
	 *
	 * IMPORTANT: Before you can access any data on this object you need to perform a request
	 *
	 * @param string $relativeUrl the relativ url for request (part after api url, starting with a slash if given)
	 * @param array  $postData    if you want to send data via post, add it in $postData as key => value
	 *
	 * @param string $httpMethod Method for requesting data. Only used if no post or get request will be made
	 *
	 * @return ocs
	 */
	public function request($relativeUrl='', $postData=array(), $httpMethod=self::HTTP_GET) {
		$ch = curl_init();
		//Standard request information
		curl_setopt($ch, CURLOPT_URL, $this->apiUrl.$relativeUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERPWD, $this->ocUser.':'.$this->ocPass);
		//check if there is any data provided in the $postData array
		if(count($postData) > 0) {
			$keyValArray = array();
			//convert array to new array with 'key="value"' string
			foreach($postData as $key => $val) $keyValArray[] = $key.'='.$val;
			//set length of content
			curl_setopt($ch,CURLOPT_POST,count($postData));
			//add post data
			curl_setopt($ch,CURLOPT_POSTFIELDS, implode('&', $keyValArray));
		}

		if($httpMethod !== self::HTTP_GET) {
			//if $httpMethod is something different from GET then set this custom request
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $httpMethod);
		}
		//send request
		$requestResult = curl_exec($ch);
		//close resource
		curl_close($ch);
		//check if there was an error -> not parsing will result in an error
		if($requestResult !== false) {
			//parse and return the xml result
			$this->parse(simplexml_load_string($requestResult));
		}
		//return myself
		return $this;
	}

	/**
	 * Parses the ocs return of an request and maps it to usable content
	 * @param SimpleXMLElement $simpleXMLElement
	 */
	private function parse(\SimpleXMLElement $simpleXMLElement) {
		//map meta information
		$this->statusCode	= (int) $simpleXMLElement->meta->statuscode;
		$this->status		= (string) $simpleXMLElement->meta->status;
		$this->message		= (string) $simpleXMLElement->meta->message;
		//map data information
		$this->data			= $simpleXMLElement->data;
	}

	/**
	 * @return int
	 */
	public function getStatusCode() {
		return $this->statusCode;
	}

	/**
	 * @return string
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * @return string
	 */
	public function getMessage() {
		return $this->message;
	}

	/**
	 * @param callable $filter
	 *
	 * @return array
	 */
	public function getData(callable $filter=null) {
		//optionally you can set a closure as filter for this method
		if(is_callable($filter)) {
			return $filter($this->data);
		}
		//if not is set, return default requested data
		return $this->data;
	}
}

