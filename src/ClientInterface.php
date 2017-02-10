<?php

namespace DatingVIP\Component\Etcd;

interface ClientInterface
{
    public function keySet(string $key, string $value, int $ttl = null, array $condition = []) ;

    public function keyCreate(string $key, string $value, int $ttl = null, array $condition = []) : bool;

    public function keyUpdate(string $key, string $value, int $ttl = null, array $condition = []) : bool;

    public function keyGet(string $key, array $flags = null) : string;

    public function keyRemove(string $key) : bool;

    public function keyExists(string $key) : bool;

    public function dirCreate(string $key, int $ttl = 0) : bool;

    public function dirUpdate(string $key, int $ttl = 0) : bool;

    public function dirRemove(string $key, bool $recursive = false) : bool;

    public function dirExists(string $key) : bool;

    public function dirGet(string $key, bool $recursive = false) : array;

    public function dirList(string $key, bool $recursive = false) : array;
}
