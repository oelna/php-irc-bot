<?php
return function (string $user, string $args, string $channel) {
    if ($args) {
    	$params = explode(' ', $args);

    	if(isset($params[0]) && isset($params[1])) {
    		return random_int((int) trim($params[0]), (int) trim($params[1]));
    	} else {
    		return random_int(1, 100);
    	}
    } else {
        return random_int(1, 100);
    }
};
