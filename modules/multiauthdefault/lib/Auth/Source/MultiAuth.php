<?php

/**
 * Authentication source which let the user chooses among a list of
 * other authentication sources
 *
 * @author Lorenzo Gil, Yaco Sistemas S.L.
 * @package simpleSAMLphp
 * @version $Id$
 */

class sspmod_multiauthdefault_Auth_Source_MultiAuth extends sspmod_multiauth_Auth_Source_MultiAuth {

	const DEFAULT_SOURCE_ID = 'default';

	/**
	 * Constructor for this authentication source.
	 *
	 * @param array $info	 Information about this authentication source.
	 * @param array $config	 Configuration.
	 */
	public function __construct($info, $config) {
		assert('is_array($info)');
		assert('is_array($config)');

		/* Call the parent constructor first, as required by the interface. */
		parent::__construct($info, $config);
	}
	
	/**
	 * Prompt the user with a list of authentication sources.
	 *
	 * This method saves the information about the configured sources,
	 * and redirects to a page where the user must select one of these
	 * authentication sources.
	 *
	 * This method never return. The authentication process is finished
	 * in the delegateAuthentication method.
	 *
	 * @param array &$state	 Information about the current authentication.
	 */
	public function authenticate(&$state) {
		assert('is_array($state)');

		$state[self::AUTHID] = $this->authId;

		/* Save the $state array, so that we can restore if after a redirect */
		$id = SimpleSAML_Auth_State::saveState($state, self::STAGEID);

		/* Redirect to the select source page. We include the identifier of the
		saved state array as a parameter to the login form */
		$url = SimpleSAML_Module::getModuleURL('multiauthdefault/loginuserpass.php');
		$params = array('AuthState' => $id);

		SimpleSAML_Utilities::redirect($url, $params);

		/* The previous function never returns, so this code is never
		executed */
		assert('FALSE');
	}
	
	public static function handleLogin($authStateId, $username, $password) {
		assert('is_string($authStateId)');
		assert('is_string($username)');
		assert('is_string($password)');

		/* Here we retrieve the state array we saved in the authenticate-function. */
		$state = SimpleSAML_Auth_State::loadState($authStateId, self::STAGEID);
		$state[self::AUTHID] = self::DEFAULT_SOURCE_ID;

		/* Retrieve the authentication source we are executing. */
		assert('array_key_exists(self::AUTHID, $state)');
		$source = SimpleSAML_Auth_Source::getById($state[self::AUTHID]);
		if ($source === NULL) {
			throw new Exception('Could not find authentication source with id ' . $state[self::AUTHID]);
		}

		/*
		 * $source now contains the authentication source on which authenticate()
		 * was called. We should call login() on the same authentication source.
		 */

		try {
			/* Attempt to log in. */
			$attributes = $source->login($username, $password);
		} catch (SimpleSAML_Error_Error $e) {
			/*
			 * Login failed. Return the error code to the login form, so that it
			 * can display an error message to the user.
			 */
			return $e->getErrorCode();
		}

		/* Save the attributes we received from the login-function in the $state-array. */
		assert('is_array($attributes)');
		$state['Attributes'] = $attributes;

		/* Return control to simpleSAMLphp after successful authentication. */
		SimpleSAML_Auth_Source::completeAuth($state);
	}
	
	/**
	 * Log out from this authentication source.
	 *
	 * This method retrieves the authentication source used for this
	 * session and then call the logout method on it.
	 *
	 * @param array &$state	 Information about the current logout operation.
	 */
	public function logout(&$state) {
		assert('is_array($state)');

		/* Get the source that was used to authenticate */
		$session = SimpleSAML_Session::getInstance();
		$authId = $session->getData(self::SESSION_SOURCE, $this->authId);

		//$source = SimpleSAML_Auth_Source::getById($authId);
		$source = SimpleSAML_Auth_Source::getById(self::DEFAULT_SOURCE_ID);
		if ($source === NULL) {
			throw new Exception('Invalid authentication source during logout: ' . $source);
		}
		/* Then, do the logout on it */
		$source->logout($state);
	}

}

?>
