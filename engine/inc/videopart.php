<?php
/*
=============================================
 Name      : MWS Video Part v1.4
 Author    : Mehmet Hanoğlu ( MaRZoCHi )
 Site      : http://dle.net.tr/
 License   : MIT License
 Date      : 08.12.2017
=============================================
*/

if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if( $member_id['user_group'] != 1 ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

include ENGINE_DIR . "/data/videopart.conf.php";

if ( ! is_writable(ENGINE_DIR . '/data/videopart.conf.php' ) ) {
	$lang['stat_system'] = str_replace( "{file}", "engine/data/videopart.conf.php", $lang['stat_system'] );
	$fail = "<div class=\"alert alert-error\">{$lang['stat_system']}</div>";
} else $fail = "";

if ( $action == "save" ) {
	if ( $member_id['user_group'] != 1 ) { msg( "error", $lang['opt_denied'], $lang['opt_denied'] ); }
	if ( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) { die( "Hacking attempt! User not found" ); }

	$save_con = $_POST['save_con'];
	$save_con['show_prevnext'] = intval($save_con['show_prevnext']);
	$save_con['show_asnavigator'] = intval($save_con['show_asnavigator']);
	$save_con['mod_on'] = intval($save_con['mod_on']);
	$save_con['source'] = intval($save_con['source']);

	$find = array(); $replace = array();
	$find[] = "'\r'"; $replace[] = "";
	$find[] = "'\n'"; $replace[] = "";

	$save_con = $save_con + $vset;
	$handler = fopen( ENGINE_DIR . '/data/videopart.conf.php', "w" );

	fwrite( $handler, "<?PHP \n\n//MWS Video Part Settings\n\n\$vset = array (\n" );
	foreach ( $save_con as $name => $value ) {
		$value = ( is_array( $value ) ) ? implode(",", $value ) : $value;
		$value = trim(strip_tags(stripslashes( $value )));
		$value = htmlspecialchars( $value, ENT_QUOTES, $config['charset']);
		$value = preg_replace( $find, $replace, $value );
		$name = trim(strip_tags(stripslashes( $name )));
		$name = htmlspecialchars( $name, ENT_QUOTES, $config['charset'] );
		$name = preg_replace( $find, $replace, $name );
		$value = str_replace( "$", "&#036;", $value );
		$value = str_replace( "{", "&#123;", $value );
		$value = str_replace( "}", "&#125;", $value );
		$value = str_replace( ".", "", $value );
		$value = str_replace( '/', "", $value );
		$value = str_replace( chr(92), "", $value );
		$value = str_replace( chr(0), "", $value );
		$value = str_replace( '(', "", $value );
		$value = str_replace( ')', "", $value );
		$value = str_ireplace( "base64_decode", "base64_dec&#111;de", $value );
		$name = str_replace( "$", "&#036;", $name );
		$name = str_replace( "{", "&#123;", $name );
		$name = str_replace( "}", "&#125;", $name );
		$name = str_replace( ".", "", $name );
		$name = str_replace( '/', "", $name );
		$name = str_replace( chr(92), "", $name );
		$name = str_replace( chr(0), "", $name );
		$name = str_replace( '(', "", $name );
		$name = str_replace( ')', "", $name );
		$name = str_ireplace( "base64_decode", "base64_dec&#111;de", $name );
		fwrite( $handler, "'{$name}' => '{$value}',\n" );
	}
	fwrite( $handler, ");\n\n?>" );
	fclose( $handler );

	msg( "info", $lang['opt_sysok'], $lang['opt_sysok_1'], "{$PHP_SELF}?mod=videopart" );

}

echoheader( "<i class=\"fa fa-play\"></i> MWS Video Part", "Parçalı videolarınızı part sistemi ile sunun" );


function showRow( $title = "", $description = "", $field = "", $indent = false, $new = false ) {
	$new_html = ( $new ) ? "<span class=\"triangle-button green\"><i class=\"icon-plus\"></i></span>" : "";
	if ( $indent ) { $_in = "<div class=\"ind_div\"></div>"; $_cl = " indented"; } else { $_in = ""; $_cl = ""; }
	echo "<tr><td class=\"col-xs-6 col-sm-6 col-md-7{$_cl}\">{$_in}<div class=\"media-heading text-semibold\">{$title}</div><span class=\"text-muted text-size-small hidden-xs\">{$description}</span></td><td class=\"col-xs-6 col-sm-6 col-md-5{$_cl}\">{$field}{$new_html}</td></tr>";
}


function makeDropDown($options, $name, $selected) {
	$output = "<select class=\"uniform\" style=\"min-width:100px;\" name=\"{$name}\">\r\n";
	foreach ( $options as $value => $description ) {
		$output .= "<option value=\"{$value}\"";
		if( $selected == $value ) {
			$output .= " selected ";
		}
		$output .= ">{$description}</option>\n";
	}
	$output .= "</select>";
	return $output;
}

function makeCheckBox($name, $selected) {
	$selected = $selected ? "checked" : "";
	return "<input class=\"switch\" type=\"checkbox\" name=\"{$name}\" value=\"1\" {$selected}>";
}


function set_selected( $arr, $sel ) {
	if ( ! is_array( $sel ) ) { $sel = explode( ",", $sel ); }
	$html = "";
	foreach( $arr as $key => $val ) {
		$selected = ( in_array( $key, array_values( $sel ) ) ) ? " selected" : "";
		$html .= "<option style=\"color: black\"" . $selected . " value=\"" . $key . "\" >" . $val . "</option>";
	}
	return $html;
}

echo <<<HTML
{$fail}
<style>
.indented { background: #e5e6d9 !important; }
</style>
<form action="{$PHP_SELF}?mod=videopart&action=save" name="conf" id="conf" method="post">
<div class="panel panel-flat">
	<div class="panel-body">
		Sistem Ayarları
	</div>
	<div class="table-responsive">
		<table class="table table-striped">
HTML;

	showRow( "Modülü Aktifleştir", "Modülü istediğiniz zaman açıp kapatabilirsiniz.", makeCheckBox( "save_con[mod_on]", "{$vset['mod_on']}" ) );
	showRow( "İçerik Kaynağı", "Part sistemini bir ilave alanda ya da fullstory kısmında kullanabilirsiniz. Buradan seçim yaparak kullanıma başlayabilirsiniz. Bu ayarı bir seferlik yapın, değiştirmeniz gerekirse part olan tüm konularda düzenleme yapmanız gerekir.", makeDropDown( array( "0" => "Uzun İçerik (fullstory)", "1" => "İlave Alan" ), "save_con[source]", $vset['source'] ) );
	showRow( "İlave Alan Seçimi", "Bu alan, eğer yukarıda İlave Alan'ı seçtiyseniz kullanılacaktır. Seçtiğiniz ilave alan içerisinden veriler okunacaktır.", "<input type=\"text\" class=\"form-control\" name=\"save_con[xf_name]\" value=\"{$vset['xf_name']}\" size=\"20\">", true );
	showRow( "Video Navigasyonu", "Önceki/Sonraki linklerini göster. Eğer kapatılırsa sadece part başlıkları gözükecektir.", makeCheckBox( "save_con[show_prevnext]", "{$vset['show_prevnext']}" ) );
	showRow( "Partları Sıkıştır", "Eğer pasifleştirilirse tüm partlar arada ... olmadan görünür.", makeCheckBox( "save_con[show_asnavigator]", "{$vset['show_asnavigator']}" ) );
	showRow( "Sayfa Öneki", "Navigasyonda sayfa numarasından önce kullanılacak olan yazıdır. Eğer [part=Başlık]...[/part] şeklinde başlık belirtilmediyse kullanılır. <br />( [part]...[/part] olarak girilen partlarda )", "<input type=\"text\" class=\"form-control\" name=\"save_con[prefix]\" value=\"{$vset['prefix']}\">" );

echo <<<HTML
	</table></div></div>
	<div style="margin-bottom:30px;">
		<input type="hidden" name="user_hash" value="{$dle_login_hash}" />
		<button type="submit" class="btn bg-teal btn-raised position-left"><i class="fa fa-floppy-o position-left"></i>{$lang['user_save']}</button>
	</div>
</form>
HTML;

echofooter();
?>