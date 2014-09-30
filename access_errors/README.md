# Access Control Error Handling in Shibboleth SP

## The problem

Shibboleth SP provides very flexible access control tools, but in case of an access error a generic page is displayed providing too little information. There is no error message in the log either, so the administrator has no further information available. 

The reasons for the access error may be:

 - user's attribute values do not comply with the specified access control definitions
 - some of the attributes used in the access control definitions are missing

In both cases it is necessary that the administrator gets notified about the problem and receives as much information as possible. Although is possible to customize the standard Shibboleth SP access error page using the appropriate template, it provides very few options.

## Workaround

One possible workaround is to make the standard Shibboleth SP access error page redirect the user to a custom error page, where the error can be diagnosed. For obvious reasons that page must be also protected by Shibboleth, but with no access control rules.

The custom error page may analyze available user attributes, log all available information (such as evnironment variables, time, etc.) and display more comprehensive error description to the user.

## Example

This example uses files from the current directory - `accessError.html`, `accessError.php`, `acl.xml`, `apache22.cfg`.

### accessError.php

This is our "diagnostic" page. It simply logs all environment variables (`$_SERVER`) and displays an error message with a code that references the log entry. Customize it to suit your needs and place it under Shibboleth protection. You need to set the path to the log file as well as the email of the administrator.

### accessError.html

This is a Shibboleth SP template page for the access error. It contains a generic error message and asks the user to click the "Diagnose" button. Make sure that the button links to our "diagnostic" page.

Then you should configure Shibboleth to use it - in `shibbolerh2.xml`:
```xml
<Errors supportContact="admin@example.cz"
   logoLocation="/shibboleth-sp/logo.jpg"
   styleSheet="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css"
   access="/path/to/accessError.html"
   ...
/>
```

### acl.xml

This file contains the access control rules used for your application.

### apache22.cfg

This is a fragment of Apache configuration which suggests how the protected application and the diagnostic page may be configured in Apache.



