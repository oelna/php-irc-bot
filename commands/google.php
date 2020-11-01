<?php
return function (string $user, string $args, string $channel) {
    if (mb_strlen(trim($args)) > 1) {
        $args = preg_replace('!\s+!', ' ', trim($args));
    	
        return 'Here is your Google search: https://www.google.com/search?q='.urlencode($args);
    }

    return 'Usage: !google <search terms>, eg: !google african elephant';
};
