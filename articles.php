<?php
  if (isset($_GET["id"])){
    if (strlen($_GET["id"])>3){
      $_GET["id"] = substr($_GET["id"],0,3);
    } 
    $_GET["id"] = addslashes($_GET["id"]);
    $article_id = intval($_GET["id"]);
    if (!file_exists('articles/'.$article_id.'.php')){
      $article_id = 0;
    }
  } else {
    $article_id = 0;
  }
  $article_name= array(0=>'',
  	1=>'Как установить официальный кошелек Yenten (YTN) на Windows?',
  	2=>'Как купить Yenten (YTN) за Рубли?'
  );
?>
<!doctype html>
<html>

<head>
  <meta charset="UTF-8">
  <meta http-equiv="Content-type" content="text/html;charset=UTF-8">
  <meta http-equiv="Content-Language" content="ru">
  <meta name="robots" content="index">
  <?php 
    if ($article_id>0) {
        echo '<title>'.$article_name[$article_id].' - Статьи - yenten.top</title>';
        echo '<meta name="description" content="'.$article_name[$article_id].' - Статьи YENTEN (YTN)">';
    }else {
        echo '<title>Статьи - yenten.top</title>';
        echo '<meta name="description" content="Статьи YENTEN (YTN)">';
    }
  ?>
  <meta name="keywords" content="кран енотов, кран yenten coin, yenten faucet, кран йентенов, yenten, ytn faucet, двач кран енотов, 
  кран йентенов двача, криптовалюта, биткоин, догикоин, dogecoin, bitcoin, 2ch, харкач, анимекоин, animecoin, anime, cpu coin, cpu майнинг, 
  cpumining, cryptocurency, yespowerr16, ytn, elon musk, илон маск, двач пул, Official Dvach, самый известный кран Yenten, халява в интернете,
  криптовалюта для процессора, tesla, btc, blockchain, ethereum , eth, двач, карасик, майнинг, ltc, crane coin, bitcoin newscran, satoshi,
  binarium coin, как майниить криптовалюту, майнинг на процессоре, заработок в интернете, cryptocoin CPU-mining only, курс yenten, курс ytn,
  Lucky Pool, cpu pool, zerg pool, 2ch pool, yenten pool, rplant, supernova, aikapool, nlpool, mining dutch, zpool, yentencoin, ytn mining, 
  yespower, proof of work, yescrypt, sugarchain, cpuchain, goldchain, uraniumx, yenten roadmap, intel core, intel, amd, ryzen, threadripper,
  xeon, jayddee, conan, аниме тян, hashrate, электронные деньги, виртуальные деньги, криптокошелек, электронный кошелек, wallet, майнер, 
  bit coin talk, bits media, 2chpool, ентен, monero, рулетка, геймблинг, roll, gambling, YENTEN Official rus, yenten my waifu, yenten waifu, статьи yenten, статьи ytn, гайд крипта, гайд криптоваюта, гайд енот, гайд yenten, как yenten, как енотов, как ytn, где yenten, где ytn, сколько енотов, сколько yenten, сколько ytn, кошелек yenten, кошелек ytn, купить yenten, купить ytn">
  <link rel="stylesheet" href="bootstrap.min.css">
  <script src="jquery.min.js"></script>
  <script src="bootstrap.min.js"></script>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  <link rel="icon" href="favicon.ico">
  <link rel="canonical" href="http://yenten.top/"/>
  <meta name="viewport" content="width=device-width, initial-scale=0.57">
</head>

<body style="background:#eee;">

<?php
  require_once("server_config.php");  

  $last_thread_link = $GLOBALS["2CH_THREAD_LINK"];

  $db = mysqli_connect( $GLOBALS['MYSQL_HOST'].":".$GLOBALS['MYSQL_PORT'] , $GLOBALS['MYSQL_USER'] , $GLOBALS['MYSQL_PASSWORD'] );

  if ($db->connect_error) {
    error_log('(index.php) Ошибка подключения (' . $db->connect_errno . ') '. $db->connect_error);
  }
?>

<link rel="stylesheet" href="styles.css">


<?php include("menudiv.php");?>

<div class="container" role="main">
  <div class="jumbotron" style="padding-top:80px;padding-bottom: 0;margin-bottom: 10px;">
    
      <?php include ("title_logo.php")?>
    
  </div>

  <div class="article_block" style="margin-bottom: 15px;margin-top: 20px;" align="center">
    <?php 
      if ($article_id == 0){
        include ('article_list.php');
      } else {
        include ('articles/'.$article_id.'.php');
        echo '<a href="articles.php"> < Назад к списку статей</a>';
      }
    ?>
  </div>

<script>
  $("#logo").addClass("hidden");
  $("#loading").removeClass("hidden");
</script>

</div>

<h5 align="center" style="margin:0;">
  <a href="https://github.com/ChervyachokMigo/2ch-yenten-faucet" target="_blank" title="Исходники">Кран на github</a> (<?php echo date("Y"); ?>) 
</h5>

<h5 align="center">
  <a href="mailto:yenten.top@gmail.com">Для пожеланий и предложений</a>
</h5>

<script src="faucet.js"></script>

<?php if ($db) mysqli_close ($db) ?>

</body>

</html>
