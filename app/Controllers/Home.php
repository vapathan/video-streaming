<?php

namespace App\Controllers;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Streaming\FFMpeg;
use Streaming;
use Streaming\Representation;

class Home extends BaseController
{

    private const KEY_DIR = 'public/videos/keys/';
    private const VIDEO_DIR =  'public/videos/media/';
    private const ORIGINAL_VIDEO_DIR =  'public/uploads/videos/';

    public function __construct()
    {
        $config = [
            'ffmpeg.binaries' => 'vendor\ffmpeg-4.3.1-win64-static\bin\ffmpeg.exe',
            'ffprobe.binaries' => 'vendor\ffmpeg-4.3.1-win64-static\bin\ffprobe.exe',
            'timeout' => 3600, // The timeout for the underlying process
            'ffmpeg.threads' => 12,   // The number of threads that FFmpeg should use
        ];

        $log = new Logger('FFmpeg_Streaming');
        $log->pushHandler(new StreamHandler('/var/log/ffmpeg-streaming.log')); // path to log file

        $this->ffmpeg = FFMpeg::create($config, $log);
    }

    private function getOriginalVideoFilePath($videoFile)
    {
        return self::ORIGINAL_VIDEO_DIR . "/" . $videoFile;
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

        /* $video->hls()
             ->encryption($save_to, $url)
             ->x264()
             ->autoGenerateRepresentations([1080, 480, 240])
             ->save('media/hls-stream.m3u8');
 */

        $format = new Streaming\Format\X264();
        $format->on('progress', function ($video, $format, $percentage) {
            echo sprintf("\r Transcoding... (%s%%)[%s%s]", $percentage, str_repeat('#', $percentage), str_repeat('-', (100 - $percentage)));
        });

        $video->hls()
            ->setFormat($format)
            ->autoGenerateRepresentations([240, 144])
            ->save('media/hls-stream.m3u8');

        return view('welcome_message');


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
                $video->move(self::ORIGINAL_VIDEO_DIR, $newName);

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

        $format = new Streaming\Format\X264();
        $format->on('progress', function ($video, $format, $percentage) {
            echo sprintf("\r Transcoding... (%s%%)[%s%s]", $percentage, str_repeat('#', $percentage), str_repeat('-', (100 - $percentage)));
        });

        $video = $this->ffmpeg->open($this->getOriginalVideoFilePath($videoFile));

        $videoFileWithoutExt = pathinfo($videoFile, PATHINFO_FILENAME);

        $save_to = self::KEY_DIR . '/'. $videoFileWithoutExt;

        //An URL (or a path) to access the key on your website
        $url = base_url($save_to);

        //$url = 'http://localhost/video-streaming/' . self::KEY_DIR;

        $videoFileWithoutExt = pathinfo($videoFile, PATHINFO_FILENAME);

        $video->hls()
            ->setFormat($format)
            ->encryption($save_to, $url, 10)
            ->x264()
            ->addRepresentations([$r_144p, $r_240p])
            ->save(self::VIDEO_DIR . '/'. $videoFileWithoutExt . '.m3u8');


    }


}
