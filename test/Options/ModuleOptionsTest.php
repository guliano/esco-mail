<?php

namespace EscoMailTest\Options;

use EscoMail\Options\ModuleOptions;

class ModuleOptionsTest extends \PHPUnit_Framework_TestCase
{
    
    public function testConfigSetProperly()
    {
        $configArray = array(
            'mail_test_mode'        => true,
            'mail_send_from'        => 'noreply@example.com',
            'mail_send_from_name'   => 'Sender Name',
            'add_root_bcc'          => true,
            'root_email'            => 'admin@example.com',
            'transport_class'       => 'Some\Namespace\TransportClass',
            'transport_options'     => array(
                'option1'   => 1,
                'option2'   => 2,
            ),
            'log_dir'               => 'path/to/sample/dir',
            'base_uri'              => 'http://example.com',
        );
        
        $options = new ModuleOptions($configArray);
        
        $this->assertEquals($configArray, $options->toArray());
        $this->assertSame($configArray['mail_test_mode'], $options->getMailTestMode());
        $this->assertSame($configArray['mail_send_from'], $options->getMailSendFrom());
        $this->assertSame($configArray['mail_send_from_name'], $options->getMailSendFromName());
        $this->assertSame($configArray['add_root_bcc'], $options->getAddRootBcc());
        $this->assertSame($configArray['root_email'], $options->getRootEmail());
        $this->assertSame($configArray['transport_class'], $options->getTransportClass());
        $this->assertSame($configArray['transport_options'], $options->getTransportOptions());
        $this->assertSame($configArray['log_dir'], $options->getLogDir());
        $this->assertSame($configArray['base_uri'], $options->getBaseUri());
    }
}
