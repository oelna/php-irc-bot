<?php
return function (string $user, string $args, string $channel) {
    $value = (bool) random_int(0, 1);
    return $value ? 'Heads' : 'Tails';
};
