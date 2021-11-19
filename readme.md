# Ness node DEMO

This is first version of NESS node with only basic functionality and two services node and prng
Devblog https://ness-main-dev.medium.com/

## TODO

 * Authentication - DONE
 * Ness coin hours

## Instalation

 * Apache
 * PHP 7+
 * Emercoin daemon with JsonRPC connection configured in `~/.emercoin/emercoin.conf` and `modules/emer/config/emercoin.php`
 * Clone PRNG from https://github.com/NESS-Network/PyUHEPRNG and run `python server.py` to launch random number generator
 * Change systemd configuration for apache in `/lib/systemd/apache2.service` or in `/lib/systemd/system/httpd.service` change the `PrivateTmp=false` to make `/tmp/*` directory readable

## Services

All output is made in JSON format.
if param `result` is `error` then the error message is stored in `error` param as string
if param `result` is `info` then the info is stored in `info` param as array
if param `result` is `data` then the data is stored in `data` param as array

### node
 * `/node/info` display all info about node
 * `/node/services` output all available services
 * `/node/nodes` display all nodes found in blockchain
 * `/node/man`display manual
 * `/node/testAuthId`test authentication by Auth ID
 * `/node/testAuthTwoWay`test authentication by Two Way Encryption

 Read more about authentication in my devblog https://ness-main-dev.medium.com/authentication-on-ness-nodes-f25e2cda0f0d

### prng
 * `/prng/seed` output randomly generated seed (regenerated every second)
 * `/prng/seedb` output randomly generated big seed (regenerated every second)
 * `/prng/numbers` output randomly generated numbers (100) (regenerated every second)
 * `/prng/numbersb` output randomly generated numbers (1000) (regenerated every second)