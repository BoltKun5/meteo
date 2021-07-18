<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\NextTrip;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpClient\HttpClient;


class MeteoController extends AbstractController
{
    /**
     * @Route("/chooseNextTrip", name="home")
     */

    public function create(Request $request): Response
    {
        try {
            $ville1 = $request->get('ville1');
            $ville2 = $request->get('ville2');
            // $ville1 = "azeaze";
            // $ville2 = "Paris";

            // return new Response($ville1." ".$ville2);

            if ($ville1 == $ville2) {
                throw new \Exception("SAME_CITIES");
            }

            $keptData = $this->compareCityData($this->getCityData($ville1), $this->getCityData($ville2));

            return new Response(json_encode($keptData));
        } catch (\Throwable $th) {
            switch ($th->getMessage()) {
                case "SAME_CITIES":
                    return new Response("Les deux villes sont identiques");
                    break;
                case "UNDEFINED_CITY":
                    return new Response("Une des deux villes n'est pas indiquées");
                    break;
                case "CITY_NOT_FOUND":
                    return new Response("Vérifiez l'orthographe des villes");
                    break;
                default:
                    return new Response($th);
            }
        }
    }


    public function compareCityData($cityData1, $cityData2)
    {
        $score1 = 0;
        $score2 = 0;
        $differencies1 = $this->getDifferencies($this->getAverageData($cityData1));
        $differencies2 = $this->getDifferencies($this->getAverageData($cityData2));

        if ($differencies1['temp'] > $differencies2['temp'])
            $score1 += 20;
        elseif ($differencies1['temp'] < $differencies2['temp'])
            $score2 += 20;

        if ($differencies1['clouds'] > $differencies2['clouds'])
            $score1 += 10;
        elseif ($differencies1['clouds'] < $differencies2['clouds'])
            $score2 += 10;

        if ($differencies1['humidity'] > $differencies2['humidity'])
            $score1 += 15;
        elseif ($differencies1['humidity'] < $differencies2['humidity'])
            $score2 += 15;

        if ($score1 > $score2) return $cityData1;
        elseif ($score1 < $score2) return $cityData2;
        else return "Valeurs égales";
    }

    public function getDifferencies($averageData)
    {
        return array(
            'temp' => 27 - $averageData['temp'],
            'clouds' => 15 - $averageData['clouds'],
            'humidity' => 60 - $averageData['humidity']
        );
    }

    public function getAverageData($cityData)
    {
        $averageTemp = 0;
        $averageClouds = 0;
        $averageHumidity = 0;
        foreach (json_decode(json_encode($cityData), true)['daily'] as $value) {
            $averageTemp += $value['temp']['day'];
            $averageClouds += $value['clouds'];
            $averageHumidity += $value['humidity'];
        }
        return array(
            'temp' => $averageTemp / 7,
            'clouds' => $averageClouds / 7,
            'humidity' => $averageHumidity / 7
        );
    }

    public function getCityData($ville)
    {
        if (!isset($ville) || $ville == "")
            throw new \Exception("UNDEFINED_CITY");

        $client = HttpClient::create();
        $cityPos = $this->getPos($ville, $client);
        $response = $this->fetchData($cityPos, $client);
        return $response;
    }

    public function fetchData($cityPos, $client = false)
    {
        if (!$client)
            $client = HttpClient::create();

        $queryParams = array(
            'lat' => $cityPos['lat'],
            'lon' => $cityPos['lon'],
            'exclude' => array(
                'current',
                'minutely',
                'hourly',
                'alerts'
            ),
            'units' => 'metric'
        );

        $url = $this->formatQueryParams("https://api.openweathermap.org/data/2.5/onecall", $queryParams);

        $response = $client->request('GET', $url);
        return json_decode($response->getContent());
    }

    public function formatQueryParams($chain, $arr)
    {
        $chain .= "?";

        foreach ($arr as $key => $value) {
            if ($key == "exclude") {
                $chain .= "exclude=";
                foreach ($arr['exclude'] as $value2) {
                    $chain .= $value2 . ",";
                }
                $chain = substr_replace($chain, "", -1);
                $chain .= "&";
            } else
                $chain .= $key . "=" . $value . "&";
        }
        $chain .= 'appid=54ef6d5ca9e20968e41347dcfa0e69af';
        return $chain;
    }

    public function getPos($ville)
    {
        $response = $this->fetchPos($ville);
        $content = $response->getContent(false);
        $decodedCord = json_decode($content);
        if ($decodedCord->cod == '404')
            throw new \Exception("CITY_NOT_FOUND");
        return array('lon' => $decodedCord->coord->lon, 'lat' => $decodedCord->coord->lat);
    }

    public function fetchPos($ville, $client = false)
    {
        if (!$client)
            $client = HttpClient::create();

        $queryParams = array(
            'q' => $ville
        );

        $url = $this->formatQueryParams("https://api.openweathermap.org/data/2.5/weather", $queryParams);

        return $client->request('GET', "$url");
    }
}
