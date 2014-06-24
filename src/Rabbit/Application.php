<?php

namespace Rabbit;

final class Application
{
    /**
     * @var \Pimple
     */
    private $container;

    public function __construct()
    {
        $container = new \Pimple();

        $config = include(CONFIG_PATH.'/config.php');

        \Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver::registerAnnotationClasses();
        $dConfig = new \Doctrine\ODM\MongoDB\Configuration();
        $dConfig->setProxyDir($config['doctrine']['odm']['proxy_dir']);
        $dConfig->setProxyNamespace('Proxies');
        $dConfig->setHydratorDir($config['doctrine']['odm']['hydrator_dir']);
        $dConfig->setHydratorNamespace('Hydrators');
        $dConfig->setDefaultDB($config['doctrine']['odm']['default_db']);
        $dConfig->setMetadataDriverImpl(
            \Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver::create(dirname(__FILE__).'/Document')
        );

        $container['doctrine.dm'] = \Doctrine\ODM\MongoDB\DocumentManager::create(new \Doctrine\MongoDB\Connection(), $dConfig);

        $container['manager.user'] = function() use ($container) {
            return new \Rabbit\Manager\UserManager($container);
        };

        $this->container = $container;
    }

    /**
     * @param int $port
     * @return \Ratchet\Server\IoServer
     */
    public function createServer($port = 8181)
    {
        return \Ratchet\Server\IoServer::factory(
            new \Ratchet\Http\HttpServer(
                new \Ratchet\WebSocket\WsServer(
                    new Chat($this->container)
                )
            ),
            $port
        );
    }
}