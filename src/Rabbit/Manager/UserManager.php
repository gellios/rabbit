<?php

namespace Rabbit\Manager;

use Rabbit\Application;

class UserManager extends AbstractManager
{
    public function getDocumentName()
    {
        return '\Rabbit\Document\User';
    }
}