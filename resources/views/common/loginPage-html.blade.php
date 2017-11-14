<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name=”renderer” content=”webkit”>
    <title>找冻口网内部OA系统</title>
    <link rel="shortcut icon" href="data:image/x-icon;," type="image/x-icon">
   <style>
        *{margin:0;padding:0;font-weight:normal}html,body{height:100%}body{font-family:"Microsoft YaHei","Helvetica Neue",Helvetica,Arial,sans-serif;font-size:14px;line-height:1.42857143;color:#333}.container{height:100%;min-height:740px;background:url("../images/login-bg.jpg") no-repeat;background-size:100% 100%;position:relative}.form-signin{width:860px;padding:15px;position:absolute;left:50%;margin-left:-445px;top:50%;margin-top:-380px}header.logo-bar{text-align:center}.form-signin .form-signin-heading{position:relative;color:#cecdcd;margin:30px 0;font-size:30px}.form-signin .checkbox{margin-bottom:10px}.form-signin-heading .left{position:absolute;width:230px;left:0;top:50%;border-top:3px solid #cecdcd}.form-signin-heading .right{position:absolute;width:230px;right:0;top:50%;border-top:3px solid #cecdcd}.input-wrap{width:570px;margin:0 auto;background-color:rgba(255,255,255,0.4);-webkit-border-radius:8px;-moz-border-radius:8px;-ms-border-radius:8px;-o-border-radius:8px;border-radius:8px;overflow:hidden}.input-wrap .header{height:70px;line-height:70px;margin:0;border-bottom:1px solid rgba(204,204,204,0.6);border-left:9px solid #3c91f4;padding-left:15px;font-size:25px}.input-wrap .header span{transform:scale(2.5);display:inline-block}.input-content{padding:50px}.form-signin .form-control{position:relative;height:auto;width:340px;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;padding:21px;font-size:16px;-webkit-border-radius:6px;-moz-border-radius:6px;-ms-border-radius:6px;-o-border-radius:6px;border-radius:6px;border:0}.form-signin .form-control:focus{z-index:2}.form-signin-heading{text-align:center}.animated{-webkit-animation-duration:.6s;animation-duration:.6s;-webkit-animation-fill-mode:both;animation-fill-mode:both}@keyframes zoomIn{from{opacity:0;-webkit-transform:scale3d(.3,.3,.3);transform:scale3d(.3,.3,.3)}50%{opacity:1}}.zoomIn{-webkit-animation-name:zoomIn;animation-name:zoomIn}.form-group{margin-bottom:15px}.form-group:before{display:table;content:" "}.form-group label{float:left;width:90px;text-align:right;padding-top:16px;font-size:20px}.form-group .input-box{margin-bottom:30px}.loginBtn{width:443px;height:80px;color:#fff;font-size:25px;background-color:#3c91f4;-webkit-border-radius:6px;-moz-border-radius:6px;-ms-border-radius:6px;-o-border-radius:6px;border-radius:6px;border:0}.prompt{font-size:18px;margin:0 0 20px 120px;position:relative;display:none}.prompt:before{content:'';display:inline-block;width:23px;height:23px;background:url("../images/!.png") no-repeat;background-size:100%;position:absolute;left:-28px;top:1px}footer.copy{position:absolute;width:100%;left:0;bottom:0;height:50px;padding-top:10px;text-align:center;color:#fff}
    </style>
</head>
<body>
<div class="container">
    <form class="form-signin">
        <header class="logo-bar"><img src="images/login-logo.png" /></header>
        <h3 class="form-signin-heading"><i class="left"></i>找冻品网内部OA系统<i class="right"></i></h3>
        <div class="input-wrap">
            <h4 class="header">
                登录 <span>·</span> LOG IN
            </h4>
            <div class="input-content">
                <div class="form-group">
                    <label for="username">账户名：</label>
                    <div class="input-box">
                        <input type="text" id="username" class="form-control" placeholder="账户名"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">密码：</label>
                    <div class="input-box">
                        <input type="password" id="password" class="form-control" placeholder="密码"/>
                    </div>
                </div>
                <div id="prompt" class="prompt">密码错误！请重新填写</div>
                <button class="loginBtn" id="loginBtn">登录</button>
            </div>
        </div>
    </form>
    <footer class="copy">
        Copyright © 2015 - 2016 Zdongpin. All Rights Reserved. 成都信领科技有限公司 版权所有
    </footer>
</div> <!-- /container -->

</body>
<script type="application/javascript" src="/js/zdp-oa.min.js"></script>
<script type="application/javascript" src="/js/login.js"></script>
</html>