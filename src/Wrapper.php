<?php

namespace Slruslan\Proxy6;

use GuzzleHttp\Client;

/**
 * Class Wrapper
 *
 * Обертка для работы с API сервиса Proxy6.net
 *
 * Написано в соответствии с документацией:
 * https://proxy6.net/developers.
 *
 * Использует GuzzleHttp.
 */
class Wrapper
{
    private $apiKey;

    private $baseUri;

    private $client;

    /**
     * @param $apiKey API ключ, можно сгенерировать здесь:
     * https://proxy6.net/user/developers
     */
    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;

        $this->baseUri = sprintf('https://proxy6.net/api/%s/', $this->apiKey);

        $this->client = new Client([
           'base_uri' => $this->baseUri
        ]);
    }

    /**
     * Получение списка ваших прокси
     *
     * @param string $state Состояние прокси (см. ProxyStates)
     * @param string $descr Технический комментарий
     *
     * Ответ (в формате stdObject):
        {
        "status": "yes",
        "user_id": "1",
        "balance": "48.80",
        "currency": "RUB",
        "list_count": 4,
        "list": {
        "11": {
        "id": "11",
        "ip": "2a00:1838:32:19f:45fb:2640::330",
        "host": "185.22.134.250",
        "port": "7330",
        "user": "5svBNZ",
        "pass": "iagn2d",
        "type": "http",
        "date": "2016-06-19 16:32:39",
        "date_end": "2016-07-12 11:50:41",
        "descr": "",
        "active": "1"
        },
        "14": {
            "id": "14",
              "ip": "2a00:1838:32:198:56ec:2696::386",
              "host": "185.22.134.242",
              "port": "7386",
              "user": "nV5TFK",
              "pass": "3Itr1t",
              "type": "http",
              "date": "2016-06-27 16:06:22",
              "date_end": "2016-07-11 16:06:22",
              "descr": "",
              "active": "1"
           }
         }
        }
     */
    public function getProxy($state = ProxyState::ALL, $descr = '')
    {
        $params = [
            'state' => $state,
            'descr' => $descr
        ];

        return $this->sendRequest('getproxy', $params);
    }

    /**
     * Получает информацию о сумме заказа
     * в зависимости от периода и кол-ва прокси
     *
     * @param $count Кол-во прокси
     * @param $period Период (дней)
     * @param string $version Версия прокси (см. ProxyVersion)
     *
     * Ответ (в формате stdObject):
        {
        "status": "yes",
        "user_id": "1",
        "balance": "48.80",
        "currency": "RUB",
        "price": 1800,
        "price_single": 0.6,
        "period": 30,
        "count": 100
        }
     */
    public function getPrice($count, $period, $version = ProxyVersion::IPV6)
    {
        $params = [
            'count' => $count,
            'period' => $period,
            'version' => $version
        ];

        return $this->sendRequest('getprice', $params);
    }


    /**
     * Получает информацию о доступном для приобретения
     * кол-ве прокси определенной страны
     *
     * @param $country Код страны (iso2)
     * @param string $version Версия прокси (см. ProxyVersion)
     *
     * Ответ (в формате stdObject)
        {
            "status": "yes",
            "user_id": "1",
            "balance": "48.80",
            "currency": "RUB",
            "count": 971
        }
     */
    public function getCount($country, $version = ProxyVersion::IPV6)
    {
        $params = [
            'country' => $country,
            'version' => $version
        ];

        return $this->sendRequest('getcount', $params);
    }

    /**
     * Получает информацию о доступных для приобретения
     * странах, по заданному типу прокси
     *
     * @param string $version Версия прокси (см. ProxyVersion)
     *
     * Ответ (в формате stdObject):
        {
            "status": "yes",
            "user_id": "1",
            "balance": "48.80",
            "currency": "RUB",
            "list": ["ru","ua","us"]
        }
     */
    public function getCountry($version = ProxyVersion::IPV6)
    {
        $params = [
            'version' => $version
        ];

        return $this->sendRequest('getcountry', $params);
    }

    /**
     * Устанавливает тип протокола у списка прокси.
     * Например, HTTPS, или SOCKS5.
     *
     * @param $ids Список ID прокси в системе (строка через запятую, либо массив)
     * @param $type Тип протокола (см. ProxyType)
     *
     * Ответ (в формате stdObject):
       {
        "status": "yes",
        "user_id": "1",
        "balance": "48.80",
        "currency": "RUB"
        }
     */
    public function setType($ids, $type)
    {
        if(is_array($ids))
            $ids = implode(',', $ids);

        $params = [
            'ids' => $ids,
            'type' => $type
        ];

        return $this->sendRequest('settype', $params);
    }

    /**
     * Меняет технический комментарий у прокси.
     *
     * @param $old Комментарий, который нужно заменить
     * @param $new Новый комментарий
     *
     * Ответ (в формате stdObject):
       {
        "status": "yes",
        "user_id": "1",
        "balance": "48.80",
        "currency": "RUB",
        "count": 4
        }
     */
    public function setDescr($old, $new)
    {
        $params = [
            'old' => $old,
            'new' => $new
        ];

        return $this->sendRequest('setdescr', $params);
    }

    /**
     * Покупает прокси на сервисе.
     * Для покупке на балансе должно быть достаточно денег.
     *
     * @param $count Кол-во прокси
     * @param $period Период (дней)
     * @param string $country Страна (iso2)
     * @param string $version Версия прокси (см. ProxyVersion)
     * @param string $type Тип прокси (см. ProxyType)
     * @param string $descr Технический комментарий (не обязательно)
     *
     * Ответ (в формате stdObject):
        {
        "status": "yes",
        "user_id": "1",
        "balance": 42.5,
        "currency": "RUB",
        "count": 1,
        "price": 6.3,
        "price_single": 0.9,
        "period": 7,
        "country": "ru",
        "list": {
        "15": {
        "id": "15",
        "ip": "2a00:1838:32:19f:45fb:2640::330",
        "host": "185.22.134.250",
        "port": "7330",
        "user": "5svBNZ",
        "pass": "iagn2d",
        "type": "http",
        "date": "2016-06-19 16:32:39",
        "date_end": "2016-07-12 11:50:41",
        "active": "1"
        }
        }
        }
     */
    public function buy($count, $period, $country = 'ru', $version = ProxyVersion::IPV6, $type = ProxyType::HTTPS, $descr = '')
    {
        $params = [
            'count' => $count,
            'period' => $period,
            'country' => $country,
            'version' => $version,
            'type' => $type,
            'descr' => $descr
        ];

        return $this->sendRequest('buy', $params);
    }

    /**
     * Продлевает действие списка прокси.
     *
     * @param $period Период продления
     * @param $ids Список ID прокси в системе (строка через запятую, либо массив)
     *
     * Ответ (в формате stdObject):
        {
        "status": "yes",
        "user_id": "1",
        "balance": 29,
        "currency": "RUB",
        "price": 12.6,
        "price_single": 0.9,
        "period": 7,
        "count": 2,
        "list": {
        "15": {
        "id": 15,
        "date_end": "2016-07-15 06:30:27"
        },
        "16": {
        "id": 16,
        "date_end": "2016-07-16 09:31:21"
        }
        }
        }
     */
    public function prolong($period, $ids)
    {
        if(is_array($ids))
            $ids = implode(',', $ids);

        $params = [
            'ids' => $ids,
            'period' => $period
        ];

        return $this->sendRequest('prolong', $params);
    }

    /**
     * Отправка запроса к API с помощью Guzzle.
     * Возвращает stdObject с разобранным JSON ответом.
     *
     * @param $method
     * @param $params
     * @return mixed
     */
    private function sendRequest($method, $params)
    {
        $response = $this->client->get($method, [
            'query' => $params
        ]);

        return json_decode($response->getBody());
    }
}
