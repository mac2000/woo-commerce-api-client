<?php
namespace Mac2000\WooCommerceApiClient;

use GuzzleHttp\Client as GuzzleClient;

class Client extends GuzzleClient {
    public function __construct($customer_key, $customer_secret, $store_url)
    {
        parent::__construct([
            'base_url' => ['http://' . parse_url($store_url, PHP_URL_HOST) . '/wc-api/{version}/', ['version' => 'v2']],
            'defaults' => [
                'auth' => 'oauth'
            ]
        ]);

        $this->getEmitter()->attach(new WooAuth1([
            'consumer_key'    => $customer_key,
            'consumer_secret' => $customer_secret
        ]));
    }
}