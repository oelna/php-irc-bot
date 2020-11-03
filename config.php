<?php

// config file for use with regular IRC servers

return array(
	'server' => array(
		'url' => 'chat.freenode.net',
		'port' => 6697,
		'ssl' => true
	),
	// your user data
	'user' => array(
		'username' => 'oelnabot',
		'realname' => 'Arno Richter',
		'nickname' => 'oelnabot',
		'password' => ''
	),
	// which channels to join on login
	'channels' => array(
		'##linux-beginners',
		'##crypto'
	)
);
