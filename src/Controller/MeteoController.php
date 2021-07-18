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
            // $ville1 = $request->get('ville1');
            // $ville2 = $request->get('ville2');

            $ville1 = "London";
            $ville2 = "Paris";
            $keptData = $this->compareCityData($this->getCityData($ville1), $this->getCityData($ville2));

            return new Response(json_encode($keptData));


        } catch (\Throwable $th) {

            return new Response($th);
            
        }
    }


    public function compareCityData($cityData1, $cityData2)
    {

        return  $cityData1;
    }

    public function getCityData($ville)
    {
        if (!isset($ville))
            throw new \Exception("UNDEFINED_CITY", 1);

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
            )
        );

        $url = $this->formatQueryParams("https://api.openweathermap.org/data/2.5/onecall", $queryParams);

        $response = $client->request('GET', $url);
        return json_decode($response->getContent());
    }

    function formatQueryParams($chain, $arr)
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
        $content = $response->getContent();
        $decodedCord = json_decode($content)->coord;
        return array('lon' => $decodedCord->lon, 'lat' => $decodedCord->lat);
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
