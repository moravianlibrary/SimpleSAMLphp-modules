<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" data-tldr="true">
<head>
	<title><?php echo $this->t('{login:user_pass_header}'); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<!--	<meta http-equiv="x-xrds-location" content="<?php echo SimpleSAML_Module::getModuleURL('xcncip2/xrds.xml'); ?>" /> -->

	<link rel='stylesheet' href="<?php echo SimpleSAML_Module::getModuleURL('xcncip2/global.css?v0'); ?>" type='text/css' />
	<!--[if IE]><style type="text/css">#login h1 a { margin-top: 35px; } #login #login_error { margin-bottom: 10px; }</style><![endif]--><!-- Curse you, IE! -->

	<script type="text/javascript">
		function focusit() {
			document.getElementById('username').focus();
		}
		window.onload = focusit;
	</script>
</head>
<body class="login">

<?php

    $loginRecoveryLink = "";
    $passwordRecoveryLink = "";
    $registeryLink = "";

    $current_lang = "en";
    $languages = $this->getLanguageList();
    foreach ($languages AS $lang => $current) {
         if ($current) {
             $current_lang = $lang;
         }
    } 
    // if ($_GET['language'] == 'en') {
    if ($current_lang == 'en') {
        $switch_lang = 'cs';
        //$remember_me = "Remember me";
        //$password_forgot = "Forgot your password?";
        //$institution_login = "Institution login";
    } else {
        $switch_lang = 'en';
        //$remember_me = "Zapamatuj si mě";
        //$password_forgot = "Zapomněli jste heslo?";
        //$institution_login = "Jednotné přihlášení";
    }
    $params = array('language' => $switch_lang);
    foreach ($this->data['stateparams'] as $name => $value) {
        $params[$name] = $value;
    }
    // $href = htmlspecialchars(SimpleSAML_Utilities::addURLparameter(SimpleSAML_Utilities::selfURL(), array('language' => $switch_lang)));
    $href = htmlspecialchars(SimpleSAML_Utilities::addURLparameter(SimpleSAML_Utilities::selfURL(), $params));
    $img = SimpleSAML_Module::getModuleURL('xcncip2/'.$switch_lang.'.gif');
    //$lang = "<a href='$href'> <img src='$img' /> </a>";
    $lang = "<a href='$href'> <img align='right' src='$img'/> </a>";
    /* $login_str = $this->t('{login:username}') . "&nbsp;<a href='http://www.mzk.cz/sluzby/navody/jak-se-prihlasit-do-katalogu'" 
       ."target='_blank' style='text-decoration: none;'>(" . $this->t('{login:help}') . ")</a>"; Nápověda link */

    $login_str = ($current_lang == 'en')?"Username":"Uživatelské jméno";

    $error = false;
    $content = trim(file_get_contents("/data/www/idps-hosted/maintenance.txt"));
    if ($content != "") {
       $error = true;
       $error_str = "{login:maintenance_in_progress}"; // "You can't login at this time. Maintenance in progress.";
    }
?>

<div class="container">
	<div class="login-header">
		<!-- <p class="lng-switch"><?php echo $lang ?></p> -->
	</div>
	<div id="login">
		
		<form name="loginform" id="loginform" action="?" method="post">			
				<p>
					<img src="<?php echo SimpleSAML_Module::getModuleURL('xcncip2/logo.png'); ?>" height="40" align="bottom" alt="logo"/>
					<span align='right'><?php echo $lang; ?></span>
				</p>
				<!-- <p><?php echo $lang ?></p> -->
				<p><h1><?php echo $this->t('{login:institution_login}'); ?></h1></p>
                                <?php if($error) {
                                ?>
                                <p style="color:#EF406B;">
                                   <h3 style="color:#EF406B;"><?php echo $this->t($error_str);?> </br> <?php echo $error_str_add; ?> </h3>
                                <p>
                                <?php } else { ?>
				<div class="loginform-wrapper">
				<p>
					<label><?php echo $login_str; ?><br />					
					<input type="text" name="username" id="username" class="input" <?php if (isset($this->data['username'])) { echo 'value="' . htmlspecialchars($this->data['username']) . '"'; } ?> size="20" tabindex="10" /></label>
				</p>
				
				<p>
					<label><?php echo $this->t('{login:password}'); ?><br />
					<input type="password" name="password" id="user_pass" class="input" value="" size="20" tabindex="20" /></label>
				</p>
				
				<p><label><input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="90" /><?php echo $this->t('{login:remember_me}'); ?> </label></p>
				
				<div class="login-btn">
					<input class="btn-large" type="submit" name="wp-submit" id="wp-submit" value="<?php echo $this->t('{login:login_button}'); ?> &raquo;" tabindex="100" />
					<?php if (!empty($registeryLink)): ?>
						<a class="btn" href="<?php echo $registeryLink ?>" title="Nejste u nás zaregistrovaní? Přejděte na online předregistraci"><?php echo $this->t('{login:registration}'); ?>&nbsp;&raquo;</a>
					<?php endif; ?>
				</div>


	<?php
	if ($this->data['errorcode'] !== NULL) {
	?>
		<div id="error">
			<img src="/<?php echo $this->data['baseurlpath']; ?>resources/icons/experience/gtk-dialog-error.48x48.png" style="float: right; margin: 15px " />
			<h2><?php echo $this->t('{login:error_header}'); ?></h2>
			<p style="clear: both"><b><?php echo $this->t('{errors:title_' . $this->data['errorcode'] . '}'); ?></b></p>
			<p><?php echo $this->t('{errors:descr_' . $this->data['errorcode'] . '}'); ?></p>
			<!--
			<table>
			<tr>
			<td>
			<p class="cervene_pozadi"> 
				<a href="http://aleph.mzk.cz/cgi-bin/login_recovery.pl" style="text-decoration: none;"><?php echo $this->t('{login:forgotten_login}'); ?></a></p>
			</td><td><p class="cervene_pozadi">
				<a href="https://aleph.mzk.cz/cgi-bin/password_recovery.pl" style="text-decoration: none;"><?php echo $this->t('{login:password_recovery}'); ?></a></p>
				</td> 
			</tr>                  
					</table>
			-->
		</div>
	<?php
	}
        ?>

		<?php if (!empty($loginRecoveryLink) || !empty($passwordRecoveryLink)): ?>
			<div class="service-btn">
				<?php if (!empty($loginRecoveryLink)): ?>
					<a class="btn" href="<?php echo $loginRecoveryLink ?>"><?php echo $this->t('{login:forgotten_login}'); ?></a>
				<?php endif; ?>
				<?php if (!empty($passwordRecoveryLink)): ?>
					<a class="btn" href="<?php echo $passwordRecoveryLink ?>"><?php echo $this->t('{login:password_recovery}'); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
			<!--</div>-->
	</div>

			<div class="login-footer">
				<p><?php echo $this->t('{login:login_comment}'); ?></p>
			</div>
                        <?php } ?>			

	<?php
	if(!empty($this->data['links'])) {
		echo '<ul class="links" style="margin-top: 2em">';
		foreach($this->data['links'] AS $l) {
			echo '<li><a href="' . htmlspecialchars($l['href']) . '">' . htmlspecialchars($this->t($l['text'])) . '</a></li>';
		}
		echo '</ul>';
	}



	?>
	<!-- 
		<?php if (isset($this->data['error'])) { ?>
			<div id="error">
			<img src="/<?php echo $this->data['baseurlpath']; ?>resources/icons/experience/gtk-dialog-error.48x48.png" style="float: left; margin: 15px " />
			<h2><?php echo $this->t('{error:error_header}'); ?></h2>
			
			<p style="padding: .2em"><?php echo $this->t($this->data['error']); ?> </p>fdajflajdlfjaldfsjla
			<table>
			<tr>
			<td>
			<p class="cervene_pozadi"> 
				<a href="http://aleph.mzk.cz/cgi-bin/login_recovery.pl" style="text-decoration: none;">Neznám číslo nebo přezdívku</a></p>
			</td><td><p class="cervene_pozadi">
				<a href="https://aleph.mzk.cz/cgi-bin/password_recovery.pl" style="text-decoration: none;">Neznám heslo</a></p>
				</td> 
			</tr>                  
					</table>  
			</div>
		<?php } ?>
	 -->	
	<?php
	foreach ($this->data['stateparams'] as $name => $value) {
		echo('<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" />');
	}
	?>
			
		</form>
	</div>
</div>


<?php
/*

	$includeLanguageBar = TRUE;
	if (!empty($_POST)) 
		$includeLanguageBar = FALSE;
	if (isset($this->data['hideLanguageBar']) && $this->data['hideLanguageBar'] === TRUE) 
		$includeLanguageBar = FALSE;
	
	if ($includeLanguageBar) {
		

		echo '<div id="languagebar">';		
		
		// echo '<form action="' . SimpleSAML_Utilities::selfURL() . '" method="get">';
		// echo '<select name="language">';
		// echo '</select>';
		// echo '</form>';
		
		$languages = $this->getLanguageList();
		$langnames = array(
			'en' => 'English',
			'de' => 'Deutsch', 
			'cs' => 'Czech',
		);
		
		$textarray = array();
		foreach ($languages AS $lang => $current) {
			if ($current) {
				$textarray[] = $langnames[$lang];
			} else {
				$textarray[] = '<a href="' . htmlspecialchars(
						SimpleSAML_Utilities::addURLparameter(
							SimpleSAML_Utilities::selfURL(), array('language' => $lang)
						)
				) . '">' . $langnames[$lang] . '</a>';
			}
		}
		echo join(' | ', $textarray);
		echo '</div>';
	}
*/
?>

</body>
</html>
