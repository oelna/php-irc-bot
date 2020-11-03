<?php

// config file for use with regular IRC servers

return array(
	'server' => array(
		'url' => 'irc.arnorichter.de',
		'port' => 47362,
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
		'#int', 
		'#html'
	)
);
