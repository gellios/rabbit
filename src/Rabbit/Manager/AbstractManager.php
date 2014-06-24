<?php

namespace Rabbit\Manager;

abstract class AbstractManager extends \Rabbit\ContainerAware
{
    /**
     * @return string
     */
    public abstract function getDocumentName();

    /**\
     * @return \Doctrine\ODM\MongoDB\DocumentRepository
     */
    public function getRepository()
    {
        return $this->getDoctrineDocumentManager()->getRepository(
            $this->getDocumentName()
        );
    }
}