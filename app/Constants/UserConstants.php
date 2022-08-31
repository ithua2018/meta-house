<?php

namespace App\Constants;

class UserConstants
{
    //性别
    const USER_GENDER_UNKNOWN= 0; //性别:未知
    const USER_GENDER_MAN= 1; //性别:男
    const USER_GENDER_WOMAN= 2; //性别:女

    //身份
    const USER_ROLE_LANDLOARD = 1; //房东
    const USER_ROLE_TENANT = 2; //租客
    const USER_ROLE_BUYER = 3; //购房
   //身份映射关系
    const ROLE_MAPPING_NAME = [
        self::USER_ROLE_LANDLOARD  => '房东',
        self::USER_ROLE_TENANT => '租客',
        self::USER_ROLE_BUYER => '购房客'
    ];

}
