<!DOCTYPE html>
<html>
    <head>
        <title>Error!</title>
    </head>
    <body>
        <div class="container">
            <div class="content">
                <div class="title">出错了</div>
                <div class="code">{{ $code }}</div>
                <div class="message">{{ $message }}</div>
                @if ( ! empty($exception))
                <div class="exception">{{ json_encode($exception) }}</div>
                @endif
            </div>
        </div>
    </body>
</html>
