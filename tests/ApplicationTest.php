<?php

use Laminas\Diactoros\Uri;
use Obullo\Http\ServerRequest;
use Laminas\ServiceManager\ServiceManager;

class ApplicationTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $appConfig = require __DIR__ . '/config/application.config.php';
        $smConfig = isset($appConfig['service_manager']) ? $appConfig['service_manager'] : [];
        $smConfig = new Obullo\Container\ServiceManagerConfig($smConfig);

        // setup service manager
        //
        $this->container = new ServiceManager;
        $smConfig->configureServiceManager($this->container);
        $this->container->setService('appConfig', $appConfig);

        // load app modules
        //
        $this->container->get('ModuleManager')->loadModules();
        $this->container->setAllowOverride(true);
    }

    public function testGetConfig()
    {
        $application = $this->container->get('Application');
        $config = $application->getConfig();
        $translatorFactory = $config['service_manager']['factories']['Laminas\I18n\Translator\TranslatorInterface'];

        $this->assertEquals($translatorFactory, Laminas\I18n\Translator\TranslatorServiceFactory::class);
    }

    public function testBootstrap()
    {
        $request = new ServerRequest();
        $request = $request->withUri(new Uri('http://example.com/test'));
        $this->container->setService('Request', $request);

        $application = $this->container->get('Application');
        $application->bootstrap();
        $response = $application->runWithoutEmit();

        $this->assertEquals('Test', $response->getBody());
    }

    public function testGetServiceManager()
    {
        $application = $this->container->get('Application');
        $serviceManager = $application->getContainer();
    
        $this->assertEquals($serviceManager, $this->container);
    }

    public function testGetEventManager()
    {
        $application = $this->container->get('Application');
        $serviceManager = $application->getEventManager();
    
        $this->assertInstanceOf('Laminas\EventManager\EventManagerInterface', $serviceManager);
    }

    public function testGetPageEvent()
    {
        $application = $this->container->get('Application');
        $pageEvent = $application->getPageEvent();
    
        $this->assertInstanceOf('Obullo\PageEvent', $pageEvent);
    }
}