<?php

namespace Rabbit;

abstract class ContainerAware
{
    /**
     * @var \Pimple
     */
    protected  $container;

    public function __construct(\Pimple $container)
    {
        $this->container = $container;
    }

    /**
     * @return \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected function getDoctrineDocumentManager()
    {
        return $this->container['doctrine.dm'];
    }

    /**
     * @return \Rabbit\Manager\UserManager
     */
    protected function getUserManager()
    {
        return $this->container['manager.user'];
    }
}