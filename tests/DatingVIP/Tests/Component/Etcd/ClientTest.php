<?php
declare(strict_types=1);

namespace DatingVIP\Tests\Component\Etcd;

use DatingVIP\Component\Etcd\Client;
use PHPUnit\Framework\TestCase;
use DatingVIP\Component\Etcd\Exception\EtcdException;

class ClientTest extends TestCase
{
    /**
     * @var Client
     */
    protected $client;

    private $dirname = '/phpunit_test';

    protected function setUp()
    {
        $this->client = new Client('', '', $this->dirname);
        $this->client->setRoot($this->dirname);
    }

    protected function tearDown()
    {
        $this->client->setRoot('/');
    }

    public function testKeySet()
    {
        $http = $this->createMock('\DatingVIP\Component\Etcd\Http\Curl');
        $http->method('put')->willReturn(null);
        $this->client->setHttpClient($http);
        $this->assertFalse($this->client->keySet('', ''));

        $http = $this->createMock('\DatingVIP\Component\Etcd\Http\Curl');
        $http->method('put')->willReturn(['node' => ['value' => 'AA']]);
        $this->client->setHttpClient($http);
        $this->assertFalse($this->client->keySet('somekey', 'BB'));

        $this->assertTrue($this->client->keySet('somekey', 'AA'));
    }

    public function testKeyGet()
    {
        $http = $this->createMock('\DatingVIP\Component\Etcd\Http\Curl');
        $http->method('put')->willReturn(null);
        $this->client->setHttpClient($http);

        try {
            $this->assertTrue($this->client->keyGet(''));
        } catch (EtcdException $e) {
            $this->assertSame('Node has not been found.', $e->getMessage());
        }

        $http = $this->createMock('\DatingVIP\Component\Etcd\Http\Curl');
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