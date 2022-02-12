# Ness node DEMO

This is first version of NESS node with only basic functionality and two services node and prng
Devblog https://ness-main-dev.medium.com/

## TODO

 * Authentication - DONE
 * Ness coin hours - DONE

## Instalation

 * Apache
 * PHP 7.4+
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
 * `http://node-url/node/pub`display node public key (encryption key)
 * `http://node-url/node/verify`display node verify key (sign/verify key)
 * `http://node-url/node/testAuthId/username/auth-id`test authentication by Auth ID
 * `http://node-url/node/testAuthTwoWay`test authentication by Two Way Encryption
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
* master-user - Master user name (the user being payed every hour for node usage)
* tarif -how mush Hours the node cost for one hour

##### user WORM file
*Emercoin blockchain record*
`worm:user:ness:user`
```xml
<worm>
        <user type="ness" nonce="R04rQis5hP2EfILpAGuU8Q==" tags="Hello World,test">
                <keys>
                        <key public="rGSy2GhojuHX+4bgE5CtRZnP2OpR7+RJebqGDCNVnlY="  verify="n9SUQ3w4x+YBggUqlN/e26lopaE2rCLmOZK9Cg2zRtc=" current="current" />
                        <key public="61RxrG8CIOSDfcjLcq+y/dhhMgeyY9I7NdDZTaoQwUs="  verify="FhnIDQZ1XDOaspV4/k+ZADSe5IqkUQCWH53C42qC3XQ="/>
                        <key public="0DThVjUslwgoZuclc0ueKZYl7r+4rfmUw2bWShyQYU0="  verify="wsvQ8HXjG3P4v9+xhnp1Nc8XhLCTb0WbK3cq9aOCHZk="/>
                        <key public="QVXITMyfQLg5tVc+ElpVX0FAN3+/nv9nGZDUIVUbiwo="  verify="UZz4azAIqO2WiNMkgkgCMu38Sw8WEOco8C6y3R2Lyuk="/>
                        <key public="8QHSSL2Hgsm6wfSFaFD+6ODW770Pr8+rdABwGKBo8WA="  verify="xQHkzmniUIDTCFWWOpA9tYzmlF+AmBHCPH5mMSZF+Bw="/>
                        <key public="EaD4ufAdkRlW7psqAhL+DrGmIVQvR+R9DiaTKzoO4Eg="  verify="wkkDp9PZWj6Dq+65Xjs42zCwkz5BWJvzQt4TE9kIc7o="/>
                        <key public="kTsf7ZKy0urSGklAxLJWbHOjFtCgFlXSEq4dHDl4GEw="  verify="A2bw4W8CNr2NXBsyDLIrobJh997u90ziaSX1HJTyJNA="/>
                        <key public="HZGLPz9PukobSM6ALz7PxqBYunimLkqAoa2WwAGrDB8="  verify="gNz9z6ZOcXJgDm1BWbRrCkz4HWJ3EB4IKAO4u/imjTU="/>
                        <key public="rYsglIKg2ZQf4yfmqjH70vaC0wCjO5mXAdHPwaWcOX4="  verify="1iC81pdum1JRgQ/9j9ceu5QsPVo5VpUjAmwY6LQPM+4="/>
                </keys>
                <!-- Here tags may be different for each type of user -->
        </user>
</worm>
```
* verify - verify public key
* public - encryption public key
* nonce - salt

## Links
* [Ness node tester]( [https://github.com/NESS-Network/NessNodeTester](https://github.com/NESS-Network/NessNodeTester))
* [Dev blog](  https://ness-main-dev.medium.com)