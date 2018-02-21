<?php 

header('Access-Control-Allow-Origin: *');

$headers = "Content-type: text/html; charset=utf-8 \r\n";
$headers .= "From: worldwifi <support@notifications.worldwifi.io>\r\n"; // от кого

$apikey = '584640f470d805483a6ee638484bfdd9-us17';
$auth = base64_encode( 'user:'.$apikey );

$subject = 'Подписка';
$name = htmlspecialchars($_POST['name']);
$email = htmlspecialchars($_POST['email']);

$data = array(
    'apikey'        => $apikey,
    'email_address' => $email,
    'status'        => 'subscribed',
    'merge_fields'  => array(
        'FNAME' => $name
    )
);
$json_data = json_encode($data);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://us17.api.mailchimp.com/3.0/lists/675ec4fcb9/members');
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',
                                            'Authorization: Basic '.$auth));
curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);                                                                                                                  


if(!(preg_match("/[0-9a-z]+@[0-9a-z]/", $email))){
    $status = 'error';
    $text = 'Введи корректный E-mail';
}else {

    $out = curl_exec($ch);

    $result = json_decode($out);

    if ($result->status == 'subscribed') {
        $mail = mail($email, $subject, 'your success', $headers);
        if ($mail) {
            $status = 'success';
            $text = 'Вы успешно подписались на наши обновления';
        }else {
            $status = 'error';
            $text = 'Повторите попытку';
        }
    }else {
        $status = 'error';
        $text = 'Этот email адрес уже используется';
    }
}


$response = json_encode(array('status'=>$status, 'text'=> $text));
echo $response;
