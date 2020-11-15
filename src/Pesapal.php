<?php


class Pesapal
{
    private $callback_url = '';

    public function Pay( $params ){
        // the defaults will be overidden if set in $params

        $defaults = [
            'amount' => '',
            'description' => '',
            'type' => 'MERCHANT',
            'reference' => '',
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'currency' => 'KES',
            'phonenumber' => '',
        ];

        if (!array_key_exists('currency', $params)) {
            if (config('Pesapal.currency') != null) {
                $params['currency'] = config('Pesapal.currency');
            }
        }

        $params = array_merge($defaults, $params);

        if (!config('Pesapal.callback_url')) {
            throw new PesapalException("callback url not provided");
        }

        $token = NULL;

        $consumer_key = config('Pesapal.consumer_key');

        $consumer_secret = config('Pesapal.consumer_secret');

        $signature_method = new OAuthSignatureMethod_HMAC_SHA1();

        $iframelink = $this->api_link('PostPesapalDirectOrderV4');

        $callback_url = url('/') . '/Pesapal-callback'; //redirect url, the page that will handle the response from Pesapal.

        $post_xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
                        <PesapalDirectOrderInfo
                            xmlns:xsi=\"http://www.w3.org/2001/XMLSchemainstance\"
                            xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\"
                            Amount=\"" . $params['amount'] . "\"
                            Description=\"" . $params['description'] . "\"
                            Type=\"" . $params['type'] . "\"
                            Reference=\"" . $params['reference'] . "\"
                            FirstName=\"" . $params['first_name'] . "\"
                            LastName=\"" . $params['last_name'] . "\"
                            Currency=\"" . $params['currency'] . "\"
                            Email=\"" . $params['email'] . "\"
                            PhoneNumber=\"" . $params['phonenumber'] . "\"
                            xmlns=\"http://www.Pesapal.com\" />";

        $post_xml = htmlentities($post_xml);

        $consumer = new OAuthConsumer($consumer_key, $consumer_secret);

        $iframe_src = OAuthRequest::from_consumer_and_token($consumer, $token, "GET", $iframelink, $params);

        $iframe_src->set_parameter("oauth_callback", $callback_url);

        $iframe_src->set_parameter("pesapal_request_data", $post_xml);

        $iframe_src->sign_request($signature_method, $consumer, $token);

        return $iframe_src;
    }

    /**
     * Get API path
     * @param null $path
     * @return string
     */
    public function api_link($path = null)
    {
        $live = 'https://www.Pesapal.com/api/';
        $demo = 'https://demo.Pesapal.com/api/';
        return (config('Pesapal.live') ? $live : $demo) . $path;
    }
}
