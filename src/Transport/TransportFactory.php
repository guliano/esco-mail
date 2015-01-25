<?php

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
