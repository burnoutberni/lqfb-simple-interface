<div class="span2">
  <div class="well sidebar-nav">
    <ul class="nav nav-list">
			<li><img src="data:image/jpg;base64,<?echo $current_member_image;?>"> <?if($session_key != ""){echo $current_member_name;} else{echo "Bitte melde dich an!";}?></li>
			<li class="nav-header">Übersicht</li>
<?
for ($i = 0; $i < count($issue); $i++) {
	if(in_array($issue[$i][2], $issue_my) == "true"){
		goto a;
	} else{
		goto b;
	}
	a:
	if($i != 0){
		$iless = $i - 1;
		if($issue[$iless][0] == $issue[$i][0]){
			$my_output .= "<li><a href='https://lqfb.piratenpartei.at/initiative/show/";
			$my_output .= $issue[$i][2];
			$my_output .= ".html'>i";
			$my_output .= $issue[$i][2];
			$my_output .= ": ";
			$my_output .= $issue[$i][1];
			$my_output .= "</a></li>";
		}
		else{
			$my_output .= "<li class='nav-header'>Thema: ";
			$my_output .= $issue[$i][0];
			$my_output .= "<li><a href='https://lqfb.piratenpartei.at/initiative/show/";
			$my_output .= $issue[$i][2];
			$my_output .= ".html'>i";
			$my_output .= $issue[$i][2];
			$my_output .= ": ";
			$my_output .= $issue[$i][1];
			$my_output .= "</a></li>";
		}
	}
	else{
		$my_output .= "<li class='nav-header'>Thema: ";
		$my_output .= $issue[$i][0];
		$my_output .= "</li><li><a href='https://lqfb.piratenpartei.at/initiative/show/";
		$my_output .= $issue[$i][2];
		$my_output .= ".html'>i";
		$my_output .= $issue[$i][2];
		$my_output .= ": ";
		$my_output .= $issue[$i][1];
		$my_output .= "</a></li>";
	}
	b:
}
$my_output .= "<li class='nav-header'>Keine weiteren Inhalte in diesem Themenbereich enthalten!</li>";
?>
<!--
<li>
<a href="#" data-toggle="collapse" data-target="#demo">
Meine Initiativen
</a>
</li>
<div id="demo" class="collapse"><div class="well"><?echo $my_output;?></div></div>-->

			<li><a id="my_ini" data-content="<ul class='nav nav-list'><?echo $my_output;?></ul>" data-html="true" data-placement="right" rel="popover" href="#" data-original-title="Meine Initiativen" data-trigger="click">Meine Initiativen</a></li>
			<li><a href="delegations.php">Meine Delegationen</a></li>
			<li><a href="index.php?logout=true">Abmelden</a></li>
		</ul>
	</div>
</div>
