<?php
return function (string $user, string $args, string $channel) {
    if (mb_strlen(trim($args)) > 1) {
        $args = preg_replace('!\s+!', ' ', trim($args));
    	
        return 'Here is your SelfHTML search: https://wiki.selfhtml.org/index.php?search='.urlencode($args);
    }

    return 'Usage: !sh <element|property>, eg: !sh background-color or !sh li';
};
