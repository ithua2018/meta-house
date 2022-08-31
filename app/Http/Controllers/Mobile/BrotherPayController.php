<?php
namespace App\Http\Controllers\Mobile;
use GuzzleHttp\Client;

class BrotherPayController extends  AbstractApiController
{
    private Client  $client;
    public function __construct( Client  $client)
    {
        $this->client = $client;
    }

    /**
     * 充值
     */
    public  function  charge()
    {
        $params = [
             'mchId' => 20000243,
             'appId' => 'a9f8177d7c0b448fa0ce3357df3327d6',
             'productId' => 9008,
             'amount' => 3000,
             'idNumber' => '123456',
             'contact' => '13655441122',
             'notifyUrl' => 'http://47.88.13.105:8088/',
        ];
       $data =  $this->buildAuthCallParams($params);
        console_debug($data);
        $body = (string) $this->client
            ->post('http://47.88.13.105:3020/api/v1/collection/create', ['form_params' => $data])
            ->getBody();
      // console_debug($body);
        return json_decode($body,true);
    }


    public function buildAuthCallParams(array $params, bool $toString = false) // @phpcs:ignore
    {

       //$query =  'amount=3000&appId=a9f8177d7c0b448fa0ce3357df3327d6&contact=13655441122&idNumber=123456&mchId=20000243&notifyUrl=http://47.88.13.105:8088/&productId=9008&key=G3VBOOVCLXVLOAW2AZPE0OWACFNIZRLWISM854LZ64H5FQ1XZPL5LXNGALXD3SA1STNKKK145Y7UYOLUKM2VOCEFVLPDP2ZP62LZYXNZVLKIRPU2DMAIMC8DWQWYYTMG';
         // $query = 'amount=3000&appId=a9f8177d7c0b448fa0ce3357df3327d6&contact=13655441122&idNumber=123456&mchId=20000243&notifyUrl=http://47.88.13.105:8088/&productId=9008&key=G3VBOOVCLXVLOAW2AZPE0OWACFNIZRLWISM854LZ64H5FQ1XZPL5LXNGALXD3SA1STNKKK145Y7UYOLUKM2VOCEFVLPDP2ZP62LZYXNZVLKIRPU2DMAIMC8DWQWYYTMG';
         $key = 'G3VBOOVCLXVLOAW2AZPE0OWACFNIZRLWISM854LZ64H5FQ1XZPL5LXNGALXD3SA1STNKKK145Y7UYOLUKM2VOCEFVLPDP2ZP62LZYXNZVLKIRPU2DMAIMC8DWQWYYTMG';
        ksort($params);
        // Generate the API signature.
        $params['key'] = $key;
       $query = urldecode(http_build_query($params));
        console_debug($query);
        $params['sign'] = strtoupper(md5($query));
        if (!$toString) {
            unset($params['key']);
            return $params;
        }

        $query = '';

        foreach ($params as $key => $value) {
            $query .= "$key=$value&";
        }

        return rtrim($query, '&');
    }

}
