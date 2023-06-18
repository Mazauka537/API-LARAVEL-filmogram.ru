<?php

namespace App\Helpers;

class KinopoiskAPI
{
    private static function fetch($curl)
    {
        $headers = array(
            'Accept: application/json',
            'Content-Type: application/json',
            'X-API-KEY: ' . env('KINOPOISK_API_TOKEN')
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HEADER, false);

        $result = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return ['status' => $http_code, 'data' => json_decode($result)];
    }

    public static function getFilm($filmId)
    {
        $curl = curl_init(env('KINOPOISK_API_URL') . '/' . $filmId);

        return self::fetch($curl);
    }

    public static function searchFilms($request, $page = 1)
    {
        $order = $request->order ?? 'NUM_VOTE';
        $type = $request->type ?? 'ALL';
        $genre = $request->genre ?? null;
        $yearFrom = $request->year_from ?? 1000;
        $yearTo = $request->year_to ?? 3000;
        $ratingFrom = $request->rating_from ?? 0;
        $ratingTo = $request->rating_to ?? 10;
        $keyword = $request->keyword ?? null;

        $url = env('KINOPOISK_API_URL');
        $url .= '?order=' . $order;

        if ($keyword)
            $url .= '&keyword=' . urlencode($keyword);
        if ($genre)
            $url .= '&genres=' . $genre;

        $url .= '&type=' . $type;
        $url .= '&yearFrom=' . $yearFrom;
        $url .= '&yearTo=' . $yearTo;
        $url .= '&ratingFrom=' . $ratingFrom;
        $url .= '&ratingTo=' . $ratingTo;
        $url .= '&page=' . $page;

        $curl = curl_init($url);

        return self::fetch($curl);
    }
}
