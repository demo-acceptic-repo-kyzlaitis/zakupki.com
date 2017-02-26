<head>
<style>
    .btn-danger {
        color: #fff;
        background-color: #d9534f;
        border-color: #d43f3a;
        margin-left: 250px;
    }

    .btn {
        display: inline-block;
        padding: 6px 12px;
        margin-bottom: 0;
        font-size: 14px;
        font-weight: 400;
        line-height: 1.42857143;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
        -ms-touch-action: manipulation;
        touch-action: manipulation;
        cursor: pointer;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        background-image: none;
        border: 1px solid transparent;
        border-radius: 4px;
    }

    a {
        color: #337ab7;
        text-decoration: none;
    }
</style>
</head>
<body>
<table border="0" cellpadding="2" cellspacing="0"
       style="width:700px; font-family: arial; font-size: 12px; page-break-after:always; display: block; visibility: visible; border: 2px solid #ccc;">
    <tbody>
    <tr>
        <td>
            <table width="100%" style="padding-left:20px; padding-right: 20px;">
                <tbody>
                <tr>
                    <td align="center">
                        <img src="/i/logo.png" alt="ТендерГид™" style="border: none; width: 350px; height: 80px;">
                    </td>
                    <td style="font-family: Arial; font-size: 11px; padding-left: 30px;">
                        <a href="http://zakupki.com.ua" style="font-family: Calibri; font-size: 13px; color: #20558a; ">Zakupki
                            UA - Електронний майданчик учасник системи публічних закупівель Prozorro. </a>
                        <br>
                        Служба підтримки Zakupki UA: +38 (044) 303-93-82, +38 (068) 492-14-28.
                    </td>
                </tr>
                </tbody>
            </table>

            <br>
        </td>
    </tr>


                <tr>
                    <td style="padding-top: 20px; padding-left: 70px; padding-right: 70px; padding-bottom: 30px;">
                        <table border="0" cellpadding="3" cellspacing="0" style="font-family: arial; font-size: 12px; ">
                            <tbody>
                            <tr>
                                <td valign="top" align="left"><strong
                                            style="text-decoration: underline;">Учасник:</strong>
                                </td>
                                <td valign="top" align="left">Постачальник: ТОВ "ЗАКУПІВЛІ ЮА"
                                    <br> Ідентифікаційний код 40381929
                                    <br>Адреса: 01133, м. Київ, бульв. Лесі Українки, буд. 5-А
                                    <br>Р/р 26001210388672 відкритий в АТ "ПроКредит Банк" в м. Києві
                                    <br>МФО 320984
                                    <br>Телефон 068-492-14-28
                                    <br>Платник єдиного податку. Не є платником ПДВ
                                </td>
                            </tr>
                            <tr>
                                <td valign="top" align="left"><strong
                                            style="text-decoration: underline;">Одержувач:</strong>
                                </td>
                                <td valign="top" align="left">{{$contacts['name']}}<br>
                                    {{$contacts['phone']}}
                                </td>
                            </tr>
                            <tr>
                                <td valign="top" align="left"><strong
                                            style="text-decoration: underline;">Платник:</strong>
                                </td>
                                <td valign="top" align="left">Той самий
                                </td>
                            </tr>
                            <tr>
                                <td valign="top" align="left"><strong
                                            style="text-decoration: underline;">Замовлення:</strong>
                                </td>
                                <td valign="top" align="left">Без замовлення
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>


                <tr>
                    <td style="padding-left: 20px; padding-right: 20px; ">
                        <center>
                            <strong style="font-size: 18px; font-family: arial;">Рахунок-фактура № {{$order_id}}
                                <br>від {{$date}} р.
                            </strong>
                        </center>
                        <br>

                        <table border="0" width="100%" cellpadding="3" cellspacing="0"
                               style="font-family: arial; font-size: 12px; border: none;">
                            <tbody>
                            <tr>
                                <td style="background-color: #e7e6e6; width:20px; border-left: 1px solid black; border-right: 1px solid black; border-top: 1px solid black;"
                                    align="center"><strong>N</strong>
                                </td>
                                <td style="background-color: #e7e6e6;  border-right: 1px solid black; border-top: 1px solid black;;"
                                    align="center"><strong>Назва</strong>
                                </td>
                                <td style="background-color: #e7e6e6; border-right: 1px solid black; border-top: 1px solid black;; width:20px;"
                                    align="center"><strong>Одиниця виміру</strong>
                                </td>
                                <td style="background-color: #e7e6e6; border-right: 1px solid black; border-top: 1px solid black;; width: 100px;"
                                    align="center"><strong>Кількість</strong>
                                </td>
                                <td style="background-color: #e7e6e6; border-right: 1px solid black; border-top: 1px solid black;; width:90px;"
                                    align="center"><strong>Ціна без ПДВ</strong>
                                </td>
                                <td style="background-color: #e7e6e6; border-right: 1px solid black; border-top: 1px solid black;; width:90px;"
                                    align="center"><strong>Сума без ПДВ</strong>
                                </td>
                            </tr>
                            <tr>
                                <td align="center"
                                    style="border-left: 1px solid black; border-right: 1px solid black; border-top: 1px solid black; border-bottom: 1px solid black;">
                                    1
                                </td>
                                <td align="center"
                                    style=" border-right: 1px solid black; border-top: 1px solid black; border-bottom: 1px solid black; text-align: left;">
                                    Інформаційно-консультаційні послуги
                                </td>
                                <td align="center"
                                    style=" border-right: 1px solid black; border-top: 1px solid black; border-bottom: 1px solid black;">
                                    посл.
                                </td>
                                <td align="center"
                                    style=" border-right: 1px solid black; border-top: 1px solid black; border-bottom: 1px solid black;">
                                    1
                                </td>
                                <td align="center"
                                    style=" border-right: 1px solid black; border-top: 1px solid black; border-bottom: 1px solid black;">{{$amount}}
                                </td>
                                <td align="center"
                                    style=" border-right: 1px solid black; border-top: 1px solid black; border-bottom: 1px solid black;">{{$amount}}
                                </td>
                            </tr>
                            <tr>
                                <td align="center" style="border: none;">
                                </td>
                                <td align="center">
                                </td>
                                <td align="center">
                                </td>
                                <td align="center">
                                </td>
                                <td align="center" style="  border-right: 1px solid black;"><strong>Разом до
                                        сплати:</strong>
                                </td>
                                <td align="center"
                                    style=" border-right: 1px solid black;  border-bottom: 1px solid black;">{{$amount}}
                                </td>
                            </tr>
                            </tbody>
                        </table>

                        <br>
                    </td>
                </tr>


                <tr>
                    <td style="padding-left: 20px; padding-right: 20px; font-family: arial; font-size: 12px; ">
                        Всього на суму:<br>
                        <strong>{{$amount}} гривень</strong><br>
                        Без ПДВ<br>
                        <strong>{{$amountText}}</strong>
                    </td>
                </tr>


                <tr>
                    <td style="padding-left: 20px; padding-right: 20px; font-family: arial; font-size: 14px; font-weight:bold;"
                        align="right">
                        <br>
                        <br>
                        <img src="/i/stamp.png" width="180px" height="150" style="margin-right:20px;"
                             style="border: none;">

                        <p class="sign" style="margin-top:-65px;">Виписав(ла):________________________</p>

                        <p>Директор Мандзюк В. В.</p>
                        <br>
                        <br>
                        <br>
                        <br>

                        <p style=" margin-right: 20px;  margin-left: 20px; border: 2px solid black; padding: 15px; font-family: arial; font-size: 12px; font-weight: 400; text-align: left; ">
                            <strong>УВАГА:</strong>
                            У платіжному дорученні обов`язково необхідно вказати наступну інформацію: <br>
                            <strong>Передоплата за інформаційно-консультаційні послуги на особовий рахунок №{{$user_id}}
                                <br>
                                згідно рахунку № {{$order_id}} від {{$date}} р. Без ПДВ.
                            </strong>
                        </p>
                        <br>
                        <br>
                    </td>
                </tr>

                </tbody></table>
</div>
            <a href="javascript:" onclick="print(); ga('send', 'event', 'behavior', 'getBlank');" class="btn btn-danger">{{Lang::get('keys.print')}}</a>
</body>
</html>