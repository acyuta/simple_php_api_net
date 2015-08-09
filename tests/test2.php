<?php 
function curlPost($url,$file) {
  $ch = curl_init();
  if (!is_resource($ch)) return false;
  curl_setopt( $ch , CURLOPT_SSL_VERIFYPEER , 0 );
  curl_setopt( $ch , CURLOPT_FOLLOWLOCATION , 0 );
  curl_setopt( $ch , CURLOPT_URL , $url );
  curl_setopt( $ch , CURLOPT_POST , 1 );
  curl_setopt( $ch , CURLOPT_POSTFIELDS , '@' . $file );
  curl_setopt( $ch , CURLOPT_RETURNTRANSFER , 1 );
  curl_setopt( $ch , CURLOPT_VERBOSE , 0 );
  $response = curl_exec($ch);
  curl_close($ch);
  return $response;
}

$filename = "data.bin";
$file = file_get_contents($filename);
$url = "http://localhost/api.php";

echo curlPost($url, $file);
