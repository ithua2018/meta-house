<?php

namespace App\Http\Controllers\Mobile;

use App\Cache\LastMessage;
use App\Cache\UnreadTalk;
use App\Constants\ResponseCode;
use App\Http\Requests\Mobile\ChatRecordsRequest;
use App\Models\UsersChatList;
use App\Models\UsersInformation;
use App\Task\ChatTask;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Psr\Http\Message\ResponseInterface;
use App\Model\EmoticonDetail;
use App\Model\FileSplitUpload;
use App\Model\User;
//use App\Model\UsersFriend;
//use App\Model\Group\Group;
use App\Amqp\Producer\ChatMessageProducer;
use App\Constants\SocketConstants;
use League\Flysystem\Filesystem;
use App\Services\TalkService;

class TalkController extends AbstractApiController
{

    protected  TalkService  $talkService;

    public function  __construct( TalkService  $talkService)
    {
        $this->talkService = $talkService;

    }

    /**
     * 获取用户对话列表
     *
     * @return ResponseInterface
     */
    public function list()
    {

        $user_id = $this->uuid();
        if ($list = UnreadTalk::getInstance()->reads($user_id)) {
            $this->talkService->updateUnreadTalkList($user_id, $list);
        }
        return $this->success($this->talkService->talks($user_id));
    }

    /**
     * 新增对话列表
     *
     * @return ResponseInterface
     */
    public function create(Request $request)
    {
        $params = $request->only(['receive_id']);
        $this->validate($request, [
            'receive_id' => 'present|integer|min:0'
        ]);

        $user_id = $this->uuid();

        $result = UsersChatList::addItem($user_id, $params['receive_id'], 1);
        if (!$result) {
            return $this->fail(ResponseCode::CHAT_CREAT_CONVERSATION);
        }

        $data = [
            'id'          => $result['id'],
            'type'        => 1,
            'group_id'    => 0,
            'friend_id'   => $result['friend_id'],
            'is_top'      => 0,
            'msg_text'    => '',
            'not_disturb' => 0,
            'online'      => 1,
            'name'        => '',
            'remark_name' => '',
            'avatar'      => '',
            'unread_num'  => 0,
            'updated_at'  => date('Y-m-d H:i:s')
        ];
        /**@var  UsersInformation $userInfo*/
        $userInfo = UsersInformation::where('uuid', $user_id)->first(['nick_name', 'avatar']);

        $data['name']       = $userInfo->nick_name;
        $data['avatar']     = $userInfo->avatar;
        $data['unread_num'] = UnreadTalk::getInstance()->read($result['friend_id'], $user_id);

        $records = LastMessage::getInstance()->read(
            (int)$result['type'], $user_id,
           1
        );

        if ($records) {
            $data['msg_text']   = $records['text'];
            $data['updated_at'] = $records['created_at'];
        }
        return $this->success(['talkItem' => $data]);
    }

    /**
     * 删除对话列表
     * @RequestMapping(path="delete", methods="post")
     *
     * @return ResponseInterface
     */
    public function delete()
    {
        $params = $this->request->inputs(['list_id']);
        $this->validate($params, [
            'list_id' => 'required|integer|min:0'
        ]);

        return UsersChatList::delItem($this->uid(), $params['list_id'])
            ? $this->response->success([], '对话列表删除成功...')
            : $this->response->fail('对话列表删除失败！');
    }

    /**
     * 对话列表置顶
     * @RequestMapping(path="topping", methods="post")
     *
     * @return ResponseInterface
     */
    public function topping()
    {
        $params = $this->request->inputs(['list_id', 'type']);
        $this->validate($params, [
            'list_id' => 'required|integer|min:0',
            'type'    => 'required|in:1,2',
        ]);

        return UsersChatList::topItem($this->uid(), $params['list_id'], $params['type'] == 1)
            ? $this->response->success([], '对话列表置顶(或取消置顶)成功...')
            : $this->response->fail('对话列表置顶(或取消置顶)失败！');
    }

    /**
     * 设置消息免打扰状态
     * @RequestMapping(path="set-not-disturb", methods="post")
     *
     * @return ResponseInterface
     */
    public function setNotDisturb()
    {
        $params = $this->request->inputs(['receive_id', 'type', 'not_disturb']);
        $this->validate($params, [
            'receive_id'  => 'required|integer|min:0',
            'type'        => 'required|in:1,2',
            'not_disturb' => 'required|in:0,1',
        ]);

        $isTrue = UsersChatList::notDisturbItem($this->uid(), $params['receive_id'], $params['type'], $params['not_disturb']);

        return $isTrue
            ? $this->response->success([], '免打扰设置成功...')
            : $this->response->fail('免打扰设置失败！');
    }

    /**
     * 更新对话列表未读数
     * @RequestMapping(path="update-unread-num", methods="post")
     *
     * @return ResponseInterface
     */
    public function updateUnreadNum()
    {
        $params = $this->request->inputs(['receive', 'type']);
        $this->validate($params, [
            'receive' => 'required|integer|min:0',
            'type'    => 'required|integer|min:0'
        ]);

        // 设置好友消息未读数
        if ($params['type'] == 1) {
            UnreadTalk::getInstance()->reset((int)$params['receive'], $this->uid());
        }

        return $this->response->success();
    }

    /**
     * 撤回聊天对话消息
     * @RequestMapping(path="revoke-records", methods="post")
     *
     * @return ResponseInterface
     */
    public function revokeChatRecords()
    {
        $params = $this->request->inputs(['record_id']);
        $this->validate($params, [
            'record_id' => 'required|integer|min:0'
        ]);

        [$isTrue, $message,] = $this->talkService->revokeRecord($this->uid(), $params['record_id']);
        if ($isTrue) {
            push_amqp(new ChatMessageProducer(SocketConstants::EVENT_REVOKE_TALK, [
                'record_id' => $params['record_id']
            ]));
        }

        return $isTrue
            ? $this->response->success([], $message)
            : $this->response->fail($message);
    }

    /**
     * 删除聊天记录
     * @RequestMapping(path="remove-records", methods="post")
     *
     * @return ResponseInterface
     */
    public function removeChatRecords()
    {
        $params = $this->request->inputs(['source', 'record_id', 'receive_id']);
        $this->validate($params, [
            'source'     => 'required|in:1,2',// 消息来源[1:好友消息;2:群聊消息;]
            'record_id'  => 'required|ids',
            'receive_id' => 'required|integer|min:0'
        ]);

        $record_ids = explode(',', $params['record_id']);

        $isTrue = $this->talkService->removeRecords(
            $this->uid(),
            $params['source'],
            $params['receive_id'],
            $record_ids
        );

        return $isTrue
            ? $this->response->success([], '删除成功...')
            : $this->response->fail('删除失败！');
    }

    /**
     * 转发聊天记录(待优化)
     * @RequestMapping(path="forward-records", methods="post")
     *
     * @return ResponseInterface
     */
    public function forwardChatRecords()
    {
        $params = $this->request->inputs(['source', 'records_ids', 'receive_id', 'forward_mode', 'receive_user_ids', 'receive_group_ids']);
        $this->validate($params, [
            // 消息来源[1:好友消息;2:群聊消息;]
            'source'       => 'required|in:1,2',
            // 聊天记录ID，多个逗号拼接
            'records_ids'  => 'required',
            // 接收者ID（好友ID或者群聊ID）
            'receive_id'   => 'required|integer|min:0',
            // 转发方方式[1:逐条转发;2:合并转发;]
            'forward_mode' => 'required|in:1,2',
            // 转发的好友的ID
            //'receive_user_ids' => 'array',
            // 转发的群聊ID
            //'receive_group_ids' => 'array',
        ]);

        $receive_user_ids = $receive_group_ids = [];
        if (isset($params['receive_user_ids']) && !empty($params['receive_user_ids'])) {
            $receive_user_ids = array_map(function ($friend_id) {
                return ['source' => 1, 'id' => $friend_id];
            }, $params['receive_user_ids']);
        }

        if (isset($params['receive_group_ids']) && !empty($params['receive_group_ids'])) {
            $receive_group_ids = array_map(function ($group_id) {
                return ['source' => 2, 'id' => $group_id];
            }, $params['receive_group_ids']);
        }

        $items = array_merge($receive_user_ids, $receive_group_ids);

        $user_id = $this->uid();
        if ($params['forward_mode'] == 1) {// 单条转发
            $ids = $this->talkService->forwardRecords($user_id, $params['receive_id'], $params['records_ids']);
        } else {// 合并转发
            $ids = $this->talkService->mergeForwardRecords($user_id, $params['receive_id'], $params['source'], $params['records_ids'], $items);
        }

        if (!$ids) {
            return $this->response->fail('转发失败！');
        }

        if ($receive_user_ids) {
            foreach ($receive_user_ids as $v) {
                UnreadTalk::getInstance()->increment($user_id, (int)$v['id']);
            }
        }

        // 消息推送队列
        foreach ($ids as $value) {
            push_amqp(new ChatMessageProducer(SocketConstants::EVENT_TALK, [
                'sender'    => $user_id,                      // 发送者ID
                'receive'   => intval($value['receive_id']),  // 接收者ID
                'source'    => intval($value['source']),      // 接收者类型 1:好友;2:群组
                'record_id' => $value['record_id']
            ]));
        }

        return $this->response->success([], '转发成功...');
    }

    /**
     * 获取对话面板中的聊天记录
     * @RequestMapping(path="records", methods="get")
     *
     * @return ResponseInterface
     */
    public function getChatRecords(ChatRecordsRequest  $request)
    {
        $params = $request->only(['record_id',  'receive_id']);
        $user_id = $this->uuid();
        $limit   = 30;
        $params['source'] = 1;
        $result = $this->talkService->getChatRecords(
            $user_id,
            $params['receive_id'],
            $params['source'],
            $params['record_id'],
            $limit
        );

        return $this->success([
            'rows'      => $result,
            'record_id' => $result ? end($result)['id'] : 0,
            'limit'     => $limit
        ]);
    }

    /**
     * 获取转发记录详情
     * @RequestMapping(path="get-forward-records", methods="get")
     *
     * @return ResponseInterface
     */
    public function getForwardRecords()
    {
        $params = $this->request->inputs(['records_id']);
        $this->validate($params, [
            'records_id' => 'required|integer|min:0'
        ]);

        $rows = $this->talkService->getForwardRecords($this->uid(), $params['records_id']);

        return $this->response->success(['rows' => $rows]);
    }

    /**
     * 查询聊天记录
     * @RequestMapping(path="find-chat-records", methods="get")
     *
     * @return ResponseInterface
     */
    public function findChatRecords()
    {
        $params = $this->request->inputs(['record_id', 'source', 'receive_id', 'msg_type']);
        $this->validate($params, [
            'source'     => 'required|in:1,2',// 消息来源[1:好友消息;2:群聊消息;]
            'record_id'  => 'required|integer|min:0',
            'receive_id' => 'required|integer|min:1',
            'msg_type'   => 'required|in:0,1,2,3,4,5,6',
        ]);

        $user_id = $this->uid();
        $limit   = 30;

        // 判断是否属于群成员
        if ($params['source'] == 2 && Group::isMember($params['receive_id'], $user_id) == false) {
            return $this->response->success([
                'rows'      => [],
                'record_id' => 0,
                'limit'     => $limit
            ], '非群聊成员不能查看群聊信息！');
        }

        if (in_array($params['msg_type'], [1, 2, 4, 5])) {
            $msg_type = [$params['msg_type']];
        } else {
            $msg_type = [1, 2, 4, 5];
        }

        $result = $this->talkService->getChatRecords(
            $user_id,
            $params['receive_id'],
            $params['source'],
            $params['record_id'],
            $limit,
            $msg_type
        );

        return $this->response->success([
            'rows'      => $result,
            'record_id' => $result ? end($result)['id'] : 0,
            'limit'     => $limit
        ]);
    }

    /**
     * 搜索聊天记录（待开发）
     * @RequestMapping(path="search-chat-records", methods="get")
     *
     * @return ResponseInterface
     */
    public function searchChatRecords()
    {

    }

    /**
     * 获取聊天记录上下文数据（待开发）
     * @RequestMapping(path="get-records-context", methods="get")
     *
     * @return ResponseInterface
     */
    public function getRecordsContext()
    {

    }

    /**
     * 上传聊天对话图片（待优化）
     * @RequestMapping(path="send-image", methods="post")
     *
     * @param Filesystem $filesystem
     * @return ResponseInterface
     */
    public function sendImage(Request $request)
    {
        $params = $request->only(['receive_id']);
        $params['source'] = 1;
        $validator = Validator::make($request->all(), [
            'receive_id' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return  $this->fail(ResponseCode::PARAM_ILLEGAL);
        }
        $file = $request->file('img');
        if (!$file || !$file->isValid()) {
            return $this->fail(ResponseCode::PARAM_ILLEGAL);
        }

        $ext = $file->extension();
        if (!in_array($ext, ['jpg', 'png', 'jpeg', 'gif', 'webp'])) {
            return $this->fail(ResponseCode::FAIL, '图片格式错误，目前仅支持jpg、png、jpeg、gif和webp');
        }
        try {
            $path = 'image/talks/' . date('Ymd') . '/' . create_image_name($ext, getimagesize($file->getRealPath()));
            $disk = Storage::disk('public');
            $disk->put($path,  file_get_contents($file->getRealPath()));
        } catch (\Exception $e) {
            return $this->response->fail();
        }
        $user_id = $this->uuid();
        // 创建图片消息记录
        $record_id = $this->talkService->createImgMessage([
           'source'     => 1,
            'msg_type'   => 2,
            'user_id'    => $user_id,
            'receive_id' => $params['receive_id'],
        ], [
            'user_id'       => $user_id,
            'file_type'     => 1,
            'file_suffix'   => $ext,
            'file_size'     => $file->getSize(),
            'save_dir'      => $path,
            'original_name' => $file->getClientOriginalName(),       //原始文件名
        ]);

        if (!$record_id) {
            return $this->response->fail('图片上传失败！');
        }
        // 消息推送队列
        $message = [
            'event'   => SocketConstants::EVENT_TALK,
            'data'    =>   [
                'sender'    => $user_id,                       // 发送者ID
                'receive'   => intval($params['receive_id']),  // 接收者ID
                'source'    => 1,      // 接收者类型[1:好友;2:群组;]
                'record_id' => $record_id
            ]
        ];
        $task = new ChatTask($message);
        $task->delay(1);// delay 3 seconds to deliver task
        $ret = Task::deliver($task);
        LastMessage::getInstance()->save((int)$params['source'], $user_id, (int)$params['receive_id'], [
            'text'       => '[图片消息]',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $this->success();
    }

    /**
     * 发送代码块消息
     * @RequestMapping(path="send-code-block", methods="post")
     *
     * @return ResponseInterface
     */
    public function sendCodeBlock()
    {
        $params = $this->request->inputs(['source', 'receive_id', 'lang', 'code']);
        $this->validate($params, [
            'source'     => 'required|in:1,2',// 消息来源[1:好友消息;2:群聊消息;]
            'receive_id' => 'required|integer|min:1',
            'lang'       => 'required',
            'code'       => 'required'
        ]);

        $user_id   = $this->uid();
        $record_id = $this->talkService->createCodeMessage([
            'source'     => $params['source'],
            'msg_type'   => 5,
            'user_id'    => $user_id,
            'receive_id' => $params['receive_id'],
        ], [
            'user_id'   => $user_id,
            'code_lang' => $params['lang'],
            'code'      => $params['code']
        ]);

        if (!$record_id) {
            return $this->response->fail('消息发送失败！');
        }

        // 消息推送队列
        push_amqp(new ChatMessageProducer(SocketConstants::EVENT_TALK, [
            'sender'    => $user_id,                       // 发送者ID
            'receive'   => intval($params['receive_id']),  // 接收者ID
            'source'    => intval($params['source']),      // 接收者类型[1:好友;2:群组;]
            'record_id' => $record_id
        ]));

        LastMessage::getInstance()->save((int)$params['source'], $user_id, (int)$params['receive_id'], [
            'text'       => '[代码消息]',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $this->response->success();
    }

    /**
     * 发送文件消息
     * @RequestMapping(path="send-file", methods="post")
     *
     * @param Filesystem $filesystem
     * @return ResponseInterface
     */
    public function sendFile(Filesystem $filesystem)
    {
        $params = $this->request->inputs(['hash_name', 'receive_id', 'source']);
        $this->validate($params, [
            'source'     => 'required|in:1,2',// 消息来源[1:好友消息;2:群聊消息;]
            'receive_id' => 'required|integer|min:1',
            'hash_name'  => 'required',
        ]);

        $user_id = $this->uid();

        $file = FileSplitUpload::where('user_id', $user_id)->where('hash_name', $params['hash_name'])->where('file_type', 1)->first();
        if (!$file || empty($file->save_dir)) {
            return $this->response->fail('文件不存在...');
        }

        $save_dir = "files/talks/" . date('Ymd') . '/' . create_random_filename($file->file_ext);

        try {
            $filesystem->copy($file->save_dir, $save_dir);
        } catch (\Exception $e) {
            return $this->response->fail('文件不存在...');
        }

        $record_id = $this->talkService->createFileMessage([
            'source'     => $params['source'],
            'msg_type'   => 2,
            'user_id'    => $user_id,
            'receive_id' => $params['receive_id']
        ], [
            'user_id'       => $user_id,
            'file_source'   => 1,
            'file_type'     => 4,
            'original_name' => $file->original_name,
            'file_suffix'   => $file->file_ext,
            'file_size'     => $file->file_size,
            'save_dir'      => $save_dir,
        ]);

        if (!$record_id) {
            return $this->response->fail('表情发送失败！');
        }

        // 消息推送队列
        push_amqp(new ChatMessageProducer(SocketConstants::EVENT_TALK, [
            'sender'    => $user_id,                       // 发送者ID
            'receive'   => intval($params['receive_id']),  // 接收者ID
            'source'    => intval($params['source']),      // 接收者类型[1:好友;2:群组;]
            'record_id' => $record_id
        ]));

        LastMessage::getInstance()->save((int)$params['source'], $user_id, (int)$params['receive_id'], [
            'text'       => '[文件消息]',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $this->response->success();
    }

    /**
     * 发送表情包消息
     * @RequestMapping(path="send-emoticon", methods="post")
     *
     * @return ResponseInterface
     */
    public function sendEmoticon()
    {
        $params = $this->request->inputs(['source', 'receive_id', 'emoticon_id']);
        $this->validate($params, [
            'source'      => 'required|in:1,2',// 消息来源[1:好友消息;2:群聊消息;]
            'receive_id'  => 'required|integer|min:1',
            'emoticon_id' => 'required|integer|min:1',
        ]);

        $user_id  = $this->uid();
        $emoticon = EmoticonDetail::where('id', $params['emoticon_id'])->where('user_id', $user_id)->first([
            'url', 'file_suffix', 'file_size'
        ]);

        if (!$emoticon) {
            return $this->response->fail('表情不存在！');
        }

        $record_id = $this->talkService->createEmoticonMessage([
            'source'     => $params['source'],
            'msg_type'   => 2,
            'user_id'    => $user_id,
            'receive_id' => $params['receive_id'],
        ], [
            'user_id'       => $user_id,
            'file_type'     => 1,
            'file_suffix'   => $emoticon->file_suffix,
            'file_size'     => $emoticon->file_size,
            'save_dir'      => $emoticon->url,
            'original_name' => '表情',
        ]);

        if (!$record_id) {
            return $this->response->fail('表情发送失败！');
        }

        // 消息推送队列
        push_amqp(new ChatMessageProducer(SocketConstants::EVENT_TALK, [
            'sender'    => $user_id,                       // 发送者ID
            'receive'   => intval($params['receive_id']),  // 接收者ID
            'source'    => intval($params['source']),      // 接收者类型[1:好友;2:群组;]
            'record_id' => $record_id
        ]));

        LastMessage::getInstance()->save((int)$params['source'], $user_id, (int)$params['receive_id'], [
            'text'       => '[表情包消息]',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $this->response->success();
    }
}
