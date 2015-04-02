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
    $client = new Woo('consumer_key', 'consumer_secret', 'http://acme.com/');

**Creating simple product**

    print_r($client->post('products', [
        'json' => [
            'product' => [
                'title' => 'Simple T-Shirt',
                'type' => 'simple',
                'regular_price' => 9.99,
                'description' => 'T-Shirt description goes here',
                'short_description' => 'short description',
                'categories' => ['Wear', 'T-Shirts'],
                'images' => [
                    ['src' => 'http://placehold.it/800x600', 'position' => 0]
                ],
                'attributes' => [
                    //Important: for predefined attributes slug is required
                    ['name' => 'Brand', 'slug' => 'brand', 'options' => ['Nike']],
                    ['name' => 'Size', 'slug' => 'size', 'options' => ['M']],
                    ['name' => 'Color', 'options' => ['White']]
                ]
            ]
        ]
    ])->json());

Notice that if you want product to use predefined attributes you should provide attribute slug.

**Create variable product**

    print_r($client->post('products', [
        'json' => [
            'product' => [
                'title' => 'Variable T-Shirt',
                'type' => 'variable',
                'regular_price' => 9.99,
                'description' => 'T-Shirt description goes here',
                'short_description' => 'short description',
                'categories' => ['Wear', 'T-Shirts'],
                'images' => [
                    ['src' => 'http://placehold.it/800x600', 'position' => 0]
                ],
                'attributes' => [
                    ['name' => 'Brand', 'slug' => 'brand', 'options' => ['Nike']],
                    ['name' => 'Size', 'slug' => 'size', 'options' => ['S','M','L'], 'variation' => true], //Notice: All options that will be used in variations should be here
                    ['name' => 'Color', 'options' => ['White', 'Black']]
                ],
                'variations' => [
                    [
                        'regular_price' => 8.99,
                        'attributes' => [['name' => 'Size', 'slug' => 'size', 'option' => 'S']]
                    ],
                    [
                        'regular_price' => 9.99,
                        'attributes' => [['name' => 'Size', 'slug' => 'size', 'option' => 'M']]
                    ],
                    [
                        'regular_price' => 10.99,
                        'attributes' => [['name' => 'Size', 'slug' => 'size', 'option' => 'L']]
                    ]
                ]
            ]
        ]
    ])->json());

**Retrieve products**

    print_r($client->get('products')->json());

