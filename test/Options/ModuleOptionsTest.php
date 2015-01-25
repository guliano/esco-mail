<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

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
