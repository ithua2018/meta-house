<?php

namespace App\Task;
use App\Cache\Repository\LockRedis;
use App\Constants\SocketConstants;
use App\Models\Chat\ChatRecord;
use App\Models\Chat\ChatRecordsFile;
use App\Services\Chat\SocketClientService;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Log;

class ChatTask  extends  Task
{
    private $data;
    private $socketClientService;
    /**
     * 消息事件与回调事件绑定
     *
     * @var array
     */
    const EVENTS = [
        // 聊天消息事件
        SocketConstants::EVENT_TALK          => 'onConsumeTalk',

        // 键盘输入事件
        SocketConstants::EVENT_KEYBOARD      => 'onConsumeKeyboard',

        // 用户在线状态事件
        SocketConstants::EVENT_ONLINE_STATUS => 'onConsumeOnlineStatus',

        // 聊天消息推送事件
        SocketConstants::EVENT_REVOKE_TALK   => 'onConsumeRevokeTalk',

        // 好友申请相关事件
        SocketConstants::EVENT_FRIEND_APPLY  => 'onConsumeFriendApply'
    ];

    private $result;
    public function __construct($data)
    {
        $this->data = $data;
        $this->socketClientService = Container::getInstance()->make(SocketClientService::
        class);
    }
    // The logic of task handling, run in task process, CAN NOT deliver task
    public function handle()
    {

       $data = $this->data;
        console_debug($data);
        try{
            if (isset($data['event'])) {
                // [加锁]防止消息重复消费
//              $lockName = sprintf('ws-message:%s-%s', SERVER_RUN_ID, $data['uuid']);
//            if (!LockRedis::getInstance()->lock($lockName, 60)) {
//                return true;
//            }
             //   $this->server_run_id = $data['server_run_id'];
                // 调用对应事件绑定的回调方法
                return $this->{self::EVENTS[$data['event']]}($data, $this->data);
            }

        }catch (\Exception $e) {
            console_debug($e->getMessage().'---'.$e->getLine().'---'.$e->getFile());
        }
    }



    /**
     * 用户上线或下线消息
     *
     * @param array       $data 队列消息
     * @param AMQPMessage $message
     * @return string
     */
    public function onConsumeOnlineStatus(array $data): bool
    {

//        $friends = UsersFriend::getFriendIds($data['data']['user_id']);
//
//        $fds = [];
//        foreach ($friends as $friend_id) {
//            $fds = array_merge($fds, $this->socketClientService->findUserFds($friend_id));
//        }
//
//        $this->socketPushNotify(array_unique($fds), json_encode([SocketConstants::EVENT_ONLINE_STATUS, $data['data']]));

        return true;
    }

    /**
     * 对话聊天消息
     *
     * @param array       $data 队列消息
     * @param AMQPMessage $message
     * @return string
     */
    public function onConsumeTalk(array $data): bool
    {

        $fds = array_merge(
            $this->socketClientService->findUserFds($data['data']['sender']),
            $this->socketClientService->findUserFds($data['data']['receive'])
        );
        // 客户端ID去重
        if (!$fds = array_unique($fds))  return true;

        /** @var ChatRecord */
        $result = ChatRecord::leftJoin('users_information', 'users_information.uuid', '=', 'chat_records.user_id')
            ->where('chat_records.id', $data['data']['record_id'])
            ->first([
                'chat_records.id',
                'chat_records.source',
                'chat_records.msg_type',
                'chat_records.user_id',
                'chat_records.receive_id',
                'chat_records.content',
                'chat_records.is_revoke',
                'chat_records.created_at',
                'users_information.nick_name',
                'users_information.avatar',
            ]);
       // console_debug($result);
        if (!$result) return true;
        $file =  [];
        switch ($result->msg_type) {
            case 2:// 文件消息
                $file = ChatRecordsFile::where('record_id', $result->id)->first(['id', 'record_id', 'user_id', 'file_source', 'file_type', 'save_type', 'original_name', 'file_suffix', 'file_size', 'save_dir']);
                $file = $file ? $file->toArray() : [];
                $file && $file['file_url'] = get_media_url($file['save_dir']);
                break;
        }

        $notify = [
            'send_user'    => $data['data']['sender'],
            'receive_user' => $data['data']['receive'],
           // 'source_type'  => $data['data']['source'],
            'data'         => $this->formatTalkMessage([
                'id'           => $result->id,
                'msg_type'     => $result->msg_type,
              //  'source'       => $result->source,
                'avatar'       => $result->avatar,
                'nickname'     => $result->nickname,
                "user_id"      => $result->user_id,
                "receive_id"   => $result->receive_id,
                "created_at"   => $result->created_at,
                "content"      => $result->content,
                "file"         => $file,

            ])
        ];

        $this->socketPushNotify($fds, json_encode([SocketConstants::EVENT_TALK, $notify]));

        return true;
    }


    /**
     * WebSocket 消息推送
     *
     * @param $fds
     * @param $message
     */
    private function socketPushNotify($fds, $message)
    {

        $server = app('swoole');
        foreach ($fds as $fd) {
            $server->exist(intval($fd)) && $server->push(intval($fd), $message);
        }
    }

    /**
     * 格式化对话的消息体
     *
     * @param array $data 对话的消息
     * @return array
     */
    private function formatTalkMessage(array $data): array
    {
        $message = [
            "id"           => 0, // 消息记录ID
            "source"       => 1, // 消息来源[1:好友私信;2:群聊]
            "msg_type"     => 1, // 消息类型
            "user_id"      => 0, // 发送者用户ID
            "receive_id"   => 0, // 接收者ID[好友ID或群ID]
            "content"      => '',// 文本消息
            "is_revoke"    => 0, // 消息是否撤销

            // 发送消息人的信息
            "nickname"     => "",// 用户昵称
            "avatar"       => "",// 用户头像


            // 不同的消息类型
            "file"         => [],

            // 消息创建时间
            "created_at"   => "",
        ];

        return array_merge($message, array_intersect_key($data, $message));
    }

    // Optional, finish event, the logic of after task handling, run in worker process, CAN deliver task
    public function finish()
    {
        Log::info(__CLASS__ . ':finish start', [$this->result]);
      //  Task::deliver(new TestTask2('task2 data')); // Deliver the other task
    }
}
