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


    function merchantStatus($pesapal_merchant_reference)
    {
        $consumer_key = config('pesapal.consumer_key');

        $consumer_secret = config('pesapal.consumer_secret');

        $statusrequestAPI = $this->api_link('querypaymentstatusbymerchantref');

        $token = $params = NULL;
        $consumer = new OAuthConsumer($consumer_key, $consumer_secret);
        $signature_method = new OAuthSignatureMethod_HMAC_SHA1();

        //get transaction status
        $request_status = OAuthRequest::from_consumer_and_token($consumer, $token, "GET", $statusrequestAPI, $params);
        $request_status->set_parameter("pesapal_merchant_reference", $pesapal_merchant_reference);
        $request_status->sign_request($signature_method, $consumer, $token);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request_status);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        if (defined('CURL_PROXY_REQUIRED')) {
            if (CURL_PROXY_REQUIRED == 'True') {
                $proxy_tunnel_flag = (defined('CURL_PROXY_TUNNEL_FLAG') && strtoupper(CURL_PROXY_TUNNEL_FLAG) == 'FALSE') ? false : true;
                curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, $proxy_tunnel_flag);
                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                curl_setopt($ch, CURLOPT_PROXY, CURL_PROXY_SERVER_DETAILS);
            }
        }

        $response = curl_exec($ch);

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $raw_header = substr($response, 0, $header_size - 4);
        $headerArray = explode("\r\n\r\n", $raw_header);
        $header = $headerArray[count($headerArray) - 1];

        //transaction status
        $elements = preg_split("/=/", substr($response, $header_size));
        $status = $elements[1];

        curl_close($ch);

        return $status;
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
