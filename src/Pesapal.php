<?php
namespace Wokora\Pesapal;

use Wokora\Pesapal\OAuth\OAuthConsumer;
use Wokora\Pesapal\OAuth\OAuthRequest;
use Wokora\Pesapal\OAuth\OAuthSignatureMethod_HMAC_SHA1;
use Wokora\Pesapal\Exceptions\PesapalException;


class Pesapal
{
    public $callback_url;
    public $amount;
    public $description;
    public $type = 'MERCHANT';
    public $reference;
    public $first_name = '';
    public $last_name = '';
    public $email;
    public $currency = 'KES';
    public $phonenumber = '';

    public function pay(){

        $params = [
            'amount' => $this->amount,
            'description' => $this->description,
            'type' => 'MERCHANT',
            'reference' => $this->reference,
            'first_name' => '',
            'last_name' => '',
            'email' => $this->email,
            'currency' => 'KES',
            'phonenumber' => '',
        ];

        if (config('pesapal.currency') != null) {
            $this->currency = config('pesapal.currency');
        }


        if (!config('pesapal.callback_url')) {
            throw new PesapalException("callback url not provided");
        }else{
            $this->callback_url = config('pesapal.callback_url');
        }

        $token = NULL;

        $consumer_key = config('pesapal.consumer_key');

        $consumer_secret = config('pesapal.consumer_secret');

        $signature_method = new OAuthSignatureMethod_HMAC_SHA1();

        $iframelink = $this->api_link('PostPesapalDirectOrderV4');

        $post_xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
                        <PesapalDirectOrderInfo
                            xmlns:xsi=\"http://www.w3.org/2001/XMLSchemainstance\"
                            xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\"
                            Amount=\"" . $this->amount . "\"
                            Description=\"" . $this->description . "\"
                            Type=\"" . $this->type . "\"
                            Reference=\"" . $this->reference . "\"
                            FirstName=\"" . $this->first_name . "\"
                            LastName=\"" . $this->last_name . "\"
                            Currency=\"" . $this->currency . "\"
                            Email=\"" . $this->email . "\"
                            PhoneNumber=\"" . $this->phonenumber . "\"
                            xmlns=\"http://www.pesapal.com\" />";

        $post_xml = htmlentities($post_xml);

        $consumer = new OAuthConsumer($consumer_key, $consumer_secret);

        $iframe_src = OAuthRequest::from_consumer_and_token($consumer, $token, "GET", $iframelink, $params);

        $iframe_src->set_parameter("oauth_callback", $this->callback_url);

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
        $live = 'https://www.pesapal.com/api/';
        $demo = 'https://demo.pesapal.com/api/';
        return (config('pesapal.live') ? $live : $demo) . $path;
    }
}
