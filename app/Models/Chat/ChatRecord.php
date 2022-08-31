<?php

declare (strict_types=1);

namespace App\Models\Chat;

use App\Models\BaseModel;

/**
 * 聊天记录数据表模型
 *
 * @property int    $id         聊天消息ID
 * @property int    $source     消息来源[1:好友消息;2:群聊消息]
 * @property int    $msg_type   消息类型[1:文本消息;2:文件消息;3:入群消息/退群消息;4:会话记录消息;5:代码块消息]
 * @property int    $user_id    发送者ID[0:代表系统消息; >0: 用户ID]
 * @property int    $receive_id 接收者ID[用户ID 或 群ID]
 * @property string $content    文本消息
 * @property int    $is_revoke  是否撤回消息[0:否;1:是]
 * @property string $created_at 创建时间
 * @package App\Model\Chat
 */
class ChatRecord extends BaseModel
{
    protected $table = 'chat_records';

    protected $fillable = [
        'source',
        'msg_type',
        'user_id',
        'receive_id',
        'content',
        'is_revoke',
        'created_at',
    ];

    protected $casts = [
        'id'         => 'integer',
        'source'     => 'integer',
        'msg_type'   => 'integer',
        'user_id'    => 'string',
        'receive_id' => 'string',
        'is_revoke'  => 'integer'
    ];
}
