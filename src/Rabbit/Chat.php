<?php

namespace Rabbit;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface
{

    private $clients;
    private $users = array();
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

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
            $from->send(json_encode(array(
                'error' => $user === null,
            )));
            return;
        }
        if ($data->command == 'sendMessage') {
            if (!isset($this->users[$from->resourceId])) {
                $from->send(json_encode(array(
                    'error' => true,
                )));
                return;
            }
            $userId = $this->users[$from->resourceId];
            /** @var \Rabbit\Document\User $user */
            $user = $this->app->getUserManager()->getRepository()->find($userId);
            if (!$user) {
                $from->send(json_encode(array(
                    'error' => true,
                )));
                return;
            }

            $message = new Document\Message();
            $message->setText($data->params->message);
            $message->setUser($user);

            $this->app->getDoctrineDocumentmanager()->persist($message);
            $this->app->getDoctrineDocumentmanager()->flush();

            foreach ($this->clients as $client) {
                if ($from !== $client) {
                    $client->send(json_encode(array(
                        'command' => 'addMessage',
                        'message' => array(
                            'user' => $user->getLogin(),
                            'timestamp' => $message->getCreatedAt()->getTimestamp(),
                            'text' => $message->getText(),
                        ),
                    )));
                }
            }
            return;
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        unset($this->users[$conn->resourceId]);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}