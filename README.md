WooCommerce API Client
======================

API client for Wordpress [WooCommerce](https://wordpress.org/plugins/woocommerce/) plugin.

It was created first of all form importing products into fresh installed store.

API documentation can be found here: http://woothemes.github.io/woocommerce-rest-api-docs/

Installation
------------

This client can be installed using Composer. Add the following to your composer.json:

    {
        "require": {
            "mac2000/woo-commerce-api-client": "1.0.*"
        }
    }

Usage Examples
--------------

Instantiation:

    use Mac2000\WooCommerceApiClient\Client as Woo;
    $client = new Woo('ck_********************************', 'cs_********************************', 'http://acme.com/');

Creating product:

    print_r($client->post('products', [
        'json' => [
            'product' => [
                'title' => 'Test 1',
                'type' => 'simple',
                'regular_price' => 9.99,
                'description' => 'Test 1 desc',
                'short_description' => '',
                'categories' => ['Test category 1', 'Test category 2'],
                'images' => [
                    ['src' => 'http://placehold.it/800x600', 'position' => 0]
                ]
            ]
        ]
    ])->json());

Retrieve products:

    print_r($client->get('products')->json());

