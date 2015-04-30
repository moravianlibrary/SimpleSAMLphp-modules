<?php
class sspmod_xcncip2_Auth_Source_XCNCIP2 extends sspmod_core_Auth_UserPassBase {

	protected $url;

	protected $eduPersonScopedAffiliation;

	public function __construct($info, &$config) {
		parent::__construct($info, $config);
		$this->url = $config['url'];
		$this->eduPersonScopedAffiliation = $config['eduPersonScopedAffiliation'];
	}

	public function login($username, $password) {
		$requestBody = $this->getLookupUserRequest($username, $password);
		$response = $this->doRequest($requestBody);
		$id = $response->xpath(
			'ns1:LookupUserResponse/ns1:UserId/ns1:UserIdentifierValue'
		);
		if (!empty($id)) {
			return array(
				'uid' => array($username),
				'eduPersonScopedAffiliation' => $this->eduPersonScopedAffiliation,
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
		$response = curl_exec($req);
		print "$body";
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
				'</ns1:LookupUser>' .
			'</ns1:NCIPMessage>';
	}

}
?>
