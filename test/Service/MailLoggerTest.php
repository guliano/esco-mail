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

namespace EscoMailTest\Service;

use Zend\Mail\AddressList;
use Zend\ServiceManager\ServiceManager;
use EscoMail\Service\MailLogger;
use EscoMail\Options\ModuleOptions;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Zend\Mail\Message;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\SharedEventManagerInterface;

class MailLoggerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    public function setUp()
    {
        $this->root = vfsStream::setup('exampleDir', 0777);
    }

    public function tearDown()
    {
        $this->root = null;
    }

    /**
     * @covers \EscoMail\Service\MailLogger::__construct
     */
    public function testCanCreateInstance()
    {
        $serviceManager = new ServiceManager();
        $options        = new ModuleOptions();

        $mailLogger     = new MailLogger($options, $serviceManager);

        $this->assertInstanceOf('EscoMail\Service\MailLogger', $mailLogger);
    }

    /**
     * @covers \EscoMail\Service\MailLogger::__construct
     * @covers \EscoMail\Service\MailLogger::getLogDirPath
     */
    public function testCanCreateInstanceWithLogDirCreation()
    {
        $configArray = array(
            'log_dir'   => vfsStream::url('exampleDir') . '/tmp'
        );

        $serviceManager = new ServiceManager();
        $options        = new ModuleOptions($configArray);

        $mailLogger     = new MailLogger($options, $serviceManager);

        $this->assertInstanceOf('EscoMail\Service\MailLogger', $mailLogger);
        $this->assertTrue($this->root->hasChild('tmp/mail.log'));
    }

    /**
     * @covers \EscoMail\Service\MailLogger::attach
     * @covers \EscoMail\Service\MailLogger::detach
     */
    public function testAttachDetach()
    {
        $configArray = array(
            'log_dir'   => vfsStream::url('exampleDir') . '/tmp'
        );

        $serviceManager = new ServiceManager();
        $options        = new ModuleOptions($configArray);

        $mailLogger     = new MailLogger($options, $serviceManager);

        $eventManager   = $this->createMock(EventManagerInterface::class);
        $sharedManager  = $this->createMock(SharedEventManagerInterface::class);

        $callbackMock   = $this->getMockBuilder('Zend\\Stdlib\\CallbackHandler')
            ->disableOriginalConstructor()
            ->getMock();

        $eventManager
            ->expects($this->any())
            ->method('getSharedManager')
            ->will($this->returnValue($sharedManager));

        $sharedManager
            ->expects($this->exactly(2))
            ->method('attach')
            ->with()
            ->will($this->returnValue($callbackMock));
        $mailLogger->attach($eventManager);

//        $eventManager
//            ->expects($this->exactly(2))
//            ->method('detach')
//            ->with($callbackMock)
//            ->will($this->returnValue(true));
//        $mailLogger->detach($eventManager);
    }

    /**
     * @param string|Address\AddressInterface|array|AddressList|Traversable $toList
     * @param string|Address\AddressInterface|array|AddressList|Traversable $ccList
     * @param string|Address\AddressInterface|array|AddressList|Traversable $bccList
     * @param array $expectedResult
     *
     * @covers \EscoMail\Service\MailLogger::prepareAddressList
     * @dataProvider provideAddressList
     */
    public function testPrepareAddressList($toList, $ccList, $bccList, $expectedResult)
    {
        $serviceManager = new ServiceManager();
        $options        = new ModuleOptions();
        $mailLogger     = new MailLogger($options, $serviceManager);

        $message = new Message();
        if ($toList !== null) {
            $message->setTo($toList);
        }
        if ($ccList !== null) {
            $message->setCc($ccList);
        }
        if ($bccList !== null) {
            $message->setBcc($bccList);
        }

        $result = $this->invokeMethod($mailLogger, 'prepareAddressList', array($message));

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @covers \EscoMail\Service\MailLogger::onMailSent
     */
    public function testLoggerOnMailSentSuccess()
    {
        $configArray = array(
            'log_dir'   => vfsStream::url('exampleDir') . '/tmp'
        );

        $serviceManager = new ServiceManager();
        $options        = new ModuleOptions($configArray);

        $mailLogger     = new MailLogger($options, $serviceManager);

        $mailSubject = 'Subject of the mail message';
        $messageMock = $this->createMock(Message::class);
        $messageMock->expects(self::once())
            ->method('getSubject')
            ->will($this->returnValue($mailSubject));
        $addressList = new AddressList();
        $messageMock->expects(self::atLeastOnce())
            ->method('getTo')
            ->will($this->returnValue($addressList->add('foo@bar.com')));

        $mvcEvent = new \Zend\Mvc\MvcEvent();
        $mvcEvent->setTarget($messageMock);

        $logFile = $configArray['log_dir'] . '/mail.log';
        $this->assertEmpty(file_get_contents($logFile));

        $mailLogger->onMailSent($mvcEvent);

        $content = file_get_contents($logFile);
        $match = preg_match("/^(.*)INFO \(6\):(.*)'$mailSubject'(.*): foo@bar.com/i", $content);
        $this->assertEquals(1, $match);
    }

    /**
     * @covers \EscoMail\Service\MailLogger::onMailSentError
     */
    public function testLoggerOnMailSentFailure()
    {
        $configArray = array(
            'log_dir'   => vfsStream::url('exampleDir') . '/tmp'
        );

        $serviceManager = new ServiceManager();
        $options        = new ModuleOptions($configArray);

        $mailLogger     = new MailLogger($options, $serviceManager);

        $mailSubject = 'Subject of the mail message';
        $messageMock = $this->createMock(Message::class, array('getSubject'));
        $messageMock->expects(self::once())
            ->method('getSubject')
            ->will($this->returnValue($mailSubject));
        $addressList = new AddressList();
        $messageMock->expects(self::atLeastOnce())
            ->method('getTo')
            ->will($this->returnValue($addressList->add('foo@bar.com')));

        $mvcEvent = new \Zend\Mvc\MvcEvent();
        $mvcEvent->setTarget($messageMock);
        $mvcEvent->setParam('error_message', 'Error message description');

        $logFile = $configArray['log_dir'] . '/mail.log';
        $this->assertEmpty(file_get_contents($logFile));

        $mailLogger->onMailSentError($mvcEvent);

        $content = file_get_contents($logFile);
        $match = preg_match(
            "/^(.*)ERR \(3\):(.*)'$mailSubject'(.*): foo@bar.com(.*): Error message description/i",
            $content
        );
        $this->assertEquals(1, $match);
    }

    public function provideAddressList()
    {
        return array(
            array(null, null, null, array()),
            array('foo@bar.com', null, null, array('foo@bar.com')),
            array(null, 'foo@bar.com', null, array('foo@bar.com')),
            array(null, null, 'foo@bar.com', array('foo@bar.com')),
            array('foo@bar.com', 'foo2@bar.com', null, array('foo@bar.com', 'foo2@bar.com')),
            array('foo@bar.com', null, 'foo2@bar.com', array('foo@bar.com', 'foo2@bar.com')),
            array(null, 'foo@bar.com', 'foo2@bar.com', array('foo@bar.com', 'foo2@bar.com')),
            array(null, 'foo@bar.com', null, array('foo@bar.com')),
            array(null, null, 'foo@bar.com', array('foo@bar.com')),
            array(
                'foo@bar.com',
                'foo2@bar.com',
                'foo3@bar.com',
                array('foo@bar.com', 'foo2@bar.com', 'foo3@bar.com')
            ),
            array(
                array('foo@bar.com', 'foo2@bar.com'),
                'foo3@bar.com',
                'foo4@bar.com',
                array('foo@bar.com', 'foo2@bar.com', 'foo3@bar.com', 'foo4@bar.com')
            ),
            array(
                array('foo@bar.com', 'foo2@bar.com'),
                array('foo3@bar.com', 'foo4@bar.com'),
                'foo5@bar.com',
                array('foo@bar.com', 'foo2@bar.com', 'foo3@bar.com', 'foo4@bar.com', 'foo5@bar.com')
            ),
            array(
                array('foo@bar.com', 'foo2@bar.com'),
                array('foo3@bar.com', 'foo4@bar.com'),
                array('foo5@bar.com', 'foo6@bar.com'),
                array('foo@bar.com', 'foo2@bar.com', 'foo3@bar.com', 'foo4@bar.com', 'foo5@bar.com', 'foo6@bar.com')
            ),
        );
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
    */
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
