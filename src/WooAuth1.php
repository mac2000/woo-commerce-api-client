<?php
namespace Mac2000\WooCommerceApiClient;

use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Post\PostBodyInterface;
use GuzzleHttp\Query;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

class WooAuth1 extends Oauth1
{
    /**
     * @var string Consumer secret, used to sign requests, leaved here because parent class hides $config
     */
    private $consumer_secret;

    public function __construct($config)
    {
        $config['request_method'] = self::REQUEST_METHOD_QUERY;
        $config['version'] = null;

        $this->consumer_secret = $config['consumer_secret'];

        parent::__construct($config);
    }

    /**
     * Calculate signature for request
     *
     * This method mostly copy pasted from original class, except its bottom part where we actually hashing our request
     *
     * @param RequestInterface $request Request to generate a signature for
     * @param array $params Oauth parameters.
     *
     * @return string
     */
    public function getSignature(RequestInterface $request, array $params)
    {

        // Remove oauth_signature if present
        // Ref: Spec: 9.1.1 ("The oauth_signature parameter MUST be excluded.")
        unset($params['oauth_signature']);

        // Add POST fields if the request uses POST fields and no files
        $body = $request->getBody();
        if ($body instanceof PostBodyInterface && !$body->getFiles()) {
            $query = Query::fromString($body->getFields(true));
            $params += $query->toArray();
        }

        // Parse & add query string parameters as base string parameters
        $query = Query::fromString((string)$request->getQuery());
        $query->setEncodingType(Query::RFC1738);
        $params += $query->toArray();

        $baseString = $this->createBaseString(
            $request,
            $this->prepareParameters($params)
        );

        // changed code
        return base64_encode(hash_hmac('sha1', $baseString, $this->consumer_secret, true));
    }

    /**
     * Convert booleans to strings, removed unset parameters, and sorts the array
     *
     * This method copied from original class as is just because it is private
     *
     * @param array $data Data array
     *
     * @return array
     */
    private function prepareParameters($data)
    {
        // Parameters are sorted by name, using lexicographical byte value
        // ordering. Ref: Spec: 9.1.1 (1).
        uksort($data, 'strcmp');

        foreach ($data as $key => $value) {
            if ($value === null) {
                unset($data[$key]);
            }
        }

        return $data;
    }
}
