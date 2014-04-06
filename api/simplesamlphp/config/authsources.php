<?php

$config = array(


//
//	// An authentication source which can authenticate against both SAML 2.0
//	// and Shibboleth 1.3 IdPs.
	'default-sp' => array(
		'saml:SP',

		// The entity ID of this SP.
		// Can be NULL/unset, in which case an entity ID is generated based on the metadata URL.
		'entityID' => NULL,

		// The entity ID of the IdP this should SP should contact.
		// Can be NULL/unset, in which case the user will be shown a list of available IdPs.
		'idp' => 'https://openidp.feide.no',

		// The URL to the discovery service.
		// Can be NULL/unset, in which case a builtin discovery service will be used.
		'discoURL' => NULL,
	),

	'live-sp' => array(
		'saml:SP',
		'entityID' => 'https://www.wall150ans.com',
		'idp' => 'https://login.safe.socgen/wall',
		'redirect.sign' => TRUE,
		'redirect.validate' => TRUE,
		'signature.algorithm' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
		'privatekey' => 'saml.pem',
		'certificate' => 'saml.crt',
	),

);
