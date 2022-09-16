<?php

namespace App\Services;

use App\Cache\ServerRunID;
use App\Services\Chat\SocketClientService;
use Exception;
use App\Models\User;
use App\Models\UsersChatList;
use App\Models\UsersFriend;
use App\Models\Chat\ChatRecord;
use App\Models\Chat\ChatRecordsCode;
use App\Models\Chat\ChatRecordsFile;
use App\Cache\LastMessage;
use App\Cache\UnreadTalk;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\DB;

class TalkService extends BaseService
{


    /**
     * 获取用户的聊天列表
     *
     * @param string $user_id 用户ID
     * @return array
     */
    public function talks(string $user_id)
    {
        $filed = [
            'list.id', 'list.type', 'list.friend_id', 'list.group_id', 'list.updated_at', 'list.not_disturb', 'list.is_top',
            'users.avatar', 'users.nick_name'
        ];

        $rows =  DB::table('users_chat_list as list')
            ->leftJoin('users_information as users', 'users.uuid', '=', 'list.friend_id')
            ->where('list.uid', $user_id)
            ->where('list.status', 1)
            ->orderBy('updated_at', 'desc')
            ->get($filed)
            ->toArray();
        if (!$rows) return [];
        $socketFDService = Container::getInstance()->make(SocketClientService::class);
        $runIdAll        = ServerRunID::getInstance()->getServerRunIdAll();

        return array_map(function ($item) use ($user_id, $socketFDService, $runIdAll) {
            $item = collect($item)->toArray();
            $data['id']          = $item['id'];
            $data['type']        = $item['type'];
            $data['friend_id']   = $item['friend_id'];
            $data['name']        = ''; // 对方昵称/群名称
            $data['avatar']      = ''; // 默认头像
            $data['remark_name'] = ''; // 好友备注
            $data['unread_num']  = 0;  // 未读消息数量
            $data['msg_text']    = '......';
            $data['updated_at']  = $item['updated_at'];
            $data['online']      = 0;
            $data['is_top']      = $item['is_top'];
            $data['not_disturb'] = $item['not_disturb'];
            $data['name']       = $item['nick_name'] ?:$item['friend_id'];
            $data['avatar']     = $item['avatar'] ?: config('rent.image_url').config('rent.default_user_avatar');
            $data['unread_num'] = UnreadTalk::getInstance()->read((int)$item['friend_id'], $user_id);
            $data['online']     = $socketFDService->isOnlineAll($item['friend_id'], $runIdAll);
            $records = LastMessage::getInstance()->read((int)$item['type'], $user_id, $item['friend_id']);
            if ($records) {
                $data['msg_text']   = $records['text'];
                $data['updated_at'] = $records['created_at'];
            } else {
                $data['updated_at'] = '2020-01-01 00:00:00';
            }

            return $data;
        }, $rows);
    }

    /**
     * 同步未读的消息到数据库中
     *
     * @param int $user_id 用户ID
     * @param     $data
     */
    public function updateUnreadTalkList(int $user_id, $data)
    {
        foreach ($data as $friend_id => $num) {
            UsersChatList::updateOrCreate([
                'uid'       => $user_id,
                'friend_id' => intval($friend_id),
                'type'      => 1
            ], [
                'status'     => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * 处理聊天记录信息
     *
     * @param array $rows 聊天记录
     * @return array
     */
    public function handleChatRecords(array $rows)
    {
        if (empty($rows)) return [];

        $files = $codes = $forwards = $invites = [];
        foreach ($rows as $value) {
            switch ($value['msg_type']) {
                case 2:
                    $files[] = $value['id'];
                    break;
                case 3:
                    $invites[] = $value['id'];
                    break;
                case 4:
                    $forwards[] = $value['id'];
                    break;
                case 5:
                    $codes[] = $value['id'];
                    break;
            }
        }

        // 查询聊天文件信息
        if ($files) {
            $files = ChatRecordsFile::whereIn('record_id', $files)->get(['id', 'record_id', 'user_id', 'file_source', 'file_type', 'save_type', 'original_name', 'file_suffix', 'file_size', 'save_dir'])->keyBy('record_id')->toArray();
        }
        foreach ($rows as $k => $row) {
            $rows[$k]['file']       = [];
            switch ($row['msg_type']) {
                case 2:// 文件消息
                    $rows[$k]['file'] = $files[$row['id']] ?? [];
                    if ($rows[$k]['file']) {
                        $rows[$k]['file']['file_url'] = get_media_url($rows[$k]['file']['save_dir']);
                    }
                    break;
            }
        }

        unset($files, $codes, $forwards, $invites);
        return $rows;
    }

    /**
     * 查询对话页面的历史聊天记录
     *
     * @param int   $user_id    用户ID
     * @param int   $receive_id 接收者ID（好友ID或群ID）
     * @param int   $source     消息来源  1:好友消息 2:群聊消息
     * @param int   $record_id  上一次查询的聊天记录ID
     * @param int   $limit      查询数据长度
     * @param array $msg_type   消息类型
     * @return array
     */
    public function getChatRecords(int $user_id, int $receive_id, int $source, int $record_id, $limit = 30, $msg_type = [])
    {
        $fields = [
            'chat_records.id',
            'chat_records.source',
            'chat_records.msg_type',
            'chat_records.user_id',
            'chat_records.receive_id',
            'chat_records.content',
            'chat_records.is_revoke',
            'chat_records.created_at',
            'users_information.nick_name',
            'users_information.avatar as avatar',
        ];
        $rowsSqlObj = ChatRecord::select($fields);
        $rowsSqlObj->leftJoin('users_information', 'users_information.uuid', '=', 'chat_records.user_id');
        if ($record_id) {
            $rowsSqlObj->where('chat_records.id', '<', $record_id);
        }
            $rowsSqlObj->where(function ($query) use ($user_id, $receive_id) {
                $query->where([
                    ['chat_records.user_id', '=', $user_id],
                    ['chat_records.receive_id', '=', $receive_id]
                ])->orWhere([
                    ['chat_records.user_id', '=', $receive_id],
                    ['chat_records.receive_id', '=', $user_id]
                ]);
            });

        if ($msg_type) {
            $rowsSqlObj->whereIn('chat_records.msg_type', $msg_type);
        }

        //过滤用户删除记录
        $rowsSqlObj->whereNotExists(function ($query) use ($user_id) {
           // $prefix = config('databases.default.prefix');
            $query->select(Db::raw(1))->from('chat_records_delete');
         //   $query->whereRaw("{$prefix}chat_records_delete.record_id = {$prefix}chat_records.id and {$prefix}chat_records_delete.user_id = {$user_id}");
            $query->whereRaw("user_id = {$user_id}");
            $query->limit(1);
        });
        $rows = $rowsSqlObj->orderBy('chat_records.id', 'desc')->limit($limit)->get()->toArray();
        return $this->handleChatRecords($rows);
    }

    /**
     * 获取转发会话记录信息
     *
     * @param int $user_id   用户ID
     * @param int $record_id 聊天记录ID
     * @return array
     */
    public function getForwardRecords(int $user_id, int $record_id)
    {
        $result = ChatRecord::where('id', $record_id)->first([
            'id', 'source', 'msg_type', 'user_id', 'receive_id', 'content', 'is_revoke', 'created_at'
        ]);

        // 判断是否有权限查看
        if ($result->source == 1 && ($result->user_id != $user_id && $result->receive_id != $user_id)) {
            return [];
        } else if ($result->source == 2 && !Group::isMember($result->receive_id, $user_id)) {
            return [];
        }

        $forward = ChatRecordsForward::where('record_id', $record_id)->first();

        $fields = [
            'chat_records.id',
            'chat_records.source',
            'chat_records.msg_type',
            'chat_records.user_id',
            'chat_records.receive_id',
            'chat_records.content',
            'chat_records.is_revoke',
            'chat_records.created_at',
            'users.nickname',
            'users.avatar as avatar',
        ];

        $rowsSqlObj = ChatRecord::select($fields);
        $rowsSqlObj->leftJoin('users', 'users.id', '=', 'chat_records.user_id');
        $rowsSqlObj->whereIn('chat_records.id', explode(',', $forward->records_id));

        return $this->handleChatRecords($rowsSqlObj->get()->toArray());
    }

    /**
     * 批量删除聊天消息
     *
     * @param int   $user_id    用户ID
     * @param int   $source     消息来源  1:好友消息 2:群聊消息
     * @param int   $receive_id 好友ID或者群聊ID
     * @param array $record_ids 聊天记录ID
     * @return bool
     */
    public function removeRecords(int $user_id, int $source, int $receive_id, array $record_ids)
    {
        if ($source == 1) {// 私聊信息
            $ids = ChatRecord::whereIn('id', $record_ids)->where(function ($query) use ($user_id, $receive_id) {
                $query->where([['user_id', '=', $user_id], ['receive_id', '=', $receive_id]])->orWhere([['user_id', '=', $receive_id], ['receive_id', '=', $user_id]]);
            })->where('source', 1)->pluck('id');
        } else {// 群聊信息
            $ids = ChatRecord::whereIn('id', $record_ids)->where('source', 2)->pluck('id');
        }

        // 判断要删除的消息在数据库中是否存在
        if (count($ids) != count($record_ids)) {
            return false;
        }

        // 判读是否属于群消息并且判断是否是群成员
        if ($source == 2 && !Group::isMember($receive_id, $user_id)) {
            return false;
        }

        $data = array_map(function ($record_id) use ($user_id) {
            return [
                'record_id'  => $record_id,
                'user_id'    => $user_id,
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }, $ids->toArray());

        return Db::table('chat_records_delete')->insert($data);
    }

    /**
     * 撤回单条聊天消息
     *
     * @param int $user_id   用户ID
     * @param int $record_id 聊天记录ID
     * @return array
     */
    public function revokeRecord(int $user_id, int $record_id)
    {
        $result = ChatRecord::where('id', $record_id)->first(['id', 'source', 'user_id', 'receive_id', 'created_at']);
        if (!$result) return [false, '消息记录不存在'];

        // 判断是否在两分钟之内撤回消息，超过2分钟不能撤回消息
        if ((time() - strtotime($result->created_at) > 120)) {
            return [false, '已超过有效的撤回时间', []];
        }

        if ($result->source == 1) {
            if ($result->user_id != $user_id && $result->receive_id != $user_id) {
                return [false, '非法操作', []];
            }
        } else if ($result->source == 2) {
            if (!Group::isMember($result->receive_id, $user_id)) {
                return [false, '非法操作', []];
            }
        }

        $result->is_revoke = 1;
        $result->save();

        return [true, '消息已撤回', $result->toArray()];
    }

    /**
     * 转发消息（单条转发）
     *
     * @param int   $user_id     转发的用户ID
     * @param int   $record_id   转发消息的记录ID
     * @param array $receive_ids 接受者数组  例如:[['source' => 1,'id' => 3045],['source' => 1,'id' => 3046],['source' =>
     *                           1,'id' => 1658]] 二维数组
     * @return array
     */
    public function forwardRecords(int $user_id, int $record_id, array $receive_ids)
    {
        $result = ChatRecord::where('id', $record_id)->whereIn('msg_type', [1, 2, 5])->first();
        if (!$result) {
            return [];
        }

        // 根据消息类型判断用户是否有转发权限
        if ($result->source == 1) {
            if ($result->user_id != $user_id && $result->receive_id != $user_id) {
                return [];
            }
        } else if ($result->source == 2) {
            if (!Group::isMember($result->receive_id, $user_id)) {
                return [];
            }
        }

        $fileInfo  = null;
        $codeBlock = null;
        if ($result->msg_type == 2) {
            $fileInfo = ChatRecordsFile::where('record_id', $record_id)->first();
        } else if ($result->msg_type == 5) {
            $codeBlock = ChatRecordsCode::where('record_id', $record_id)->first();
        }

        $insRecordIds = [];
        Db::beginTransaction();
        try {
            foreach ($receive_ids as $item) {
                $res = ChatRecord::create([
                    'source'     => $item['source'],
                    'msg_type'   => $result->msg_type,
                    'user_id'    => $user_id,
                    'receive_id' => $item['id'],
                    'content'    => $result->content,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                if (!$res) {
                    throw new Exception('插入消息记录失败');
                }

                $insRecordIds[] = $res->id;

                if ($result->msg_type == 2) {
                    if (!ChatRecordsFile::create([
                        'record_id'     => $res->id,
                        'user_id'       => $fileInfo->user_id,
                        'file_source'   => $fileInfo->file_source,
                        'file_type'     => $fileInfo->file_type,
                        'save_type'     => $fileInfo->save_type,
                        'original_name' => $fileInfo->original_name,
                        'file_suffix'   => $fileInfo->file_suffix,
                        'file_size'     => $fileInfo->file_size,
                        'save_dir'      => $fileInfo->save_dir,
                        'created_at'    => date('Y-m-d H:i:s')
                    ])) {
                        throw new Exception('插入文件消息记录失败');
                    }
                } else if ($result->msg_type == 5) {
                    if (!ChatRecordsCode::create([
                        'record_id'  => $res->id,
                        'user_id'    => $user_id,
                        'code_lang'  => $codeBlock->code_lang,
                        'code'       => $codeBlock->code,
                        'created_at' => date('Y-m-d H:i:s')
                    ])) {
                        throw new Exception('插入代码消息记录失败');
                    }
                }
            }

            Db::commit();
        } catch (Exception $e) {
            Db::rollBack();
            return [];
        }

        return $insRecordIds;
    }

    /**
     * 转发消息（多条合并转发）
     *
     * @param int   $user_id     转发的用户ID
     * @param int   $receive_id  当前转发消息的所属者(好友ID或者群聊ID)
     * @param int   $source      消息来源  1:好友消息 2:群聊消息
     * @param array $records_ids 转发消息的记录ID
     * @param array $receive_ids 接受者数组  例如:[['source' => 1,'id' => 3045],['source' => 1,'id' => 3046],['source' =>
     *                           1,'id' => 1658]] 二维数组
     * @return array|bool
     */
    public function mergeForwardRecords(int $user_id, int $receive_id, int $source, array $records_ids, array $receive_ids)
    {
        // 支持转发的消息类型
        $msg_type = [1, 2, 5];

        $sqlObj = ChatRecord::whereIn('id', $records_ids);

        // 验证是否有权限转发
        if ($source == 2) {// 群聊消息
            // 判断是否是群聊成员
            if (!Group::isMember($receive_id, $user_id)) {
                return false;
            }

            $sqlObj = $sqlObj->where('receive_id', $receive_id)->whereIn('msg_type', $msg_type)->where('source', 2)->where('is_revoke', 0);
        } else {// 私聊消息
            // 判断是否存在好友关系
            if (!UsersFriend::isFriend($user_id, $receive_id)) {
                return [];
            }

            $sqlObj = $sqlObj->where(function ($query) use ($user_id, $receive_id) {
                $query->where([
                    ['user_id', '=', $user_id],
                    ['receive_id', '=', $receive_id]
                ])->orWhere([
                    ['user_id', '=', $receive_id],
                    ['receive_id', '=', $user_id]
                ]);
            })->whereIn('msg_type', $msg_type)->where('source', 1)->where('is_revoke', 0);
        }

        $result = $sqlObj->get();

        // 判断消息记录是否存在
        if (count($result) != count($records_ids)) {
            return [];
        }

        $rows = ChatRecord::leftJoin('users', 'users.id', '=', 'chat_records.user_id')
            ->whereIn('chat_records.id', array_slice($records_ids, 0, 3))
            ->get(['chat_records.msg_type', 'chat_records.content', 'users.nickname']);

        $jsonText = [];
        foreach ($rows as $row) {
            switch ($row->msg_type) {
                case 1:
                    $jsonText[] = [
                        'nickname' => $row->nickname,
                        'text'     => mb_substr(str_replace(PHP_EOL, "", $row->content), 0, 30)
                    ];
                    break;
                case 2:
                    $jsonText[] = [
                        'nickname' => $row->nickname,
                        'text'     => '【文件消息】'
                    ];
                    break;
                case 3:
                    $jsonText[] = [
                        'nickname' => $row->nickname,
                        'text'     => '【代码消息】'
                    ];
                    break;
            }
        }

        $insRecordIds = [];
        Db::beginTransaction();
        try {
            foreach ($receive_ids as $item) {
                $res = ChatRecord::create([
                    'source'     => $item['source'],
                    'msg_type'   => 4,
                    'user_id'    => $user_id,
                    'receive_id' => $item['id'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                if (!$res) {
                    throw new Exception('插入消息失败');
                }

                $insRecordIds[] = [
                    'record_id'  => $res->id,
                    'receive_id' => $item['id'],
                    'source'     => $item['source']
                ];

                if (!ChatRecordsForward::create([
                    'record_id'  => $res->id,
                    'user_id'    => $user_id,
                    'records_id' => implode(',', $records_ids),
                    'text'       => json_encode($jsonText),
                    'created_at' => date('Y-m-d H:i:s'),
                ])) {
                    throw new Exception('插入转发消息失败');
                }
            }

            Db::commit();
        } catch (Exception $e) {
            Db::rollBack();
            return [];
        }

        return $insRecordIds;
    }

    /**
     * 关键词搜索聊天记录
     *
     * @param int   $user_id    用户ID
     * @param int   $receive_id 接收者ID(用户ID或群聊接收ID)
     * @param int   $source     聊天来源（1:私信 2:群聊）
     * @param int   $page       当前查询分页
     * @param int   $page_size  分页大小
     * @param array $params     查询参数
     * @return array
     */
    public function searchRecords(int $user_id, int $receive_id, int $source, int $page, int $page_size, array $params)
    {
        $fields = [
            'chat_records.id',
            'chat_records.source',
            'chat_records.msg_type',
            'chat_records.user_id',
            'chat_records.receive_id',
            'chat_records.content',
            'chat_records.is_revoke',
            'chat_records.created_at',

            'users.nickname',
            'users.avatar as avatar',
        ];

        $rowsSqlObj = ChatRecord::select($fields)->leftJoin('users', 'users.id', '=', 'chat_records.user_id');
        if ($source == 1) {
            $rowsSqlObj->where(function ($query) use ($user_id, $receive_id) {
                $query->where([
                    ['chat_records.user_id', '=', $user_id],
                    ['chat_records.receive_id', '=', $receive_id]
                ])->orWhere([
                    ['chat_records.user_id', '=', $receive_id],
                    ['chat_records.receive_id', '=', $user_id]
                ]);
            });
        } else {
            $rowsSqlObj->where('chat_records.receive_id', $receive_id);
            $rowsSqlObj->where('chat_records.source', $source);
        }

        if (isset($params['keywords'])) {
            $rowsSqlObj->where('chat_records.content', 'like', "%{$params['keywords']}%");
        }

        if (isset($params['date'])) {
            $rowsSqlObj->whereDate('chat_records.created_at', $params['date']);
        }

        $count = $rowsSqlObj->count();
        if ($count == 0) {
            return $this->getPagingRows([], 0, $page, $page_size);
        }

        $rows = $rowsSqlObj->orderBy('chat_records.id', 'desc')->forPage($page, $page_size)->get()->toArray();
        return $this->getPagingRows($this->handleChatRecords($rows), $count, $page, $page_size);
    }

    /**
     * 创建图片消息
     *
     * @param $message
     * @param $fileInfo
     * @return bool|int
     */
    public function createImgMessage($message, $fileInfo)
    {
        Db::beginTransaction();
        try {
            $message['created_at'] = date('Y-m-d H:i:s');
            $insert                = ChatRecord::create($message);

            if (!$insert) {
                throw new Exception('插入聊天记录失败...');
            }

            $fileInfo['record_id']  = $insert->id;
            $fileInfo['created_at'] = date('Y-m-d H:i:s');
            if (!ChatRecordsFile::create($fileInfo)) {
                throw new Exception('插入聊天记录(文件消息)失败...');
            }

            Db::commit();
        } catch (Exception $e) {
            Db::rollBack();
            return false;
        }

        return $insert->id;
    }

    /**
     * 创建代码块消息
     *
     * @param array $message
     * @param array $codeBlock
     * @return bool|int
     */
    public function createCodeMessage(array $message, array $codeBlock)
    {
        Db::beginTransaction();
        try {
            $message['created_at'] = date('Y-m-d H:i:s');
            $insert                = ChatRecord::create($message);
            if (!$insert) {
                throw new Exception('插入聊天记录失败...');
            }

            $codeBlock['record_id']  = $insert->id;
            $codeBlock['created_at'] = date('Y-m-d H:i:s');
            if (!ChatRecordsCode::create($codeBlock)) {
                throw new Exception('插入聊天记录(代码消息)失败...');
            }

            Db::commit();
        } catch (Exception $e) {
            Db::rollBack();
            return false;
        }

        return $insert->id;
    }

    /**
     * 创建代码块消息
     *
     * @param array $message
     * @param array $emoticon
     * @return bool|int
     */
    public function createEmoticonMessage(array $message, array $emoticon)
    {
        Db::beginTransaction();
        try {
            $message['created_at'] = date('Y-m-d H:i:s');
            $insert                = ChatRecord::create($message);
            if (!$insert) {
                throw new Exception('插入聊天记录失败...');
            }

            $emoticon['record_id']  = $insert->id;
            $emoticon['created_at'] = date('Y-m-d H:i:s');
            if (!ChatRecordsFile::create($emoticon)) {
                throw new Exception('插入聊天记录(代码消息)失败...');
            }

            Db::commit();
        } catch (Exception $e) {
            Db::rollBack();
            return false;
        }

        return $insert->id;
    }

    /**
     * 创建代码块消息
     *
     * @param array $message
     * @param array $emoticon
     * @return bool|int
     */
    public function createFileMessage(array $message, array $emoticon)
    {
        Db::beginTransaction();
        try {
            $message['created_at'] = date('Y-m-d H:i:s');
            $insert                = ChatRecord::create($message);
            if (!$insert) {
                throw new Exception('插入聊天记录失败...');
            }

            $emoticon['record_id']  = $insert->id;
            $emoticon['created_at'] = date('Y-m-d H:i:s');
            if (!ChatRecordsFile::create($emoticon)) {
                throw new Exception('插入聊天记录(代码消息)失败...');
            }

            Db::commit();
        } catch (Exception $e) {
            Db::rollBack();
            return false;
        }

        return $insert->id;
    }
}
