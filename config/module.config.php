<?php

namespace EscoMail;

return array(
    'service_manager' => array(
        'factories' => array(
            'EscoMail\Options'              => 'EscoMail\Options\ModuleOptionsFactory',
            'EscoMail\Transport'            => 'EscoMail\Transport\TransportFactory',
            'EscoMail\Renderer'             => 'EscoMail\View\RendererFactory',
            'EscoMail\Service\MailService'  => 'EscoMail\Service\MailServiceFactory',
            'EscoMail\Logger'               => 'EscoMail\Service\MailLoggerFactory',
        ),
    ),
);
