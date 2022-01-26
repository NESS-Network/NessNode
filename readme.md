# Ness node DEMO

This is first version of NESS node with only basic functionality and two services node and prng
Devblog https://ness-main-dev.medium.com/

## TODO

 * Authentication - DONE
 * Ness coin hours - DONE

## Instalation

 * Apache
 * PHP 7+
 * Emercoin daemon with JsonRPC connection configured in `~/.emercoin/emercoin.conf` and `modules/emer/config/emercoin.php`
 * Clone PRNG from https://github.com/NESS-Network/PyUHEPRNG and run `python server.py` to launch random number generator
 * Change systemd configuration for apache in `/lib/systemd/apache2.service` or in `/lib/systemd/system/httpd.service` change the `PrivateTmp=false` to make `/tmp/*` directory readable
 * RUN `exec/cron.php` every hour using cron utility, this will pay needed fee from every user address to master user address (every hour payment).

## Services

All output is made in JSON format.
if param `result` is `error` then the error message is stored in `error` param as string
if param `result` is `info` then the info is stored in `info` param as array
if param `result` is `data` then the data is stored in `data` param as array

### node
All data is sent in HTTP POST or GET request and returned in JSON format
 * `http://node-url/node/info` display all info about node
 * `http://node-url/node/services` output all available services
 * `http://node-url/node/nodes` display all nodes found in blockchain
 * `http://node-url/node/man`display manual
 * `http://node-url/node/testAuthId/username/auth-id`test authentication by Auth ID
 * `http://node-url/node/testAuthTwoWay`test authentication by Two Way Encryption
 * `http://node-url/node/get-address/username/auth-id` Get user payment address or return existing one (Auth ID)
Returned data: `{data: {address: 784y5t4787ytw487yt}}
 * `http://node-url/node/userinfo/username/auth-id`Display all info about user (Auth ID)
Returned data: `{data: {'addr': 'hw9gw4rogj', 'counter': 0, 'balance': {'coins': 5000, 'hours': 82689, 'fee': 8269, 'available': 74420}, 'is_active': True}}`
is_active - can user use this node (has enough hours to pay the node)
counter - how many hours user was active (was using this node)
* `http://node-url/node/balance/username/auth-id`Get user current balance (Auth ID)
Returned data: `{data: {balance: {'coins': 5000, 'hours': 82689, 'fee': 8269, 'available': 74420}}}`
hours - total hours
fee - fee substracted from hours
available - available hours for withdraw
 * `http://node-url/node/withdraw`Withdraw funds (Two Way Encryption)
 Input data (POST): `{data: {coins: 1, hours: 111, to_addr: 495u4ugjhgt}, username: user, sig: 54e65e5j}`
sig - signature of data
to_addr - external address, where to withdraw

*Error format*: `{error: "Error text message"}`

 Read more about authentication in my devblog https://ness-main-dev.medium.com/authentication-on-ness-nodes-f25e2cda0f0d
 Read more about payment system https://ness-main-dev.medium.com/counter-random-payment-12813584826f
 
### Config files
##### ~/.ness
* `emer.json` Connection to emercoin RPC
* `ness.json` Connection to PrivateNess daemon
* `node.json` Node params
* `payments.json`Payments list
* `prng.json` PRNG service config
* `users.json` Users list - users address, counter and random-hours

### prng
 * `/prng/seed` output randomly generated seed (regenerated every second)
 * `/prng/seedb` output randomly generated big seed (regenerated every second)
 * `/prng/numbers` output randomly generated numbers (100) (regenerated every second)
 * `/prng/numbersb` output randomly generated numbers (1000) (regenerated every second)

## Testing
##### create-user.php
Create test user ( in `user.json` without using blockchain)
Usage:  `php create-username.php <username> [address]` (address is optional)
##### payment-test.php
Usage: `php test.php <username>`pay for single hour (similar as exec/cron.php but for single user)