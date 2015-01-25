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

namespace EscoMail\Transport;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Mail\Transport\TransportInterface;
use Zend\Mail\Transport\File as FileTransport;

class TransportFactory implements FactoryInterface
{

    /**
     * @var PluginManager
     */
    private $transportPluginManager;


    public function __construct()
    {
        $this->transportPluginManager = new PluginManager();
    }

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /* @var $config \EscoMail\Options\ModuleOptions */
        $config = $serviceLocator->get('EscoMail\Options');

        if ($config->getTransportClass()) {

            /* @var $transport TransportInterface */
            $transport = $this->transportPluginManager->get($config->getTransportClass());

            if ($transport instanceof FileTransport) {
                $transportOptions = $config->getTransportOptions();
                if (isset($transportOptions['path'])) {
                    if (!file_exists($transportOptions['path'])) {
                        $oldUmask = umask(0);
                        mkdir($transportOptions['path'], 0777, true);
                        umask($oldUmask);
                    }
                }
            }

            if ($config->getTransportOptions()) {
                $options = $this->getOptionsObject($transport, $config->getTransportOptions());

                if (method_exists($transport, 'setOptions')) {
                    $transport->setOptions($options);
                } elseif (method_exists($transport, 'setParameters')) {
                    $transport->setParameters($options);
                }
            }

            return $transport;
        } else {
            throw new \RuntimeException('Transport Class config not set');
        }
    }

    /**
     * Build an options object suitable for use with the specified transport.
     *
     * Uses the transport's class name and concatenates Options on the end.
     * If the resultant class doesn't exist, just returns the original options object.
     *
     * @param TransportInterface $transport
     * @param array|\Traversable|null $options
     * @return mixed
     */
    private function getOptionsObject($transport, $options)
    {
        $optionsClass = get_class($transport) . 'Options';

        if (class_exists($optionsClass)) {
            return new $optionsClass($options);
        }

        return $options;
    }
}
