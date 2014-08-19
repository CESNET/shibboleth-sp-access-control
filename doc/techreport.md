# Shibboleth Service Provider Access Control

## Abstract
This technical report deals with access control to a service accessible by a Shibboleth Service Provider. It contains a step by step instruction how to permit only a specified group of users to utilize the service. A decision about permiting a user is made on the Service Provider's side based on an attribute supplied by an Identity Provider. This document does not cover neither Shibboleth Service Provider nor Shibboleth Identity Provider installation and configuration, it is meant for setups where just access control is required to be implemented on top of a working federated environment.

*Keywords:* Access Control, Apache, Attributes, Authorization, Identity Provider, Service Provider, Shibboleth

## Introduction
As the number of online services is growing, so grows the number of user names and passwords used to login which is uncomfortable for users. Simultaneously, user management is getting very time consuming and more complicated which is uncomfortable for organizations operating these services. Moreover, if a user should not be allowed to access any service anymore, administrators have to delete that user's account on every single service.

This unpleasant issue is very well addressed by Single Sing-On (SSO) technology, alternatively also known as federated identity access, implemented by open-source project [Shibboleth][]. This software implements both the essential elements of federated identity access, i.e. an Identity Provider (IdP) and a Service Provider (SP), which are shortly described later in the text.

Using SSO means that with just a single user account (user name and password) managed by a single home organization, users are capable of accessing a great amount of applications easily not only within their home organization but even in a different organization. And when a user should not have access to services anymore, only one account has to be deactivated or deleted. Moreover, depending on system configuration, users do not need to repeatedly type their credentials when connecting to a different service after initial login until their login session is expired. Users need to type their user name and password usually once maybe twice a day and can utilize lots of applications.

Single Sing-On mechanism is composed of two main entities as stated above. The first entity, an Identity Provider, manages user authentization and provides user attributes. Nevertheless, authentization (i.e. user verification) itself is not enough as it only determines who the user is, but it does not decide whether the user is allowed to use the application. The second entity, a Service Provider, in this approach controls access to the application a user is connecting to and consumes user attributes provided by the IdP. So, the second step after authentization is authorization (i.e. giving a permission to do/use something). This second step (authorization, access control, ...) at the SP is the main topic of this paper. Without proper access control management all users are allowed to utilize any service which is probably not a desired situation.

There are three various access control mechanisms. First, access control based on web server rules if [Apache][] is employed. Second, a [Shibboleth][] SP can manage access. And third, the application a user is logging in can control access. In this document, the second approach is described and a working example from [eduID.cz][] federation operated by the Czech NREN [CESNET][] is presented.

The solution presented assume that an Identity Provider supplies a Service Provider with various user attributes that are used to precisely control access to a service. For example, although teachers and students have their account from the same university, teachers are allowed to use an application for planning exams while students are not, etc.

## Technical Background
% TODO

% There are [three elements][NativeSPAccessControl] to attach an access control policy to the resource, i.e. `<htaccess>`, `<AccessControlProvider>` and `<AccessControl>`.

Access control rules are defined in an XML document. More information about writting rules is available in official Shibboleth [documentation][NativeSPXMLAccessControl].

## Example Use Case
% TODO

[CESNET][] operates [eduID.cz][] federation which has been intended for academia users only. However, having public libraries added in to our federation recently, new non-academia users are present in the federation. Those users should not be allowed to utilize some services due to various reasons.

Although the solution we present is not the only one possible, we have decided to implement it since administrators of SPs that do not care about public library users do not need to change anything in their SP configuration. Only those SPs that would like to disallow public library users have to change their configuration.

In the following subsections, we present our solution to filter users based on `affiliation` attribute that have to suit regular expression `^employee@.+\.cz$`, i.e. only employees from Czech organizations are allowed.

### Apache Configuration
Apache needs to know which directory we mean to protect by a Shibboleth session. Assume that `/limit/access` directory is the one that can be accessed only by authentizated users with the proper value of the `affiliation` attribute defined in `/path/to/ac.xml` file.

Code snippet for Apache would then look like this:

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
[Shibboleth]: http://shibboleth.net/
[Apache]: http://httpd.apache.org/
[CESNET]: http://www.cesnet.cz/
[eduID.cz]: http://www.eduid.cz/
[eduID.cz-SPAC]: https://www.eduid.cz/cs/tech/filtrovani-uzivatelu-dokumentace
[NativeSPAccessControl]: https://wiki.shibboleth.net/confluence/display/SHIB2/NativeSPAccessControl
[NativeSPXMLAccessControl]: https://wiki.shibboleth.net/confluence/display/SHIB2/NativeSPXMLAccessControl

