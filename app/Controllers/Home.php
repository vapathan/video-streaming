<?php

namespace App\Controllers;

use CodeIgniter\Files\Exceptions\FileNotFoundException;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Streaming\FFMpeg;
use Streaming;
use Streaming\Representation;

class Home extends BaseController
{
    private const MEDIA_DIR = '/data2/www/medias';
    private const KEYS_DIR = self::MEDIA_DIR . '/keys';
    private const VIDEOS_DIR = self::MEDIA_DIR . '/videos';
    private const UPLOADS_DIR = self::MEDIA_DIR . '/uploads/';
    private const EXT = '.m3u8';
    private const VIDEOS_BASE_URL = 'https://streaming.matoshri.edu.in/media/videos/';
    private const API_KEY = '8fGWSCmfgciIRSLEwGsEhcPyfFCKg5teBD3wLSBFKajChhZ1I9zjQYOnotocBQpJ';

    public function __construct()
    {
        $config = [
            //'ffmpeg.binaries' => 'vendor/ffmpeg-4.3.1-win64-static/bin/ffmpeg.exe',
            'ffmpeg.binaries' => '/usr/local/bin/ffmpeg',
            //'ffprobe.binaries' => 'vendor/ffmpeg-4.3.1-win64-static/bin/ffprobe.exe',
            'ffprobe.binaries' => '/usr/local/bin/ffprobe',
            'timeout' => 3600, // The timeout for the underlying process
            'ffmpeg.threads' => 12,   // The number of threads that FFmpeg should use
        ];

        $log = new Logger('FFmpeg_Streaming');
        $log->pushHandler(new StreamHandler('/var/log/ffmpeg-streaming.log')); // path to log file

        $this->ffmpeg = FFMpeg::create($config, $log);
        helper('text');
    }

    private function getOriginalVideoFilePath($videoFile)
    {
        return self::UPLOADS_DIR . "/" . $videoFile;
    }


    public function play()
    {
        return view('play');
    }

    /*
     * upload video to server
     */
    public function upload()
    {
        $response = [];
        $response['status'] = 'error';

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

            if ($video->move(self::UPLOADS_DIR, $newName)) {

                $response['status'] = 'success';
                $response['videoId'] = pathinfo($newName, PATHINFO_FILENAME);

                $data = [
                    'name' => $video->getName(),
                    'type' => $video->getClientMimeType()
                ];

               // pclose(popen("start /B " . 'php index.php home save  ' . $newName, "r"));
                //exec('php index.php home save  ' . $newName );
                exec('php index.php home save  ' . $newName . ' > /dev/null &');
            }

        }

        echo json_encode($response);
        exit;

    }


    /*
     * Encode using HLS and save video
     */
    public function save($videoFile)
    {
        $response = [];
        if ($this->request->isCLI()) {
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

            $save_to =   self::KEYS_DIR .'/'. $videoFileWithoutExt;

            //An URL (or a path) to access the key on your website
            $url = base_url('media/keys/'.$videoFileWithoutExt);

            $videoFileWithoutExt = pathinfo($videoFile, PATHINFO_FILENAME);

            $video->hls()
                ->encryption($save_to, $url, 10)
                ->x264()
                ->addRepresentations([$r_144p, $r_240p, $r_360p, $r_480p, $r_720p, $r_1080p])
                ->save($this->getVideoFilePath($videoFileWithoutExt));

            $response['status'] = 'success';

        } else {
            $response['status'] = 'error';
        }

        echo json_encode($response);

    }

    //Generate and return URL and token for a requested video
    public function getVideo()
    {
        $apiKey = $this->request->getHeaderLine('Authorization');
        $response = [];
        $response['status'] = 'error';
        $response['url'] = null;
        $response['otp'] = null;
        if ($this->request->getMethod() == 'post' && $apiKey == self::API_KEY) {
            $id = $this->request->getVar('id');

            try {
//		echo $this->getVideoFilePath($id);
//		exit;	
	        $file = new \CodeIgniter\Files\File($this->getVideoFilePath($id), true);
                $response['status'] = 'success';
                $response['url'] = self::VIDEOS_BASE_URL . $id . self::EXT;
                $response['otp'] = random_string('alnum', 16);
                session()->set('videoOTP', $response['otp']);

            } catch (FileNotFoundException $e) {
                $response['messgae'] = 'File not found';

            }

        } else {
            $response['messgae'] = 'Invalid request';
        }
    
	/*$this->response
	->setHeader('Access-Control-Allow-Origin','https://learning.matoshri.edu.in')
	->setHeader('Access-Control-Allow-Methods',' GET, POST, PATCH, PUT, DELETE, OPTIONS')
	->setHeader('Access-Control-Allow-Headers',' Origin, Content-Type, X-Auth-Token');
	 */

      return $this->response->setJSON($response);
       
    }


    private function execInBackground($cmd)
    {
        if (substr(php_uname(), 0, 7) == "Windows") {
            pclose(popen("start /B " . $cmd, "r"));
        } else {
            exec($cmd . " > /dev/null &");
        }
    }


    public function getVideoStatus()
    {
        $apiKey = $this->request->getHeaderLine('Authorization');
        $response = [];
        $response['status'] = 'Not Found';

        if ($this->request->getMethod() == 'post' && $apiKey == self::API_KEY) {
            $id = $this->request->getVar('id');

            try {

                $uploadedFile = new \CodeIgniter\Files\File(self::UPLOADS_DIR . '/' . $id . '.mp4', true);
                $response['status'] = 'Pre-Upload';
                if ($uploadedFile != null) {
                    $encodedFile = new \CodeIgniter\Files\File($this->getVideoFilePath($id), true);

                    if ($encodedFile != null) {
                        $response['status'] = 'ready';
                    } else {
                        $response['status'] = 'queued';
                    }
                }


            } catch (FileNotFoundException $e) {
                $response['messgae'] = 'File not found';

            }

        } else {
            $response['messgae'] = 'Invalid request';
        }
        echo json_encode($response);
        exit();
    }

    public function deleteVideo()
    {
        $apiKey = $this->request->getHeaderLine('Authorization');
        $response = [];
        $response['status'] = 'Not Found';

        if ($this->request->getMethod() == 'post' && $apiKey == self::API_KEY) {
            $id = $this->request->getVar('id');

            try {

                $uploadedFile = self::UPLOADS_DIR . '/' . $id . '.mp4';
                if (unlink($uploadedFile)) {
                    $mask = self::VIDEOS_DIR . '/' . $id . '*.*';
                    array_map("unlink", glob($mask));
                    $response['status'] = 'success';
                    $response['messgae'] = 'Video deleted successfully.';
                }else{
                    $response['status'] = 'error';
                    $response['messgae'] = 'Error occured while deleting a Video.';
                }


            } catch (FileNotFoundException $e) {
                $response['messgae'] = 'File not found';

            }

        } else {
            $response['messgae'] = 'Invalid request';
        }
        echo json_encode($response);
        exit();
    }


    public function extractImage()
    {

    }

    public function message($to = 'World')
    {
        echo "Hello {$to}!" . PHP_EOL;
    }

    private function getVideoFilePath($fileName)
    {
        return self::VIDEOS_DIR . '/' . $fileName . self::EXT;
    }


}
