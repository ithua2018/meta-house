<?php

use Illuminate\Support\Facades\Log;
use  Illuminate\Support\Str;
function getRealIp()
{

    $ip = data_get($_SERVER, 'HTTP_X_FORWARDED_FOR'); //获取用户ip
    console_debug($ip);
    if (strstr($ip, ",")) {#如果经过代理有多个ip,循环处理
        $ip_arr = explode(',', $ip);
        foreach ($ip_arr as $ip) {
            $ipint = sprintf('%u', ip2long($ip));#ip2long — 将 IPV4 的字符串互联网协议转换成长整型数字
            if ($ipint >= 0 && $ipint <= 50331647 || // {"0.0.0.0","2.255.255.255"},
                $ipint >= 167772160 && $ipint <= 184549375 || // {"10.0.0.0","10.255.255.255"},
                $ipint >= 2130706432 && $ipint <= 2147483647 || // {"127.0.0.0","127.255.255.255"},
                $ipint >= 2851995648 && $ipint <= 2852061183 || // {"169.254.0.0","169.254.255.255"}
                $ipint >= 2886729728 && $ipint <= 2887778303 || // {"172.16.0.0","172.31.255.255"},
                $ipint >= 3221225984 && $ipint <= 3221226239 || // {"192.0.2.0","192.0.2.255"},
                $ipint >= 3232235520 && $ipint <= 3232301055 || // {"192.168.0.0","192.168.255.255"},
                $ipint >= 4294967040 && $ipint <= 4294967295 // {"255.255.255.0","255.255.255.255"}
            ){
                continue;
            }else{
                break;
            }



        }
    }
    return trim($ip);
}

if (! function_exists('now')) {
    /**
     * Create a new Carbon instance for the current time.
     *
     * @param  \DateTimeZone|string|null  $tz
     * @return \Illuminate\Support\Carbon
     */
    function now($tz = null)
    {
        return \Illuminate\Support\Facades\Date::now($tz);
    }
}
/**
 * 打印到控制台
 * @param $val
 */
function console_debug($val)
{
    if(is_array($val)) {
      $val = json_encode($val);
    }
    Log::channel('stderr')->debug( $val );
}



/**
 * 图片保存路径转换
 * @param string $new 保存的目录
 * @param string $img 图片
 * @return string
 */
function imgPathShift($new, $img)
{
    $path = 'storage/temporary/';
    $img = explode($path, $img);
    if (count($img) == 2) {
        Storage::move('public/temporary/' . $img['1'], 'public/image/' . $new . '/' . $img['1']);
        //拷贝不同规格的图片
        $imageSpecification = config('image.specification');
        $iarr = explode('.', $img['1']);
        foreach ($imageSpecification as $specification) {
            $img_specification = $iarr[0] . "_$specification." . $iarr['1'];
            if (Storage::exists('public/temporary/' . $img_specification)) { //判断文件是否存在
                Storage::move('public/temporary/' . $img_specification, 'public/image/' . $new . '/' . $img_specification);
            }
        }
        return '/storage/image/' . $new . '/' . $img['1'];
    } else {
        return $img['0'];
    }

}

/**
 * 根据图片路径进行删除
 * @param $directory // 图片所在目录
 * @param $img // 图片url
 * @return string
 */
function imgPathDelete($directory, $img)
{
    $img = explode('/', $img);
    if (count($img) > 1) {
        //删除不同规格的图片
        $imageSpecification = config('image.specification');
        $iarr = explode('.', end($img));
        foreach ($imageSpecification as $specification) {
            $img_specification = $iarr[0] . "_$specification." . $iarr['1'];
            if (Storage::exists('public/image/' . $directory . '/' . $img_specification)) { //判断文件是否存在
                Storage::delete('public/image/' . $directory . '/' . $img_specification);
            }
        }
        Storage::delete('public/image/' . $directory . '/' . end($img));
    }
}

/**
 * 获取无限分级
 * @param array $items 数据源
 * @param string $pid 父类键值
 * @param string $son 子类键名
 * @return array
 */
function genTree($items, $pid = "pid", $son = "children")
{
    $map = [];
    $tree = [];
    foreach ($items as &$it) {
        $map[$it['id']] = &$it;
    }
    foreach ($items as &$it) {
        $parent = &$map[$it[$pid]];
        if ($parent) {
            $parent[$son][] = &$it;
        } else {
            $tree[] = &$it;
        }
    }
    return $tree;
}

if (! function_exists('starts_with')) {
    /**
     * Determine if a given string starts with a given substring.
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     * @return bool
     */
    function starts_with($haystack, $needles)
    {
        return Str::startsWith($haystack, $needles);
    }
}

function findNum($str=''):string{
    $str=trim($str);
    if(empty($str)){return '';}
    $temp=array('1','2','3','4','5','6','7','8','9','0');
    $result='';
    for($i=0;$i<strlen($str);$i++){
        if(in_array($str[$i],$temp)){
            $result.=$str[$i];
        }
    }
    return $result;
}

function genUid($user_id, $role_id)
{
    return  intval($user_id.'0'.$role_id);
}

/**
 * 随机生成图片名
 *
 * @param string $ext      图片后缀名
 * @param array  $filesize 图片文件大小信息
 * @return string
 */
function create_image_name(string $ext, array $filesize)
{
    return uniqid() . Str::random() . '_' . $filesize[0] . 'x' . $filesize[1] . '.' . $ext;
}

/**
 * 获取媒体文件url
 *
 * @param string $path 文件相对路径
 * @return string
 */
function get_media_url(string $path)
{
    return sprintf('%s/storage/%s', rtrim(config('rent.image_url'), '/'), ltrim($path, '/'));
}

/**
 * Get a URL for static file requests.
 * If this installation of Koel has a CDN_URL configured, use it as the base.
 * Otherwise, just use a full URL to the asset.
 *
 * @param string $name The optional resource name/path
 */
function static_url(?string $name = null): string
{
    $cdnUrl = trim(config('koel.cdn.url'), '/ ');

    return $cdnUrl ? $cdnUrl . '/' . trim(ltrim($name, '/')) : trim(asset($name));
}

/**
 * A copy of Laravel Mix but catered to our directory structure.
 *
 * @throws InvalidArgumentException
 */
function asset_rev(string $file, ?string $manifestFile = null): string
{
    static $manifest = null;

    $manifestFile = $manifestFile ?: public_path('mix-manifest.json');

    if ($manifest === null) {
        $manifest = json_decode(file_get_contents($manifestFile), true);
    }

    if (isset($manifest[$file])) {
        return file_exists(public_path('hot'))
            ? "http://localhost:8080$manifest[$file]"
            : static_url($manifest[$file]);
    }

    throw new InvalidArgumentException("File $file not defined in asset manifest.");
}
//加强版unserialize
function pro_unserialize( string $str): ?array {
    if(PHP_VERSION > 7.2) { //需要替换r:的值
        if(strpos($str,'r:') !== false) {
            $arr =   explode(':{', $str);
            $text = trim($arr[1], '}');
            $arr2 = array_filter(explode(';', $text));
            $keys = []; $values = [];
            foreach($arr2 as $k => $v) {
                if($k %2 === 0) {
                    array_push($keys, $v);
                } else {
                    array_push($values, $v);
                }
            }
            $newStr = '';
            foreach($values as  $key => $val) {
                if(strpos($val,'r:') !== false) {
                    $arrs =  explode('r:', $val);
                    $pos = $arrs[1]-2;
                    $val = $values[$pos];
                }
                $newStr .= $keys[$key].';'.$val.';';
            }
            $str= $arr[0].':{'.$newStr.'}';
        }
    }
    return  unserialize($str);
}

function album_cover_path(string $fileName): string
{
    return public_path(config('koel.album_cover_dir') . $fileName);
}

function album_cover_url(string $fileName): string
{
    return static_url(config('koel.album_cover_dir') . $fileName);
}

function artist_image_path(string $fileName): string
{
    return public_path(config('koel.artist_image_dir') . $fileName);
}

function artist_image_url(string $fileName): string
{
    return static_url(config('koel.artist_image_dir') . $fileName);
}

function koel_version(): string
{
    return trim(file_get_contents(base_path('.version')));
}
