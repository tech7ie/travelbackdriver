<?php
namespace App\Helpers;
use Cassandra\Date;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use App\Models\Message;
use Log;

class Websocket implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
//        date_default_timezone_set('UTC');
        $msg = json_decode($msg);

        if (isset($msg->connect_id)) {
            foreach ($this->clients as $client) {
                if ($from === $client) {
                    // Присваиваем user_id = resourceId
                    $client->resourceId = $msg->connect_id;
                    $clientIds = [];

                    foreach ($this->clients as $clientData) {
                        $clientIds[] = $clientData->resourceId;
                    }

                    $adminStatus = (in_array(1, $clientIds)) ? 'online' : 'offline';
                    $client->send(json_encode([
                        'adminStatus' => $adminStatus,
                    ]));
                }
            }
            // Если админ законнектился, шлем всем пользователям что он онлайн
            if ($msg->connect_id === 1) {
                if (isset($msg->chat_user)) {
                    $chats = Message::where('user_id', $msg->chat_user)->get();

                    foreach ($chats as $chat) {
                        $chat->update([
                            'status' => 1
                        ]);
                    }
                }

                foreach ($this->clients as $client) {
                    $client->send(json_encode([
                        'adminStatus' => 'online'
                    ]));

                    if (isset($msg->chat_user) && $client->resourceId === $msg->chat_user) {
                        $client->send(json_encode([
                            'chatRead' => true,
                        ]));
                    }
                }
            }
        } else {
            $userID = $msg->user_id;
            $userMSG = $msg->msg;

            foreach ($this->clients as $client) {
                if (($from !== $client || $client->resourceId === 1) && $client->resourceId == $userID) { // Если сообщение от админа
                    $msgData = json_encode([
                        'msg' => $userMSG,
                        'date' => gmdate("Y-m-d\TH:i:s.u\Z")
                    ]);
                    $client->send($msgData);
                }
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        if ($conn->resourceId === 1) {
            foreach ($this->clients as $clientData) {
                $clientData->send(json_encode([
                    'adminStatus' => 'offline'
                ]));
            }
        }

        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}
