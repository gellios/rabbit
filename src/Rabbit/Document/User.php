<?php

namespace Rabbit\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class User
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
    private $login;

    /**
     * @var string
     *
     * @ODM\String
     */
    private $password;

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
     * @param string $login
     * @return User
     */
    public function setLogin($login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password= $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }
}