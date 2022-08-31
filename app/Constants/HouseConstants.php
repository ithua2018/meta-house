<?php

namespace App\Constants;

class HouseConstants
{
    //房屋类型
    const  HOUSES_TYPE_SELL = 1;
    const  HOUSES_TYPE_RENT = 2;
    const  HOUSES_TYPE_SHARE = 3;

    //上架下架
    const HOUSES_STATE_OFF_SHELF = 0;
    const HOUSES_STATE_ON_SHELF = 1;

    //房屋列表状态
    const HOUSES_STATE_UNRENTE_UNBUY = 1;
    const HOUSES_STATE__RENTED = 2;
    const HOUSES_STATE__BUIED = 3;

}
