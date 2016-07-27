<nav class="navbar navbar-default navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="http://<?=$_SERVER['HTTP_HOST']?>">База заказов</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <li><a href="http://<?=$_SERVER['HTTP_HOST']?>/specialisti/list.php">Список специалистов</a></li>
                <li><a href="http://<?=$_SERVER['HTTP_HOST']?>/orders/index.php">Список заказов</a></li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Еще<span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li class="dropdown-header">БД заказов</li>

                        <li><a href="http://<?=$_SERVER['HTTP_HOST']?>/orders/index.php">Список заказов</a></li>
                        <li><a>Открыть заказ
                                <br>
                                <div class="input-group" style="margin: 7px 0 2px 0;">
                                    <input type="text" name="id_order_for_open" style=" padding: 5px 10px; font-size: 14px" placeholder="ID заказа" class="form-control">
                                        <span class="input-group-btn">
                                            <button class="btn btn-default" type="button" style="font-size: 14px; padding: 6px 8px;" onclick="window.location.href='http://baza-remontprofi.ru/orders/view_order.php?id='+$('input[name=\'id_order_for_open\']').val();">открыть</button>
                                          </span>
                                </div>
                                </a>

                        </li>
                        <li><a href="http://<?=$_SERVER['HTTP_HOST']?>/orders/new.php">Добавить заказ</a></li>

                        <li role="separator" class="divider"></li>

                        <li class="dropdown-header">БД специалистов</li>

                        <li><a href="http://<?=$_SERVER['HTTP_HOST']?>/specialisti/list.php">Список специалистов</a></li>
                        <li><a href="http://<?=$_SERVER['HTTP_HOST']?>/specialisti/working_time.php">Занятость специалистов</a></li>
                        <li><a href="http://<?=$_SERVER['HTTP_HOST']?>/specialisti/add_specialist.php">Добавить специалиста</a></li>

                        <li role="separator" class="divider"></li>

                        <li class="dropdown-header">БД объявлений</li>

                        <li><a href="http://<?=$_SERVER['HTTP_HOST']?>/ads/index.php">Список объявлений</a></li>
                        <li><a href="http://<?=$_SERVER['HTTP_HOST']?>/ads/add_ads.php">Добавить объявление</a></li>

                        <li role="separator" class="divider"></li>

                        <li class="dropdown-header">БД звонков</li>

                        <li><a href="http://<?=$_SERVER['HTTP_HOST']?>/calls/index.php">Список звонков</a></li>
                    </ul>
                </li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?=$user_info['name'].' '.$user_info['lastname']?><span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="#">Профиль</a></li>
                        <li><a href="#">Статистика</a></li>
                        <li><a href="http://<?=$_SERVER['HTTP_HOST']?>/inc/work/logout.php">Выход</a></li>
                    </ul>
                </li>
                <li style="background: #5cb85c;"><a href="http://<?=$_SERVER['HTTP_HOST']?>/orders/new_call.php" target="_blank" style="color: #fff;">Добавить звонок</a></li>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>