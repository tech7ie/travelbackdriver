<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<div class="header">
    <div class="wrapper">
        <div class="icon">
            <img src="https://api.drivermytripline.com/icons/applogo.svg" alt="logo">
        </div>
    </div>
</div>
<div class="main">
    <div class="wrapper">
        <div class="main__inner">
            <div class="title">
                <h2>
                    Ride offer from {{ $data->route->fromCity->name }} to {{ $data->route->toCity->name }}
                    <br/>
                    on {{ $data->route_date }}
                </h2>
                <br/>
            </div>
            <div class="content">
                <span>Dear Partner {{ $data->user->profile->first_name }},</span>
                <br/>
                <br/>
                <span>Thank you for confirming this ride. Please make the following trip.</span>
            </div>
            <br/>
            <br/>
            <div class="offer">
                <div class="offer__item">
                    <div class="offer__item_name">
                        <span>Date</span>
                    </div>
                    <div class="offer__item_content">
                        <span>{{ $data->route_date }}</span>
                    </div>
                </div>
                <hr/>
                <div class="offer__item">
                    <div class="offer__item_name">
                        <span>Booking number</span>
                    </div>
                    <div class="offer__item_content">
                        <span>{{ $data->id }}</span>
                    </div>
                </div>
                <div class="offer__item">
                    <div class="offer__item_name">
                        <span>From</span>
                    </div>
                    <div class="offer__item_content">
                        <span>{{ $data->route->fromCity->name }}</span>
                    </div>
                </div>
                <div class="offer__item">
                    <div class="offer__item_name">
                        <span>To</span>
                    </div>
                    <div class="offer__item_content">
                        <span>{{ $data->route->toCity->name }}</span>
                    </div>
                </div>
                <div class="offer__item">
                    <div class="offer__item_name">
                        <span>Pickup location  </span>
                    </div>
                    <div class="offer__item_content">
                        <span>{{ $data->pickup_address }}</span>
                    </div>
                </div>
                <div class="offer__item">
                    <div class="offer__item_name">
                        <span>Dropoff location</span>
                    </div>
                    <div class="offer__item_content">
                        <span>{{ $data->drop_off_address }}</span>
                    </div>
                </div>
                @if ($data->places->count())
                <div class="offer__item">
                    <div class="offer__item_name">
                        <span>Stops</span>
                    </div>
                    <div class="offer__item_content">
                        <ul>
                            @foreach($data->places as $place)
                                <li>{{ $place->title_en }} {{ $place->durations }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif
                <hr/>
                <div class="offer__items line">
                    <div class="offer__item">
                        <div class="offer__item_name">
                            <span>Passengers</span>
                        </div>
                        <div class="offer__item_content">
                            <span>{{ $data->adults }}</span>
                        </div>
                    </div>
                    <div class="offer__item">
                        <div class="offer__item_name">
                            <span>Childrens </span>
                        </div>
                        <div class="offer__item_content">
                            <span>{{ $data->childrens }}</span>
                        </div>
                    </div>
                    <div class="offer__item">
                        <div class="offer__item_name">
                            <span>Luggage</span>
                        </div>
                        <div class="offer__item_content">
                            <span>{{ $data->luggage }}</span>
                        </div>
                    </div>
                </div>
                <hr/>
                @if ($data->user->role_id !== 5)
                <div class="offer__item">
                    <!--ЕСЛИ РОЛЬ НЕ DRIVER-->
                    <div class="offer__item_name">
                        <span>Price</span>
                    </div>
                    <div class="offer__item_content">
                        <span>{{ $data->amount }}€</span>
                    </div>
                </div>
                @endif
                <div class="offer__item">
                    <div class="offer__item_name">
                        <span>Payout status</span>
                    </div>
                    @if ($data->payment_type == 2)
                    <div class="offer__item_content">
                        <span>Paid out - cash</span>
                    </div>
                    @endif
                    @if ($data->payment_type == 1)
                        <div class="offer__item_content">
                            <span>Paid out - online</span>
                        </div>
                    @endif
                </div>
                <div class="offer__item">
                    <div class="offer__item_name">
                        <span>Service class</span>
                    </div>
                    <div class="offer__item_content">
                        <span>{{ $data->getCars[0]->title }}</span>
                    </div>
                </div>
                @if ($data->comment !== '' && $data->comment !== null)
                <div class="offer__item">
                    <!--ЕСЛИ ЕСТЬ-->
                    <div class="offer__item_name">
                        <span>Customer comment</span>
                    </div>
                    <div class="offer__item_content">
                        <span>{{ $data->comment }}</span>
                    </div>
                </div>
                @endif
            </div>
            <div class="text">
                <p>Be sure to double-check the ride information, which can also be found in the MYTRIPLINE driver app.</p>
                <br/>
                <p>Attached to this email is a collection label in pdf format with the customer's name.</p>
                <br/>
                <p style="font-weight: 600">It's a pleasure to work together.</p>
                <br/>
                <p style="color: #757575;">This is an automated email. If you have any questions or concerns, we're here to help. Contact us via our Help Center.</p>
                <br/>
                <br/>
            </div>
        </div>
    </div>
</div>
<div class="footer">
    <div class="wrapper">
        <div class="footer__inner">
            <hr/>
            <div class="footer__title">
                <h2>MYTRIPLINE</h2>
            </div>
            <div class="footer__subtitle">
                <span style="color: #757575;">Every detail matters,although detail is just a word.</span>
            </div>
            <div class="footer__icons">
                <img src="https://api.drivermytripline.com/icons/inst.svg" alt="link">
                <img src="https://api.drivermytripline.com/icons/Facebook.svg" alt="link">
                <img src="https://api.drivermytripline.com/icons/youtube.svg" alt="link">
                <img src="https://api.drivermytripline.com/icons/Watsapp.svg" alt="link">
            </div>
            <div class="footer__info">
                <span>www.mytripline.com</span>
                <br/>
                <span>Vojtěšská 211/6,  Nové Město, 110 00 Prague</span>
                <br/>
                <span>©2023 All rights reserved.</span>
            </div>
        </div>
    </div>
</div>
</body>
<style>
    @font-face {
        font-family: 'Inter';
        src: local('Inter'), url("https://api.drivermytripline.com/fonts/Inter-Regular.ttf") format('truetype');
    }
    *{
        padding: 0;
        margin: 0;
    }
    h2,h3,p,span,li{
        font-family: 'Inter', sans-serif;
    }
    .wrapper{
        width: 90%;
        margin: 0 auto;
    }
    .header{
        margin: 40px 0 24px 0;
    }
    .offer{
        border-radius: 17px;
        background: rgba(241, 188, 124, 0.47);
        padding: 20px;
        margin-bottom: 30px;
    }
    .offer__item:not(:last-child){
        margin-bottom: 10px;
    }
    .offer__item_name{
        font-style: normal;
        font-weight: 500;
        font-size: 11px;
        display: flex;
        align-items: center;
        letter-spacing: -0.02em;
        color: #1969A9;
        margin-bottom: 4px;
    }
    .offer__item_content{
        font-style: normal;
        font-weight: 500;
        font-size: 12px;
        line-height: 20px;
        letter-spacing: -0.02em;
        color: rgba(0, 0, 0, 0.9);
    }
    .offer hr{
        border: none;
        height: 1px;
        background: #1969A9;
        margin-bottom: 10px;
    }
    .offer li{
        margin-left: 12px;
    }
    .line{
        width: 80%;
        display: flex;
        justify-content: space-between;
    }
    .title h2{
        font-style: normal;
        font-weight: 600;
        font-size: 24px;
        line-height: 38px;
        letter-spacing: -0.05em;
    }
    .content p,.content span{
        font-style: normal;
        font-weight: 500;
        font-size: 16px;
        letter-spacing: -0.02em;
    }
    .footer{
        margin-bottom: 40px;
    }
    .footer hr{
        margin-bottom: 20px;
    }
    .footer__title{
        text-align: center;
        margin-bottom: 10px;
    }
    .footer__info{
        text-align: center;
    }
    .footer__info span{
        line-height: 28px;
    }
    .footer__icons{
        display: flex;
        justify-content: center;
        margin: 20px 0;
    }
    .footer__icons img:not(:last-child){
        margin-right: 12px;
    }
</style>
</html>
