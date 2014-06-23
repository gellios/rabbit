<?php

namespace Rabbit\Manager;

abstract class AbstractManager
{
    /** @var  \Doctrine\ODM\MongoDB\DocumentManager */
    protected $dm;

    public function __construct(\Doctrine\ODM\MongoDB\DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * @return string
     */
    public abstract function getDocumentName();

    /**\
     * @return \Doctrine\ODM\MongoDB\DocumentRepository
     */
    public function getRepository()
    {
        return $this->dm->getRepository($this->getDocumentName());
    }
}