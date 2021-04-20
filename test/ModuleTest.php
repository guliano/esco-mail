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

namespace EscoMailTest;

use EscoMail\Module;

class ModuleTest extends \PHPUnit_Framework_TestCase
{

    public function testConfigIsArray()
    {
        $module = new Module();
        $this->assertInternalType('array', $module->getConfig());
    }

    public function testCanAttachLogger()
    {
        $module         = new Module();

        $mvcEvent       = $this->createMock('Zend\Mvc\MvcEvent');
        $application    = $this->createMock('Zend\Mvc\Application', array(), array(), '', false);
        $eventManager   = $this->createMock('Zend\EventManager\EventManagerInterface');
        $serviceManager = $this->createMock('Zend\ServiceManager\ServiceManager');

        $mvcEvent->expects($this->once())->method('getApplication')->will($this->returnValue($application));
        $application->expects($this->once())->method('getServiceManager')->will($this->returnValue($serviceManager));
        $application->expects($this->once())->method('getEventManager')->will($this->returnValue($eventManager));

        $logger = $this->createMock('EscoMail\Service\MailLogger', array(), array(), '', false);

        //nie wiem czy na 100% jest dobrze przeprowadzony test
        $serviceManager->expects($this->once())
            ->method('get')
            ->with('EscoMail\Logger')
            ->will($this->returnValue($logger));

        $module->onBootstrap($mvcEvent);
    }
}
