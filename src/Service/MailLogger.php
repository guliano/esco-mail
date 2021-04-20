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

namespace EscoMail\Service;

use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;
use EscoMail\Options\ModuleOptions;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mail\Message;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;

class MailLogger extends AbstractListenerAggregate implements ListenerAggregateInterface
{
    /**
     * @var ModuleOptions
     */
    protected $options;

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var \Zend\Stdlib\CallbackHandler
     */
    protected $listeners = array();

    /**
     * Wartość umask
     *
     * @var integer
     */
    private $uMask;

    public function __construct(ModuleOptions $options, ServiceLocatorInterface $serviceLocator)
    {
        $this->options = $options;
        $this->serviceLocator = $serviceLocator;

        if ($this->options->getLogDir()) {
            $dirPath = $this->getLogDirPath($this->options->getLogDir());

            $this->logger = new Logger();
            $writer = new Stream($dirPath . '/mail.log');
            $this->logger->addWriter($writer);

            if (null !== $this->uMask) {
                umask($this->uMask);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        if ($this->options->getLogDir()) {
            $sharedEvents = $events->getSharedManager();
            $this->listeners[] = $sharedEvents->attach(
                'EscoMail\Service\MailServiceFactory',
                'mailSent',
                array($this, 'onMailSent'),
                100
            );
            $this->listeners[] = $sharedEvents->attach(
                'EscoMail\Service\MailServiceFactory',
                'mailSent.error',
                array($this, 'onMailSentError'),
                100
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    /**
     * Callback function invoking on successful mail sent
     *
     * @param Event $e
     */
    public function onMailSent(Event $e)
    {
        /* @var Message */
        $message        = $e->getTarget();
        $addressList    = $this->prepareAddressList($message);

        $this->logger->info(sprintf(
            "E-mail '%s' has been sent to following recipients: %s",
            $message->getSubject(),
            implode(', ', $addressList)
        ));
    }

    /**
     * Callback function invoking on mail sending error
     *
     * @param Event $e
     */
    public function onMailSentError(Event $e)
    {
        /* @var Message */
        $message        = $e->getTarget();
        $params         = $e->getParams();
        $addressList    = $this->prepareAddressList($message);

        $this->logger->err(sprintf(
            "E-mail '%s' has been not sent to following recipients: %s. Error message: %s",
            $message->getSubject(),
            implode(', ', $addressList),
            $params['error_message']
        ));
    }

    /**
     * Returns full path to log dir
     *
     * @param string $dir
     * @return string
     */
    private function getLogDirPath()
    {
        $dir = $this->options->getLogDir();
        if (!is_dir($dir)) {
            $this->uMask = umask(0);
            mkdir($dir, 0777, true);
        }
        return $dir;
    }

    /**
     * Prepares list of all mail recipients
     *
     * @param Message $message
     * @return array
     */
    private function prepareAddressList(Message $message)
    {
        $addressList = array();
        foreach ($message->getTo() as $address) {
            $addressList[] = $address->getEmail();
        }
        foreach ($message->getCc() as $address) {
            $addressList[] = $address->getEmail();
        }
        foreach ($message->getBcc() as $address) {
            $addressList[] = $address->getEmail();
        }

        return $addressList;
    }
}
