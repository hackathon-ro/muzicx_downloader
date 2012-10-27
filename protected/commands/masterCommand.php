<?php

class masterCommand extends ConsoleCommand {

    public function actionIndex() {
        $videoID = "U5upOFxxvJM";
//        $videoID = "GVcY--XzeIc#!";

        $page = @file_get_contents('http://www.youtube.com/get_video_info?&video_id=' . $videoID);

        preg_match('/^(status=fail)(.*)$/', $page, $status);
        if (isset($status["1"]) && $status["1"] == "status=fail") {
            ConsoleCommand::log("masterCommand->actionIndex", "DEBUG", "This video cannot be downloaded");
            return false;
        }

        preg_match('/(.*)token=(.*?)&thumbnail_url=(.*)/', $page, $token);
        $token = urldecode($token[2]);

        $link = "http://www.youtube.com/get_video?video_id={$videoID}&t={$token}&fmt=18";   //  mp4
//        ConsoleCommand::log("masterCommand->actionIndex", "DEBUG", $link);

        $path = '/tmp/video_' . uniqid() . '.mp4';

//        $ch = curl_init($link);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//
//        $data = curl_exec($ch);
//
//        curl_close($ch);
//
//        file_put_contents($path, $data);


        $out = fopen($path, 'wb');
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_FILE, $out);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $link);

        curl_setopt($ch, CURLOPT_USERAGENT, "YouTube Video Downloader");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100000);

        curl_exec($ch);
        echo "<br>Error is : " . curl_error($ch);

        curl_close($ch);
    }

}