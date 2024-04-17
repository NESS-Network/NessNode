# Ness Service Node

This is Privatess Service Node, a place for different microservices

At the moment there are 3 services

1. Node service - service for registration and for displaying different information about current service node and current user
2. PRNG service - service for generation ultra-high entropy random data
3. Files service - a file storage and sharing service

Devblog https://ness-main-dev.medium.com/


## Instalation

### Generate node config file (node.key.json) using [Privateness tools](  https://github.com/NESS-Network/PrivatenessTools)
 * Generate node key (keygen.py)
 * Register node in blockchain (`key.py nvs` and `key.py worm`)

### WEB server stuff
 * Apache
 * PHP 8.0+ with *php-curl* and *php-xml* mods
 * composer

### Install Emercoin
 * Emercoin daemon with JsonRPC connection configured in `~/.emercoin/emercoin.conf` and `modules/emer/config/emercoin.php`

### Install PRNG server
 * Clone PRNG from https://github.com/NESS-Network/PyUHEPRNG and run `python server.py` to launch random number generator
 * Change systemd configuration for apache in `/lib/systemd/apache2.service` or in `/lib/systemd/system/httpd.service` change the `PrivateTmp=false` to make `/tmp/*` directory readable

### Install ness node
 * Clone Ness Service Node from https://github.com/NESS-Network/NessNode
 * ``` cd services/node && composer install && composer update ```
 * ``` cd services/prng && composer install && composer update ```
 * ``` cd services/files && composer install && composer update ```
 * RUN `php exec/make-config.php`
 * RUN `php exec/register-master-user.php`
 * RUN `php exec/self-test.php`
 ### Configure CRON
 * RUN `php exec/cron.php` every hour using cron utility, this will pay needed fee from every user address to master user address (every hour payment).

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
 * `http://node-url/node/pub`display node public key (encryption key)
 * `http://node-url/node/verify`display node verify key (sign/verify key)
 * `http://node-url/node/test/auth/username/auth-id`test authentication by Auth ID
 * `http://node-url/node/test/auth`test authentication by Two Way Encryption
 * `http://node-url/node/test/auth-shadow/username/auth-id`test authentication by Auth ID using shadowname
 * `http://node-url/node/test/auth-shadow`test authentication by Two Way Encryption using shadowname
 * `http://node-url/node/get-address/username/auth-id`
   Get user payment address or return existing one (Auth ID)
   Returned data: `{data: {address: 784y5t4787ytw487yt}}
 * `http://node-url/node/userinfo/username/auth-id`
 
 Display all info about user (Auth ID)
 
 Returned data: `{data: {'addr': 'hw9gw4rogj', 'counter': 0, 'balance': {'coins': 5000, 'hours': 82689, 'fee': 8269, 'available': 74420}, 'is_active': True}}`
 
 is_active - can user use this node (has enough hours to pay the node)
 
 counter - how many hours user was active (was using this node)
* `http://node-url/node/balance/username/auth-id`

Get user current balance (Auth ID)

Returned data: `{data: {balance: {'coins': 5000, 'hours': 82689, 'fee': 8269, 'available': 74420}}}`

hours - total hours

fee - fee substracted from hours

available - available hours for withdraw
 * `http://node-url/node/userinfo`
 Returned data: `{data: {userinfo: {'joined': True, 'is_active': True, 'balance': 1.00000}}}`

  joined - is user joined (registered)

  is_active - is user active (balance > counter)

  balance - total user balance

 * `http://node-url/node/join`
 Returned data: `{data: {'address': shdfgih5gh4, 'shadowname': rt9gj498h495h}`

  address - Internal NESS address of user

  shadowname - internal name of user (shadowname)

 * `http://node-url/node/joined`
  Returned data: `{data: {'joined': True, 'address': shdfgih5gh4, 'shadowname': rt9gj498h495h}`
 OR `{data: {'joined': False}}` (is not joined (registered))

  joined - is user joined (registered)

  address - Internal NESS address of user

  shadowname - internal name of user (shadowname)

 * `http://node-url/node/withdraw`
 
 Withdraw coins and hours (Two Way Encryption)
 
 Input data (POST): `{data: {coins: 1, hours: 111, to_addr: 495u4ugjhgt}, username: user, sig: 54e65e5j}`
 
sig - signature of data

to_addr - external address, where to withdraw

*Error format*: `{error: "Error text message"}`

 Read more about authentication in my [dev-blog]( https://ness-main-dev.medium.com/authentication-on-ness-nodes-f25e2cda0f0d)
 
 Read more about [payment system]( https://ness-main-dev.medium.com/counter-random-payment-12813584826f)

  * `http://node-url/prng/seed/username/auth-id` - Random generated SEED (PRNG service https://github.com/NESS-Network/PyUHEPRNG)
  * `http://node-url/prng/seedb/username/auth-id` - Random generated numbers
  * `http://node-url/prng/numbers/username/auth-id` - Random generated large SEED 
  * `http://node-url/prng/numbersb/username/auth-id` - Big ammount of random generated 
 
#### Config files
##### ~/.ness
* `emer.json` Connection to emercoin RPC
* `ness.json` Connection to PrivateNess daemon
* `node.json` Node params
* `payments.json`Payments list
* `prng.json` PRNG service config
* `users.json` Users list - users address, counter and random-hours
* `files.json` Files service config

### prng
 * `/prng/seed` output randomly generated seed (regenerated every second)
 * `/prng/seedb` output randomly generated big seed (regenerated every second)
 * `/prng/numbers` output randomly generated numbers (100) (regenerated every second)
 * `/prng/numbersb` output randomly generated numbers (1000) (regenerated every second)

### Files
 * `/files/quota` disk usage quota for current user
 * `/files/list` file list
 * `/files/fileinfo` fileinfo for selected file
 * `/files/download/$file_id/$shadowname/$auth-id` download selected file (with resume support)
 * `/files/touch` create selected file
 * `/files/remove` remove selected file
 * `/files/append/$file_id/$shadowname/$auth-id` upload and a block of new file and append it to existing file (created by touch)
 * `/files/pub/$file_id-$shadowname-$auth-id` download selected file (public use), (with resume support)

## Testing
You can generate test users (without blockchain) to test [Counter Random Payments](https://ness-main-dev.medium.com/counter-random-payment-12813584826f)

##### exec/create-user.php
Create test user ( in `users.json` without using blockchain)
Usage:  `php create-username.php <username> [address]` (address is optional)
##### exec/payment-test.php
Usage: `php test.php <username>`pay for single hour (similar as exec/cron.php but for single user)

##### *user.json*  file structure
```
{"master":{"addr":"e56he5jh5e7j6rjr6jr7","counter":2,"random_hours":7219},
"User":{"addr":"e56he5jh5e7j6rjr6jr7","counter":2,"random_hours":7568},
"ZZZ":{"addr":"e56he5jh5e7j6rjr6jr7","counter":2,"random_hours":7112},
"123":{"addr":"e56he5jh5e7j6rjr6jr7","counter":2,"random_hours":7038},
"zxc":{"addr":"e56he5jh5e7j6rjr6jr7","counter":2,"random_hours":7495},
"user":{"addr":"e56he5jh5e7j6rjr6jr7","counter":0,"random_hours":7403}}
```

* *master* - master user ( master-user="master" param from WORM file )
* *ZZZ* - username
* *addr* - address in Ness blockchain
* *counter* - amount of hours user was active, every hour the counter gets incremented (if user is active and the user payed *tariff* amount of Hours successfully)
* *random_hours* - next payment time in hours ( if counter = random_hours then payment() )

##### node WORM file
*Emercoin blockchain record*
`worm:node:ness:http://my-ness-node.net`
```xml
<worm>
        <node type="ness" url="http://my-ness-node.net" nonce="Q3khjWopdxiLpPweVo6+BQ=="    verify="Q13IcdGM6CLjH+zZ/EaPgK+2C8igkh8/x0aEgZVVfTw=" public="dJplXPV7cqsC518qg0bJXoWknhqkIZQNTnksVHaSq2E=" master-user="master-user-name" tariff="24" tags="Test,My test node,Hello world">
                <!-- Here tags may be different for each type of node or each node -->
        </node>
</worm>

```
* url - url of the node
* verify - verify public key
* public - encryption public key
* nonce - salt
* master - user - Master user name (the user being payed every hour for node usage)
* tarif - how mush Hours the node cost for one hour

##### user WORM file
*Emercoin blockchain record*
`worm:user:ness:user`
```xml
<worm>
        <user type="ness" nonce="R04rQis5hP2EfILpAGuU8Q==" tags="Hello World,test" public="rYsglIKg2ZQf4yfmqjH70vaC0wCjO5mXAdHPwaWcOX4="  verify="1iC81pdum1JRgQ/9j9ceu5QsPVo5VpUjAmwY6LQPM+4="/>
                <!-- Here tags may be different for each type of user -->
        </user>
</worm>
```
* verify - verify public key
* public - encryption public key
* nonce - salt

## Links
* [Privateness tools](  https://github.com/NESS-Network/PrivatenessTools)
* [Dev blog](  https://ness-main-dev.medium.com)