#!/usr/bin/php
<?php
$username='your username goes here';
$key='api_key goes here';
$secret='api_private_key goes here';

$options = getopt("e:h");

function nonce() {
$nonce = round(microtime(true)*100);
return $nonce; }

function generate_hash() {
$string = nonce().$GLOBALS['username'].$GLOBALS['key'];
$hash = hash_hmac('sha256', $string, $GLOBALS['secret']);
$hash = strtoupper($hash);
return $hash; }


function cdate() {
$current_date_time = date ("M-d-y H:i:s T ");
return $current_date_time;}
$version="0.1";

if (isset($options['h'])) { //display help page

echo "Cloaked Nemesis ver. $version\n";
echo "Usage:\n";
echo "-e 1 executes the order for entire NMC balance. No order will be placed if you don't specify this.\n";
echo "-h displays this help page\n";
die (1);
}
echo cdate()."Cloacked Nemesis v. $version STARTED\n";
$url = 'https://cex.io/api/ticker/GHS/NMC';
$io = curl_init();
curl_setopt($io, CURLOPT_URL, $url);
curl_setopt($io, CURLOPT_RETURNTRANSFER, true);
curl_setopt($io, CURLOPT_USERAGENT, 'phpCloakedNemesis');
$out = curl_exec($io);
$data = json_decode($out,true);
$price_last = $data['last'];
echo cdate()."Last price = $price_last\n";
$url = 'https://cex.io/api/balance/';
$post = 'key='.$key.'&signature='.generate_hash().'&nonce='.nonce();
$io = curl_init();
curl_setopt($io, CURLOPT_URL, $url);
curl_setopt($io, CURLOPT_RETURNTRANSFER, true);
curl_setopt($io, CURLOPT_POST, true);
curl_setopt($io, CURLOPT_USERAGENT, 'phpCloakedNemesis');
curl_setopt($io, CURLOPT_POSTFIELDS, $post);
$out = curl_exec($io);
curl_close($io);
$data = json_decode($out, true);
$available_NMC = $data['NMC'];
$available = $available_NMC['available'];
echo cdate()."available NMC balance is $available\n";
$execute = round($available/$price_last,3);
echo cdate()."if executed now, estimated GHS=$execute\n";

// order exec part

if (isset($options['e'])) {
if ($options['e']==1) {
echo cdate()."*** Execution ENABLED\n";
$price=$price_last;
$amount=round($available/$price,5,PHP_ROUND_HALF_DOWN);
$url = 'https://cex.io/api/place_order/GHS/NMC';
$post = 'key='.$key.'&signature='.generate_hash().'&nonce='.nonce().'&type=buy&amount='.$amount.'&price='.$price;
$io = curl_init();
curl_setopt($io, CURLOPT_URL, $url);
curl_setopt($io, CURLOPT_RETURNTRANSFER, true);
curl_setopt($io, CURLOPT_POST, true);
curl_setopt($io, CURLOPT_USERAGENT, 'phpCloakedNemesis');
curl_setopt($io, CURLOPT_POSTFIELDS, $post);
$out = curl_exec($io);
curl_close($io);
echo cdate()."Amount GHS = ".$amount."\n";
$data = json_decode($out, true);
if (isset($data['error'])) { echo cdate()."failed to execute trade. Error recv from server: ".$data['error']."\n";}
if (isset($data['amount'])) { echo cdate()."trade executed; Amount is ".$data['amount']."\n";}
} // e=1 ends here
} // order execution if ends here 
echo cdate()."Cloacked Nemesis v. $version FINISHED\n";
die(0);
?>




