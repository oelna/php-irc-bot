<?php
return function (string $user, string $args, string $channel) {
    if ($args) {
        $args = preg_replace('!\s+!', ' ', $args);
    	$params = explode(' ', $args);

    	if(!empty(trim($params[0])) && !empty(trim($params[2]))) {
            $n = 100 * (int) trim($params[0]) / (int) trim($params[2]);
            return round($n, 2) . '%';
    	}
    }

    return "Usage: !chance <number> in <number>, eg. !chance 1 in 5";
};
