<?php

namespace App\Controllers;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Streaming\FFMpeg;
use Streaming;
use Streaming\Representation;

class Home extends BaseController
{
    private const MEDIA_DIR = 'media';
    private const KEYS_DIR = self::MEDIA_DIR . '/keys';
    private const VIDEOS_DIR = self::MEDIA_DIR. '/videos';
    private const UPLOADS_DIR = self::MEDIA_DIR . '/uploads';

    public function __construct()
    {
        $config = [
            'ffmpeg.binaries' => '/usr/local/bin/ffmpeg',
            'ffprobe.binaries' => '/usr/local/bin/ffprobe',
            'timeout' => 3600, // The timeout for the underlying process
            'ffmpeg.threads' => 12,   // The number of threads that FFmpeg should use
        ];

        $log = new Logger('FFmpeg_Streaming');
        $log->pushHandler(new StreamHandler('/var/log/ffmpeg-streaming.log')); // path to log file

        $this->ffmpeg = FFMpeg::create($config, $log);
    }

    private function getOriginalVideoFilePath($videoFile)
    {
        return self::UPLOADS_DIR . "/" . $videoFile;
    }



    public function play()
    {

        return view('play');
    }

    public function upload()
    {

        if ($this->request->getMethod() == 'post') {
            helper(['form', 'url']);

            $input = $this->validate([
                'file' => [
                    'uploaded[file]',
                    'mime_in[file,video/mp4]',
                ]
            ]);

            if (!$input) {
                print_r('Choose a valid file');
            } else {
                $video = $this->request->getFile('file');
                $newName = $video->getRandomName();
                $video->move(self::UPLOADS_DIR, $newName);

                $data = [
                    'name' => $video->getName(),
                    'type' => $video->getClientMimeType()
                ];

                //print_r($data);

                $this->saveVideo($video->getName());

                print_r('File has successfully uploaded');
            }
        } else {

            return view('upload');
        }
    }


    public function saveVideo($videoFile)
    {
        $r_144p = (new Representation)->setKiloBitrate(95)->setResize(256, 144);
        $r_240p = (new Representation)->setKiloBitrate(150)->setResize(426, 240);
        $r_360p = (new Representation)->setKiloBitrate(276)->setResize(640, 360);
        $r_480p = (new Representation)->setKiloBitrate(750)->setResize(854, 480);
        $r_720p = (new Representation)->setKiloBitrate(2048)->setResize(1280, 720);
        $r_1080p = (new Representation)->setKiloBitrate(4096)->setResize(1920, 1080);
        $r_2k = (new Representation)->setKiloBitrate(6144)->setResize(2560, 1440);
        $r_4k = (new Representation)->setKiloBitrate(17408)->setResize(3840, 2160);


        $video = $this->ffmpeg->open($this->getOriginalVideoFilePath($videoFile));

        $videoFileWithoutExt = pathinfo($videoFile, PATHINFO_FILENAME);

        $save_to = self::KEYS_DIR . '/' . $videoFileWithoutExt;

        //An URL (or a path) to access the key on your website
        $url = base_url($save_to);

        $videoFileWithoutExt = pathinfo($videoFile, PATHINFO_FILENAME);

        $video->hls()
            ->encryption($save_to, $url, 10)
            ->x264()

            ->addRepresentations([$r_144p, $r_240p])
            ->save(self::VIDEOS_DIR .'/'. $videoFileWithoutExt . '.m3u8');

    }

    public function extractImage()
    {

    }


}