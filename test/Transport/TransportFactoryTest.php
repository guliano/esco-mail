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

namespace EscoMailTest\Transport;

use Zend\ServiceManager\ServiceManager;
use EscoMail\Transport\TransportFactory;
use EscoMail\Options\ModuleOptions;
use EscoMail\Transport\PluginManager;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Zend\Mail\Transport\File;
use Zend\Mail\Transport\Sendmail;
use Zend\Mail\Transport\Smtp;

class TransportFactoryTest extends \PHPUnit_Framework_TestCase
{

    private $serviceManager;

    /**
     * @var vfsStreamDirectory
     */
    private $root;

    public function setUp()
    {
        parent::setUp();

        $this->serviceManager   = new ServiceManager();
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

        $factory($this->serviceManager, 'EscoMail\Transport');
    }

    /**
     * @dataProvider getConfigArray
     */
    public function testCreateServiceWithSomeOptions($configArray, $expected)
    {
        $config = new ModuleOptions($configArray);

        $this->serviceManager->setService('EscoMail\Options', $config);

        $factory = new TransportFactory();
        $transport = $factory($this->serviceManager, 'EscoMail\Transport');

        self::assertInstanceOf($expected, $transport);
    }

    public function testCreateServiceWithDirectoryCreation()
    {
        $configArray = array(
            'transport_class'   => File::class,
            'transport_options' => array(
                'path' => vfsStream::url('exampleDir') . '/tmp'
            ),
        );
        $config = new ModuleOptions($configArray);

        $this->serviceManager->setService('EscoMail\Options', $config);

        $factory = new TransportFactory();
        $transport = $factory($this->serviceManager, 'EscoMail\Transport');

        self::assertInstanceOf(File::class, $transport);
        self::assertTrue($this->root->hasChild('tmp'));
    }

    public function getConfigArray()
    {
        return array(
            array(
                array(
                    'transport_class'   => Sendmail::class,
                    'transport_options' => 'some parameter',
                ),
                Sendmail::class
            ),
            array(
                array(
                    'transport_class'   => Sendmail::class,
                    'transport_options' => 'some parameter',
                ),
                Sendmail::class
            ),
            array(
                array(
                    'transport_class'   => Sendmail::class,
                    'transport_options' => array(
                        'parameter1'    => 'value',
                        'parameter2'    => true,
                    ),
                ),
                Sendmail::class
            ),
            array(
                array(
                    'transport_class'   => Smtp::class,
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
                Smtp::class
            ),
        );
    }
}
