<?php

namespace EscoMail;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\EventManager\EventInterface;

class Module implements
    ConfigProviderInterface,
    BootstrapListenerInterface
{

    public function onBootstrap(EventInterface $e)
    {
        $app            = $e->getApplication();
        $serviceManager = $app->getServiceManager();
        $eventManager   = $app->getEventManager();

        /* @var $eventManager \Zend\EventManager\EventManagerInterface */
        $eventManager->attachAggregate($serviceManager->get('EscoMail\Logger'));
    }

    /**
     * {@InheritDoc}
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

}
