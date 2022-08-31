<?php
namespace App\Services\Chat;
use App\Constants\SocketConstants;
use App\Jobs\ChatQueue;

use App\Models\User;
use App\Task\ChatTask;
use App\Task\TestTask;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Hhxsv5\LaravelS\Swoole\WebSocketHandlerInterface;
use Illuminate\Container\Container;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;
use Illuminate\Support\Facades\Auth;
class WebSocketService  extends SocketClientService implements WebSocketHandlerInterface
{
    /**@var \Swoole\Table $wsTable */
    private $wsTable;
    /**
     * 消息事件绑定
     */
    const EVENTS = [
        SocketConstants::EVENT_TALK     => 'onTalk',
        SocketConstants::EVENT_KEYBOARD => 'onKeyboard',
    ];

    public function __construct()
    {
       // $this->wsTable = app('swoole')->wsTable;
    }
    // Scene：bind UserId & FD in WebSocket
    public function onOpen(Server $server, Request $request)
    {
       //  var_dump(app('swoole') === $server);// The same instance
        /**@var User $user */
         $user = Auth::user();
         $userId = $user ? $user->id : 0; // 0 means a guest user who is not logged in
         if (!$userId) {
             // Disconnect the connections of unlogged users
             $server->disconnect($request->fd);
             return;
         }
        $user_id = genUid($userId, $user->current_role);
        $role = $user['current_role'];
        $server->push($request->fd,"用户连接信息 : user_id:{$user_id}----{$userId}-----{$role} | fd:{$request->fd} 时间：" . date('Y-m-d H:i:s'));
        // 判断是否存在异地登录
        $isOnline = $this->isOnlineAll($user_id);
        // 若开启单点登录，则主动关闭之前登录的连接
        if ($isOnline) {
            // TODO 预留
        }
        // 绑定fd与用户关系
        $this->bindRelation($request->fd, $user_id);
//        $this->wsTable->set('uid:' . $uuid, ['value' => $request->fd]);// Bind map uid to fd
//        $this->wsTable->set('fd:' . $request->fd, ['value' => $uuid]);// Bind map fd to uid
        if (!$isOnline) {
            // 推送消息至队列
            $message = [
                'uuid'    => uniqid((strval(mt_rand(0, 1000)))),
                'event'   => SocketConstants::EVENT_ONLINE_STATUS,
                'data'    =>  [
                    'user_id' => $user_id,
                    'status'  => 1,
                    'notify'  => '好友上线通知...'
                ],

            ];

           // dispatch(new ChatQueue($message));
            $task = new ChatTask($message);
            $ret = Task::deliver($task);
        }

       // $server->push($request->fd, "Welcome to LaravelS #{$request->fd}");
    }
    public function onMessage(Server $server, Frame $frame)
    {
        // Broadcast
//        foreach ($this->wsTable as $key => $row) {
//            if (strpos($key, 'uid:') === 0 && $server->isEstablished($row['value'])) {
//                $content = sprintf('Broadcast: new message "%s" from #%d', $frame->data, $frame->fd);
//                $server->push($row['value'], $content);
//            }
//        }
        // 判断是否为心跳检测
        if ($frame->data == 'PING') return;

        $result = json_decode($frame->data, true);
        if (!isset(self::EVENTS[$result['event']])) return;
        $messageHandleService = Container::getInstance()->make(MessageHandleService::class);
        // 回调事件处理函数
        call_user_func_array([
            $messageHandleService,
            self::EVENTS[$result['event']]
        ], [$server, $frame, $result['data']]);
    }
    public function onClose(Server $server, $fd, $reactorId)
    {
//        $uid = $this->wsTable->get('fd:' . $fd);
//        if ($uid !== false) {
//            $this->wsTable->del('uid:' . $uid['value']); // Unbind uid map
//        }
//        $this->wsTable->del('fd:' . $fd);// Unbind fd map
//        $server->push($fd, "Goodbye #{$fd}");
        $user_id = $this->findFdUserId($fd);
        // 删除 fd 绑定关系
        $this->removeRelation($fd);
        // 判断是否存在异地登录
        $isOnline = $this->isOnlineAll($user_id);
        if (!$isOnline) {
            $message = [
                'uuid'    => uniqid((strval(mt_rand(0, 1000)))),
                'event'   => SocketConstants::EVENT_ONLINE_STATUS,
                'data'    =>  [
                    'user_id' =>  $user_id,
                    'status'  => 0,
                    'notify'  => '好友离线通知通知...'
                ],

            ];
            $task = new ChatTask($message);
            $ret = Task::deliver($task);
            $server->push($fd, "客户端FD:{$fd} 已关闭连接 ，用户ID为【{$user_id}】，关闭时间：" . date('Y-m-d H:i:s'));
        }
    }
}
