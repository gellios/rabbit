<?php

namespace Rabbit\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class Message
{
    /**
     * @var int
     *
     * @ODM\Id
     */
    private $id = 0;

    /**
     * @var string
     *
     * @ODM\String
     */
    private $text;

    /**
     * @var User
     *
     * @ODM\ReferenceOne(targetDocument="User")
     */
    private $user;

    /**
     * @var \DateTime
     *
     * @ODM\Date
     */
    private $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime('now');
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $text
     * @return Message
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param User $user
     * @return Message
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}