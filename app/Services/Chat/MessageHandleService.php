<?php

namespace App\Services\Chat;

use App\Constants\SocketConstants;
use App\Jobs\ChatQueue;
use App\Task\ChatTask;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Hyperf\Di\Annotation\Inject;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;
use App\Amqp\Producer\ChatMessageProducer;
use App\Models\Chat\ChatRecord;
use App\Cache\LastMessage;
use App\Cache\UnreadTalk;

class MessageHandleService  extends SocketClientService
{


    /**
     * 对话消息
     *
     * @param Response|Server $server
     * @param Frame           $frame
     * @param array|string    $data 解析后数据
     * @return void
     */
    public function onTalk($server, Frame $frame, $data)
    {
        $user_id = $this->findFdUserId($frame->fd);
        if ($user_id != $data['send_user']) {
            return;
        }
        // 验证消息类型 私聊|群聊
        $data['source_type'] = 1;
        if (!in_array($data['source_type'], [1, 2])) {
            return;
        }
        $result = ChatRecord::create([
            'source'     => $data['source_type'],
            'msg_type'   => 1,
            'user_id'    => $data['send_user'],
            'receive_id' => $data['receive_user'],
            'content'    => htmlspecialchars($data['text_message']),
            'created_at' => date('Y-m-d H:i:s'),
        ]);



        if (!$result) return;
        // 判断是否私聊
        if ($result->source == 1) {
            // 设置好友消息未读数
            UnreadTalk::getInstance()->increment($result->user_id, $result->receive_id);
        }

        // 缓存最后一条聊天消息
        LastMessage::getInstance()->save($result->source, $result->user_id, $result->receive_id, [
            'text'       => mb_substr($result->content, 0, 30),
            'created_at' => $result->created_at
        ]);

        // 推送消息至队列
        $message = [
            'event'   => 'event_talk',
            'data'    =>   [
                'sender'    => $data['send_user'],     // 发送者ID
                'receive'   =>$data['receive_user'],  // 接收者ID
                'source'    => intval($data['source_type']),   // 接收者类型[1:好友;2:群组;]
                'record_id' => $result->id,
                'uuid'    => uniqid((strval(mt_rand(0, 1000)))),
            ]

        ];
      //  dispatch(new ChatQueue($message));
        $task = new ChatTask($message);
         $task->delay(1);// delay 3 seconds to deliver task
        // $task->setTries(3); // When an error occurs, try 3 times in total
        $ret = Task::deliver($task);
    }

    /**
     * 键盘输入消息
     *
     * @param Response|Server $server
     * @param Frame           $frame
     * @param array|string    $data 解析后数据
     */
    public function onKeyboard($server, Frame $frame, $data)
    {
        push_amqp(new ChatMessageProducer('event_keyboard', [
            'send_user'    => $data['send_user'],     // 发送者ID
            'receive_user' => $data['receive_user'],  // 接收者ID
        ]));
    }
}
