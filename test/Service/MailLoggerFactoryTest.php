<?php

namespace EscoMailTest\Service;

use Zend\ServiceManager\ServiceManager;
use EscoMail\Options\ModuleOptions;
use EscoMail\Service\MailLoggerFactory;

class MailLoggerFactoryTest extends \PHPUnit_Framework_TestCase
{
    
    public function testCreateService()
    {
        $options = new ModuleOptions();
        
        $serviceManager = new ServiceManager();
        $serviceManager->setService('EscoMail\Options', $options);
        
        $factory    = new MailLoggerFactory();
        $logger     = $factory->createService($serviceManager);
        
        $this->assertInstanceOf('EscoMail\Service\MailLogger', $logger);
    }
}
