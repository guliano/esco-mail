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
