<?php

namespace EscoMail\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MailLoggerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $options = $serviceLocator->get('EscoMail\Options');
        
        $logger = new MailLogger($options, $serviceLocator);
        
        return $logger;
    }
}
