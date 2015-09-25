<?php
namespace Mac2000\WooCommerceApiClient;

use GuzzleHttp\Client as GuzzleClient;

class Client extends GuzzleClient {
    public function __construct($customer_key, $customer_secret, $store_url, array $options = [])
    {
        $options['base_url'] = [$store_url . '/wc-api/{version}/', ['version' => 'v2']];

        if(!isset($options['defaults'])) $options['defaults'] = [];
        $options['defaults']['auth'] = 'oauth';

        parent::__construct($options);

        $this->getEmitter()->attach(new WooAuth1([
            'consumer_key'    => $customer_key,
            'consumer_secret' => $customer_secret
        ]));
    }
}