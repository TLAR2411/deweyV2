<?php
  
  header("Access-Control-Allow-Origin: *");
  header("Access-Control-Allow-Methods: PUT, GET, POST");
  header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
  // $text=urlencode($_GET['text']);
  // $text=urlencode("Sieng Roeun Scaned On Date : ". date("d-m-Y"));
  $text =$_GET['text'];
  $id = $_GET['id'];
  $chat_id='-4762087132';
  $token='7764601839:AAGxWkULqfsXd0J5aehA3KPUFS8a7hs7KlM';
  $keyboard = [
    'inline_keyboard' => [
        [
            ['text' => '✅ Approve',
              'callback_data' => 
              json_encode([
                  'action' => 'approve',
                  'id'=>$id,
                  'name'=>$_GET['name']
                  ]
              )
          ],
            ['text' => '🛑 Not Approve',
              'callback_data' => 
               json_encode([
                    'action' => 'not approve',
                    'id'=>$id,
                    'name'=>$_GET['name']
                  ]
              )
            ]
        ]
    ]
];
$url = "https://api.telegram.org/bot$token/sendMessage?" . http_build_query([
    "chat_id" => $chat_id,
    "text" => $text,
    "reply_markup" => json_encode($keyboard)
]);

// Send request
$response = file_get_contents($url);

echo $response;
 
?>


