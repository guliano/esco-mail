<?php

namespace EscoMailTest\Transport;

use Zend\ServiceManager\ServiceManager;
use EscoMail\Transport\TransportFactory;
use EscoMail\Options\ModuleOptions;
use EscoMail\Transport\PluginManager;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class TransportFactoryTest extends \PHPUnit_Framework_TestCase
{
    
    private $serviceManager;
    private $pluginManager;
    
    /**
     * @var vfsStreamDirectory
     */
    private $root;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->serviceManager   = new ServiceManager();
        $this->pluginManager    = new PluginManager();
        $this->root             = vfsStream::setup('exampleDir', 0777);
    }
    
    public function tearDown()
    {
        $this->root = null;
    }
    
    public function testCreateServiceCanThrowException()
    {
        $configArray = array(
            'transport_class'   => '',
            'transport_options' => array(),
        );
        $config = new ModuleOptions($configArray);
        
        $this->serviceManager->setService('EscoMail\Options', $config);
        
        $factory = new TransportFactory();
        $this->setExpectedException('RuntimeException');
        
        $factory->createService($this->serviceManager);
    }
    
    /**
     * @dataProvider getConfigArray
     */
    public function testCreateServiceWithSomeOptions($configArray, $expected)
    {
        $config = new ModuleOptions($configArray);
        
        $this->serviceManager->setService('EscoMail\Options', $config);
        
        $factory = new TransportFactory();
        $transport = $factory->createService($this->serviceManager);
        
        $this->assertInstanceOf($expected, $transport);
    }
    
    public function testCreateServiceWithDirectoryCreation()
    {
        $configArray = array(
            'transport_class'   => 'Zend\Mail\Transport\File',
            'transport_options' => array(
                'path' => vfsStream::url('exampleDir') . '/tmp'
            ),
        );
        $config = new ModuleOptions($configArray);
        
        $this->serviceManager->setService('EscoMail\Options', $config);
        
        $factory = new TransportFactory();
        $transport = $factory->createService($this->serviceManager);
        
        $this->assertInstanceOf('Zend\Mail\Transport\File', $transport);
        $this->assertTrue($this->root->hasChild('tmp'));
    }
    
    public function getConfigArray()
    {
        return array(
            array(
                array(
                    'transport_class'   => 'Zend\Mail\Transport\Sendmail',
                    'transport_options' => 'some parameter',
                ),
                'Zend\Mail\Transport\Sendmail'
            ),
            array(
                array(
                    'transport_class'   => 'Zend\Mail\Transport\Sendmail',
                    'transport_options' => 'some parameter',
                ),
                'Zend\Mail\Transport\Sendmail'
            ),
            array(
                array(
                    'transport_class'   => 'Zend\Mail\Transport\SendMail',
                    'transport_options' => array(
                        'parameter1'    => 'value',
                        'parameter2'    => true,
                    ),
                ),
                'Zend\Mail\Transport\Sendmail'
            ),
            array(
                array(
                    'transport_class'   => 'Zend\Mail\Transport\Smtp',
                    'transport_options' => array(
                        'name'              => 'localhost',
                        'host'              => 'example.com',
                        'port'              => 465,
                        'connection_class'  => 'login',
                        'connection_config' => array(
                            'ssl' => 'ssl',
                            'username' => 'username',
                            'password' => 'password',
                        ),
                    ),
                ),
                'Zend\Mail\Transport\Smtp'
            ),
        );
    }
}
