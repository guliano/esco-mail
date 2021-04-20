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

namespace EscoMailTest\View;

use Zend\ServiceManager\ServiceManager;
use EscoMail\View\RendererFactory;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\HelperPluginManager;
use Zend\View\Resolver\AggregateResolver;
use Zend\View\Helper\Url;
use Zend\Mvc\Router\Http\TreeRouteStack;

class RendererFactoryTest extends \PHPUnit_Framework_TestCase
{

    public function testCreateServiceFromServiceManager()
    {
        $configArray = array(
            'view_manager' => array(
                'template_map' => array(
                    'some/view' => '/some/dir',
                ),
            ),
        );

        $viewRenderer = $this->createMock(PhpRenderer::class);

        $serviceManager = new ServiceManager();
        $serviceManager->setService('Config', $configArray);
        $serviceManager->setService('ViewRenderer', $viewRenderer);

        $factory        = new RendererFactory();
        $renderer       = $factory($serviceManager, PhpRenderer::class);

        $this->assertInstanceOf(PhpRenderer::class, $renderer);
    }

    public function testCreateServiceWithoutServiceManager()
    {
        $configArray = array(
            'view_manager' => array(
                'template_map' => array(
                    'some/view' => '/some/dir',
                ),
            ),
        );

        $helperManager  = $this->createMock(HelperPluginManager::class, array(), array(), '', false);
        $viewResolver   = $this->createMock(AggregateResolver::class);

        $serviceManager = new ServiceManager();
        $serviceManager->setService('Config', $configArray);
        $serviceManager->setService('ViewResolver', $viewResolver);
        $serviceManager->setService('ViewHelperManager', $helperManager);

        $factory        = new RendererFactory();
        $renderer       = $factory($serviceManager, PhpRenderer::class);
        $this->assertInstanceOf(PhpRenderer::class, $renderer);

        $this->assertInstanceOf(HelperPluginManager::class, $renderer->getHelperPluginManager());
    }

    public function testCreateServiceWithoutServiceManagerAndWithBaseUri()
    {
        $configArray = array(
            'view_manager' => array(
                'template_map' => array(
                    'some/view' => '/some/dir',
                ),
            ),
            'esco_mail' => array(
                'base_uri' => 'http://example.com',
            ),
        );

        $helperManager  = $this->createMock(HelperPluginManager::class, array(), array(), '', false);
        $viewResolver   = $this->createMock(AggregateResolver::class);
        $urlHelper      = $this->createMock(Url::class);
        $httpRouter     = $this->createMock(TreeRouteStack::class);

        $serviceManager = new ServiceManager();
        $serviceManager->setService('Config', $configArray);
        $serviceManager->setService('ViewResolver', $viewResolver);
        $serviceManager->setService('ViewHelperManager', $helperManager);
        $serviceManager->setService('HttpRouter', $httpRouter);

        $helperManager->expects($this->once())->method('get')->with('Url')->will($this->returnValue($urlHelper));

        $httpRouter->expects($this->once())->method('setBaseUrl')->with($configArray['esco_mail']['base_uri']);
        $urlHelper->expects($this->once())->method('setRouter')->with($httpRouter);

        $factory        = new RendererFactory();
        $renderer       = $factory($serviceManager, PhpRenderer::class);
        $this->assertInstanceOf(PhpRenderer::class, $renderer);

        $this->assertInstanceOf(HelperPluginManager::class, $renderer->getHelperPluginManager());
    }
}
