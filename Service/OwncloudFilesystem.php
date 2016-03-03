<?php

namespace Syren7\OwncloudApiBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use League\Flysystem\File;
use Syren7\OwncloudApiBundle\Model\FsObject;
use League\Flysystem\Filesystem;
use League\Flysystem\WebDAV\WebDAVAdapter;
use Sabre\DAV\Client;
use Sabre\DAV\Exception;
use Sabre\DAV\Property\ResourceType;
use League\Flysystem\FileNotFoundException;

class OwncloudFilesystem {

	/**
	 * @var Client $client
	 */
	protected $client;
	/**
	 * @var Filesystem $fs
	 */
	protected $fs;
	/**
	 * @var WebDAVAdapter $adapter
	 */
	protected $adapter;

	/**
	 * OwncloudApi constructor.
	 *
	 * @param string $ocHost
	 * @param string $ocUser
	 * @param string $ocPass
	 * @param string $ocFolder
	 */
	public function __construct($ocHost, $ocUser, $ocPass, $ocFolder) {
		//check wherever there is a leading or trailing slash on $ocFolder
		if($ocFolder !== '') {
			//if there is a leading / (start at 0 with 1 length is a /)
			if(substr($ocFolder, 0, 1) === '/') $ocFolder = substr($ocFolder, 1);
			//if there is no trailing slash, add one
			if($ocFolder[strlen($ocFolder)-1] !== '/') $ocFolder .= '/';
		}
		//check host for trailing slash
		if($ocHost[strlen($ocHost)-1] === '/') $ocHost = substr($ocHost, 0, strlen($ocHost)-1);
		//Create settings array for curl connection
		$settings = array(
			//the webdav url ist created from your owncloud url + '/remote.php/webdav/' + your specific folder if wished
			'baseUri' => $ocHost.'/remote.php/webdav/'.$ocFolder,
			'userName' => $ocUser,
			'password' => $ocPass,
		);
		//create required connection objects
		$this->client 	= new Client($settings);
		$this->adapter	= new WebDAVAdapter($this->client);
		$this->fs 		= new Filesystem($this->adapter);
	}


	/**
	 * Returns an ArrayCollection with the contents of the current requested folder
	 *
	 * @param string $folder Request foldername relative to root folder
	 *
	 * @return ArrayCollection|boolean Content of folder as array collection. If folder does not exist false
	 * @throws \Sabre\DAV\Exception
	 */
	public function getDirectoryContents($folder='') {
		try {
			return $this->parsePropResult(
				$this->client->propFind($folder, array(
					'{DAV:}resourcetype', #if its a folder you can use the ResourceType and check if it's from type {DAV:}collection
					'{DAV:}getcontentlength', #if a file: filesize in bytes
					'{DAV:}getlastmodified', #last modified date
					'{DAV:}quota-used-bytes', #if a dir: sum of data in bytes of it's contents
					'{DAV:}getcontenttype', #if a file: contenttype of file
				), 1)
			);
		}
		catch(Exception $e) {
			return false;
		}
	}

	/**
	 * Returns file handle from OC
	 * @param $path
	 *
	 * @return bool|File
	 */
	public function getFile($path) {
		$file = $this->fs->get($path);
		if($file instanceof File) {
			return $file;
		}

		return false;
	}

	/**
	 * Creates a new folder in
	 * @param string $directoryName
	 *
	 * @return bool
	 */
	public function createDirectory($directoryName) {
		return $this->fs->createDir($directoryName);
	}

	/**
	 * Removes a folder from oc
	 * @param string $directoryName
	 *
	 * @return bool
	 */
	public function removeDirectory($directoryName) {
		return $this->fs->deleteDir($directoryName);
	}

	/**
	 * Creates/uploads/updates a file on oc remote
	 *
	 * @param string $localPath  File on local server
	 * @param string $remotePath File on oc Server
	 * @throws FileNotFoundException
	 * @return bool
	 *
	 */
	public function createFile($localPath, $remotePath='') {
		//if local file not exists throw exception
		if(!file_exists($localPath)) {
			throw new FileNotFoundException("OwncloudApi: Local file not found");
		}
		//otherwise upload to server
		return $this->fs->put($remotePath, file_get_contents($localPath));
	}

	/**
	 * Remove a file from oc
	 * @param string $path
	 *
	 * @return bool
	 */
	public function removeFile($path='') {
		try {
			return $this->fs->delete($path);
		}
		catch(FileNotFoundException $e){
			return false;
		}
	}
	/**
	 * @param string 	$filePath 		Path to file on cloud
	 * @param array 	$xmlResponse	The XML DAV response from dav-server
	 *
	 * @return bool|FsObject
	 */
	private function createObjectFromXML($filePath, $xmlResponse) {
		if($xmlResponse) {
			//create Object
			$fsObject = new FsObject($filePath);
			/** @var ResourceType $resType */
			$resType = $xmlResponse['{DAV:}resourcetype'];
			//if $resType is invalid there is an error
			if($resType instanceof ResourceType) {
				//determine type from ResourceType
				//is('{DAV:}collection') checks this resource on type collection (means folder)
				if($resType->is('{DAV:}collection')) {
					$fsObject->setType(FsObject::TYPE_DIR);
				} else {
					$fsObject->setType(FsObject::TYPE_FILE);
					//get contenttype
					$fsObject->setContentType($xmlResponse['{DAV:}getcontenttype']);
				}
				//determine size if its a dir the size means the complete size of its contents
				//if its a file, size means the filesize in bytes
				if($fsObject->getType()==FsObject::TYPE_DIR) {
					$fsObject->setSize(intval($xmlResponse['{DAV:}quota-used-bytes']));
				} else {
					$fsObject->setSize(intval($xmlResponse['{DAV:}getcontentlength']));
				}
				//create DateTime object
				$fsObject->setDate(new \DateTime($xmlResponse['{DAV:}getlastmodified']));

				return $fsObject;
			}
		}
		//if invalid data then return false
		return false;
	}

	/**
	 * Method converts the array result of Client::propFind() into an correspondig ArrayCollection of FsObjects
	 *
	 * @param array $result
	 *
	 * @return ArrayCollection
	 */
	private function parsePropResult($result) {
		//init collection
		$data = new ArrayCollection();
		//check if there is valid content (not false)
		if($result) {
			foreach($result as $path => $entry) {
				//Note: $path (the array key is url encoded)
				$object = $this->createObjectFromXML(urldecode($path), $entry);
				//if object is not false add it to collection
				if($object !== false) {
					$data->add($object);
				}
			}
			//Remember: The first object of list is always the folder which contains all following objects (starting at index 1)
			//so perhaps you want to call: $yourResultCollection->remove(0) to remove the first element
		}
		//returning file tree
		return $data;
	}
}