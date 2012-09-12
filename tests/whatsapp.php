#!/usr/bin/php
<?php
/**
 * 12-sep-2012 note: this is currently broken, to be fixed in the next push.
 */
require "../src/php/whatsprot.class.php";

function fgets_u($pStdn) {
	$pArr = array($pStdn);

	if (false === ($num_changed_streams = stream_select($pArr, $write = NULL, $except = NULL, 0))) {
		print("\$ 001 Socket Error : UNABLE TO WATCH STDIN.\n");
		return FALSE;
	} elseif ($num_changed_streams > 0) {
		return trim(fgets($pStdn, 1024));
	}
}

$nickname = "Your Name";
$sender = "346xxxxxxxx";
$imei = "35xxxxxxxxxxxxx";

$password = md5(strrev($imei));

if ($argc < 2) {
	echo "USAGE: ".$_SERVER['argv'][0]." [-l] [-s <phone> <message>] [-i <phone>]\n";
	echo "\tphone: full number including country code, without '+' or '00'\n";
	echo "\t-s: send message\n";
	echo "\t-l: listen for new messages\n";
	echo "\t-i: interactive conversation with <phone>\n";
	exit(1);
}

$dst=$_SERVER['argv'][2];
$msg="";
for ($i=3; $i<$argc; $i++) {
	$msg.=$_SERVER['argv'][$i]." ";
}

echo "[] Logging in as '$nickname' ($sender)\n";
$wa = new WhatsProt("$sender", $password, "$nickname");
$wa->Connect();
$wa->Login();

if ($_SERVER['argv'][1] == "-i") {
	echo "\n[] Interactive conversation with $dst:\n";
	stream_set_timeout(STDIN,1);
	while(TRUE) {
		$buff = $wa->read();
		$line = fgets_u(STDIN);
		if ($line != "") {
			if (strrchr($line, " ")) {
				// needs PHP >= 5.3.0
				$command = trim(strstr($line, ' ', TRUE));
			} else {
				$command = $line;
			}
			switch ($command) {
				case "/query":
					$dst = trim(strstr($line, ' ', FALSE));
					echo "[] Interactive conversation with $dst:\n";
					break;
				case "/accountinfo":
					echo "[] Account Info: ";
					$wa->accountInfo();
					break;
				case "/lastseen":
					echo "[] Request last seen $dst: ";
					$wa->RequestLastSeen("$dst"); 
					break;
				default:
					echo "[] Send message to $dst: $line\n";
					$wa->Message(time()."-1","$dst","$line");
					break;
			}
		}
	}
	exit(0);
}

//echo "\n[] Account Info: ";
//$wa->accountInfo();

if ($_SERVER['argv'][1] == "-l") {
	echo "\n[] Listen mode:\n";
	while (TRUE) {
		$buff = $wa->read();
		if (strlen($buff) != 0)
			echo "\n";
		sleep(1);
	}
	exit(0);
}

echo "\n[] Request last seen $dst: ";
$wa->RequestLastSeen("$dst"); 

echo "\n[] Send message to $dst: $msg\n";
$wa->Message(time()."-1","$dst","$msg");
echo "\n";
?>
