<?php
require __DIR__ . '/../vendor/autoload.php';
 
 
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
 
 
use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use \LINE\LINEBot\MessageBuilder\AudioMessageBuilder;
use \LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use \LINE\LINEBot\MessageBuilder\VideoMessageBuilder;
use \LINE\LINEBot\SignatureValidator as SignatureValidator;
 
 
$pass_signature = true;


 
// set LINE channel_access_token and channel_secret
$channel_access_token = "B9h0QfSxK3g6cDGBE9YnTKPqF3N31qP2f+crDf5Qi4UzUnoQzkUYP4qArMseGRxSkv/tep7B78zqLeitmUz3KvsSJNQMRa3QjLUsaBmjqAv9bf0nSd39KWrDAEqOxEiqr5n/mwOu6x2fF5jAYk+RqAdB04t89/1O/w1cDnyilFU=";
$channel_secret = "48dbd9a6612ec9c6deb8ce1fdca9b115";
 
 
// inisiasi objek bot
$httpClient = new CurlHTTPClient($channel_access_token);
$bot = new LINEBot($httpClient, ['channelSecret' => $channel_secret]);
 
 
 
 
$app = AppFactory::create();
$app->setBasePath("/public");
 
 
 
 
$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello World!");
    return $response;
});
 
 
// buat route untuk webhook
$app->post('/webhook', function (Request $request, Response $response) use ($channel_secret, $bot, $httpClient, $pass_signature) {
    // get request body and line signature header
    $body = $request->getBody();
    $signature = $request->getHeaderLine('HTTP_X_LINE_SIGNATURE');
 
 
    // log body and signature
    file_put_contents('php://stderr', 'Body: ' . $body);
 
 
    if ($pass_signature === false) {
        // is LINE_SIGNATURE exists in request header?
        if (empty($signature)) {
            return $response->withStatus(400, 'Signature not set');
        }
 
 
        // is this request comes from LINE?
        if (!SignatureValidator::validateSignature($body, $channel_secret, $signature)) {
            return $response->withStatus(400, 'Invalid signature');
        }
    }
    // Variabel buatan Atl Ngokhey
    
 
    $data = json_decode($body, true);
    if(is_array($data['events'])){
        foreach ($data['events'] as $event)
        {   
            if ($event['type'] == 'message')
            {   
                if($event['message']['type'] == 'text')
                {
                    $multiMessageBuilder = new MultiMessageBuilder();
                    if(strtolower($event['message']['text']) == '!myuserid'){ // memberi userid
                    $stickerMessageBuilder= new StickerMessageBuilder(1,117);
                    $textMessageBuilder = new TextMessageBuilder('Id kamu : ');
                    $textMessageUserId = new TextMessageBuilder($event['source']['userId']);
                    $multiMessageBuilder->add($textMessageBuilder);
                    $multiMessageBuilder->add($textMessageUserId);
                    $result = $bot->replyMessage($event['replyToken'], $multiMessageBuilder);

                    }else if(strtolower($event['message']['text'])=='!menu'){ //main menu dengan flex
                        $flexTemplate = file_get_contents("../flex_main.json");
                        $result = $httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
                            'replyToken' => $event['replyToken'],
                            'messages'   => [
                                [
                                    'type'     => 'flex',
                                    'altText'  => '[Menu Utama]',
                                    'contents' => json_decode($flexTemplate)
                                ]
                            ],
                        ]);
                    }else if(strtolower($event['message']['text'])=='!start'){
                        $kumpulanStart = new MultiMessageBuilder();
                        $start1 = new TextMessageBuilder('Selamat Datang di Akun Bot Kodok');
                        $start2 = new TextMessageBuilder('Bot ini ditujukan untuk anda yang ingin mulai belajar bahasa pemrograman C++.
                        
Dalam bot ini disediakan materi materi yang dapat dipelajari, kemudian juga terdapat quiz untuk melatih pengetahuan tentang materi yang telah diberikan.');
                        $start3 = new TextMessageBuilder('Sebagai langkah awal, silahkan ketikkan perintah "!menu" untuk membuka menu utama');
                        $start4 = new StickerMessageBuilder('1','407');
                        $kumpulanStart->add($start1);
                        $kumpulanStart->add($start2);
                        $kumpulanStart->add($start3);
                        $kumpulanStart->add($start4);
                        $result = $bot->replyMessage($event['replyToken'],$kumpulanStart);
                    }else if(strtolower($event['message']['text'])=='!learn'){ // Materi2
                        $flexTemplate = file_get_contents("../flex_learn.json"); // flex message
                        $result = $httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
                            'replyToken' => $event['replyToken'],
                            'messages'   => [
                                [
                                    'type'     => 'flex',
                                    'altText'  => '[Materi C++]',
                                    'contents' => json_decode($flexTemplate)
                                ]
                            ],
                        ]);
                    }else if(strtolower($event['message']['text'])=='!lc1'){ // Materi2
                        $flexTemplate = file_get_contents("../flex_lc1.json"); // flex message
                        $result = $httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
                            'replyToken' => $event['replyToken'],
                            'messages'   => [
                                [
                                    'type'     => 'flex',
                                    'altText'  => '[Materi C++]',
                                    'contents' => json_decode($flexTemplate)
                                ]
                            ],
                        ]);
                    }
                }//Content api
                elseif (    
                    $event['message']['type'] == 'image' or
                    $event['message']['type'] == 'video' or
                    $event['message']['type'] == 'audio' or
                    $event['message']['type'] == 'file'
                ) {
                    $contentURL = " https://kodook.herokuapp.com/public/content/" . $event['message']['id'];
                    $contentType = ucfirst($event['message']['type']);
                    $result = $bot->replyText($event['replyToken'],
                        $contentType . " yang kamu kirim aku taro di sini yaa:\n" . $contentURL);
                } 
                $response->getBody()->write(json_encode($result->getJSONDecodedBody()));
                    return $response
                        ->withHeader('Content-Type', 'application/json')
                        ->withStatus($result->getHTTPStatus());
            }
        }
        return $response->withStatus(200, 'for Webhook!'); //buat ngasih response 200 ke pas verify webhook
    }
    return $response->withStatus(400, 'No event sent!');
});
$app->get('/pushmessage', function ($req, $response) use ($bot) {
    // send push message to user
    $userId = 'Ue92a5b9df7e195607bddab3f3fa8f336';
    $textMessageBuilder = new TextMessageBuilder('Halo, ini pesan push dari kodok');
    $result = $bot->pushMessage($userId, $textMessageBuilder);
 
    $response->getBody()->write("Pesan push nya sudah saya kirim !");
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus($result->getHTTPStatus());
});
$app->get('/multicast', function($req, $response) use ($bot)
{
    // list of users
    $userList = [
        'Ue92a5b9df7e195607bddab3f3fa8f336','Udcbc496f3391cdd5f5ab5c5e365e7e2d'];//bisa ditambahkan 'abc','cda'] gitu
 
    // send multicast message to user
    $textMessageBuilder = new TextMessageBuilder('Halo, ini pesan multicast');
    $result = $bot->multicast($userList, $textMessageBuilder);
    $response->getBody()->write("Pesan multicast nya sudah saya kirim mas");
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus($result->getHTTPStatus());
});
$app->get('/profile/{userId}', function ($req, $response, $args) use ($bot) {
    // get user profile
    $userId = $args['userId'];
    $result = $bot->getProfile($userId);
    $response->getBody()->write(json_encode($result->getJSONDecodedBody()));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus($result->getHTTPStatus());
});
$app->get('/content/{messageId}', function ($req, $response, $args) use ($bot) {
    // get message content
    $messageId = $args['messageId'];
    $result = $bot->getMessageContent($messageId);
    // set response
    $response->getBody()->write($result->getRawBody());
    return $response
        ->withHeader('Content-Type', $result->getHeader('Content-Type'))
        ->withStatus($result->getHTTPStatus());
});
$app->run();