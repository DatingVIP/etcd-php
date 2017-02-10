# Etcd PHP Client

etcd is a distributed configuration system, part of the coreos project.

This repository provides a client library for etcd for PHP applications.
Inspired by [linkorb/etcd-php](https://github.com/linkorb/etcd-php).
For our purpose, we need a more elastic version for etcd client. It can use with predefined HTTP client (no matter what it is) and also supports PHP 7.1.

The etcd-php uses curl library as a default client and creates own instance of http client. 

## Installating etcd

To install etcd, follow instructions that etcd team posts on Releases page of the project:

[https://github.com/DatingVIP/etcd/releases/](https://github.com/DatingVIP/etcd/releases/)

## Installing DatingVIP/etcd

Easiest way is to install it using composer:

```json
{
    "require" : {
        "DatingVIP/etcd": "^1.0"
    }
}
```

## Using Client

```php
use use DatingVIP\Component\Etcd\Client;

$client = new Client('http://127.0.0.1:4001');

// If you have own http client object & if it has proper adapter 
// proper means - need to implement DatingVIP\Component\Etcd\Client\HttpInterface)
$httpAdapter = new MyAdapter($myHttpClient);
$client->setHttpClient($httpAdapter);

// Get, set, update, remove key
if (!$client->keyExists('/key/name')) {
    $client->keySet('/key/name', 'value');
}
$client->set('/key/name', 'value', 10); // Set TTL
print $client->keyGet('/key/name');

$client->keyUpdate('/key/name', 'new value');

$client->keyRemove('/key/name');

// Working with dirs
if (!$client->dirExists('/dir/path')) {
    $client->dirCreate('/dir/path');
}
$client->dirUpdate('/dir/path', 10); // Set TTL
$client->dirRemove('/dir/path');

// Get dir info
$client->dirInfo('/dir/path');

// List subdirectories
$client->dirList('/dir/path');
```

## SSL

Client can be configured not to verify SSL peer:

```php
$client = (new Client('https://127.0.0.1:4001'))->verifySslPeer(false);
```

as well as to use a custom CA file:

```php
$client = (new Client('https://127.0.0.1:4001'))->verifySslPeer(true, '/path/to/ca/file');
```
