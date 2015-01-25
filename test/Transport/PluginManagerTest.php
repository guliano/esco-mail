<?php

namespace EscoMailTest\View;

use EscoMail\Transport\PluginManager;

class PluginManagerTest extends \PHPUnit_Framework_TestCase
{
    
    public function testValidatePlugin()
    {
        $plugin = new PluginManager();
        
        $transport = $this->getMock('Zend\Mail\Transport\File');
        $result = $plugin->validatePlugin($transport);
        $this->assertNull($result);
        
        $this->setExpectedException('Zend\Mail\Exception\RuntimeException');
        $plugin->validatePlugin(new \stdClass());
    }
}
