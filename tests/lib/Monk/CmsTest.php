<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use Requests_Session;
use Requests_Response;

use Tests\Helpers;
use Monk\Cms;
use Monk\Cms\Exception;

class CmsTest extends TestCase
{
    /**
     * @group __construct
     */
    public function testConstructAcceptsConfig()
    {
        $cms = new Cms(array('siteId' => 54321));

        $this->assertEquals($cms->getConfig()['siteId'], 54321);
    }

    /**
     * @group __construct
     */
    public function testConstructUsesDefaultConfigWhenConfigNotSet()
    {
        $cms = new Cms();

        $this->assertEquals($cms->getConfig()['siteId'], null);
    }

    /**
     * @group setDefaultConfig
     */
    public function testSetDefaultConfigOverwritesPreviousValueWhenSet()
    {
        $defaultConfig = Cms::setDefaultConfig(array('siteId' => 12345));

        $this->assertEquals($defaultConfig['siteId'], 12345);

        $defaultConfig = Cms::setDefaultConfig(array('siteId' => null));

        $this->assertEquals($defaultConfig['siteId'], null);
    }

    /**
     * @group setDefaultConfig
     */
    public function testSetDefaultConfigUsesPreviousValueWhenNotSet()
    {
        $defaultConfig = Cms::setDefaultConfig(array('siteId' => 12345));

        $this->assertEquals($defaultConfig['url'], 'http://api.monkcms.com');

        $defaultConfig = Cms::setDefaultConfig(array('siteId' => null));

        $this->assertEquals($defaultConfig['url'], 'http://api.monkcms.com');
    }

    /**
     * @group setDefaultConfig
     */
    public function testSetDefaultConfigReturnsNewDefaultConfig()
    {
        $defaultConfig = Cms::setDefaultConfig(array('siteId' => 12345));

        $this->assertArraySubset(array('siteId' => 12345), $defaultConfig);

        Cms::setDefaultConfig(array('siteId' => null));
    }

    /**
     * @group setConfig
     */
    public function testSetConfigOverwritesDefaultWhenSet()
    {
        $cms = new Cms();

        $this->assertEquals($cms->getConfig()['siteId'], null);

        $cms->setConfig(array('siteId' => 54321));

        $this->assertEquals($cms->getConfig()['siteId'], 54321);
    }

    /**
     * @group setConfig
     */
    public function testSetConfigUsesDefaultWhenNotSet()
    {
        $cms = new Cms();

        $this->assertEquals($cms->getConfig()['url'], 'http://api.monkcms.com');

        $cms->setConfig(array('siteId' => 54321));

        $this->assertEquals($cms->getConfig()['url'], 'http://api.monkcms.com');
    }

    /**
     * @group setConfig
     */
    public function testSetConfigReturnsSelfForChaining()
    {
        $cms = new Cms();

        $this->assertEquals($cms->setConfig(array('siteId' => 54321)), $cms);
    }

    /**
     * @group getConfig
     */
    public function testGetConfig()
    {
        $cms = new Cms(array('siteId' => 54321));

        $this->assertArraySubset(array('siteId' => 54321), $cms->getConfig());
    }

    /**
     * @group get
     */
    public function testGetAcceptsQueryParamsArray()
    {
        $expectedQueryString = '?SITEID=54321&NR=4&arg0=sermon&arg1=display_%3A_list&arg2=howmany_%3A_5&arg3=json';

        $cms = new Cms(array(
            'request'    => Helpers::expectSuccessfulRequestToQueryString($this, $expectedQueryString),
            'siteId'     => 54321,
            'siteSecret' => 'secret'
        ));

        $cms->get(array(
            'module'  => 'sermon',
            'display' => 'list',
            'howmany' => 5
        ));
    }

    /**
     * @group get
     */
    public function testGetAcceptsModuleAndDisplayQueryParamsAsSlashSeparatedString()
    {
        $expectedQueryString = '?SITEID=54321&NR=3&arg0=sermon&arg1=display_%3A_list&arg2=json';

        $cms = new Cms(array(
            'request'    => Helpers::expectSuccessfulRequestToQueryString($this, $expectedQueryString),
            'siteId'     => 54321,
            'siteSecret' => 'secret'
        ));

        $cms->get('sermon/list');
    }

    /**
     * @group get
     */
    public function testGetAcceptsModuleAndDisplayAndFindQueryParamsAsSlashSeparatedString()
    {
        $expectedQueryString = '?SITEID=54321&NR=4&arg0=sermon&arg1=display_%3A_detail&arg2=find_%3A_sermon-slug' .
                               '&arg3=json';

        $cms = new Cms(array(
            'request'    => Helpers::expectSuccessfulRequestToQueryString($this, $expectedQueryString),
            'siteId'     => 54321,
            'siteSecret' => 'secret'
        ));

        $cms->get('sermon/detail/sermon-slug');
    }

    /**
     * @group get
     */
    public function testGetAcceptsSlashSeparatedStringAndQueryParamsArray()
    {
        $expectedQueryString = '?SITEID=54321&NR=5&arg0=sermon&arg1=display_%3A_list&arg2=nonfeatures' .
                               '&arg3=howmany_%3A_5&arg4=json';

        $cms = new Cms(array(
            'request'    => Helpers::expectSuccessfulRequestToQueryString($this, $expectedQueryString),
            'siteId'     => 54321,
            'siteSecret' => 'secret'
        ));

        $cms->get('sermon/list', array(
            'nonfeatures' => true,
            'howmany'     => 5
        ));
    }

    /**
     * @group get
     */
    public function testGetReturnsDecodedJsonArrayWhenSuccessful()
    {
        $cms = new Cms(array(
            'request'    => Helpers::mockSuccessfulRequest($this),
            'siteId'     => 54321,
            'siteSecret' => 'secret'
        ));

        $this->assertArrayHasKey('show', $cms->get('sermon/list'));
    }

    /**
     * @group get
     */
    public function testGetThrowsExceptionWhenFailure()
    {
        $cms = new Cms(array(
            'request'    => Helpers::mockFailureRequest($this),
            'siteId'     => 54321,
            'siteSecret' => 'secret'
        ));

        $this->expectException(Exception::class);

        $cms->get('sermon/list');
    }
}
