# Shibboleth Service Provider Access Control

## Abstract
This technical report deals with access control to a service accessible by a Shibboleth Service Provider. It contains a step by step instruction how to permit only a specified group of users to utilize the service. A decision about permiting a user is made on the Service Provider's side based on an attribute supplied by an Identity Provider. This document does not cover neither Shibboleth Service Provider nor Shibboleth Identity Provider installation and configuration, it is meant for setups where just access control is required to be implemented on top of a working federated environment.

*Keywords:* Access Control, Apache, Attributes, Authorization, Identity Provider, Service Provider, Shibboleth

## Introduction
As the number of online services is growing, so grows the number of user names and passwords used to login which is uncomfortable for users. Simultaneously, user management is getting very time consuming and more complicated which is uncomfortable for organizations operating these services. Moreover, if a user should not be allowed to access any service anymore, administrators have to delete that user's account on every single service.

This unpleasant issue is very well addressed by Single Sing-On (SSO) technology, alternatively also known as federated identity access, implemented by open-source project [Shibboleth][]. This software implements both the essential elements of federated identity access, i.e. an Identity Provider (IdP) and a Service Provider (SP), which are shortly described later in the text.

Using SSO means that with just a single user account (user name and password) managed by a single home organization, users are capable of accessing a great amount of applications easily not only within their home organization but even in a different organization. And when a user should not have access to services anymore, only one account has to be deactivated or deleted. Moreover, depending on system configuration, users do not need to repeatedly type their credentials when connecting to a different service after initial login until their login session is expired. Users need to type their user name and password usually once maybe twice a day and can utilize lots of applications.

Single Sing-On mechanism is composed of two main entities as stated above. The first entity, an Identity Provider, manages user authentization and provides user attributes. Nevertheless, authentization (i.e. user verification) itself is not enough as it only determines who the user is, but it does not decide whether the user is allowed to use the application. The second entity, a Service Provider, in this approach controls access to the application a user is connecting to and consumes user attributes provided by the IdP. So, the second step after authentization is authorization (i.e. giving a permission to do/use something). This second step (authorization, access control, ...) at the SP is the main topic of this technical report. Without proper access control management all users are allowed to utilize any service which is probably not a desired situation.

There are three various access control mechanisms. First, access control based on web server rules if [Apache][] is employed. Second, a [Shibboleth][] SP can manage access. And third, the application a user is logging in can control access. In this document, the second approach is described and a working example from [eduID.cz][] federation operated by the Czech NREN [CESNET][] is presented.

The solution presented assume that an Identity Provider supplies a Service Provider with various user attributes that are used to precisely control access to a service. For example, users from the same organization might be allowed to connect to a resource or not depending on department, etc.

## Technical Background
There are various means to user filtering that have been discussed within [CESNET][]. Their differences lie in scalability, implementation speed and implementation demands:

  1. Separate metadata for libraries and other entities in the federation.
  2. Using attribute authority.
  3. Filtering based on a newly specified attribute.
  4. Filtering based on an existing attribute.

### Separate metadata
This quite easy solution has an important drawback. All library users (including library employees) would be disallowed to use services making this manner unacceptable.

### Attribute authority
Such a solution is unacceptable as well at this moment since this approach is very time consuming to implement and we have needed an immediate solution. However, in a distant future we could think of implementation.

### Using a new attribute
Specifying a new attribute (e.g. Research & Education) is very elegant and sophisticated, but it would require a long discussion within [eduID.cz][] federation. Moreover, all users would have to be tagged with a corresponding attribute value. Every IdP would have to release this attribute and every SP would have to control access based on this attribute. There might be situations, when a SP uses this attribute already, but an IdP does not releases it yet. As access to services could be limited during implementation, this solution is omitted.

### Using an existing attribute
Rejecting the previous manners, we have left with this attitude. Employing an already defined attribute, we have an efficient solution. Implementation is very easy and fast. There is no work to be done at any IdP. Only SPs requiring user filtering have to update configuration. No service interruptions is present during implementation.

## Example Use Case
[CESNET][] operates [eduID.cz][] federation which has been intended for academia users only. However, having public libraries added in to our federation recently, new non-academia users are present in the federation. Those users should not be allowed to utilize some services due to various reasons.

Although the solution we present is not the only one possible, we have decided to implement it since administrators of SPs that do not care about public library users do not need to change anything in their SP configuration. Only those SPs' administrators that would like to disallow public library users have to alter their configuration.

In the following subsections, we present our solution to filter users based on `affiliation` attribute that have to match regular expression `^employee@.+\.cz$`, i.e. only employees from Czech organizations are allowed.

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

For more detailed information about Apache configuration see official [Shibboleth documentation][NativeSPApacheConfig].

### Shibboleth Configuration
We have to set a prefix for metadata attributes in `shibboleth2.xml` file inside `<ApplicationDefaults>` elment. Setting this option will prefix attributes extracted from metadata with that value and enables applications to differentiate between attributes about the user and attributes about the user's identity provider [NativeSPApplication][].

To set the prefix to `md-` value, edit `shibboleth2.xml` appropriately:

```xml
<ApplicationDefaults entityID="https://example.org/shibboleth/" metadataAttributePrefix="md-">
```

Next, we can define attribute mapping in `attribute-map.xml` file. Say, we have defined the following attribute in our metadata, using which we add an attribute to IdPs operated by libraries:

```xml
<mdattr:EntityAttributes>
    <mdasrt:Attribute Name="http://macedir.org/entity-category" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri">
        <mdasrt:AttributeValue>http://eduid.cz/uri/idp-group/library</mdasrt:AttributeValue>
    </mdasrt:Attribute>
</mdattr:EntityAttributes>
```

So, mapping our attribute to `entityCategory` variable can be done using the following code:

```xml
<Attribute name="http://macedir.org/entity-category" id="entityCategory" />
```

In this situation, `entityCategory` variable prefixed with `md-` contains the value of the attribute (in our example `http://eduid.cz/uri/idp-group/library`) which might be used in order to control access which is defined by rules in an XML document:

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

This access control allows only users with `eduPersonScopedAffiliation` attribute matching regular expression `^employee@.+\.cz$`. Additional information about writting rules is available in official Shibboleth [documentation][NativeSPXMLAccessControl].

## Other Use Cases
Presented solution might be deployed in a number of various situations where access control is required as it is easy and clear. A few more extra examples on top of previously mentioned use case follow.

  1. Although teachers and students have their account from the same university, teachers are allowed to use an application for planning exams while students are not. And contrary, students are allowed to use an application for applying to exams while teachers are not.

  2. %%% ještě nějaký příklad...

  3. %%% ještě nějaký příklad...

## Issues
One issue that may appear with this type of access control is related with attributes. In an federation envrionment, there is no guarantee that an IdP releases all the required attributes (in this example -- eduPersonScopedAffiliation) at all or that it releases the attributes to all the SPs in the federation. Moreover, attributes may be released, however, chances are that values will be different than expected. In such situations, sensible error handling should take place to inform a user about what went wrong. Also, negotiating attribute release with the IdP would be ideal when possible.

Depending on configuration, a SP should regularly download updated federation metadata. However, chances are that a SP which does not update metadata is present. In that case, the SP is not informed about entity used for access control and... %%% co se stane? regexp nebude sedět, takže uživateli bude odmítnut přístup nebo to skončí v nějakém nedefinovaném stavu?

If an error occurs, we do not know the reason. There are two approaches. First, redirecting user to a web page to log environment variables and trying to diagnose what has happend. Second, integrating access control to the application users are logging in. The second approach improve the user experience, but it is complicated to implement.

## Conclusion
In this technical report, we have described a situation in our federation that leads us to the need for controlling access. Four potential methods have been described while three have been rejected for various mentioned reasons. The method we have decided to employed is easy to implement and does not cause any service interruptions while deploying. Additionally, if there is no need for access control at a particual SP, nothing has to be done at all.

The solution is based on an existing attribute recorded in federation metadata. A SP controlling access to a resource extracts this attribute listed in metadata and uses it in addition to user attributes obtained from an IdP. While all these attributes match access control rules, a user is allowed to the resource. Otherwise access is denied.

%%% jak vypadá chybová hláška?

## References
[Shibboleth]: http://shibboleth.net/
[Apache]: http://httpd.apache.org/
[CESNET]: http://www.cesnet.cz/
[eduID.cz]: http://www.eduid.cz/
[eduID.cz-SPAC]: https://www.eduid.cz/cs/tech/filtrovani-uzivatelu-dokumentace
[NativeSPApplication]: https://wiki.shibboleth.net/confluence/display/SHIB2/NativeSPApplication
[NativeSPApacheConfig]: https://wiki.shibboleth.net/confluence/display/SHIB2/NativeSPApacheConfig
[NativeSPAccessControl]: https://wiki.shibboleth.net/confluence/display/SHIB2/NativeSPAccessControl
[NativeSPXMLAccessControl]: https://wiki.shibboleth.net/confluence/display/SHIB2/NativeSPXMLAccessControl

