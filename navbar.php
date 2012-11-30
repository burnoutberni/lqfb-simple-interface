<div class="navbar navbar-inverse navbar-fixed-top">
  <div class="navbar-inner">
    <div class="container-fluid">
      <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </a>
      <a class="brand" href="index.php">LiquidFeedback</a>
      <div class="nav-collapse collapse">
<?
if($session_key != ""){
echo "<ul class=\"nav pull-right\"><li class=\"dropdown pull-right\"><a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">Angemeldet als ";
echo $current_member_name;
echo "<b class=\"caret\"></b></a><ul class=\"dropdown-menu\"><div style=\"margin:5px;\"><p><img src=\"data:image/jpg;base64,";
echo $current_member_image;
echo "\"> ";
echo $current_member_name;
echo "</p><p><a href=\"index.php?logout=true\">Abmelden</a></p></ul></li></ul>";
} else{
echo "<form action=\"index.php\" method=\"post\" class=\"navbar-form pull-right\"><input type=\"text\" class=\"span2\" name=\"api_key\" placeholder=\"API-Schlüssel\"><button type=\"submit\" class=\"btn\">Anmelden</button></form>";
}
?>
      </div><!--/.nav-collapse -->
			<div>
				<form class="navbar-search pull-left">
					<input type="text" class="search-query" placeholder="Suche - Noch nicht implementiert">
				</form>
			</div>
    </div>
  </div>
</div>
