<?php

namespace EscoMail\Transport;

use Zend\Mail\Exception\RuntimeException;
use Zend\Mail\Transport\TransportInterface;
use Zend\ServiceManager\AbstractPluginManager;

class PluginManager extends AbstractPluginManager
{

    /**
     * {@inheritDoc}
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof TransportInterface) {
            return;
        }

        throw new RuntimeException(sprintf(
            'Plugin of type %s is invalid; must implement Zend\Mail\Transport\TransportInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin))
        ));
    }
}
