<?php

namespace DatingVIP\Component\Etcd;

interface HttpInterface
{
    public function get(string $url, array $query_arguments = []): array;
    public function post(string $url, array $payload = [], array $query_arguments = []): array;
    public function put(string $url, array $payload = [], array $query_arguments = []);
    public function delete(string $url, array $query_arguments = []);
}
