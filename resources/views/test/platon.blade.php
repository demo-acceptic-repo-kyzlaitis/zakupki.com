<?php

/* client's password */
$pass = 'a5qTsU2nVhhZuDrwV6jf2RAuLXYGwc0x';

$data['key']            = 'UPS1HBPGVJ';			// Client's KEY
$data['url']            = env('BASE_URL').'/platon/success';	// Return URL after success transaction

/* Prepare product data for coding */
$data['data']           = base64_encode(json_encode(array('amount' => '1.00','name' => 'Поповненя рахунку','currency' => 'UAH')));

$data['payment'] = 'CC';

/* Calculation of signature */
$sign = md5(strtoupper( strrev($data['key']).
        strrev($data['payment']).
        strrev($data['data']).
        strrev($data['url']).
        strrev($pass)
));

?>
        <!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Redirect</title>
</head>
<body>
<form action="https://secure.platononline.com/payment/auth" method="post">
    <input type="hidden" name="payment" value="<?=$data['payment']?>" />
    <input type="hidden" name="key" value="<?=$data['key']?>" />
    <input type="hidden" name="url" value="<?=$data['url']?>" />
    <input type="hidden" name="data" value="<?=$data['data']?>" />
    <input type="hidden" name="sign" value="<?=$sign?>" />
    <button type="submit">Пополнить счет на 1 грн.</button>
</form>
</body>
</html>
