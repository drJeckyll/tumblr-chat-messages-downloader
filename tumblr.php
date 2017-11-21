#!/usr/bin/php
<?php

function usage($name)
{
	echo "\nUsage: " . $name . " -u username -p password -b blog [-c conversation] [-f filename] [-s] [-d yyyymmdd]\n\n";
	echo "\tFirst run script only with username, password and blog to get list of conversations.\n";
	echo "\tThen run script again specifying conversation you want to download.\n\n";
	echo "\t-u, --username (required)\n\t\ttumblr username or E-mail\n\n";
	echo "\t-p, --password (required)\n\t\ttumblr password\n\n";
	echo "\t-b, --blog (required)\n\t\ttumblr blog without .tumblr.com (required)\n\n";
	echo "\t-c, --conversation (optional)\n\t\tconversation id from the list\n\n";
	echo "\t-f, --file filename (optional)\n\t\toutput file name\n\n";
	echo "\t-s, --split (optional) (require -f)\n\t\tput output in separete files for each day: filename-yyyymmdd.ext\n\n";
	echo "\t-d, --date  yyyymmdd (optional)\n\t\toutput only log for specified date\n\n";
	echo "\n";

	exit;
}

$username = "";
$password = "";
$blog = "";
$conversation = "";
$file = "";
$split = 0;
$date = "";
$a = getopt("u:p:b:c:f:sd:", array("username:", "password:", "blog:", "conversation:", "file:", "split", "date:"));
if (!isset($a['u']) && !isset($a['username'])) usage($argv[0]); else {
	if (isset($a['u'])) $username = $a['u'];
	if (isset($a['username'])) $username = $a['username'];
}
if (!isset($a['p']) && !isset($a['password'])) usage($argv[0]); else {
	if (isset($a['p'])) $password = $a['p'];
	if (isset($a['password'])) $password = $a['password'];
}
if (!isset($a['b']) && !isset($a['blog'])) usage($argv[0]); else {
	if (isset($a['b'])) $blog = $a['b'] . ".tumblr.com";
	if (isset($a['blog'])) $blog = $a['blog'] . ".tumblr.com";
}
if (isset($a['c'])) $conversation = $a['c'];
if (isset($a['conversation'])) $conversation = $a['conversation'];
if (isset($a['f'])) $file = $a['f'];
if (isset($a['file'])) $file = $a['file'];
if (isset($a['s'])) $split = 1;
if (isset($a['split'])) $split = 1;
if (isset($a['d'])) $date = $a['d'];
if (isset($a['date'])) $date = $a['date'];

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

	$dfound = 0;
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
