# Mezzio-security

This library offers an implementation of the UserRepositoryInterface used with an authentication adapter, providing a persistence
layer for mezzio authentication.

It also handles the authorization and implements an easy flat permissions system.

This library also provides an implementation of the PhpSessionPersistenceInterface.
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


and of course the current ConfigProvider: ***\MezzioSecurity\ConfigProvider***


You also need to configure the laminas abstract factory in your global configuration:
````php
\Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory::class,
````

An implementation of Laminas EventManager is required.

e.g:
````php
<?php

declare(strict_types=1);

namespace App\Factory;

use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\LazyListener;
use Psr\Container\ContainerInterface;

class EventManagerFactory
{
    public function __invoke(ContainerInterface $container): EventManager
    {
        $events = $container->get('config')['events'] ?? [];

        $eventManager = new EventManager();

        foreach ($events as $eventName => $listenerClasses) {
            if (!is_string($eventName)) {
                continue;
            }

            $listenerClasses = is_array($listenerClasses) ? $listenerClasses : [$listenerClasses];
            foreach ($listenerClasses as $listenerClass) {
                //TODO: use LazyEvent Listener

                if (is_a($listenerClass, EventManagerAwareInterface::class, true)) {
                    /** @var callable&EventManagerAwareInterface $listener */
                    $listener = $container->get($listenerClass);
                    $listener->setEventManager($eventManager);
                } else {
                    $listener = new LazyListener(
                        [
                            'listener' => $listenerClass,
                            'method' => '__invoke'
                        ],
                        $container
                    );
                }

                if (!is_callable($listener)) {
                    continue;
                }

                $eventManager->attach($eventName, $listener);
            }
        }
        return $eventManager;
    }
}

````
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