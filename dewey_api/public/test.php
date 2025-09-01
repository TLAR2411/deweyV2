<?php
  header("Access-Control-Allow-Origin: *");
  header("Access-Control-Allow-Methods: PUT, GET, POST");
  header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
// Replace with your bot token
 $botToken = '7764601839:AAGxWkULqfsXd0J5aehA3KPUFS8a7hs7KlM';
 $apiUrl = "https://api.telegram.org/bot$botToken";

// Function to send a request to the Telegram Bot API
function sendTelegramRequest($method, $parameters = [])
{
    global $apiUrl;
    $url = $apiUrl . '/' . $method;

    $options = [
        'http' => [
            'header'  => "Content-Type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($parameters),
        ],
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    return json_decode($result, true);
}

// Get incoming updates (for webhook, read from php://input)
$input = file_get_contents("php://input");
$update = json_decode($input, true);


if (isset($update['callback_query'])) {
   
    $callbackQuery = $update['callback_query'];
    $callbackData = json_decode($callbackQuery['data'], true);
    // $chatId = $callbackQuery['message']['chat']['id']  // Create an inline keyboard
    $messageId = $callbackQuery['message']['message_id'];
    $chatId='-4762087132'; 
    $action = $callbackData['action'];
    $originalMessageId = $callbackData['id'];
    $name_student = $callbackData['name'];
    if($action === 'approve')
    {
        $url="https://disreportcard.com/api/updaterequest/$originalMessageId/0";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        // Execute the GET request
        $response = curl_exec($ch);
        $chatId='-4762087132';
        // Check for errors
        if (curl_errno($ch)) {
           // echo "cURL Error: " . curl_error($ch);
           
            sendTelegramRequest('sendMessage', [
                'chat_id' => $chatId,
                'text' => curl_error($ch)
               
            ]);
        } else {
            // Output the response
           // echo "Response: " . $response;
            sendTelegramRequest('sendMessage', [
                'chat_id' => $chatId,
                'text' => "✅ Approved Student ID : ".$name_student
               
            ]);
        }

         
        
    }else{
            
         $url="https://disreportcard.com/api/updaterequest/$originalMessageId/3";
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        // Execute the GET request
        $response = curl_exec($ch);
        $chatId='-4762087132';
        // Check for errors
        if (curl_errno($ch)) {
           // echo "cURL Error: " . curl_error($ch);
           
            sendTelegramRequest('sendMessage', [
                'chat_id' => $chatId,
                'text' => curl_error($ch)
               
            ]);
        } else {
            // Output the response
           // echo "Response: " . $response;
           sendTelegramRequest('sendMessage', [
              'chat_id' => $chatId,
              'text' => "🛑 Not Approved ".$name_student
             
            ]);
        }
        
    }
    
}