{{$id}}
<pre>
<?php
        if (isset($time)) {
            echo 'time:'.$time."\n";

        }
        if (isset($user)) {
            echo 'user:'.$user."\n";
        }
    if (isset($error))
        echo var_export($error, true);
    if (isset($data))
        echo var_export($data, true);
?>
</pre>

@include('emails.footer-email-sign')