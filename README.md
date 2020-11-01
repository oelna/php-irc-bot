# PHP IRC Chatbot

A minimal IRC Chatbot written in PHP following a subset of [RFC1459](https://tools.ietf.org/html/rfc1459).

This is originally a fork of https://github.com/Hammster/php-irc-bot

**I updated it with:**

- customizable chat commands in separate `.php` files
- SSL/TLS support
- structured config at the beginning of the file
- convenience functions for sending raw messages and `PRIVMSG`
- improved channel joining for better RFC compatibility
- in some places, different error handling

## Usage

- configure values in `bot.php`
- add or modify commands in the `commands` dir, following the examples
- run `php ./bot.php` in you command line or set up a daemon (supervisord, etc.)
