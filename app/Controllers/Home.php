<?php

namespace App\Controllers;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Streaming\FFMpeg;

class Home extends BaseController
{
	public function index()
	{


        $config = [
            'ffmpeg.binaries'  => '..\vendor\ffmpeg-4.3.1-win64-static\bin\ffmpeg.exe',
            'ffprobe.binaries' => '..\vendor\ffmpeg-4.3.1-win64-static\bin\ffprobe.exe',
            'timeout'          => 3600, // The timeout for the underlying process
            'ffmpeg.threads'   => 12,   // The number of threads that FFmpeg should use
        ];

        $log = new Logger('FFmpeg_Streaming');
        $log->pushHandler(new StreamHandler('/var/log/ffmpeg-streaming.log')); // path to log file

        $ffmpeg = FFMpeg::create($config, $log);

		return view('welcome_message');
	}
}
