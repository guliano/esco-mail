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

use Zend\ServiceManager\ServiceManager;
use EscoMail\Service\MailServiceFactory;
use EscoMail\Transport\TransportFactory;
use Zend\EventManager\EventManager;
use EscoMail\Options\ModuleOptions;
use Zend\View\Model\ViewModel;
use org\bovigo\vfs\vfsStream;
use Zend\Mail\Transport\Exception\RuntimeException;

class MailServiceFactoryTest extends \PHPUnit_Framework_TestCase
{

    public function testCreateService()
    {
        $serviceManager = new ServiceManager();

        $factory = new MailServiceFactory();
        $service = $factory($serviceManager, 'EscoMail\Service\MailServiceFactory');

        $this->assertInstanceOf('EscoMail\Service\MailServiceFactory', $service);
    }

    /**
     * @covers EscoMail\Service\MailServiceFactory::setEventManager
     * @covers EscoMail\Service\MailServiceFactory::getEventManager
     */
    public function testSetAndGetEventManager()
    {
        $eventManager = new EventManager();

        $mailService = new MailServiceFactory();
        $mailService->setEventManager($eventManager);

        $this->assertEquals($eventManager, $mailService->getEventManager());
        $this->assertEquals(array('EscoMail\Service\MailServiceFactory'), $eventManager->getIdentifiers());
    }

    /**
     * @covers EscoMail\Service\MailServiceFactory::setBasePathToModel
     *
     * @dataProvider provideModuleOptions
     */
    public function testSetBasePathToModel($baseUri, $espectedResult)
    {
        $configArray = array(
            'base_uri'   => $baseUri
        );

        $options = new ModuleOptions($configArray);

        $viewModelMock = new ViewModel();

        $serviceManager = new ServiceManager();
        $serviceManager->setService('EscoMail\Options', $options);
        $factory = new MailServiceFactory();
        $mailService = $factory($serviceManager, 'EscoMail\Service\MailServiceFactory');

        $result = $this->invokeMethod($mailService, 'setBasePathToModel', array($viewModelMock));

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
        $this->assertEquals($espectedResult, $result->getVariable('basePath'));
    }

    /**
     * @covers EscoMail\Service\MailServiceFactory::getMessage
     */
    public function testGetMessage()
    {
        $configArray = array(
            'mail_send_from_name' => 'John Doe',
            'mail_send_from'      => 'john.doe@example.com',
        );

        $options = new ModuleOptions($configArray);

        $serviceManager = $this->createMock('Zend\ServiceManager\ServiceManager');

        $factory = new MailServiceFactory();
        $mailService = $factory($serviceManager, 'EscoMail\Service\MailServiceFactory');

        $serviceManager
            ->expects($this->once())
            ->method('get')
            ->with('EscoMail\Options')
            ->will($this->returnValue($options));
        $this->assertInstanceOf('Zend\Mail\Message', $mailService->getMessage());

        $serviceManager
            ->expects($this->never())
            ->method('get')
            ->with('EscoMail\Options')
            ->will($this->returnValue($options));
        $this->assertInstanceOf('Zend\Mail\Message', $mailService->getMessage());
    }

    /**
     * @covers EscoMail\Service\MailServiceFactory::getTransport
     *
     */
    public function testGetTransport()
    {
        vfsStream::setup('exampleDir', 0777);
        $configArray = array(
            'transport_class'   => 'Zend\Mail\Transport\File',
            'transport_options' => array(
                'path' => vfsStream::url('exampleDir') . '/data/mail',
            ),
        );

        $options = new ModuleOptions($configArray);
        $serviceManager = new ServiceManager();
        $serviceManager->setService('EscoMail\Options', $options);

        $transportFactory = new TransportFactory();
        $transport = $transportFactory($serviceManager, 'EscoMail\Transport\TransportFactory');
        $serviceManager->setService('EscoMail\Transport', $transport);

        $factory = new MailServiceFactory();
        $mailService = $factory($serviceManager, 'EscoMail\Service\MailServiceFactory');

        $this->assertInstanceOf('Zend\Mail\Transport\TransportInterface', $mailService->getTransport());
    }

    /**
     * @covers EscoMail\Service\MailServiceFactory::getRenderer
     */
    public function testGetRenderer()
    {
        $viewRenderer = $this->createMock('Zend\View\Renderer\PhpRenderer');

        $serviceManager = new ServiceManager();
        $serviceManager->setService('ViewRenderer', $viewRenderer);

        $serviceManager->setService('EscoMail\Renderer', $viewRenderer);

        $factory = new MailServiceFactory();
        $mailService = $factory($serviceManager, 'EscoMail\Service\MailServiceFactory');

        $this->assertInstanceOf('Zend\View\Renderer\RendererInterface', $mailService->getRenderer());
    }

    /**
     * @covers EscoMail\Service\MailServiceFactory::send
     *
     * @dataProvider provideSendOptions
     */
    public function testSend(
        $testMode,
        $addRootBcc,
        $expectedRecipientAddress,
        $expectedToCount,
        $expectedBccCount,
        $expectedSubject
    ) {
        $configArray = array(
            'mail_send_from' => 'john.doe@example.com',
            'mail_send_from_name' => 'John Doe',
            'mail_test_mode' => $testMode,
            'add_root_bcc' => $addRootBcc,
            'root_email' => 'root@example.com',
        );
        $options = new ModuleOptions($configArray);

        $serviceManager = new ServiceManager();
        $serviceManager->setService('EscoMail\Options', $options);

        $transportMock = $this->createMock('Zend\Mail\Transport\InMemory');
        $serviceManager->setService('EscoMail\Transport', $transportMock);

        $factory = new MailServiceFactory();
        $mailService = $factory($serviceManager, 'EscoMail\Service\MailServiceFactory');

        $eventManagerMock = $this->createMock('Zend\EventManager\EventManagerInterface');
        $mailService->setEventManager($eventManagerMock);

        $transportMock->expects($this->once())->method('send')->will($this->returnValue(true));
        $eventManagerMock->expects($this->once())->method('trigger')->with('mailSent');

        $mailService->getMessage()->addTo('deliver@example.com');
        $mailService->getMessage()->setSubject('Mail subject');
        $this->assertTrue($mailService->send());

        $this->assertEquals($expectedSubject, $mailService->getMessage()->getSubject());
        $this->assertTrue($mailService->getMessage()->getTo()->has($expectedRecipientAddress));
        $this->assertEquals('john.doe@example.com', $mailService->getMessage()->getFrom()->current()->getEmail());
        $this->assertEquals('John Doe', $mailService->getMessage()->getFrom()->current()->getName());
        $this->assertEquals($expectedToCount, $mailService->getMessage()->getTo()->count());
        $this->assertEquals($expectedBccCount, $mailService->getMessage()->getBcc()->count());
    }

    /**
     * @covers EscoMail\Service\MailServiceFactory::send
     */
    public function testSendFailure()
    {
        $configArray = array(
            'mail_send_from' => 'john.doe@example.com',
            'mail_send_from_name' => 'John Doe',
            'mail_test_mode' => false,
            'add_root_bcc' => false,
            'root_email' => 'root@example.com',
        );
        $options = new ModuleOptions($configArray);

        $serviceManager = new ServiceManager();
        $serviceManager->setService('EscoMail\Options', $options);

        $transportMock = $this->createMock('Zend\Mail\Transport\InMemory');
        $serviceManager->setService('EscoMail\Transport', $transportMock);

        $factory = new MailServiceFactory();
        $mailService = $factory($serviceManager, 'EscoMail\Service\MailServiceFactory');

        $eventManagerMock = $this->createMock('Zend\EventManager\EventManagerInterface');
        $mailService->setEventManager($eventManagerMock);

        $transportMock
            ->expects($this->once())
            ->method('send')
            ->will($this->throwException(new RuntimeException('Error while sending mail')));
        $eventManagerMock->expects($this->once())->method('trigger')->with('mailSent.error');

        $mailService->getMessage()->addTo('deliver@example.com');
        $mailService->getMessage()->setSubject('Mail subject');
        $this->assertFalse($mailService->send());
    }

    /**
     * @covers EscoMail\Service\MailServiceFactory::prepareMessage
     */
    public function testPrepareTextMessage()
    {
        $configArray = array(
            'mail_send_from' => 'john.doe@example.com',
            'mail_send_from_name' => 'John Doe',
        );

        $options = new ModuleOptions($configArray);
        $serviceManager = new ServiceManager();
        $serviceManager->setService('EscoMail\Options', $options);

        $factory = new MailServiceFactory();
        $mailService = $factory($serviceManager, 'EscoMail\Service\MailServiceFactory');

        $mailService->prepareMessage('Mail subject', 'This is body of the text message');

        $message = $mailService->getMessage();

        $this->assertEquals('Mail subject', $message->getSubject());
        $this->assertEquals('This is body of the text message', $message->getBodyText());

        /* @var $body \Zend\Mime\Message */
        $body = $message->getBody();

        $this->assertCount(1, $body->getParts());

        /* @var $currentPart \Zend\Mime\Part */
        $currentPart = current($body->getParts());

        $this->assertEquals('text/html', $currentPart->type);
        $this->assertEquals('UTF-8', $currentPart->charset);
    }

    /**
     * @covers EscoMail\Service\MailServiceFactory::prepareMessage
     */
    public function testPrepareHtmlMessage()
    {
        $configArray = array(
            'mail_send_from' => 'john.doe@example.com',
            'mail_send_from_name' => 'John Doe',
        );

        $options = new ModuleOptions($configArray);
        $serviceManager = new ServiceManager();
        $serviceManager->setService('EscoMail\Options', $options);

        $viewRenderer = $this->createMock('Zend\View\Renderer\PhpRenderer');

        $serviceManager->setService('ViewRenderer', $viewRenderer);
        $serviceManager->setService('EscoMail\Renderer', $viewRenderer);

        $factory = new MailServiceFactory();
        $mailService = $factory($serviceManager, 'EscoMail\Service\MailServiceFactory');

        $viewModel = new ViewModel();
        $viewModel->setTemplate('template/name');
        $viewModel->setVariable('foo', 'bar');
        $viewRenderer
            ->expects($this->once())
            ->method('render')
            ->with($viewModel)
            ->will($this->returnValue('<p>Html message</p>'));
        $mailService->prepareMessage('Mail subject', $viewModel);

        $message = $mailService->getMessage();

        $this->assertEquals('Mail subject', $message->getSubject());
        $this->assertEquals('<p>Html message</p>', $message->getBodyText());

        /* @var $body \Zend\Mime\Message */
        $body = $message->getBody();

        $this->assertCount(1, $body->getParts());

        /* @var $currentPart \Zend\Mime\Part */
        $currentPart = current($body->getParts());

        $this->assertEquals('text/html', $currentPart->type);
        $this->assertEquals('UTF-8', $currentPart->charset);
    }

    /**
     * @covers EscoMail\Service\MailServiceFactory::prepareMessage
     *
     * @dataProvider provideAttachments
     */
    public function testPrepareMessageWithAttachment($attachments, $expectedPartsCount)
    {
        $configArray = array(
            'mail_send_from' => 'john.doe@example.com',
            'mail_send_from_name' => 'John Doe',
        );

        $options = new ModuleOptions($configArray);
        $serviceManager = new ServiceManager();
        $serviceManager->setService('EscoMail\Options', $options);

        $factory = new MailServiceFactory();
        $mailService = $factory($serviceManager, 'EscoMail\Service\MailService');

        vfsStream::setup('exampleDir', 0777);
        $fp = fopen(vfsStream::url('exampleDir') . '/example.txt', 'w');
        fputs($fp, 'text attachment', 1024);
        fclose($fp);
        $fp = fopen(vfsStream::url('exampleDir') . '/example2.txt', 'w');
        fputs($fp, 'text attachment 2', 1024);
        fclose($fp);

        $mailService->prepareMessage('Mail subject', 'This is body of the text message', $attachments);

        $message = $mailService->getMessage();

        $this->assertEquals('Mail subject', $message->getSubject());
        $this->assertContains(
            'This is a message in Mime Format.  If you see this, your mail reader does not support this format.',
            $message->getBodyText()
        );

        /* @var $body \Zend\Mime\Message */
        $body = $message->getBody();

        $this->assertCount($expectedPartsCount, $body->getParts());
    }

    public function provideAttachments()
    {
        return array(
            array(
                array(
                    array(
                        'url' => vfsStream::url('exampleDir') . '/example.txt',
                        'type' => 'application/octet-stream',
                        'filename' => 'nameofattachment.txt',
                        'encoding' => 'base64',
                    ),
                ),
                2,
            ),
            array(
                array(
                    array(
                        'url' => vfsStream::url('exampleDir') . '/example.txt',
                        'type' => 'application/octet-stream',
                        'filename' => 'nameofattachment.txt',
                        'encoding' => 'base64',
                    ),
                    array(
                        'url' => vfsStream::url('exampleDir') . '/example2.txt',
                        'type' => 'application/octet-stream',
                        'filename' => 'nameofattachment2.txt',
                        'encoding' => 'base64',
                    ),
                ),
                3,
            ),
            array(
                array(
                    array(
                        'url' => vfsStream::url('exampleDir') . '/example.txt',
                        'type' => 'application/octet-stream',
                        'filename' => 'nameofattachment.txt',
                        'encoding' => 'base64',
                    ),
                    array(
                        'url' => vfsStream::url('exampleDir') . '/notexists.txt',
                        'type' => 'application/octet-stream',
                        'filename' => 'nameofattachment2.txt',
                        'encoding' => 'base64',
                    ),
                    array(
                        'url' => vfsStream::url('exampleDir') . '/example2.txt',
                        'type' => 'application/octet-stream',
                        'filename' => 'nameofattachment3.txt',
                        'encoding' => 'base64',
                    ),
                ),
                3,
            ),
        );
    }

    public function provideSendOptions()
    {
        return array(
            array(false, false, 'deliver@example.com', 1, 0, 'Mail subject'),
            array(false, true, 'deliver@example.com', 1, 1, 'Mail subject'),
            array(true, false, 'root@example.com', 1, 0, '[TEST] Mail subject'),
            array(true, true, 'root@example.com', 1, 0, '[TEST] Mail subject'),
        );
    }

    public function provideModuleOptions()
    {
        return array(
            array('http://example.com', 'http://example.com'),
            array('', null),
            array(null, null),
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
