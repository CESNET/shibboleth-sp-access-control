<?php

return array(
    'header' => array(
        'authors' => array(
            'Ivan Novakov',
            'Jan Oppolzer'
        ),
        'date' => '10.10.2014',
        'keywords' => 'Access Control, Apache, Attributes, Authorization, Federated Identity, Identity Provider, Service Provider, Shibboleth',
        'abstract' => 'This technical report deals with implementing access control to a resource secured by a Shibboleth Service Provider. It contains step by step instructions how to permit only a specified group of users to access a service. A decision about permitting a user is made on the Service Provider\'s side based on an attribute found in federation metadata. This document does not cover neither Shibboleth Service Provider nor Shibboleth Identity Provider installation and configuration, it is meant for setups where only access control is required to be implemented on top of a working federated identity environment.'
    ),
    'biblist' => array(
        'Shibboleth' => 'http://shibboleth.net/',
        'Entitlements' => 'https://www.incommon.org/federation/attributesummary.html',
        'Apache' => 'http://httpd.apache.org/',
        'CESNET' => 'http://www.cesnet.cz/',
        'eduID.cz' => 'http://www.eduid.cz/',
        'NativeSPApplication' => 'https://wiki.shibboleth.net/confluence/display/SHIB2/NativeSPApplication',
        'NativeSPApacheConfig' => 'https://wiki.shibboleth.net/confluence/display/SHIB2/NativeSPApacheConfig',
        'NativeSPXMLAccessControl' => 'https://wiki.shibboleth.net/confluence/display/SHIB2/NativeSPXMLAccessControl',
        'NativeSPAddAttribute' => 'https://wiki.shibboleth.net/confluence/display/SHIB2/NativeSPAddAttribute',
        'NativeSPhtaccess' => 'https://wiki.shibboleth.net/confluence/display/SHIB2/NativeSPhtaccess',
        'ErrorHandling' => 'https://github.com/CESNET/shibboleth-sp-access-control/tree/master/access_errors'
    )
);
