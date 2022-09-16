<?php

namespace App\Cache;

use App\Cache\Repository\HashRedis;

/**
 * 私信消息未读数 - 缓存助手
 *
 * @package App\Cache
 */
class UnreadTalk extends HashRedis
{
    public $name = 'unread-talk';

    /**
     * 消息未读数自增
     *
     * @param int $sender  发送者ID
     * @param int $receive 接收者ID
     */
    public function increment(int $sender, int $receive)
    {
        $this->incr($this->flag($sender, $receive), 1);
    }

    /**
     * 读取消息未读数
     *
     * @param int $sender  发送者ID
     * @param int $receive 接收者ID
     * @return int
     */
    public function read(int $sender, int $receive): int
    {

        return (int)$this->get($this->flag($sender, $receive));
    }

    /**
     * 消息未读数清空
     *
     * @param int $sender  发送者ID
     * @param int $receive 接收者ID
     */
    public function reset(int $sender, int $receive)
    {
        $this->rem($this->flag($sender, $receive));
    }

    /**
     * 获取 Hash 成员 key
     *
     * @return string
     */
    public function flag(int $sender, int $receive)
    {
        return sprintf("%s_%s", $sender, $receive);
    }

    /**
     * 读取指定用户的未读消息列表
     *
     * @param int $user_id 用户ID
     * @return array
     */
    public function reads(int $user_id): array
    {
        $cursor = '0';
         $pattern_arr['count'] = 20;
         $pattern_arr['match'] ='*_' . $user_id;
        while($cursor !== 0){
            // 命令位置 vendor\predis\predis\src\Command\HashScan.php
            $info =$this->redis()->hscan($this->getCacheKey(), $cursor, $pattern_arr);
            $cursor = intval($info[0] ?? 0);
            $list = $info[1] ?? [];
            $arr = [];
            if($list){
                foreach($list as $field => $v){
                    $arr[explode('_', $field)[0]] = $v;
                }
            }
        }

        return  $arr ?? [];
        }


}
