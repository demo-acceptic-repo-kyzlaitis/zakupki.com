
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans; font-size: 10px; }
        .mainWidth{
            display: block;
            width: 700px;
            height: 870px;
            border: 2px solid #ccc;
            padding-left: 20px;

        }
        .headerM{
            width: 100%;
        }
        .img_style{
            width: 350px;
            height: 80px;
        }
        .Head_par1{
            display: inline-block;
        }
        .Head_par2{
            display: inline-block;
            margin-top: 30px;
            margin-bottom: 30px;
            margin-left: 5px;
        }
        .text_block {
            position: absolute;
            left: 420px;
            top: 50px;
            width: 300px;
        }

        a{

            color: #20558a;
            text-decoration: none;
        }
        .link_Zakupki{
            margin: 0px;
        }
        .text{

            margin: 0px;
        }
        .body_first{

            margin-bottom: 20px;
            position: absolute;
            left: 100px;
            width: 535px;

        }

        .tag1{

            display: inline-block;
            position: absolute;
        }
        .sec_section{
            display: inline-block;
            margin-left: 90px;

        }
        .header_text{
            position: absolute;
            top: 360px;
            left: 200px;
            width: 350px;
        }

        .inline{
            display: inline;
        }
        .margin0{
            margin: 0px;
        }

        .table_style{
            position: absolute;

            border: none;
            width: 660px;
            top: 420px;
        }
        .summury{
            position: absolute;

            top: 530px;
            width: 155px;
        }
        .stamp_img{
            width: 180px;
            height: 150px;
            margin-right: 60px;
            border: none;
        }
        .sign{
            margin-top:-65px;
            margin-right: 50px;
        }
        .stamp{
            position: absolute;
            top: 574px;
            left: 450px;
        }
        .name{
            margin-right: 50px;
        }
        .someInfo{
            position: absolute;
            margin-right: 75px;
            border: 2px solid black;
            padding: 15px;

            font-weight: 400;
            text-align: left;
            width: 560px;
            top: 750px;
            left: 30px;
        }
        .factor{
            font-size: 14px;
        }
        strong {
            font-size: 20px;
        }
    </style>
</head>
<body>
<div class="mainWidth">

    <div class="headerM">
        <div class="Head_par2">
            <img src="{{asset('i/logo.png')}}" alt="" class="img_style">
            <div class="text_block">
                <a href="http://zakupki.com.ua">Zakupki UA - Електронний майданчик учасник<br> системи публічних закупівель Prozorro.</a><br>
                <span class="text">Служба підтримки Zakupki UA: +38 (044) 303-93-82,<br> +38 (068) 492-14-28.</span>
            </div>
        </div>
    </div>

    <div class="body_first">
        <div class="first_section">
            <div class="tag1"><p class="margin0 left_part"><strong>Учасник:</strong></p></div>
            <div class="sec_section"><p class="inline">Постачальник: ТОВ "ЗАКУПІВЛІ ЮА"<br>
                    Ідентифікаційний код 40381929<br>
                    Адреса: 01133, м. Київ, бульв. Лесі Українки, буд. 5-А <br>
                    Р/р 26001210388672 відкритий в АТ "ПроКредит Банк" в м. Києві <br>
                    МФО 320984 <br>
                    Телефон 068-492-14-28 <br>
                    Платник єдиного податку. Не є платником ПДВ<br>
                </p>
            </div>
        </div> <br>
        <div class="second_section">
            <div class="tag1"><p class="margin0 left_part"><strong>Одержувач:</strong></p></div>
            <div class="sec_section"><p class="inline">{{$contacts['name']}}<br>
                    {{$contacts['phone']}}
                </p>
            </div>
        </div><br>
        <div class="third_section">
            <div class="tag1"><p class="margin0 left_part"><strong>Платник:</strong></p></div>
            <div class="sec_section"><p class="inline">Той самий
                </p>
            </div>
        </div><br>
        <div class="last_section">
            <div class="tag1"><p class="margin0 left_part"><strong>Замовлення:</strong></p></div>
            <div class="sec_section"><p class="inline">Без замовлення
                </p>
            </div>
        </div>
    </div>
    <center class="header_text">
        <strong class="factor">Рахунок-фактура № {{$order_id}}<br>від {{$date}} р.</strong>
    </center>
    <table class="table_style" border="0" width="100%" cellpadding="3" cellspacing="0">
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

    <div class="summury">
        Всього на суму:<br>
        <strong>{{$amount}} гривень</strong><br>
        Без ПДВ<br>
        <strong>{{$amountText}}</strong>
    </div>
    <div class="stamp">
        <img class="stamp_img" src="{{asset('i/stamp.png')}}">
        <p class="sign"><strong>Виписав(ла):________________________</strong></p>
        <p class="name"><strong>Директор Мандзюк В. В.</strong></p>
    </div>
    <p class="someInfo">
        <strong>УВАГА:</strong>
        У платіжному дорученні обов`язково необхідно вказати наступну інформацію: <br>
        <strong>Передоплата за інформаційно-консультаційні послуги на особовий рахунок №{{$user_id}}
            <br>
            згідно рахунку № {{$order_id}} від {{$date}} р. Без ПДВ.
        </strong>
    </p>

</div>

</body>
</html>