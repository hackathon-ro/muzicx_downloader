<?php

/**
 * Youtube Helper 
 */
class YoutubeHelper {

    public static function get_content_of_url($url) {
        echo LogHelper::pre($url);

        $ohyeah = curl_init();
        curl_setopt($ohyeah, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ohyeah, CURLOPT_URL, $url);

       // echo LogHelper::pre($ohyeah);

        $data = curl_exec($ohyeah);

        echo LogHelper::pre($data);

        curl_close($ohyeah);

        return $data;
    }

    public static function get_flv_link($string) {
        preg_match_all("/watch_fullscreen(.*)plid/", $string, $matches);

        echo LogHelper::pre($matches);

        return array();
        $arrs = (explode('&', $outdata));
        foreach ($arrs as $arr) {
            list($i, $x) = explode("=", $arr);
            $$i = $x;
        }
        $link = 'http://www.youtube.com/get_video?video_id=' . $video_id . '&t=' . $t;

        return array($video_id, $link);
    }

    public static function get_youtube($url) {
        $stream = self::get_content_of_url($url);

        //echo LogHelper::pre($stream);

        return self::get_flv_link($stream);
    }

}