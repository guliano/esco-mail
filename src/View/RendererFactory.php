<?php

namespace EscoMail\View;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\TemplateMapResolver;

class RendererFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /* @var $config ModuleOptions */
        $config = $serviceLocator->get('Config');

        if ($serviceLocator->has('ViewRenderer')) {
            return $serviceLocator->get('ViewRenderer');
        } else {
            $renderer = new PhpRenderer();

            // register helpers
            $helperManager = $serviceLocator->get('ViewHelperManager');
            if (isset($config['esco_mail']['base_uri']) && !empty($config['esco_mail']['base_uri'])) {
                $urlHelper = $helperManager->get('Url');
                $httpRouter = $serviceLocator->get('HttpRouter');
                $httpRouter->setBaseUrl($config['esco_mail']['base_uri']);
                $urlHelper->setRouter($httpRouter);
            }
            $renderer->setHelperPluginManager($helperManager);

            // register resolver
            $resolver = $serviceLocator->get('ViewResolver');
            $resolver->attach(new TemplateMapResolver($config['view_manager']['template_map']));
            $renderer->setResolver($resolver);

            return $renderer;
        }
    }
}
