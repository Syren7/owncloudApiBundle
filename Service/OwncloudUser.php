<?php

namespace Syren7\OwncloudApiBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Syren7\OwncloudApiBundle\lib\ocs;
use Sabre\DAV\Exception;

class OwncloudUser {
	//api routes for user access
	const TYPE_USERS_ADD				= '/users';
	const TYPE_USERS_GET 				= '/users';
	const TYPE_USERS_REMOVE				= '/users/{username}';
	const TYPE_USERS_EDIT 				= '/users/{username}';
	const TYPE_USERS_GROUPS 			= '/users/{username}/groups';
	const TYPE_USERS_ADDTOGROUP 		= '/users/{username}/groups';
	const TYPE_USERS_REMOVEFROMGROUP 	= '/users/{username}/groups';
	const TYPE_USERS_ADDSUBADMIN 		= '/users/{username}/subadmins';
	const TYPE_USERS_REMOVESUBADMIN		= '/users/{username}/subadmins';
	const TYPE_USERS_SUBADMINGROUPS		= '/users/{username}/subadmins';
	//API routes for groups
	const TYPE_GROUP_GET				= '/groups';
	const TYPE_GROUP_ADD				= '/groups';
	const TYPE_GROUP_DELETE				= '/groups/{groupname}';
	const TYPE_GROUP_MEMBERS			= '/groups/{groupname}';
	const TYPE_GROUP_SUBADMINS			= '/groups/{groupname}/subadmins';
	//Fields for user update
	const FIELD_DISPLAY					= 'display';
	const FIELD_QUOTA					= 'quota';
	const FIELD_PASSWORD				= 'password';
	const FIELD_EMAIL					= 'email';
	/**
	 * @var ocs $ocs
	 */
	protected $ocs;

	/**
	 * OwncloudApiUser constructor.
	 *
	 * @param ContainerInterface $containerInterface
	 */
	public function __construct(ContainerInterface $containerInterface) {
		//reading parameters from config
		$ocHost = $containerInterface->getParameter('syren7_owncloud.host');
		$ocUser = $containerInterface->getParameter('syren7_owncloud.user');
		$ocPass = $containerInterface->getParameter('syren7_owncloud.pass');

		$this->ocs = new ocs($ocHost, $ocUser, $ocPass);
	}

	/**
	 * @param string $username New user to be created
	 * @param string $password Password for new user
	 *
	 * @return bool
	 */
	public function createUser($username, $password) {
		return $this->ocs->request($this->generateUrl(self::TYPE_USERS_ADD), array(
			'userid' 	=> $username,
			'password' 	=> $password,
		))->getStatusCode() == 100;
	}

	/**
	 * @param string $username username to be updated
	 * @param string $key Which field should be updated: email, quota, display, password
	 * @param string $value New value for field
	 *
	 * @return bool
	 *
	 */
	public function updateUser($username, $key, $value) {
		//small check on key field and check on empty value
		if(!in_array($key, array('email', 'quota', 'display', 'password')) || trim($value) === '') return false;
		//execute
		return $this->ocs->request(
			$this->generateUrl(self::TYPE_USERS_EDIT, $username),
			array('key' => $key, 'value' => $value),
			ocs::HTTP_PUT
		)->getStatusCode() == 100;
	}

	/**
	 * updateUser() helper method for updateing the password of an user
	 * @param $username
	 * @param $password
	 *
	 * @return bool
	 */
	public function updateUserPassword($username, $password) {
		return $this->updateUser($username, self::FIELD_PASSWORD, $password);
	}

	/**
	 * updateUser() helper method for updateing the quota of an user
	 * @param $username
	 * @param $quota
	 *
	 * @return bool
	 */
	public function updateUserQuota($username, $quota) {
		return $this->updateUser($username, self::FIELD_QUOTA, $quota);
	}

	/**
	 * updateUser() helper method for updateing the email address of an user
	 * @param $username
	 * @param $email
	 *
	 * @return bool
	 */
	public function updateUserEmail($username, $email) {
		return $this->updateUser($username, self::FIELD_EMAIL, $email);
	}

	/**
	 * updateUser() helper method for updateing the displayname of an user
	 * @param $username
	 * @param $displayName
	 *
	 * @return bool
	 */
	public function updateUserDisplayname($username, $displayName) {
		return $this->updateUser($username, self::FIELD_DISPLAY, $displayName);
	}

	/**
	 * @param string $username Name of user to be deleted
	 *
	 * @return bool
	 */
	public function removeUser($username) {
		return $this->ocs->request(
			$this->generateUrl(self::TYPE_USERS_REMOVE, $username),
			array(),
			ocs::HTTP_DELETE
		)->getStatusCode() == 100;
	}

	/**
	 * Returns an array with all available users from cloud or false on request error
	 * @return array|bool
	 */
	public function getUsers() {
		$result = $this->ocs->request($this->generateUrl(self::TYPE_USERS_GET));

		if($result->getStatusCode() == 100) {
			return $result->getData(function($data) {
				return (array) $data->users->element;
			});
		}

		return false;
	}

	/**
	 * Retrieves a list of groups the specified user is a member of.
	 * @param string $username
	 *
	 * @return array|bool
	 */
	public function getGroupsOfUser($username) {
		$result = $this->ocs->request($this->generateUrl(self::TYPE_USERS_GROUPS, $username));

		if($result->getStatusCode() == 100) {
			return $result->getData(function($data) {
				return (array) $data->groups->element;
			});
		}

		return false;
	}

	/**
	 * Adds the specified user to the specified group
	 * @param $username
	 * @param $groupname
	 *
	 * @return bool
	 */
	public function addUserToGroup($username, $groupname) {
		return $this->ocs->request(
			$this->generateUrl(self::TYPE_USERS_ADDTOGROUP, $username),
			array('groupid' => $groupname),
			ocs::HTTP_POST
		)->getStatusCode() == 100;
	}

	/**
	 * Removes the specified user from the specified group
	 *
	 * @param $username
	 * @param $groupname
	 *
	 * @return bool
	 */
	public function removeUserFromGroup($username, $groupname) {
		return $this->ocs->request(
			$this->generateUrl(self::TYPE_USERS_REMOVEFROMGROUP, $username),
			array('groupid' => $groupname),
			ocs::HTTP_DELETE
		)->getStatusCode() == 100;
	}

	/**
	 * Makes a user the subadmin of a group
	 *
	 * @param $username
	 * @param $groupname
	 *
	 * @return bool
	 */
	public function createSubAdmin($username, $groupname) {
		return $this->ocs->request(
			$this->generateUrl(self::TYPE_USERS_ADDSUBADMIN, $username),
			array('groupid' => $groupname),
			ocs::HTTP_POST
		)->getStatusCode() == 100;
	}

	/**
	 * Removes the subadmin rights for the user specified from the group specified
	 *
	 * @param $username
	 * @param $groupname
	 *
	 * @return bool
	 */
	public function removeSubAdmin($username, $groupname) {
		return $this->ocs->request(
			$this->generateUrl(self::TYPE_USERS_REMOVESUBADMIN, $username),
			array('groupid' => $groupname),
			ocs::HTTP_DELETE
		)->getStatusCode() == 100;
	}

	/**
	 * Returns the groups in which the user is a subadmin
	 *
	 * @param $username
	 *
	 * @return array|bool
	 */
	public function getSubAdminGroups($username) {
		$result = $this->ocs->request($this->generateUrl(self::TYPE_USERS_SUBADMINGROUPS, $username));

		if($result->getStatusCode() == 100) {
			return $result->getData(function($data) {
				return (array) $data->element;
			});
		}

		return false;
	}

	/**
	 * Retrieves a list of groups from the ownCloud server
	 *
	 * @return array|bool
	 */
	public function getGroups() {
		$result = $this->ocs->request($this->generateUrl(self::TYPE_GROUP_GET));

		if($result->getStatusCode() == 100) {
			return $result->getData(function($data) {
				return (array) $data->groups->element;
			});
		}

		return false;
	}

	/**
	 * Adds a new group
	 * @param $groupname
	 *
	 * @return bool
	 */
	public function addgroup($groupname) {
		return $this->ocs->request($this->generateUrl(self::TYPE_GROUP_ADD), array(
			'groupid' 	=> $groupname,
		))->getStatusCode() == 100;
	}

	/**
	 * Retrieves a list of group members
	 * @param $groupname
	 *
	 * @return array|bool
	 */
	public function getGroupMembers($groupname) {
		$result = $this->ocs->request($this->generateUrl(self::TYPE_GROUP_MEMBERS, $groupname));

		if($result->getStatusCode() == 100) {
			return $result->getData(function($data) {
				return (array) $data->users->element;
			});
		}

		return false;
	}

	/**
	 * Returns subadmins of the group
	 * @param $groupname
	 *
	 * @return array|bool
	 */
	public function getSubAdmins($groupname) {
		$result = $this->ocs->request($this->generateUrl(self::TYPE_GROUP_SUBADMINS, $groupname));

		if($result->getStatusCode() == 100) {
			return $result->getData(function($data) {
				return (array) $data->element;
			});
		}

		return false;
	}

	/**
	 * Removes a group
	 * @param $groupname
	 *
	 * @return bool
	 */
	public function deleteGroup($groupname) {
		return $this->ocs->request($this->generateUrl(self::TYPE_GROUP_DELETE, $groupname),
			array(),
			ocs::HTTP_DELETE
		)->getStatusCode() == 100;
	}

	/**
	 * Helper Method for creating suburls for request
	 * @param string $type
	 * @param string $name
	 *
	 * @return string
	 */
	private function generateUrl($type, $name='') {
		$search = array(
			'{username}',
			'{groupname}',
		);

		$replace = array(
			$name,
			$name,
		);

		return str_replace($search, $replace, $type);
	}
}