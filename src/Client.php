<?php

namespace DatingVIP\Component\Etcd;

use DatingVIP\Component\Etcd\ClientInterface;
use DatingVIP\Component\Etcd\Exception\EtcdException;
use DatingVIP\Component\Etcd\Exception\KeyNotFoundException;
use RecursiveArrayIterator;
use DatingVIP\Component\Etcd\Http\Curl;

class Client implements ClientInterface
{
    private $server = 'http://127.0.0.1:4001';

    private $apiversion = 'v2';

    private $root = '';

    private $dirs = [];

    private $values = [];

    /**
     * @var HttpInterface
     */
    private $http;

    public function __construct(string $server = '', string $version = '', string $root = '')
    {
        $server = rtrim($server, '/');

        if ($server) {
            $this->server = $server;
        }
        if ($version) {
            $this->apiversion = $version;
        }
        if ($root) {
            $this->root = $root;
        }

        $this->setHttpClient(new Curl());
    }

    public function setHttpClient(HttpInterface $http)
    {
        $this->http = $http;
    }

    private function http()
    {
        if (!$this->http) {
            throw new \RuntimeException('Http client not defined.');
        }

        return $this->http;
    }

    private function buildKeyUri(string $key): string
    {
        if (strpos($key, '/') !== 0) {
            $key = '/' . $key;
        }

        $root = $this->root;
        if (strlen($root) > 0 && strpos($root, '/') !== 0) {
            $root = '/' . $root;
        }

        $uri = '/' . $this->apiversion . '/keys' . $root . $key;

        return $this->server . $uri;
    }

    /**
     * Set the default root directory. the default is `/`
     * If the root is others e.g. /datingvip when you set new key,
     * or set dir, all of the key is under the root
     * e.g.
     * <code>
     *    $client->setRoot('/datingvip');
     *    $client->set('key1, 'value1');
     *    // the new key is /datingvip/key1
     * </code>
     */
    public function setRoot(string $root): ClientInterface
    {
        if (strpos('/', $root) === false) {
            $root = '/' . $root;
        }
        $this->root = rtrim($root, '/');

        return $this;
    }

    public function getNode(string $key, ?array $flags = null)
    {
        $query = [];
        if ($flags) {
            $query = [
                'query' => $flags
            ];
        }

        $response = $this->http()->get($this->buildKeyUri($key), $query);

        if (isset($response['errorCode'])) {
            throw new KeyNotFoundException($response['message'], $response['errorCode']);
        }

        if (empty($response['node'])) {
            throw new KeyNotFoundException('Node has not been found.');
        }

        return $response['node'];
    }

    public function keySet(string $key, string $value, ?int $ttl = null, array $condition = []): bool
    {
        $data = ['value' => $value];

        if ($ttl) {
            $data['ttl'] = $ttl;
        }

        $result = $this->http()->put($this->buildKeyUri($key), $data, $condition);
        if ($result && $result['node']['value'] == $value) {
            return true;
        }

        return false;
    }

    public function keyCreate(string $key, string $value, ?int $ttl = null, array $condition = []): bool
    {
        $extra = ['prevExist' => 'false'];

        if ($condition) {
            $extra = array_merge($extra, $condition);
        }

        return $this->keySet($key, $value, $ttl, $extra);
    }

    public function keyUpdate(string $key, string $value, ?int $ttl = null, array $condition = []): bool
    {
        $extra = ['prevExist' => 'true'];

        if ($condition) {
            $extra = array_merge($extra, $condition);
        }

        return $this->keySet($key, $value, $ttl, $extra);
    }

    public function keyGet(string $key, ?array $flags = null): string
    {
        $node = $this->getNode($key, $flags);

        return $node['value'] ?? '';
    }

    public function keyRemove(string $key): bool
    {
        return (bool) $this->http()->delete($this->buildKeyUri($key));
    }

    public function keyExists(string $key): bool
    {
        $url = $this->buildKeyUri($key);
        $response = json_decode($this->curlExec($this->curl($url), $url), true);

        if (!empty($response['node']) && array_key_exists('value', $response['node'])) {
            return true;
        }

        return false;
    }

    public function dirCreate(string $key, int $ttl = 0): bool
    {
        $data = ['dir' => 'true'];

        if ($ttl) {
            $data['ttl'] = $ttl;
        }

        return (bool)$this->http()->put($this->buildKeyUri($key), $data, ['prevExist' => 'false']);
    }

    public function dirUpdate(string $key, int $ttl = 0): bool
    {
        $data = ['dir' => 'true'];

        if ($ttl) {
            $data['ttl'] = $ttl;
        }

        return (bool) $this->http()->put($this->buildKeyUri($key), $data, ['prevExist' => 'true']);
    }

    public function dirRemove(string $key, bool $recursive = false): bool
    {
        $query = ['dir' => 'true'];

        if ($recursive === true) {
            $query['recursive'] = 'true';
        }

        return (bool) $this->http()->delete($this->buildKeyUri($key), $query);
    }

    public function dirExists(string $key): bool
    {
        $url = $this->buildKeyUri($key);
        $response = json_decode($this->curlExec($this->curl($url), $url), true);

        if (!empty($response['node']) && !empty($response['node']['dir'])) {
            return true;
        }

        return false;
    }

    public function dirGet(string $key, bool $recursive = false): array
    {
        $query = [];
        if ($recursive) {
            $query['recursive'] = 'true';
        }

        return $this->http()->get($this->buildKeyUri($key), $query);
    }

    public function dirList(string $key, bool $recursive = false): array
    {
        try {
            $data = $this->dirGet($key, $recursive);
        } catch (EtcdException $e) {
            throw $e;
        }

        return $this->traversalDir((new RecursiveArrayIterator($data)));
    }

    private function traversalDir(RecursiveArrayIterator $iterator): array
    {
        $key = '';
        while ($iterator->valid()) {
            if ($iterator->hasChildren()) {
                $this->traversalDir($iterator->getChildren());
            } else {
                if ($iterator->key() == 'key' && ($iterator->current() != '/')) {
                    $this->dirs[] = $key = $iterator->current();
                }

                if ($iterator->key() == 'value') {
                    $this->values[$key] = $iterator->current();
                }
            }
            $iterator->next();
        }

        return $this->dirs;
    }
}
