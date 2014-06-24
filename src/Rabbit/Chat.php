<?php

namespace Rabbit;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

final class Chat implements MessageComponentInterface
{
    const EVENT_TYPE_AUTH_SUCCESS = 3;
    const EVENT_TYPE_AUTH_ERROR = 4;
    const EVENT_TYPE_MESSAGE_NEW = 5;

    /**
     * @var \SplObjectStorage
     */
    private $clients;

    /**
     * @var array
     */
    private $users = array();

    /**
     * @var Application
     */
    private $app;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->clients = new \SplObjectStorage;
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        $this->debug("New connection! ({$conn->resourceId})");
    }

    /**
     * @param ConnectionInterface $from
     * @param string $msg
     */
    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg);
        if ($data->command == 'auth') {
            /** @var \Rabbit\Document\User $user */
            $user = $this->app->getUserManager()->getRepository()->findOneBy(array(
                'login' => $data->params->login,
                'password' => hash('sha256', $data->params->password),
            ));
            unset($this->users[$from->resourceId]);
            if ($user) {
                $this->users[$from->resourceId] = $user->getId();
            }
            if ($user) {
                $this->send($from, self::EVENT_TYPE_AUTH_SUCCESS);
                $this->debug("User '{$user->getLogin()}' is logged in");
            } else {
                $this->send($from, self::EVENT_TYPE_AUTH_ERROR);
            }
            return;
        }
        if ($data->command == 'sendMessage') {
            if (!isset($this->users[$from->resourceId])) {
                $this->send($from, self::EVENT_TYPE_AUTH_ERROR);
                return;
            }
            $userId = $this->users[$from->resourceId];
            /** @var \Rabbit\Document\User $user */
            $user = $this->app->getUserManager()->getRepository()->find($userId);
            if (!$user) {
                $this->send($from, self::EVENT_TYPE_AUTH_ERROR);
                return;
            }

            $message = new Document\Message();
            $message->setText($data->params->message);
            $message->setUser($user);

            $this->app->getDoctrineDocumentmanager()->persist($message);
            $this->app->getDoctrineDocumentmanager()->flush();

            foreach ($this->clients as $client) {
                if ($from !== $client) {
                    $this->send($client, self::EVENT_TYPE_MESSAGE_NEW, array(
                        'message' => array(
                            'user' => $user->getLogin(),
                            'timestamp' => $message->getCreatedAt()->getTimestamp(),
                            'text' => $message->getText(),
                        ),
                    ));
                }
            }
            return;
        }
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        unset($this->users[$conn->resourceId]);
        $this->debug("Connection {$conn->resourceId} has disconnected");
    }

    /**
     * @param ConnectionInterface $conn
     * @param \Exception $e
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $this->debug("An error has occurred: {$e->getMessage()}");
        $conn->close();
    }

    /**
     * @param $client
     * @param string $event
     * @param array $data
     */
    public function send($client, $event, $data = array())
    {
        $client->send(json_encode(array(
            'event' => $event,
            'data' => $data,
        )));
    }

    /**
     * @param string $message
     */
    public function debug($message)
    {
        echo date('Y:m:d H:i:s'), " {$message}\n";
    }
}