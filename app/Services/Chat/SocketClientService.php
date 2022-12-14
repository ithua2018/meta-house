<?php

namespace App\Services\Chat;

use App\Cache\SocketFdBindUser;
use App\Cache\SocketUserBindFds;

/**
 * Socket客户端ID服务
 *
 * @package App\Service
 */
class SocketClientService
{
    /**
     * 客户端fd与用户ID绑定关系
     *
     * @param int    $fd      客户端fd
     * @param string    $user_id 用户ID
     * @param string $run_id  服务运行ID（默认当前服务ID）
     */
    public function bindRelation(int $fd, string $user_id, $run_id = SERVER_RUN_ID)
    {
        SocketFdBindUser::getInstance()->bind($fd, $user_id, $run_id);
        SocketUserBindFds::getInstance()->bind($fd, $user_id, $run_id);
    }

    /**
     * 解除指定的客户端fd与用户绑定关系
     *
     * @param int    $fd     客户端ID
     * @param string $run_id 服务运行ID（默认当前服务ID）
     */
    public function removeRelation(int $fd, $run_id = SERVER_RUN_ID)
    {
        $user_id = $this->findFdUserId($fd);

        SocketFdBindUser::getInstance()->unBind($fd, $run_id);
        SocketUserBindFds::getInstance()->unBind($fd, $user_id, $run_id);
    }

    /**
     * 检测用户当前是否在线（指定运行服务器）
     *
     * @param string    $user_id 用户ID
     * @param string $run_id  服务运行ID（默认当前服务ID）
     * @return bool
     */
    public function isOnline(string $user_id, $run_id = SERVER_RUN_ID): bool
    {
        return SocketUserBindFds::getInstance()->isOnline($user_id, $run_id);
    }

    /**
     * 检测用户当前是否在线(查询所有在线服务器)
     *
     * @param string    $user_id 用户ID
     * @param array $run_ids 服务运行ID
     * @return bool
     */
    public function isOnlineAll(string $user_id, array $run_ids = []): bool
    {

        return SocketUserBindFds::getInstance()->isOnlineAll($user_id, $run_ids);
    }

    /**
     * 查询客户端fd对应的用户ID
     *
     * @param int    $fd     客户端ID
     * @param string $run_id 服务运行ID（默认当前服务ID）
     * @return int
     */
    public function findFdUserId(int $fd, $run_id = SERVER_RUN_ID): int
    {
        console_debug('再次获取key：'.$run_id);
        return SocketFdBindUser::getInstance()->findUserId($fd, $run_id);
    }

    /**
     * 查询用户的客户端fd集合(用户可能存在多端登录)
     *
     * @param string    $user_id 用户ID
     * @param string $run_id  服务运行ID（默认当前服务ID）
     * @return array
     */
    public function findUserFds(string $user_id, $run_id = SERVER_RUN_ID)
    {
        return SocketUserBindFds::getInstance()->findFds($user_id, $run_id);
    }
}
