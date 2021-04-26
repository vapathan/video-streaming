<!DOCTYPE html>
<html>
<head>
    <meta charset=utf-8/>
    <title>videojs-contrib-hls embed</title>
    <link href="<?=base_url('public/assets/plugins/video/video-js.css');?>" rel="stylesheet">
</head>
<body>
<h1>Video.js Example Embed</h1>

<video-js id="my_video_1" class="vjs-default-skin" controls preload="auto" width="640" height="268">

</video-js>
<ul id="list">
    <li><a href="javascript:void(0);" data-id="1616575517_76222995b8a94de1102f">Video1</a></li>
    <li><a href="javascript:void(0);" data-id="1616569461_54af2cafee702b6f92fb">Video2</a></li>
    <li><a href="javascript:void(0);">Video3</a></li>


</ul>


<script src="<?= base_url('public/assets/plugins/jquery/jquery.min.js'); ?>"></script>
<script src="<?= base_url('public/assets/plugins/video/video.min.js'); ?>"></script>


<script>
    var player = videojs('my_video_1', {
        playbackRates: [0.5, 1, 1.5, 2],
        responsive: true,
    });

    var Button = videojs.getComponent('Button');
    videojs.regis
    var button = new Button(player, {
        clickHandler: function (event) {
            videojs.log('Clicked');
        }
    });


    $(document).ready(function () {
        $('#list li a').on('click', function (e) {
            getVideo($(this).data('id'));
        });

        player.ready(function () {
            videojs.options.autoplay = true;
        });
    });

    function getVideo(id) {
        $.ajax({
            url: "<?=base_url('get-video');?>",
            dataType: "json",
            data: {'id': id},
            method: 'POST',
            success: function (response) {
                if (!response['otp']) {
                    return;
                } else {
                    player.src({type: 'application/x-mpegURL', src: response.url});
                    player.play();
                }
            }

        });
    }
</script>

</body>
</html>
