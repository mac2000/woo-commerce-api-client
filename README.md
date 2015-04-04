WooCommerce API Client
======================

API client for Wordpress [WooCommerce](https://wordpress.org/plugins/woocommerce/) plugin.

It was created first of all form importing products into fresh installed store.

API documentation can be found here: http://woothemes.github.io/woocommerce-rest-api-docs/

Installation
------------

This client can be installed using Composer. Add the following to your composer.json:

```json
{
    "require": {
        "mac2000/woo-commerce-api-client": "1.0.*"
    }
}
```

Usage Examples
--------------

Instantiation:

```php
use Mac2000\WooCommerceApiClient\Client as Woo;
$client = new Woo('consumer_key', 'consumer_secret', 'http://acme.com/');
```

**Creating simple product**

```php
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
                //Important: for predefined attributes slug is required, and options should contain slugs rather than names
                ['name' => 'Brand', 'slug' => 'brand', 'options' => ['nike']],
                ['name' => 'Size', 'slug' => 'size', 'options' => ['M']],
                ['name' => 'Color', 'options' => ['White']]
            ]
        ]
    ]
])->json());
```

Notice that if you want product to use predefined attributes you should provide attribute slug.

**Create variable product**

```php
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
```

**Retrieve products**

```php
print_r($client->get('products')->json());
```

**Retrie all product ids**

```php
$page = 0;
$product_ids = [];

do {
    $page++;

    $response = $client->get('products', ['query' => [
        'fields' => 'id',
        'page' => $page,
        'filter' => [
            'limit' => 5
        ]
    ]]);

    preg_match_all('/<(?P<href>[^>]+)>; rel="(?P<rel>[^"]+)"/i', $response->getHeader('Link'), $links, PREG_SET_ORDER);
    $links = array_reduce($links, function($carry, $item) {
        $carry[$item['rel']] = $item['href'];
        return $carry;
    }, []);
    
    $json = $response->json();

    $product_ids = array_merge($product_ids, array_map(function($product) {
        return $product['id'];
    }, $json['products']));

} while(isset($links['next']));

print_r($product_ids);
```

Missing API
===========

Unfortunately at moment not all things are implemented in WooCommerce API but there is still a way to manage your store.

Project contains class `MissingApiHelper` that implements some missing things via XMLRPC.

For example as you know attributes for product can be two types - taxonomy that can be used in layered navigation and custom post meta.

If you want create product with taxonomy attribute you should create it first.

Here is full example of creating such product:

```php
<?php
use Mac2000\WooCommerceApiClient\Client;
use Mac2000\WooCommerceApiClient\MissingApiHelper;

require_once 'vendor/autoload.php';

$client = new Client('ck_******', 'cs_******', 'http://acme.com/');
$helper = new MissingApiHelper('http://acme.com/xmlrpc.php', 'admin', '******');

// This is optional step, WooCommerce api will create categories
// but it will create them flat, so we are creating them manually
// to ensure that they belong to each other
$helper->ensureTwoLevelProductCategory('Wear', 'T-Shirts');

// Notice: At this moment there is no way to create attributes from outside
// so you should create them by hand before adding options
$brand = $helper->ensureAttributeOption('Brand', 'Nike');
$color = $helper->ensureAttributeOption('Color', 'White');

$brand_slug = str_replace('pa_', '', $brand['taxonomy']);
$color_slug = str_replace('pa_', '', $color['taxonomy']);

$product = [
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
        ['name' => 'Brand', 'slug' => $brand_slug, 'options' => [$brand['slug']]], // Select attribute
        ['name' => 'Color', 'slug' => $color_slug, 'options' => [$color['slug']]], // Text attribute
        ['name' => 'Jackets', 'options' => ['One']] // Custom attribute
    ]
];

$response = $client->post('products', ['json' => ['product' => $product]]);
print_r($response->json());
```