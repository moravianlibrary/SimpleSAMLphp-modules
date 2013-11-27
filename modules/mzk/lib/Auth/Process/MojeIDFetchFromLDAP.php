<?php

class sspmod_mzk_Auth_Process_mojeIDFetchFromLDAP extends SimpleSAML_Auth_ProcessingFilter {

	private $sourceId;
	
	private $attributeName;

	/**
	 * Initialize this filter.
	 *
	 * @param array $config  Configuration information about this filter.
	 * @param mixed $reserved  For future use.
	 */
	public function __construct($config, $reserved) {
		parent::__construct($config, $reserved);
		$this->source    = isset($config['source'])    ? $config['source']    : 'default';
		$this->attribute = isset($config['attribute']) ? $config['attribute'] : 'labeledURI';
	}


	/**
	 * Add attributes from an LDAP server.
	 *
	 * @param array &$request  The current request
	 */
	public function process(&$request) {
		$attributes = &$request['Attributes'];
		if (isset($attributes['openid.local_id'])) {
			$openId = $attributes['openid.local_id'][0];
			$source = SimpleSAML_Auth_Source::getById('default');
			$attributes = $source->lookupUserByAttributeName('labeledURI', $openId);
		}
	}

}