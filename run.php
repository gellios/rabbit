<?php

require_once dirname(__FILE__).'/bootstrap.php';

$app = new \Rabbit\Application();
$app->createServer()->run();