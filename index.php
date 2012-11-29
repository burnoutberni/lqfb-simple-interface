<?
$api_key = $_POST["api_key"];
$logout = $_GET["logout"];

if($_GET["my_initiative"] == "true"){
	setcookie("my_initiative", "true");
} elseif($_GET["my_initiative"] == "false"){
	setcookie("my_initiative", "false");
}

if($logout == "true"){
	setcookie("session_key", "");
	$session_key = "";
}
else{
	$session_key = $_COOKIE["session_key"];
}

if($session_key == ""){
	$host = '88.198.24.116';
	$path = '/session';
	$data = 'key='.urlencode($api_key);
	$fp = fsockopen($host, 25520, $errno, $errstr, 30);
	if (!$fp) {
		$buffer .= "$errstr ($errno)<br />\n";
	} else {
		$out = "POST ".$path." HTTP/1.1\r\n";
		$out .= "Host: ".$host."\r\n";
		$out .= "Content-Type: application/x-www-form-urlencoded; charset=utf-8\r\n";
		$out .= "Content-Length: ".strlen($data)."\r\n";
		$out .= "Connection: Close\r\n\r\n";
		$out .= $data;
		fwrite($fp, $out);
 
		while (!feof($fp)) {
			$buffer .= fgets($fp, 128);
		}
		fclose($fp);
	}
	$session_post = explode("\"session_key\":\"",$buffer);
	$session_post1 = explode("\",\"status\":",$session_post[1]);
	$session_key = $session_post1[0];
}

setcookie("session_key", $session_key);

$base_url = "http://88.198.24.116:25520/";

//issue_state wird aus der URI erhoben, wenn leer, dann voting
$issue_state = $_GET["issue_state"];
if($issue_state == ""){
	$issue_state = "open";
}

//area_id wird aus der URI erhoben, wenn leer, dann bisherige area_id
if($_GET["area_id"] != ""){
	$area_id = $_GET["area_id"];
}

//wenn area_id leer, dann kein area-Filter
if($area_id != ""){
	$area_url = "&area_id=" . $area_id;
}



//Initiativen werden aus der API gezogen, JSON => array
$url_initiative = $base_url . "initiative?session_key=" . $session_key . "&limit=1000&issue_state=" . $issue_state . "&initiative_eligible=true" . $area_url;
$string = file_get_contents($url_initiative);
$json = json_decode($string,true);
for ($i = 0; $i < count($json['result']); $i++) {
	$issue[$i][0] = $json['result'][$i]['issue_id'];
	$issue[$i][1] = $json['result'][$i]['name'];
	$issue[$i][2] = $json['result'][$i]['id'];
}

//Themenbereiche werden aus der API gezogen, JSON => array
$url_area = $base_url . "area?session_key=" . $session_key . "&unit_id=1";
$string_area = file_get_contents($url_area);
$json_area = json_decode($string_area,true);
for ($i = 0; $i < count($json_area['result']); $i++) {
	$issue_area[$i][0] = $json_area['result'][$i]['id'];
	$issue_area[$i][1] = $json_area['result'][$i]['name'];
}

//User-Liste werden aus der API gezogen, JSON => array
$url_member = $base_url . "member?session_key=" . $session_key;
$string_member = file_get_contents($url_member);
$json_member = json_decode($string_member,true);
for ($i = 0; $i < count($json_member['result']); $i++) {
	$issue_member[$i][0] = $json_member['result'][$i]['id'];
	$issue_member[$i][1] = $json_member['result'][$i]['name'];
}

//Session-Infos werden aus der API gezogen
//ID des derzeitigen Nutzers: JSON => string
$url_info = $base_url . "info?session_key=" . $session_key;
$string_info = file_get_contents($url_info);
$json_info = json_decode($string_info,true);
$current_member_id = $json_info['current_member_id'];

//Name des derzeitigen Nutzers wird gesucht (und hoffentlich gefunden ;) )
for ($i = 0; $i < count($issue_member); $i++) {
	if($issue_member[$i][0] == $current_member_id){
		$current_member_name = $issue_member[$i][1];
	}
}

$url_my = $base_url . "initiator?session_key=" . $session_key . "&member_id=" . $current_member_id;
$string_my = file_get_contents($url_my);
$json_my = json_decode($string_my,true);
for ($i = 0; $i < count($json_my['result']); $i++) {
	$issue_my[] = $json_my['result'][$i]['initiative_id'];
}

//Session-Infos werden aus der API gezogen
//ID des derzeitigen Nutzers: JSON => string
$url_image = $base_url . "member_image?session_key=" . $session_key . "&member_id=" . $current_member_id;
$string_image = file_get_contents($url_image);
$json_image = json_decode($string_image,true);
$current_member_image = $json_image['result'][0]['data'];

//Custom Sortierfunktion
function cmp($a,$b){
    //get which string is less or 0 if both are the same
    $cmp = strcasecmp($a[0], $b[0]);
    //if the strings are the same, check name
    if($cmp == 0){
        //compare the name
        $cmp = strcasecmp($a[2], $b[2]);
    }
    return $cmp;
}

//Initiativen werden nach Themen sortiert
usort($issue, 'cmp');
usort($issue_my, 'cmp');
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>LiquidFeedback</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Testing Interface powered by LiquidFeedback-APIs">
    <meta name="author" content="Bernhard Hayden">

    <!-- Le styles -->
    <link href="css/bootstrap.css" rel="stylesheet">
    <style type="text/css">
      body {
				background: url('../img/bg-tags-at.png') repeat;
        padding-top: 60px;
        padding-bottom: 40px;
      }
      .sidebar-nav {
        padding: 9px 0;
      }
			#footer {
				background: white;
			}
    </style>
    <link href="css/bootstrap-responsive.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Fav and touch icons
    <link rel="shortcut icon" href="ico/favicon.ico">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="ico/apple-touch-icon-57-precomposed.png">-->
  </head>

  <body>

		<?include 'navbar.php';?>

    <div class="container-fluid">
      <div class="row-fluid">

				<?include 'sidebar.php';?>

        <div class="span10">
          <div class="alert alert-block alert-info">
						<button type="button" class="close" data-dismiss="alert">×</button>
            <h1>Hallo, <?if($session_key != ""){echo $current_member_name;} else{echo "bitte melde dich an";}?>!</h1>
            <p>Du befindest dich in einer der ersten Alpha-Versionen des "Simple Interface" für die LiquidFeedback Instanz der Piratenpartei Österreichs.</p>
						<p>Dein Session-Key lautet: <?echo $session_key;?></p>
						<p>Deine Member-Id lautet: <?echo $current_member_id;?></p>
          </div>
					<div>
								<div class="btn-group">
    							<a class="btn btn-inverse dropdown-toggle" data-toggle="dropdown" href="#">
<?
//Derzeitiger Themenbereich wird ausgegeben
for ($i = 0; $i < count($issue_area); $i++) {
	if($area_id == $issue_area[$i][0]){
		echo $issue_area[$i][1] . " ";
	}
}
if($area_id == ""){
	echo "Alle Themenbereiche ";
}
?>
<span class="caret"></span></a>
    							<ul class="dropdown-menu">
										<li><a href="index.php?issue_state=<?echo $issue_state;?>">Alle Themenbereiche</a></li>
<?
//Alle Themenbereiche werden aufgelistet
for ($i = 0; $i < count($issue_area); $i++) {
	echo "<li><a href=\"index.php?issue_state=";
	echo $issue_state;
	echo "&area_id=";
	echo $issue_area[$i][0];
	echo "\">";
	echo $issue_area[$i][1];
	echo "</a></li>";
}
?>
    							</ul>
								</div>
								<div class="btn-group">
									<a class="btn" href="index.php?issue_state=open">Alle offenen Initiativen</a>
									<a class="btn btn-danger" href="index.php?issue_state=admission<?echo $area_url;?>">Neu</a>
									<a class="btn btn-warning" href="index.php?issue_state=discussion<?echo $area_url;?>">Diskussion</a>
									<a class="btn btn-info" href="index.php?issue_state=verification<?echo $area_url;?>">Eingefroren</a>
									<a class="btn btn-success" href="index.php?issue_state=voting<?echo $area_url;?>">Abstimmung</a>
								</div>
					</div>
          <div class="well sidebar-nav">
            <ul class="nav nav-list">
							<li>
<?
for ($i = 0; $i < count($issue); $i++) {
	if($_COOKIE["my_initiative"] == "true"){
		if(in_array($issue[$i][2], $issue_my) == "true"){
			goto c;
		} else{
			goto d;
		}
	}
	c:
	if($i != 0){
		$iless = $i - 1;
		if($issue[$iless][0] == $issue[$i][0]){
			echo "<li><a href=\"https://lqfb.piratenpartei.at/initiative/show/";
			echo $issue[$i][2];
			echo ".html\">i";
			echo $issue[$i][2];
			echo ": ";
			echo $issue[$i][1];
			echo "</a></li>";
		}
		else{
		echo "<li class=\"nav-header\">Thema: ";
		echo $issue[$i][0];
		echo "<li><a href=\"https://lqfb.piratenpartei.at/initiative/show/";
		echo $issue[$i][2];
		echo ".html\">i";
		echo $issue[$i][2];
		echo ": ";
		echo $issue[$i][1];
		echo "</a></li>";
		}
	}
	else{
		echo "<li class=\"nav-header\">Thema: ";
		echo $issue[$i][0];
		echo "</li><li><a href=\"https://lqfb.piratenpartei.at/initiative/show/";
		echo $issue[$i][2];
		echo ".html\">i";
		echo $issue[$i][2];
		echo ": ";
		echo $issue[$i][1];
		echo "</a></li>";
	}
	d:
}
echo "<li class=\"nav-header\">Keine weiteren Inhalte in diesem Themenbereich enthalten!</li></ul></li>";
?>
            </ul>
          </div><!--/.well -->
        </div><!--/span-->
      </div><!--/row-->
		</div><!--/.fluid-container-->
		<div id="footer" style="margin:0px;">
      <hr>

      <footer>
        <p>Eine kleine Spielerei von Bernhard <a href="http://wiki.piratenpartei.at/wiki/Benutzer:Burnoutberni">'burnoutberni'</a> Hayden.</p>
				<p>Datenquelle: <a href="http://lqfb.piratenpartei.at">http://lqfb.piratenpartei.at</a></p>
      </footer>

    </div>

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="js/jquery.js"></script>
    <script src="js/bootstrap-transition.js"></script>
    <script src="js/bootstrap-alert.js"></script>
    <script src="js/bootstrap-modal.js"></script>
    <script src="js/bootstrap-dropdown.js"></script>
    <script src="js/bootstrap-scrollspy.js"></script>
    <script src="js/bootstrap-tab.js"></script>
    <script src="js/bootstrap-tooltip.js"></script>
    <script src="js/bootstrap-popover.js"></script>
    <script src="js/bootstrap-button.js"></script>
    <script src="js/bootstrap-collapse.js"></script>
    <script src="js/bootstrap-carousel.js"></script>
    <script src="js/bootstrap-typeahead.js"></script>
		<script>  
			$(function ()  
				{ $("#my_ini").popover();  
			});  
		</script>
  </body>
</html>

