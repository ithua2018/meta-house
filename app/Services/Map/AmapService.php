<?php

namespace App\Services\Map;

use App\Services\AbstractApiClient;
use App\Services\ApiConsumerInterface;
use App\Values\LastfmLoveTrackParameters;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\Utils;
use Illuminate\Support\Collection;
use Throwable;

class AmapService extends AbstractApiClient implements ApiConsumerInterface
{

    protected string $keyParam = 'key';

    /**
     * 获取地铁站点
     * 深圳adcode 440300
     */
    public  function  getSubwayStation(array $data, int $page=1, int $offset=50):?array
    {
        try {
            $key = sprintf('amap_subway_station_%u_%u_$u', $data['adcode'], $page, $offset);
            return $this->cache->remember(
                md5($key),
                now()->addWeek(),
                function () use ($data, $page, $offset): ?array {
                    $adcode = $data['adcode'];
                    $city_id = $data['city_id'];
                    $response = $this->get("/place/text?city=$adcode&types=150500&offset=$offset&page=$page");

                    if (!$response || !isset($response->pois)) {
                        return null;
                    }
                    return $this->buildSubwayStationInformation($response->pois, $city_id);
                }
            );
        } catch (Throwable $e) {
            $this->logger->error($e);
            return null;
        }

    }

    protected  function buildSubwayStationInformation($pois,$data):?array
    {
        $arr = [];
        $i = 0;
        foreach($pois as $p) {
            $lines = explode(';', $p->address);
            $locations = explode(',',$p->location);
            $j = 0;
            foreach($lines as $line) {
                $arr[$i] =  [
                    'station_id'  => $p->id,
                    'pname' => $p->pname,
                    'cityname' => $p->cityname,
                    'city_id' => $data['city_id'],
                    'adcode' => $data['adcode'],
                    'adname' => $p->adname,
                    'lon' =>  $locations[0],
                    'lat' => $locations[1],
                    'name' => str_replace('(地铁站)', '',$p->name),
                    'is_finished' => 1
                ];
                if(false === strpos($line, '(在建)')) {
                    $arr[$i]['is_finished'] = 1;
                    $arr[$i]['line'] =  $line;
                } else {
                    $arr[$i]['is_finished'] = 0;
                    $line = str_replace('(在建)', '',$line);
                    $arr[$i]['line'] =  $line;
                }
                $arr[$i]['id'] =  md5($p->id.$line);
                $j++;
                $i++;
            }
        }

        return $arr;

    }

    //逆地理编码
    public function getRegeo(string  $lon, string $lat):?array
    {
        $location = implode(',', [$lon, $lat]);
        try {
            $key = sprintf('amap_regeo_%s_%s',$lon, $lat);
            return $this->cache->remember(
                md5($key),
                now()->addWeek(),
                function () use ($location): ?array {
                    $response = $this->get("/geocode/regeo?location=$location");

                    if (!$response || !isset($response->regeocode)) {
                        return null;
                    }
                    return $this->buildRegeoInformation($response->regeocode);
                }
            );
        } catch (Throwable $e) {
            $this->logger->error($e);
            return null;
        }
    }
   private function buildRegeoInformation($regeocode)
    {
        if(empty($regeocode->formatted_address)) {
            return null;
        }
        $addressComponent = $regeocode->addressComponent;
        return [
            'address' => $regeocode->formatted_address,
            'country' => $addressComponent->country,
            'province' => $addressComponent->province,
            'city' => $addressComponent->city,
            'district' => $addressComponent->district ?: '',
            'township' => $addressComponent->township?: ''
        ];
    }

    /**
     * 地理编码
     * @param string $address
     * @return array|null
     */
    public function GetGeo(string $address):?array {
        try {
            $key = sprintf('amap_geo_%s',$address);
            return $this->cache->remember(
                md5($key),
                now()->addWeek(),
                function () use ($address): ?array {
                    $response = $this->get("/geocode/geo?address=$address");
                    if (!$response || !isset($response->geocodes)) {
                        return null;
                    }
                    return $this->buildGeoInformation($response->geocodes);
                }
            );
        } catch (Throwable $e) {
            $this->logger->error($e);
            return null;
        }
    }

    private function buildGeoInformation($geocodes)
    {
       foreach ($geocodes as $item ) {
           $location = $item->location;
       }
        return explode(',', $location);
    }




    /**
     *
     * @param array $params The array of parameters
     * @param bool $toString Whether to turn the array into a query string
     *
     * @return array<mixed>|string
     */
    public function buildAuthCallParams(array $params, bool $toString = false) // @phpcs:ignore
    {
        $params['api_key'] = $this->getKey();
        ksort($params);
        // Generate the API signature.
        $str = '';

        foreach ($params as $name => $value) {
            $str .= $name . $value;
        }

        $str .= $this->getSecret();
        $params['api_sig'] = md5($str);
        if (!$toString) {
            return $params;
        }

        $query = '';

        foreach ($params as $key => $value) {
            $query .= "$key=$value&";
        }

        return rtrim($query, '&');
    }
    public function getKey(): ?string
    {
        return config('rent.amap.key')?: '2046305c42a03e4df51ff6aceba68c87';
    }

    public function getEndpoint(): ?string
    {
        return config('rent.amap.endpoint')?:'https://restapi.amap.com/v3';
    }

    public function getSecret(): ?string
    {
        return config('rent.amap.secret');
    }


}
