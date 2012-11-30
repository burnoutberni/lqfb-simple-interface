<?
//API-Key aus navbar Form
$api_key = $_POST["api_key"];

//Logout => Löscht Session-Key aus Cookie und $session_key
$logout = $_GET["logout"];
if($logout == "true"){
	setcookie("session_key", "");
	$session_key = "";
} else{
	$session_key = $_COOKIE["session_key"];
}

//Delegationen aus myModal
$deleg_unit = $_POST["deleg_unit"];
$deleg_member = $_POST["deleg_member"];

//Neuen Session-Key erzeugen - POST /session
if($session_key == "" && $api_key != ""){
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

//Session-Key in Cookie speichern
setcookie("session_key", $session_key);

//Neue Delegation speichern od. alte löschen - POST /delegation
if($deleg_unit != ""){
	if($deleg_member != ""){
		if($deleg_member == "!!delete"){
			$trustee_id = "&delete=true";
		} else{
			$trustee_id = "&trustee_id=".urlencode($deleg_member);
		}
		$host = '88.198.24.116';
		$path = '/delegation';
		$data = 'unit_id='.urlencode($deleg_unit).$trustee_id.'&session_key='.urlencode($session_key);
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
	}
}

//URL des API-Servers
$base_url = "http://88.198.24.116:25520/";

//GET issue_state überprüfen, wenn leer, dann open
$issue_state = $_GET["issue_state"];
if($issue_state == ""){
	$issue_state = "open";
}

//GET area_id überprüfen, wenn leer, dann bisherige area_id
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
$url_member = $base_url . "member?limit=1000&session_key=" . $session_key;
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

//Bild des derzeitigen Nutzers
$url_image = $base_url . "member_image?session_key=" . $session_key . "&member_id=" . $current_member_id;
$string_image = file_get_contents($url_image);
$json_image = json_decode($string_image,true);
$current_member_image = $json_image['result'][0]['data'];

//Meine Initiativen werden aus der API gezogen, JSON => array
$url_my = $base_url . "initiator?session_key=" . $session_key . "&member_id=" . $current_member_id;
$string_my = file_get_contents($url_my);
$json_my = json_decode($string_my,true);
for ($i = 0; $i < count($json_my['result']); $i++) {
	$issue_my[] = $json_my['result'][$i]['initiative_id'];
}

//Delegierte Units werden aus der API gezogen, JSON => array
$url_delegation_unit = $base_url . "delegation?scope=unit&direction=out&session_key=" . $session_key . "&member_id=" . $current_member_id;
$string_delegation_unit = file_get_contents($url_delegation_unit);
$json_delegation_unit = json_decode($string_delegation_unit,true);
for ($i = 0; $i < count($json_delegation_unit['result']); $i++) {
	$array_delegation_unit[$i][0] = $json_delegation_unit['result'][$i]['unit_id'];
	$array_delegation_unit[$i][1] = $json_delegation_unit['result'][$i]['trustee_id'];
}

//Alle Units werden aus der API gezogen, JSON => array
$url_unit = $base_url . "unit?session_key=" . $session_key;
$string_unit = file_get_contents($url_unit);
$json_unit = json_decode($string_unit,true);
for ($i = 0; $i < count($json_unit['result']); $i++) {
	$array_unit[$i][0] = $json_unit['result'][$i]['id'];
	$array_unit[$i][1] = $json_unit['result'][$i]['name'];
}

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

//Custom Sortierfunktion 2
function cmpp($a,$b){
    //get which string is less or 0 if both are the same
    $cmpp = strcasecmp($a[1], $b[1]);
    //if the strings are the same, check name
    if($cmpp == 0){
        //compare the name
        $cmpp = strcasecmp($a[0], $b[0]);
    }
    return $cmpp;
}

//Initiativen werden nach Themen sortiert
usort($issue, 'cmp');
//Meine Initiativen werden sortiert
usort($issue_my, 'cmp');
//Nutzer werden alphabetisch nach Namen sortiert
usort($issue_member, 'cmpp');
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
				background: #4c2582 url('img/Banner_Sonne_web.jpg') no-repeat;
        padding-top: 60px;
        padding-bottom: 40px;
      }
      .sidebar-nav {
        padding: 9px 0;
      }
			footer {
				color: white;
			}
    </style>
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
          <div class="well">
						<h2>Delegationen</h2>
						<p>Hier kannst du deine Delegationen einsehen und bearbeiten:</p>
						<p><a class="btn" href="#change" data-toggle="modal">Delegationen ändern & hinzufügen & entfernen</a></p>
						<table class="table table-hover"><thead><tr><th>Delegierter Bereich</th><th>Delegiert an</th></tr></thead><tbody>
<?
//Delegationen aus array in anderes array
for ($i = 0; $i < count($array_delegation_unit); $i++) {
	for ($e = 0; $e < count($array_unit); $e++) {
		if($array_delegation_unit[$i][0] == $array_unit[$e][0]){
			$delegation_output[$i][] = $array_unit[$e][1];
		}
	}
	for ($o = 0; $o < count($issue_member); $o++) {
		if($array_delegation_unit[$i][1] == $issue_member[$o][0]){
			$delegation_output[$i][] = $issue_member[$o][1];
		}
	}
}

//Array in Tabelle ausgeben
for ($i = 0; $i < count($delegation_output); $i++) {
	echo "<tr><td>" . $delegation_output[$i][0] . "</td><td>" . $delegation_output[$i][1] . "</td></tr>";
}

//"Fehlermeldung"
if($delegation_output[0][0] == ""){
	echo "<tr><td>Bisher keine Delegationen angelegt!</td><td></td></tr>";
}
?>
					</tbody></table>
         </div><!--/.well -->

<!-- Modal -->
<div id="change" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="myModalLabel">Delegationen</h3>
	</div>
	<div class="modal-body">
		<div class="span5">
		<p><i class="icon-th-list"></i> Gliederung</p>
		<form action="delegations.php" method="post">
		<select name="deleg_unit">
			<option value="">Wähle eine Gliederung</option>
<?
//Alle Units auflisten
for ($i = 0; $i < count($array_unit); $i++) {
	echo "<option value=\"" . $array_unit[$i][0] . "\">" . $array_unit[$i][1] . "</option>";
}
?>
		</select>
		</div>
		<div class="span2">
		<p><i class="icon-chevron-right"></i></p>
		</div>
		<div class="span5">
		<p><i class="icon-user"></i> Nutzer</p>
		<select name="deleg_member">
			<option value="">Wähle einen Nutzer</option>
			<option value="!!delete">--Delegation aufheben--</option>
<?
//Alle Nutzer auflisten
for ($i = 0; $i < count($issue_member); $i++) {
	if($issue_member[$i][1] != ""){
		echo "<option value=\"" . $issue_member[$i][0] . "\">" . $issue_member[$i][1] . "</option>";
	}
}
?>
		</select>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true">Schließen</button>
		<button class="btn btn-primary" type="submit">Änderungen speichern</button>
		</form>
	</div>
</div>
        </div><!--/span-->
      </div><!--/row-->

      <footer>
        <p>Eine kleine Spielerei von Bernhard <a href="http://wiki.piratenpartei.at/wiki/Benutzer:Burnoutberni">'burnoutberni'</a> Hayden.</p>
				<p><?echo $buffer;?></p>
				<p><?echo $trustee_id;?></p>
				<p>Datenquelle: <a href="http://lqfb.piratenpartei.at">http://lqfb.piratenpartei.at</a></p>
      </footer>

    </div><!--/.fluid-container-->

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

