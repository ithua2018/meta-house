<?php

namespace App\Services\Map;

use App\Services\AbstractApiClient;
use App\Services\ApiConsumerInterface;
use App\Values\LastfmLoveTrackParameters;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\Utils;
use Illuminate\Support\Collection;
use Throwable;

class BaiduMapService extends AbstractApiClient implements ApiConsumerInterface
{

    protected string $keyParam = 'ak';


    //逆地理编码
    public function getRegeo(string  $lon, string $lat):?array
    {
        $location = implode(',', [$lat, $lon]);
        try {
            $key = sprintf('bdmap_regeo_%s_%s',$lon, $lat);
            return $this->cache->remember(
                md5($key),
                now()->addWeek(),
                function () use ($location): ?array {
                    $response = $this->get("/reverse_geocoding/v3/?location=$location&output=json&coordtype=wgs84ll");
                    if (!$response || !isset($response->result)) {
                        return null;
                    }
                    return $this->buildRegeoInformation($response->result);
                }
            );
        } catch (Throwable $e) {
            console_debug($e->getMessage());
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
            'business' => $regeocode->business,
            'address' => $regeocode->formatted_address,
            'country' => $addressComponent->country,
            'province' => $addressComponent->province,
            'city' => $addressComponent->city,
            'district' => $addressComponent->district ?: '',
            'township' => $addressComponent->town?: ''
        ];
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
        return config('rent.bdmap.key')?: 'xyRLyQCvX9d4LijKbMDUPanHUkjhpyXZ';
    }

    public function getEndpoint(): ?string
    {
        return config('rent.bdmap.endpoint')?:'https://api.map.baidu.com';
    }

    public function getSecret(): ?string
    {
        return config('rent.amap.secret');
    }


}
