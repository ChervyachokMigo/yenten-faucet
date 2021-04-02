<?php
  require_once("server_config.php");  

  $last_thread_link = $GLOBALS["2CH_THREAD_LINK"];

?>

<div class="navbar  navbar-fixed-top faucet-nav" role="navigation">
  <div class="headcontainer1">

    <div class="navbar-header" title="Пососать">
      <a class="navbar-brand" href="/">
        <img src="logo.png" class="icon_brand" border="0" />
      <img src="logo_hover.png" class="icon_brand" border="0" />        
      Двач кран енотов
      </a>
    </div>

          <div class="btn-group">
             <a class="btn-head btn btn-secondary dropdown-toggle" title="Перейти на Двач пул" href="https://2chpool.cc/getting_started"><img src="2ch.png" class="icon_2ch">Двач пул</a>
           </div>

           <div class="btn-group">
             <a class="btn-head btn btn-secondary dropdown-toggle" title="Почитать" href="articles.php">Статьи</a>
           </div>

          <div class="btn-group ">
            <button class="btn-head btn btn-secondary dropdown-toggle" title="Раскрыть категорию" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Йентен</button>
            <div class="dropdown-menu">
              <a class="dropdown-item list-group-item list-group-item-action " href="https://yentencoin.info/">Офф сайт</a>
              <a class="dropdown-item list-group-item list-group-item-action " href="https://miningpoolstats.stream/yenten">Браузер пулов</a>
              <a class="dropdown-item list-group-item list-group-item-action " href="https://ytn.ccore.online/">Браузер монеты</a>
              <a class="dropdown-item list-group-item list-group-item-action "  href="http://explorer.yentencoin.info/info">Старейший браузер</a>
              <a class="dropdown-item list-group-item list-group-item-action " href="https://www.coingecko.com/ru/%D0%9A%D1%80%D0%B8%D0%BF%D1%82%D0%BE%D0%B2%D0%B0%D0%BB%D1%8E%D1%82%D1%8B/yenten">Курс</a>
              <a class="dropdown-item list-group-item list-group-item-action " href="https://crex24.com/ru/exchange/YTN-BTC">Обмен (Crex24)</a>
              <a class="dropdown-item list-group-item list-group-item-action " href="https://graviex.net/markets/ytnbtc">Обмен (Graviex)</a>
            </div>
          </div>


          <div class="btn-group">
            <button class="btn-head btn btn-secondary dropdown-toggle" title="Раскрыть категорию" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Кошелек</button>
            <div class="dropdown-menu">
              <a class="dropdown-item list-group-item list-group-item-action " href="https://github.com/yentencoin/yenten/releases/">Официальный</a>
              <a class="dropdown-item list-group-item list-group-item-action " href="https://github.com/ChervyachokMigo/YENTEN-WALLET-EASY-INSTALLER/releases">Легкая установка</a>
              <a class="dropdown-item list-group-item list-group-item-action " href="https://github.com/ChervyachokMigo/YENTEN-WALLET-EASY-INSTALLER/releases/download/bootstrap/bootstrap.rar">Бутстрап (блокчейн)</a>
            </div>
          </div>

          <div class="btn-group">
            <button class="btn-head btn btn-secondary dropdown-toggle" title="Раскрыть категорию" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Майнер</button>
            <div class="dropdown-menu">
              <a class="dropdown-item list-group-item list-group-item-action" href="https://github.com/JayDDee/cpuminer-opt/releases/">CPU-Miner от JayDDee</a>
              <a class="dropdown-item list-group-item list-group-item-action" href="https://github.com/ChervyachokMigo/YENTEN-2ch-CPUMINER-BATCH-CONFFIGURER/releases">GUI Майнер (Конфигуратор)</a>
              <a class="dropdown-item list-group-item list-group-item-action" href="https://github.com/ChervyachokMigo/GoogleShellCloud">На Гугл Консоли</a>
            </div>
          </div>
          <div class="btn-group">
            <button class="btn-head btn btn-secondary dropdown-toggle" title="Раскрыть категорию" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Общение</button>
            <div class="dropdown-menu">
              <a class="dropdown-item list-group-item list-group-item-action" href="https://t.me/joinchat/IvO17wh-OzQ6qvbH">Телеграм конфа</a>
              <a class="dropdown-item list-group-item list-group-item-action" href="https://discord.gg/APMmzZ9uYw">Дискорд</a>
              <a class="dropdown-item list-group-item list-group-item-action" href=<?php echo '"' . $last_thread_link . '"' ;?> >Двач тред в /cc/</a>
              <a class="dropdown-item list-group-item list-group-item-action" href="https://bitcointalk.org/index.php?topic=5098631" >Тред на Бит-коин-толк</a>
              <a class="dropdown-item list-group-item list-group-item-action" href="https://forum.bits.media/index.php?/topic/61231-ytn-cpu-mining-yenten-v310-yespowerr16/" >Тред на Битс-медиа</a>
            </div>
          </div>       

  </div>
</div>