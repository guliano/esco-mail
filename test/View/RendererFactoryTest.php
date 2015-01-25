<?php

namespace EscoMailTest\View;

use Zend\ServiceManager\ServiceManager;
use EscoMail\View\RendererFactory;

class RendererFactoryTest extends \PHPUnit_Framework_TestCase
{
    
    public function testCreateServiceFromServiceManager()
    {
        $configArray = array(
            'view_manager' => array(
                'template_map' => array(
                    'some/view' => '/some/dir',
                ),
            ),
        );
        
        $viewRenderer = $this->getMock('Zend\View\Renderer\PhpRenderer');
        
        $serviceManager = new ServiceManager();
        $serviceManager->setService('Config', $configArray);
        $serviceManager->setService('ViewRenderer', $viewRenderer);
        
        $factory        = new RendererFactory();
        $renderer       = $factory->createService($serviceManager);
        
        $this->assertInstanceOf('Zend\View\Renderer\PhpRenderer', $renderer);
    }
    
    public function testCreateServiceWithoutServiceManager()
    {
        $configArray = array(
            'view_manager' => array(
                'template_map' => array(
                    'some/view' => '/some/dir',
                ),
            ),
        );
        
        $helperManager  = $this->getMock('Zend\View\HelperPluginManager', array(), array(), '', false);
        $viewResolver   = $this->getMock('Zend\View\Resolver\AggregateResolver');
        
        $serviceManager = new ServiceManager();
        $serviceManager->setService('Config', $configArray);
        $serviceManager->setService('ViewResolver', $viewResolver);
        $serviceManager->setService('ViewHelperManager', $helperManager);
        
        $factory        = new RendererFactory();
        $renderer       = $factory->createService($serviceManager);
        $this->assertInstanceOf('Zend\View\Renderer\PhpRenderer', $renderer);
        
        $this->assertInstanceOf('Zend\View\HelperPluginManager', $renderer->getHelperPluginManager());
    }
    
    public function testCreateServiceWithoutServiceManagerAndWithBaseUri()
    {
        $configArray = array(
            'view_manager' => array(
                'template_map' => array(
                    'some/view' => '/some/dir',
                ),
            ),
            'esco_mail' => array(
                'base_uri' => 'http://example.com',
            ),
        );
        
        $helperManager  = $this->getMock('Zend\View\HelperPluginManager', array(), array(), '', false);
        $viewResolver   = $this->getMock('Zend\View\Resolver\AggregateResolver');
        $urlHelper      = $this->getMock('Zend\View\Helper\Url');
        $httpRouter     = $this->getMock('Zend\Mvc\Router\Http\TreeRouteStack');
        
        $serviceManager = new ServiceManager();
        $serviceManager->setService('Config', $configArray);
        $serviceManager->setService('ViewResolver', $viewResolver);
        $serviceManager->setService('ViewHelperManager', $helperManager);
        $serviceManager->setService('HttpRouter', $httpRouter);
        
        $helperManager->expects($this->once())->method('get')->with('Url')->will($this->returnValue($urlHelper));
        
        $httpRouter->expects($this->once())->method('setBaseUrl')->with($configArray['esco_mail']['base_uri']);
        $urlHelper->expects($this->once())->method('setRouter')->with($httpRouter);
        
        $factory        = new RendererFactory();
        $renderer       = $factory->createService($serviceManager);
        $this->assertInstanceOf('Zend\View\Renderer\PhpRenderer', $renderer);
        
        $this->assertInstanceOf('Zend\View\HelperPluginManager', $renderer->getHelperPluginManager());
    }
}
