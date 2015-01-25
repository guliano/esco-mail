<?php

namespace EscoMailTest\Options;

use Zend\ServiceManager\ServiceManager;
use EscoMail\Options\ModuleOptionsFactory;

class ModuleOptionsFactoryTest extends \PHPUnit_Framework_TestCase
{
    
    public function testFactory()
    {
        $configArray = array('esco_mail' => array());
        
        $serviceManager = new ServiceManager();
        $serviceManager->setService('Config', $configArray);
        
        $factory        = new ModuleOptionsFactory();
        $moduleOptions  = $factory->createService($serviceManager);
        
        $this->assertInstanceOf('EscoMail\Options\ModuleOptions', $moduleOptions);
    }
}
