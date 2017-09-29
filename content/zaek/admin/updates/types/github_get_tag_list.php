<?php
$aUrl = explode('/', $_POST['params']['repo']);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.github.com/repos/".$aUrl[count($aUrl)-2]."/".$aUrl[count($aUrl)-1]."/tags");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
$output = curl_exec($ch);
curl_close($ch);

return (array)@json_decode($output);