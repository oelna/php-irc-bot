<?php
return function (string $user, string $args, string $channel) {
    if (mb_strlen(trim($args)) > 1) {
        $args = preg_replace('!\s+!', ' ', trim($args));
    	
        return 'Here is your Stackoverflow search: https://stackoverflow.com/search?q='.urlencode($args);
    }

    return 'Usage: !so <search terms>, eg: !so random number in javascript';
};
