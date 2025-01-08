<?php

namespace App\Http\Controllers;

use App\Http\Traits\GeneralTrait;
use Illuminate\Http\Request;
use App\Models\Message;
//use WebSocket\Client;
use ElephantIO\Client;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    use GeneralTrait;

    public function sendMessage(Request $request)
    {
        try {
            $message =
                (
                [
                    'sender_id' => $request->sender_id,
                    'receiver_id' => $request->receiver_id,
                    'message' => $request->message,
                ]
                );
            Message::create($message);
            $url = 'http://localhost:3000';
            $options = ['client' => Client::CLIENT_4X];
            $client = Client::create($url, $options);
            $client->connect();
            if ($client->emit('message', $message)) {
                $message1 = $message;
            }
            $client->disconnect();
            return $this->apiResponse($message1);
        } catch (Exception $e) {
            return $this->apiResponse(null, false, $e->getMessage(), 500);
        }
    }

    public function receiveMessage(Request $request)
    {
//        try {
//            $url = 'http://localhost:3000';
////            $options = ['client' => Client::CLIENT_4X];
//            $client = new  Client('ws://localhost:3000/socket.io/?EIO=4&transport=websocket
//');
//            echo 'sucsses'.'<br>';
//            while (True) {
//                $data = $client->receive();
//                if ($data) {
//                        echo $data;
//                    }
//                        $client->disconnect();
//            }
//
//        } catch (\Exception $ex) {
////            return $this->apiResponse(null, false, $ex->getMessage(), 500);
//            echo $ex->getMessage();
//        }
        echo '<html>
    <script>
    var socket = io.connect("http://localhost:3000");
    socket.on("message", function(data){
        console.log($data);
    })
</script>
    </html>'.'hello';
    }
}
