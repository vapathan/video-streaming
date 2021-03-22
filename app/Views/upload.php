<!DOCTYPE html>
<html>
<head>
    <meta charset=utf-8 />
    <title>videojs-contrib-hls embed</title>

    <link rel="stylesheet"  href="<?=base_url('vendor/dropzone-5.7.0/dist/dropzone.css');?>">
    <link href="https://unpkg.com/video.js/dist/video-js.css" rel="stylesheet">
</head>
<body>
<h1>Video.js Example Embed</h1>

<form action="<?=base_url('upload');?>"
      class="dropzone"
      id="my-awesome-dropzone" enctype="multipart/form-data"></form>
</form>
<script src="https://unpkg.com/video.js/dist/video.js"></script>
<script src="https://unpkg.com/@videojs/http-streaming/dist/videojs-http-streaming.js"></script>
<script src="<?=base_url('vendor/dropzone-5.7.0/dist/dropzone.js');?>"></script>

<script>

</script>

</body>
</html>