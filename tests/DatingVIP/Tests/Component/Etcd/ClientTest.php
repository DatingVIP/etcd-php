<?php

declare(strict_types=1);

namespace DatingVIP\Tests\Component\Etcd;

use DatingVIP\Component\Etcd\Client;
use DatingVIP\Component\Etcd\Exception\EtcdException;
use DatingVIP\Component\Etcd\Http\Curl;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    protected Client $client;

    private string $dirname = '/phpunit_test';

    protected function setUp(): void
    {
        $this->client = new Client('', '', $this->dirname);
        $this->client->setRoot($this->dirname);
    }

    protected function tearDown(): void
    {
        $this->client->setRoot('/');
    }

    public function testKeySet()
    {
        $http = $this->createStub(Curl::class);
        $http->method('put')->willReturn(null);
        $this->client->setHttpClient($http);
        $this->assertFalse($this->client->keySet('', ''));

        $http = $this->createStub(Curl::class);
        $http->method('put')->willReturn(['node' => ['value' => 'AA']]);
        $this->client->setHttpClient($http);
        $this->assertFalse($this->client->keySet('somekey', 'BB'));

        $this->assertTrue($this->client->keySet('somekey', 'AA'));
    }

    public function testKeyGet()
    {
        $http = $this->createStub(Curl::class);
        $http->method('put')->willReturn(null);
        $this->client->setHttpClient($http);

        try {
            $this->assertTrue($this->client->keyGet(''));
        } catch (EtcdException $e) {
            $this->assertSame('Node has not been found.', $e->getMessage());
        }

        $http = $this->createStub(Curl::class);
        $http->method('get')->willReturn([
            'action' => 'get',
            'node' => [
                'key' => '/aa',
                'value' => 'bb',
                'modifiedIndex' => '256',
                'createdIndex' => '256'
            ]
        ]);
        $this->client->setHttpClient($http);
        $this->assertSame('bb', $this->client->keyGet('aa'));
    }
}
