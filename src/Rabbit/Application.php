<?php

namespace Rabbit;

use Symfony\Component\HttpFoundation\Session\Session;

class Application extends \Pimple
{
    public function __construct()
    {
        parent::__construct();

        $app = $this;

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

        $this['doctrine.dm'] = \Doctrine\ODM\MongoDB\DocumentManager::create(new \Doctrine\MongoDB\Connection(), $dConfig);

        $this['manager.user'] = function() use ($app) {
            return new \Rabbit\Manager\UserManager($this->getDoctrineDocumentmanager());
        };
    }

    /**
     * @return \Doctrine\ODM\MongoDB\DocumentManager
     */
    public function getDoctrineDocumentmanager()
    {
        return $this['doctrine.dm'];
    }

    /**
     * @return \Rabbit\Manager\UserManager
     */
    public function getUserManager()
    {
        return $this['manager.user'];
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
                    new Chat($this)
                )
            ),
            $port
        );
    }
}