<?php

$threadid = 1355462;
$channelid = 109585;
$numofcharacters = 3;

function httprequest($url, $postvars = NULL){
	$ch = curl_init();

	// set URL and other appropriate options
	curl_setopt($ch, CURLOPT_URL, $url);
	$headers = Array("Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
	"Accept-Language: en-us,en;q=0.5",
	"Expect: 0");
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie.txt");
    curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie.txt");
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
    curl_setopt($ch, CURLOPT_TIMEOUT, 40);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	//curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
	
	if($postvars){
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);
	}

	// grab URL and pass it to the browser
	$data = curl_exec($ch);

	// close cURL resource, and free up system resources
	curl_close($ch);
	
	return $data;
}

function Logout(){
	unlink("cookie.txt");
}

function Login($username, $password){
	$postvars = array();
	$postvars['username'] = $username;
	$postvars['password'] = $password;
	$postvars['login'] = 'Login';
	$postvars['Check'] = 'OK';
	$postvars['r'] = '';
	$postvars['t'] = '';

	$data = httprequest("https://www.eveonline.com/login.asp", http_build_query($postvars));
	file_put_contents("outputlogin.html", $data);
}

function GetCharacters($userdata){
	global $threadid;
	$data = httprequest("http://www.eveonline.com/ingameboard.asp?a=reply&threadID=$threadid");
	$doc = new DOMDocument();
	$doc->LoadHTML($data);
	$selects = $doc->getElementsByTagName("select");
	foreach($selects as $select){
		$name = $select->getAttribute("name");
		echo "$name\n";
		if($name == "characterID"){
			foreach($select->childNodes as $child){
				$charid = $child->getAttribute("value");
				$charname = $child->nodeValue;
				echo "$charname:$charid\n";
				array_push($userdata['characters'], $charid);
			}
		}
	}
}

function Reply($threadid, $channelid, $charid, $text){	
	$postvars = Array();
	$postvars['subject'] = '';
	$postvars['text'] = $text;
	$postvars['countdown'] = 4000 - strlen($text);
	$postvars['characterID'] = $charid;
	$postvars['signature'] = 1;
	$postvars['channelID'] = $channelid;
	$postvars['threadID'] = $threadid;
	$postvars['line'] = 1;
	$postvars['charID'] = '';
	print_r($postvars);
	
	$data = httprequest("http://www.eveonline.com/ingameboard.asp?a=post_reply", http_build_query($postvars));
	file_put_contents("outputreply.html", $data);
}

$users = array("Ab0rted" => Array("password" => "sdfg", "characters" => array()),
//"newusername" => Array("password" => "newpassword", "characters" => array()),
);
//print_r($users);
$texts = array("Today the earth will turn 180", "This is what will make the green", "The children have arrived to make this open", "Join our public channel", "This will change your life", "nothing says cool this like", "the time is now", "Once every time we join the public", "YEterdaqy we were open, maybe today we are too", "tomorrow you will see what we are talking about", "awesome", "this is great");

$userindex = 0;
while(TRUE){
	Logout();
	$i = 0;
	foreach($users as $username => $userdata){
		$password = $userdata['password'];
		if($i == $userindex){
			break;
		}
		$i++;
	}
	echo "Logging in as $username...\n";
	Login($username, $password);
	if(count($userdata['characters']) == 0){
		GetCharacters(&$userdata);
	}
	foreach($userdata['characters'] as $charid){
		Reply($threadid, $channelid, $charid, $texts[rand(0, count($texts) - 1)]);
		echo "Bumped thread with charid $charid\n";
		sleep((60 * 60 * 24) / $numofcharacters);
	}
	$userindex++;
	if($userindex >= count($users)){
		$userindex = 0;
	}
	
}



?>