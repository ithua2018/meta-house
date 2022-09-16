<?php

namespace App\Constants;

/**
 * HTTP 响应状态码枚举
 *
 * @package App\Constants
 */
class ResponseCode
{
    // 通用返回码
    const SUCCESS = [0, '成功'];
    const FAIL = [-1, '错误'];
    const CODE_SYSTEM_BUSY = [-2, '系统繁忙，请稍后再试'];
    const PARAM_ILLEGAL = [4001, '参数不合法'];
    const PARAM_VALUE_ILLEGAL = [4002, '参数值不对'];
    const DATA_IS_NULL = [4004, '找不到数据'];
    const UN_LOGIN = [5001, '请登录'];
    const UPDATED_FAIL = [5005, '更新数据失败'];

    //业务状态码
    //1xxxx有关用户
    const AUTH_NOT_FOUND_ACCOUNT = [10001, '账号不存在'];
    const AUTH_PASSWORD_WRONG = [10002, '密码错误'];
    const AUTH_SMS_CODE_WRONG = [10003, '验证码错误'];
    const AUTH_INVALID_MOBILE = [10004, '手机号格式不正确'];
    const AUTH_CAPTCHA_UNSUPPORT = [10005, ''];
    const AUTH_CAPTCHA_FREQUENCY = [10006, '验证码未超时1分钟，不能发送'];
    const AUTH_UNSETTING_LOGIN_PWD = [10007, '请设置登录密码'];
    const AUTH_NOT_SAME_PWD = [10008, '两次输入的密码不一致'];
    const AUTH_SELECT_ROLE_FAIL=[10009, '选择身份失败'];
    const AUTH_NOT_SELECT_ROLE=[10010, '还未选择身份'];
    //上传错误  2XXXX
    const UPLOAD_COMMON_ERROR = [20001, ''];
    const UPLOAD_IMAGE_NOT_EXISTS = [20002, '图片资源不存在,请重新上传'];
    //地理位置
    const LOCATION_INFO_ERROR = [30001, ''];

    //聊天相关
    const CHAT_CREAT_CONVERSATION = [6001, '创建会话失败'];

    }
