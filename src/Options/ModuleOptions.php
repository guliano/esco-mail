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

namespace EscoMail\Options;

use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions
{
    /**
     * Turn off strict options mode
     */
    protected $__strictMode__ = false;

    /**
     * Test mode
     *
     * @var bool
     */
    protected $mailTestMode = false;

    /**
     * Sender e-mail address
     * @var string
     */
    protected $mailSendFrom;

    /**
     * Sender name
     *
     * @var string
     */
    protected $mailSendFromName;

    /**
     * Add root e-mail address to bcc?
     *
     * @var bool
     */
    protected $addRootBcc = false;

    /**
     * Root e-mail address
     *
     * @var string
     */
    protected $rootEmail;

    /**
     * Transport class
     *
     * @var string
     */
    protected $transportClass;

    /**
     * Transport options
     *
     * @var array
     */
    protected $transportOptions = array();

    /**
     * Path to log dir
     *
     * @var string
     */
    protected $logDir;

    /**
     * Base Uri that can be used in composed e-mails for links
     *
     * @var string
     */
    protected $baseUri = 'http://example.com';

    /**
     * @return string
     */
    public function getTransportClass()
    {
        return $this->transportClass;
    }

    /**
     * Set Transport Class
     *
     * @param string $transportClass
     */
    public function setTransportClass($transportClass)
    {
        $this->transportClass = $transportClass;
    }

    /**
     * Get Transport Options
     *
     * @return array
     */
    public function getTransportOptions()
    {
        return $this->transportOptions;
    }

    /**
     * Set Transport Options
     *
     * @param array $options
     */
    public function setTransportOptions($options)
    {
        $this->transportOptions = (array) $options;
    }

    /**
     * Get Mail Send From
     *
     * @return string
     */
    public function getMailSendFrom()
    {
        return $this->mailSendFrom;
    }

    /**
     * Set Mail Send From
     *
     * @param string $from
     */
    public function setMailSendFrom($from)
    {
        $this->mailSendFrom = $from;
    }

    /**
     * Get Mail Send From Name
     *
     * @return string
     */
    public function getMailSendFromName()
    {
        return $this->mailSendFromName;
    }

    /**
     * Set Mail Send From Name
     *
     * @param string $fromName
     */
    public function setMailSendFromName($fromName)
    {
        $this->mailSendFromName = $fromName;
    }

    /**
     * Get Mail Test Mode
     *
     * @return bool
     */
    public function getMailTestMode()
    {
        return $this->mailTestMode;
    }

    /**
     * Set Mail Test Mode
     *
     * @param array $mode
     */
    public function setMailTestMode($mode)
    {
        $this->mailTestMode = (bool) $mode;
    }

    /**
     * Get root email
     *
     * @return string
     */
    public function getRootEmail()
    {
        return $this->rootEmail;
    }

    /**
     * Set root email
     *
     * @param string $email
     */
    public function setRootEmail($email)
    {
        $this->rootEmail = $email;
    }

    /**
     * Get Add Root Bcc
     *
     * @return bool
     */
    public function getAddRootBcc()
    {
        return $this->addRootBcc;
    }

    /**
     * Set Add Root Bcc
     *
     * @param bool $flag
     */
    public function setAddRootBcc($flag)
    {
        $this->addRootBcc = (bool) $flag;
    }

    /**
     * Get Base URI
     *
     * @return array
     */
    public function getBaseUri()
    {
        return $this->baseUri;
    }

    /**
     * Set Base URI
     *
     * @param string $uri
     */
    public function setBaseUri($uri)
    {
        $this->baseUri = $uri;
    }

    /**
     * Get Log Dir
     *
     * @return array
     */
    public function getLogDir()
    {
        return $this->logDir;
    }

    /**
     * Set Log Dir
     *
     * @param string $dir
     */
    public function setLogDir($dir)
    {
        $this->logDir = $dir;
    }
}
