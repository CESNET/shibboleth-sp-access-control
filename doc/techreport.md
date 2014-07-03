# Shibboleth Service Provider Access Control

## Introduction
Single Sign-On (SSO) is a very comfort technique to access diverse services using one login user name and password. However, running a service in a federation does not necessarily mean that every single user authenticated against an Identity Provider (IdP) must be able to log in to any Service Provider (SP) and use its resources or services. Depending on configuration, the IdP supplies a SP with various user attributes that might be used to precisely control access to a service. For example, teachers might be allowed to use a service while students are not.

Controlling access is possible not only by using user attributes...

## Technical Background
Access control rules are defined in an XML document. More information about writting rules is available in official Shibboleth [documentation][NativeSPXMLAccessControl].

## Example Use Case
[CESNET][] operates [eduID.cz][] federation which has been intended for academia users only. However, having public libraries added in to our federation, new non-academia users are present in the federation. Those users should not be allowed to utilize some services due to various reasons.

Although the solution we present is not the only one possible, we have decided to implement it since administrators of SPs that do not care about public library users do not need to change anything in their SP configuration. Only those SPs that would like to disallow public library users have to change configuration.

In the following paragraphs, we present our solution to filter users based on `affiliation` attribute.

### Apache Configuration
```apache
<Directory /limit/access>
    AuthType shibboleth
    ShibRequestSetting requireSession 1
    ShibAccessControl /path/to/ac.xml 
</Directory>
```

### Shibboleth Configuration
We have to set a prefix for metadata attributes in `shibboleth2.xml` file inside `<ApplicationDefaults>` elment, for example:

```xml
<ApplicationDefaults entityID="https://example.org/shibboleth/" metadataAttributePrefix="md-">
```

Next, we can define attribute mapping in `attribute-map.xml`. Say, we have defined the following attribute in our metadata:

```xml
<mdattr:EntityAttributes>
    <mdasrt:Attribute Name="http://macedir.org/entity-category" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri">
        <mdasrt:AttributeValue>http://eduid.cz/uri/idp-group/library</mdasrt:AttributeValue>
    </mdasrt:Attribute>
</mdattr:EntityAttributes>
```

So, the mapping can be done using the following code:

```xml
<Attribute name="http://macedir.org/entity-category" id="entityCategory" />
```

In this situation, `md-entityCategory` variable contains the value of the attribute which might be used in order to control access:

```xml
<AccessControl type="edu.internet2.middleware.shibboleth.sp.provider.XMLAccessControl">
    <OR>    
        <RuleRegex require="affiliation">^employee@.+\.cz$</RuleRegex>    
            <NOT>    
                <Rule require="md-entityCategory">http://eduid.cz/uri/idp-group/library</Rule>       
            </NOT>    
    </OR>    
</AccessControl>
```

This access control allows only users with `eduPersonScopedAffiliation` attribute matching regular expression `^employee@.+\.cz$`.

## Other Use Cases
...

## Issues
One issue that may appear with this type of access control is related with attributes. In an federation envrionment, there is no guarantee that the IdP releases all the required attributes (in this example -- eduPersonScopedAffiliation) at all or that it releases the attributes to all the SPs in the federation. Moreover, attributes may be released, however, chances are that values will be different than expected. In such situations, sensible error handling should take place to inform a user about what went wrong. Also, negotiating attribute release with the IdP would be ideal when possible.

If an error occurs, we do not know the reason. There are two approaches. First, redirecting user to a web page to log environment variables and trying to diagnose what has happend. Second, integrating access control to the application users are logging in. The second approach improve user experience, but it is complicated to implement.

## Conclusion
----------------------------------------------------------------------

## References
[CESNET]: http://www.cesnet.cz/
[eduID.cz]: http://www.eduid.cz/
[eduID.cz-SPAC]: https://www.eduid.cz/cs/tech/filtrovani-uzivatelu-dokumentace
[NativeSPXMLAccessControl]: https://wiki.shibboleth.net/confluence/display/SHIB2/NativeSPXMLAccessControl


