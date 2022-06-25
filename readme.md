# PHP 介接三竹簡訊

composer require jackcc/sms  
$sms = new Jack\Sms;  
$sms->login(['username' => '', 'password' => '']);  
$sms->send('手機', '名稱', '訊息');  