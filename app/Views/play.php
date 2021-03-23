<!DOCTYPE html>
<html>
<head>
    <meta charset=utf-8 />
    <title>videojs-contrib-hls embed</title>

    <!--

    Uses the latest versions of video.js and videojs-http-streaming.

    To use specific versions, please change the URLs to the form:

    <link href="https://unpkg.com/video.js@6.7.1/dist/video-js.css" rel="stylesheet">
    <script src="https://unpkg.com/video.js@6.7.1/dist/video.js"></script>
    <script src="https://unpkg.com/@videojs/http-streaming@0.9.0/dist/videojs-http-streaming.js"></script>

    -->

    <link href="https://unpkg.com/video.js/dist/video-js.css" rel="stylesheet">
</head>
<body>
<h1>Video.js Example Embed</h1>

<video-js id="my_video_1" class="vjs-default-skin" controls preload="auto" width="640" height="268">

</video-js>



<script src="https://unpkg.com/video.js/dist/video.js"></script>
<script src="https://unpkg.com/@videojs/http-streaming/dist/videojs-http-streaming.js"></script>

<script>

    var player = videojs('my_video_1',{
        sources: [{
            src: 'https://streaming.matoshri.edu.in/media/videos/1616492537_8c1a67f040f62ec82a38.m3u8',
            type:'application/x-mpegURL'
        }]
    });
</script>

</body>
</html>