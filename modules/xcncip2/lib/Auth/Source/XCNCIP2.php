<?php
class sspmod_xcncip2_Auth_Source_XCNCIP2 extends sspmod_core_Auth_UserPassBase {

	protected $url;

	protected $eppnScope;

        protected $trustSSLHost;

	protected $certificateAuthority;

	protected $eduPersonScopedAffiliation;

	public function __construct($info, &$config) {
		parent::__construct($info, $config);

		$fullname = $config['fullname'];
		print("<fullname value=\"$fullname\"></fullname>"); // Will be removed later by jQuery
		// It is being used to set later the Title based on authsource choosed ..

		$this->url = $config['url'];
		$this->eppnScope = $config['eppnScope'];

		if(empty($this->eppnScope)) {
			throw new SimpleSAML_Error_Exception('Cannot have eppnScope empty! .. You have to set it in authsource.php');
		}

		$this->trustSSLHost = $config['trustSSLHost'];
		$this->certificateAuthority = $config['certificateAuthority'];
		$this->eduPersonScopedAffiliation = $config['eduPersonScopedAffiliation'];
	}

	public function login($username, $password) {
		$requestBody = $this->getLookupUserRequest($username, $password);
		$response = $this->doRequest($requestBody);
		$id = $response->xpath(
				'ns1:LookupUserResponse/ns1:UserId/ns1:UserIdentifierValue'
				);
		if (!empty($id)) {

			$userId = (String) $response->xpath(
					'ns1:LookupUserResponse/ns1:UserId/ns1:UserIdentifierValue')[0];

			if(empty($userId)) {
				throw new Exception('UserId was not found - cannot continue without user\'s Institution Id Number');
			}

			$agencyId = (String) $response->xpath(
					'ns1:LookupUserResponse/ns1:UserId/ns1:AgencyId')[0];

			if(empty($agencyId)) {
				throw new Exception('AgencyId was not found - cannot continue authenticating without SIGLA');
			}

			$electronicAddresses = $response->xpath(
					'ns1:LookupUserResponse/ns1:UserOptionalFields/ns1:UserAddressInformation/ns1:ElectronicAddress'
					);

			$mail = null;
			foreach ($electronicAddresses as $recent) {
				if (strpos((String) $recent->xpath('ns1:ElectronicAddressType')[0], 'mail') !== FALSE) {
					$mail = (String) $recent->xpath('ns1:ElectronicAddressData')[0];
					break;
				}
			}

			$firstname = (String) $response->xpath(
					'ns1:LookupUserResponse/ns1:UserOptionalFields/ns1:NameInformation/' .
					'ns1:PersonalNameInformation/ns1:StructuredPersonalUserName/ns1:GivenName')[0];

			$lastname = (String) $response->xpath(
					'ns1:LookupUserResponse/ns1:UserOptionalFields/ns1:NameInformation/' .
					'ns1:PersonalNameInformation/ns1:StructuredPersonalUserName/ns1:Surname')[0];
			return array(
					'eduPersonPrincipalName' => array($username . '@' . $this->eppnScope),
					'eduPersonScopedAffiliation' => $this->eduPersonScopedAffiliation,
					'userLibraryId' => array($userId),
					'mail' => array($mail),
					'givenName' => array($firstname),
					'sn' => array($lastname),
					'homeLib' => array($agencyId),
				    );
		} else {
			throw new SimpleSAML_Error_Error('WRONGUSERPASS');
		}

	}

	protected function doRequest($body) {
		$req = curl_init($this->url);
		curl_setopt($req, CURLOPT_POST, 1);
		curl_setopt($req, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($req, CURLOPT_HTTPHEADER, array(
					'Content-type: application/xml; charset=utf-8',
					));
		curl_setopt($req, CURLOPT_POSTFIELDS, $body);

		if ($this->trustSSLHost) {
			curl_setopt($req, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($req, CURLOPT_SSL_VERIFYPEER, 0);
		} else {
			curl_setopt($req, CURLOPT_VERBOSE, 1);
			curl_setopt($req, CURLOPT_CERTINFO, 1);

			if (!empty($this->certificateAuthority)) 
				curl_setopt($req, CURLOPT_CAINFO, $this->certificateAuthority);
		}
		
		$response = curl_exec($req);
		$result = simplexml_load_string($response);

		if (is_a($result, 'SimpleXMLElement')) {
			$result->registerXPathNamespace('ns1', 'http://www.niso.org/2008/ncip');
			return $result;
		} else {
			throw new RuntimeException("Problem parsing XML");
		}
	}

	protected function getLookupUserRequest($username, $password) {
		return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
			'<ns1:NCIPMessage xmlns:ns1="http://www.niso.org/2008/ncip" ' .
			'ns1:version="http://www.niso.org/schemas/ncip/v2_0/imp1/' .
			'xsd/ncip_v2_0.xsd">' .
			'<ns1:LookupUser>' .
			'<ns1:AuthenticationInput>' .
			'<ns1:AuthenticationInputData>' .
			htmlspecialchars($username) .
			'</ns1:AuthenticationInputData>' .
			'<ns1:AuthenticationDataFormatType>' .
			'text/plain' .
			'</ns1:AuthenticationDataFormatType>' .
			'<ns1:AuthenticationInputType>' .
			'User Id' .
			'</ns1:AuthenticationInputType>' .
			'</ns1:AuthenticationInput>' .
			'<ns1:AuthenticationInput>' .
			'<ns1:AuthenticationInputData>' .
			htmlspecialchars($password) .
			'</ns1:AuthenticationInputData>' .
			'<ns1:AuthenticationDataFormatType>' .
			'text/plain' .
			'</ns1:AuthenticationDataFormatType>' .
			'<ns1:AuthenticationInputType>' .
			'Password' .
			'</ns1:AuthenticationInputType>' .
			'</ns1:AuthenticationInput>' .
			'<ns1:UserElementType ns1:Scheme="http://www.niso.org/ncip/v1_0/schemes/userelementtype/userelementtype.scm">Name Information</ns1:UserElementType>' .
			'<ns1:UserElementType ns1:Scheme="http://www.niso.org/ncip/v1_0/schemes/userelementtype/userelementtype.scm">User Address Information</ns1:UserElementType>' .
			'</ns1:LookupUser>' .
			'</ns1:NCIPMessage>';
	}

}
?>
