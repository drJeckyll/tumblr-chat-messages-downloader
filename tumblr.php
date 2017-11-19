#!/usr/bin/php
<?php

if ($argc < 4)
{
	echo "\nUsage: " . $argv[0] . " username password blog [conversation] [file]\n\n";
	exit;
}
if ($argc == 4)
{
	$username = $argv[1];
	$password = $argv[2];
	$blog = $argv[3] . ".tumblr.com";
	$conversation = "";
} else {
	$username = $argv[1];
	$password = $argv[2];
	$blog = $argv[3] . ".tumblr.com";
	$conversation = $argv[4];
	if ($argc == 6)
	{
		$file = $argv[5];
	} else $file = "";
}

echo "\nFetch: login, ";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://www.tumblr.com/login");
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_COOKIESESSION, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, "tumbltcookiefile");
curl_setopt($ch, CURLOPT_POST, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$r = curl_exec($ch);

preg_match('#<meta name="tumblr-form-key" id="tumblr_form_key" content="(.*?)">#', $r,$matches);
$key = $matches[1];

$post = array(
	'tracking_url'		=> '/login',
	'determine_email'	=> $username,
	'user[age]'			=> '',
	'user[email]'		=> $username,
	'user[password]'	=> $password,
	'version'			=> 'STANDARD',
	'form_key'			=> $key,
);

echo "home, ";
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
$r = curl_exec($ch);

preg_match('#polling_token&quot;:&quot;(.*?)&quot;,&quot;#', $r, $matches);
$token = @$matches[1];
preg_match('#mention_key&quot;:&quot;(.*?)&quot;,&quot;#', $r, $matches);
$mention = @$matches[1];

if (($token == "") || ($mention == ""))
{
	echo "done.\n\nInvlid username or password!\n\n";

	exit;
}

if ($conversation == "")
{
	echo "conversations, ";

	$conv = array();
	$next = "xxx";
	$q = "https://www.tumblr.com/svc/conversations?participant=" . $blog . "&_=" . time() . "000";
	while ($next != "")
	{
		curl_setopt($ch, CURLOPT_URL, $q);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'X-Requested-With: XMLHttpRequest',
		));
		curl_setopt($ch, CURLOPT_POST, false);
		$r = curl_exec($ch);
		$r = json_decode($r);
		$next = @$r->response->conversations->_links->next->href;

		foreach ($r->response->conversations as $c)
		{
			foreach ($c->participants as $p)
			{
				$conv[$c->id][] = $p->name;
			}
		}
		
		$q = "https://www.tumblr.com" . $next;
	}

	echo "done.\n\n";

	echo "\nConversations: \n";
	foreach ($conv as $i => $c)
	{
		echo $i . " " . join(" <=> ", $c) . "\n";
	}
	echo "\n";

	exit;
}

$messages = array();
$next = "xxx";
$q = "https://www.tumblr.com/svc/conversations/messages?conversation_id=" . $conversation . "&participant=" . $blog . "&_=" . time() . "000";
while ($next != "")
{
	$t = 0;

	curl_setopt($ch, CURLOPT_URL, $q);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'X-Requested-With: XMLHttpRequest',
	));
	curl_setopt($ch, CURLOPT_POST, false);
	$y = 5;
	while ($y > 0)
	{
		$r = curl_exec($ch);
		$r = json_decode($r);
		if (is_object($r)) break;
		$y--;
		echo "retry, ";
	}
	$next = @$r->response->messages->_links->next->href;
	$r = $r->response->messages->data;

	if (count($r))
	foreach ($r as $i)
	{
		if ($t == 0)
		{
			$t = $i->ts;

			echo date("d/m/Y, H:i:s", $t / 1000) . ", ";
		}

		$user = @array_shift(explode(".", $i->participant));

		if ($i->type == "TEXT")
			$messages[$i->ts] = date("d/m/Y, H:i:s", $i->ts / 1000) . " " . $user . ": " . $i->message;
		else
		if ($i->type == "IMAGE")
		{
			$images = array();
			foreach ($i->images as $img) $images[] = $img->original_size->url;
		
			$messages[$i->ts] = date("d/m/Y, H:i:s", $i->ts / 1000) . " " . $user . ": " . join(" , ", $images);
		} else
		if ($i->type == "POSTREF")
		{
			$messages[$i->ts] = date("d/m/Y, H:i:s", $i->ts / 1000) . " " . $user . ": " . $i->post->post_url;
		} else {
			echo "\nUNKNOWN\n";
			print_r($i);
		}
	}
	
	$q = "https://www.tumblr.com" . $next;
}

echo "done.\n\n";

ksort($messages);
$messages = join("\n", array_values($messages));
if ($file != "")
{
	file_put_contents($file, $messages . "\n");
} else
	echo $messages . "\n\n";

curl_close($ch);
