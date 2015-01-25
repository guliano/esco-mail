<?php

namespace EscoMailTest;

use EscoMail\Module;

class ModuleTest extends \PHPUnit_Framework_TestCase
{

    public function testConfigIsArray()
    {
        $module = new Module();
        $this->assertInternalType('array', $module->getConfig());
    }

    public function testCanAttachLogger()
    {
        $module         = new Module();

        $mvcEvent       = $this->getMock('Zend\Mvc\MvcEvent');
        $application    = $this->getMock('Zend\Mvc\Application', array(), array(), '', false);
        $eventManager   = $this->getMock('Zend\EventManager\EventManagerInterface');
        $serviceManager = $this->getMock('Zend\ServiceManager\ServiceManager');

        $mvcEvent->expects($this->once())->method('getApplication')->will($this->returnValue($application));
        $application->expects($this->once())->method('getServiceManager')->will($this->returnValue($serviceManager));
        $application->expects($this->once())->method('getEventManager')->will($this->returnValue($eventManager));

        $logger = $this->getMock('EscoMail\Service\MailLogger', array(), array(), '', false);

        //nie wiem czy na 100% jest dobrze przeprowadzony test
        $serviceManager->expects($this->once())
            ->method('get')
            ->with('EscoMail\Logger')
            ->will($this->returnValue($logger));

        $module->onBootstrap($mvcEvent);
    }
}
