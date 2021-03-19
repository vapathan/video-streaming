<?php

namespace App\Controllers;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Streaming\FFMpeg;
use Streaming\Representation;

class Home extends BaseController
{

    public function __construct()
    {
        $config = [
            'ffmpeg.binaries' => '..\vendor\ffmpeg-4.3.1-win64-static\bin\ffmpeg.exe',
            'ffprobe.binaries' => '..\vendor\ffmpeg-4.3.1-win64-static\bin\ffprobe.exe',
            'timeout' => 3600, // The timeout for the underlying process
            'ffmpeg.threads' => 12,   // The number of threads that FFmpeg should use
        ];

        $log = new Logger('FFmpeg_Streaming');
        $log->pushHandler(new StreamHandler('/var/log/ffmpeg-streaming.log')); // path to log file

        $this->ffmpeg = FFMpeg::create($config, $log);
    }

    public function index()
    {


        /*$video = $this->ffmpeg->open(base_url('public/assets/01 Introduction.mp4'));

        $r_144p  = (new Representation)->setKiloBitrate(95)->setResize(256, 144);
        $r_240p  = (new Representation)->setKiloBitrate(150)->setResize(426, 240);
        $r_360p  = (new Representation)->setKiloBitrate(276)->setResize(640, 360);
        $r_480p  = (new Representation)->setKiloBitrate(750)->setResize(854, 480);
        $r_720p  = (new Representation)->setKiloBitrate(2048)->setResize(1280, 720);
        $r_1080p = (new Representation)->setKiloBitrate(4096)->setResize(1920, 1080);
        $r_2k    = (new Representation)->setKiloBitrate(6144)->setResize(2560, 1440);
        $r_4k    = (new Representation)->setKiloBitrate(17408)->setResize(3840, 2160);

        $video->dash()
            ->x264()
            ->addRepresentations([$r_144p, $r_240p, $r_360p, $r_480p, $r_720p, $r_1080p, $r_2k, $r_4k])
            ->save('dash-stream.mpd');*/

        $video = $this->ffmpeg->open(base_url('public/assets/01 Introduction.mp4'));

        //A path you want to save a random key to your local machine
        $save_to = 'videos/key';

//An URL (or a path) to access the key on your website
        $url = 'http://localhost/video-streaming/public/videos/key';
// or $url = '/"PATH TO THE KEY DIRECTORY"/key';

        $video->hls()
            ->encryption($save_to, $url)
            ->x264()
            ->autoGenerateRepresentations([1080, 480, 240])
            ->save('media/hls-stream.m3u8');

        return view('welcome_message');


    }

    public function play()
    {
        $video = $this->ffmpeg->open(base_url('public/assets/01 Introduction.mp4'));

        return view('play');
    }
}
