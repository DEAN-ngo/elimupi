<script>
<?php 
$lang = isset($_GET['lang'])? $_GET['lang'] : '';
?>

<?php
	$uri = '?';
	$admin = false;
	foreach( $_GET as $param => $value){
		if( $param != 'lang' )
			$uri .= $param . "=" . $value;	
	}
	if( isset( $_GET['resource'] ) && $_GET['resource'] == 'elimuPiAdmin')
		$admin = true;
	$lang = isset($_GET['lang'])? $_GET['lang'] : '';
?>

var urls = [
	"<?php echo $uri; ?>&lang=sw_ke",
	"<?php echo $uri; ?>&lang=en_gb",
	"<?php echo $uri; ?>&lang=am_et"
]

document.addEventListener( 'DOMContentLoaded', () => {
	var el = document.getElementById( 'language-selector' ),
		current = (new URL(document.location)).searchParams.get('lang');
	
	if(current == 'sw_ke')
		el.value = 'swahili'
	else if( current == 'english' || ! current)
		el.value = 'english'
	else if( current == 'amharic')
		el.value = 'amharic'

	el.addEventListener( 'change', ( evnt ) => {
		var lang = evnt.target.value
		if( lang == '')
			;
		else if( lang == 'swahili')
			document.location = urls[0]
		else if( lang == 'english')
			document.location = urls[1]
		else if( lang == 'amharic')
			document.location = urls[2]
	})
} )

</script>
<div class="col-12">

	<!-- Logo -->
		<a href="index.php"><img src="images/elimuPi-logo.jpg" id="logo" /></a>

	<!-- Nav -->
		<navi id="navi">
			<a href="index.php">
				<img src='images/home.jpg' title='<?php echo _('Home');?>'/>
			</a>
			<a href="http://softwares.elimupi.online" target="_blank">
				<img src='images/wikipedia-small.jpg' title='<?php echo _('Apps');?>'/>
				<span><?php echo _('Apps');?></span>
			</a>
            <a href="documents.php?lang=<?php echo $lang?>">
				<img src='images/documents-small.jpg' title='<?php echo _('Documents');?>'/>
				<span><?php echo _('Docs');?></span>
			</a>
            <a href="#" style="cursor: default;">
				<img src='images/elimuOnline-small.jpg' title='<?php echo _('Elimu.online');?>'/>
				<span><?php echo _('Elimu.online');?></span>
			</a>
            <a href="support.php?lang=<?php echo $lang?>">
				<img src='images/help.jpg' title='<?php echo _('Support');?>'/>
			</a>
			
            <select id='language-selector' style='appearance:auto;border:none'>
				<option value='english'>English</option>
				<option value='swahili'>Kiswahili</option>
				<option value='amharic'>አማርኛ</option>
			</select>

			<a href="admin/?lang=<?php echo $lang?>#login">
				<img src='images/lock.jpg' title='<?php echo _('Login');?>' id='loginIcon'/>
			</a>
		</navi>

</div>

<script type='text/javascript'>
function login_icon_to_login_text(loginName){
	var el = document.querySelector("#loginIcon")
	if(el){
		el.parentElement.innerText = loginName
		el.remove()
	}
}
function has_credetials(){
	var cr = localStorage.getItem( 'credentials' )
	if( cr ){
		return JSON.parse( cr ).user
	}
	else
		return false
}
document.addEventListener("DOMContentLoaded", () => {
	var ls = has_credetials()
	if(ls)
		login_icon_to_login_text(ls)
})
</script>