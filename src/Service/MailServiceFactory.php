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

use EscoMail\Options\ModuleOptions;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;

use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;

use Zend\Mail\Transport\TransportInterface;
use Zend\View\Renderer\RendererInterface;
use Zend\View\Model\ViewModel;

use Zend\Stdlib\ArrayUtils;

use Zend\Mail\Exception\RuntimeException;

class MailServiceFactory implements FactoryInterface, EventManagerAwareInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * @var Message
     */
    protected $message;

    /**
     * Gets a mail message with some of the fields populated.
     *
     * @return Message
     */
    public function getMessage()
    {
        if ($this->message) {
            return $this->message;
        }

        return $this->newMessage();
    }

    /**
     * Creates a new message with some of the fields populated.
     *
     * @return Message
     */
    private function newMessage()
    {
        /* @var $config ModuleOptions */
        $config = $this->serviceLocator->get('EscoMail\Options');

        $this->message = new Message();
        $this->message->setFrom($config->getMailSendFrom(), $config->getMailSendFromName());

        return $this->message;
    }

    /**
     * Wysyła wiadomość e-mail
     *
     * @return boolean
     */
    public function send()
    {
        /* @var $config ModuleOptions */
        $config = $this->serviceLocator->get('EscoMail\Options');

        $message = $this->getMessage();
        if (true === $config->getMailTestMode()) {
            // test mail for root
            $message->setTo($config->getRootEmail());
            $message->setSubject('[TEST] ' . $message->getSubject());
        } else {
            if (true === $config->getAddRootBcc()) {
                $message->addBcc($config->getRootEmail());
            }
        }

        try {
            $transport = $this->getTransport();
            $transport->send($message);

            $this->getEventManager()->trigger('mailSent', $message);
            return true;
        } catch (RuntimeException $e) {
            $this->getEventManager()->trigger('mailSent.error', $message, array('error_message' => $e->getMessage()));
            return false;
        }
    }

    /**
     * Gets the configured mail transport.
     *
     * @return TransportInterface
     */
    public function getTransport()
    {
        return $this->serviceLocator->get('EscoMail\Transport');
    }

    /**
     * Gets renderer
     *
     * @return RendererInterface
     */
    public function getRenderer()
    {
        return $this->serviceLocator->get('EscoMail\Renderer');
    }

    /**
     * Prepares the message for sending by rendering the view.
     *
     * @param string $subject
     * @param ViewModel|string $modelOrText
     * @param array $attachments Adresy do załączników
     */
    public function prepareMessage($subject, $modelOrText, $attachments = array())
    {
        if ($modelOrText instanceof ViewModel) {
            $modelOrText = $this->setBasePathToModel($modelOrText);

            /* @var RendererInterface $viewRenderer */
            $renderer = $this->getRenderer();

            $mimePart = new MimePart($renderer->render($modelOrText));
            $mimePart->type = 'text/html';
            $mimePart->charset = 'UTF-8';
        } elseif (is_string($modelOrText)) {
            $mimePart = new MimePart($modelOrText);
            $mimePart->type = 'text/html';
            $mimePart->charset = 'UTF-8';
        }

        $attachmentParts = array();
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                if (!is_file($attachment['url'])) {
                    // no attachment file
                    //TODO: log event
                    continue;
                }

                $attachmentPart = new MimePart(file_get_contents($attachment['url']));
                $attachmentPart->type = (isset($attachment['type'])) ? $attachment['type'] : 'application/octet-stream';
                $attachmentPart->disposition = 'attachment; filename="' . $attachment['filename'] . '"';
                $attachmentPart->encoding = (isset($attachment['encoding'])) ? $attachment['encoding'] : 'base64';

                $attachmentParts[] = $attachmentPart;
            }
        }

        $body = new MimeMessage();
        $body->setParts(ArrayUtils::merge(array($mimePart), $attachmentParts));

        $message = $this->newMessage();
        $message->setSubject($subject);
        $message->setBody($body);
    }

    /**
     * Injects base path to viewModel
     *
     * @param \Zend\View\Model\ViewModel $model
     * @return \Zend\View\Model\ViewModel
     */
    private function setBasePathToModel(ViewModel $model)
    {
        /* @var $config ModuleOptions */
        $config = $this->serviceLocator->get('EscoMail\Options');

        if ($config->getBaseUri()) {
            $model->setVariable('basePath', $config->getBaseUri());
        }

        return $model;
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->serviceLocator = $container;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $eventManager->addIdentifiers(array(
            get_called_class()
        ));

        $this->eventManager = $eventManager;
    }

    /**
     * {@inheritDoc}
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }
}
