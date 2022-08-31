<?php

namespace App\Services;


use Illuminate\Support\Facades\Cache;
use Toplan\PhpSms\Facades\Sms;

class SmsService extends BaseService
{
    CONST SMS_VALID_TIME = 600;
    private string $sms_cache_key = '';

    /**
     * 发送短信
     * @param string $mobile
     * @param string $content
     * @return bool
     */
    public function send(string $mobile):bool
    {
      $code =  $this->cacheMsgCode($mobile);
//       $res =  Sms::make()->to($mobile)->content($code)->send();
//       if(!$res) {
//          //todo
//           return false;
//       }
       return true;
    }

    /**
     * 设置缓存key
     * @param string $mobile
     * @return string
     */
    private function setKey(string $mobile):string
    {
        return 'sms|'.$mobile;
    }

    /**
     * 缓存短信验证码
     */
    private function cacheMsgCode(string $mobile):int
    {
        //如果是开发环境 默认1234
       if(app()->environment(['testing', 'local'])) {
           $code = 1234;
           $ttl = null;
       } else {
           $code = rand(1000,9999);
           $ttl = self::SMS_VALID_TIME;
       }
       $code = strval($code);
        Cache::store('redis')->put($this->setKey($mobile), $code, $ttl);
        return $code;
    }

    /**
     * 验证短信验证码的正确性
     * @param string $code
     * @param string $mobile
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function checkCodeValid(string $code, string $mobile):bool
    {

        $key = $this->setKey($mobile);
        $code_sended = Cache::store('redis')->get($key);
        if($code_sended != $code) {
            return false;
        }
        return true;
    }


    /**
     *  登录成功则提前销毁验证码
     * @param string $mobile
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function destoryCode(string  $mobile):void
    {
        $key = $this->setKey($mobile);
        Cache::store('redis')->delete($key);
    }























}
