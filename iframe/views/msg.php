<!DOCTYPE html>
<html>
    <head>
        <title><?php echo $title; ?></title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style type="text/css">
            body{ margin: 0px; border: 0px; padding: 20px; font-size: 14px;}
            #content{padding: 10px;color: #888; text-indent: 1em; background-color: #FFC5C5; color:#FF4343; font-weight: bold;}
            #wait_div{padding: 30px;color: #FF5C00; background-color: #f2f2f2; min-height: 230px; }
            #wait{color:#15BE23;}
            #href{color:#0099FF;}
            #main{ width: 850px; margin: 0px auto; }
            .top1{min-height: 80px;}
        </style>
    </head>
    <body>
        <div class="top1" ></div>
        <div id="main">
            <div id="content"><?php if($title): echo $title; else: echo '系统异常！'; endif; ?></div>
            <div id="wait_div">
                <p><?php echo $content; ?></p>
                <p><b id="wait"><?php echo($waitSecond); ?></b>&nbsp;秒之后，页面自动跳转到 &nbsp;&nbsp;<a id="href" href="<?php echo $url; ?>"><?php echo $urltitle ?></a></p>
            </div>
        </div>
        <script type="text/javascript">
            (function() {
                var wait = document.getElementById('wait'), href = document.getElementById('href').href;
                var interval = setInterval(function() {
                    var time = --wait.innerHTML;
                    if (time == 0) {
                        location.href = href;
                        clearInterval(interval);
                    }
                    ;
                }, 1000);
            })();
        </script>
    </body>
</html>
