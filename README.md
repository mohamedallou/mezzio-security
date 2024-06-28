# Mezzio-security

This library offers an implementation of the UserRepositoryInterface used with an authentication adapter, providing a persistence
layer for mezzio authentication.

It also handles the authorization and implements an easy flat permissions system.


## Configuration
You need to add the following config provider class to your modules list
```bash
\Laminas\InputFilter\ConfigProvider::class
\Mezzio\LaminasView\ConfigProvider::class,
Mezzio\Session\ConfigProvider,
\Mezzio\Authentication\ConfigProvider,
\Mezzio\Authentication\Session\ConfigProvider::class,
```
if they are not added automatically.

The library handles the login and the user management.

It is important to note that the login data hast to be submitted in the form of url encoded or multipart format,
if not, we need to make use of the ***ParsedBody*** Middleware.

And we have to pipe the session middleware before the routing middleware
````php
$app->pipe(\Mezzio\Session\SessionMiddleware::class);
$app->pipe(\Mezzio\Router\Middleware\RouteMiddleware::class);
````

### Requirements
This library requires an implementation of the ***Doctrine\ORM\EntityManagerInterface***.

## Adding Translation to the Problemdetails listener

## Coming features:
 - Support session and basic authentication simultaneously
 - Support Translation of validation messages
 - add JWT Token Auth and blacklist
 - add IP Restriction
 - Add IP Logging
 - block user after x failed login attempts
 - Add Openapi annotations