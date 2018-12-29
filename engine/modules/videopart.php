<?php
/*
=============================================
 Name      : MWS Video Part v1.5
 Author    : Mehmet Hanoğlu ( MaRZoCHi )
 Site      : http://dle.net.tr/
 License   : MIT License
 Date      : 26.12.2017
=============================================
*/

if ( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}


include ENGINE_DIR . "/data/videopart.conf.php";
$v_clear = false;
if ( $vset['mod_on'] ) {
	if ( $vset['source'] == "0" ) {
		$source = $row['full_story'];
	} else {
		$xf = xfieldsdataload( $row['xfields'] );
		$source = $xf[ $vset['xf_name'] ];
	}


// iframe hack
	if ( strpos( $source, "[frame=" ) !== false ) {
		$source = preg_replace( "#\\[frame=(.+?)\\]#is", "<iframe src=\"$1\" scrolling=\"no\" frameborder=\"0\" width=\"685\" height=\"400\" allowfullscreen=\"true\" webkitallowfullscreen=\"true\" mozallowfullscreen=\"true\"></iframe>", $source );
	}
// iframe hack

	if ( strpos( $source, "[part" ) !== false ) {
		$matches = array();
		/*
		preg_match_all( "#\[part\](.+?)\[/part\]#is", $source, $matches );
		if ( count( $matches[0] ) ) {
			$matches[2] = $matches[1];
			for ( $x = 0; $x < count( $matches[2] ); $x++ ) { $matches[1][$x] = $vset['prefix'] . " " . ( $x + 1 ); }
		} else {
			preg_match_all( "#\[part=(.+?)\](.+?)\[/part\]#is", $source, $matches );
		}
		*/
		preg_match_all( "#\[part=*(.*?)\](.*?)\[/part\]#is", $source, $matches );

		if ( count( $matches[0] ) > 0 ) {
			unset( $matches[0] );
			$part_story = $matches[2];
			$part_titles = $matches[1];
			$part_count = count( $matches[2] );

			$row['full_story'] = $row['full_story'] . str_repeat( "{PAGEBREAK}", $part_count );

			if ( $news_page <= 0 OR $news_page > $part_count OR ( isset( $_GET['news_page'] ) AND $_GET['news_page'] === "0" ) ) {
				$news_page = 1;
				if ( $config['seo_control'] ) {
					$re_url = str_replace( $config['http_home_url'], "/", $full_link );
					header("HTTP/1.0 301 Moved Permanently");
					header("Location: {$re_url}");
					die("Redirect");
				}
			}
			$_curr = $news_page - 1;

			if ( $vset['source'] == "0" ) {
				$source = preg_replace( '#(\A[\s]*<br[^>]*>[\s]*|<br[^>]*>[\s]*\Z)#is', '', $part_story[ $_curr ] ); // remove <br/> at end of string
			} else {
				$source = preg_replace( '#(\A[\s]*<br[^>]*>[\s]*|<br[^>]*>[\s]*\Z)#is', '', $part_story[ $_curr ] ); // remove <br/> at end of string
			}
			if ( $part_count > 0 ) {
				$tpl2 = new dle_template();
				$tpl2->dir = TEMPLATE_DIR;
				$tpl2->load_template( 'part-navigation.tpl' );
				if ( $vset['show_prevnext'] ) {
					if ( $news_page < $part_count ) {
						$pages = $news_page + 1;
						if ( $config['allow_alt_url'] ) {
							$nextpage = "<a href=\"" . $short_link . "page," . $pages . "," . $row['alt_name'] . ".html\">";
						} else {
							$nextpage = "<a href=\"$PHP_SELF?newsid=" . $row['id'] . "&amp;news_page=" . $pages . "\">";
						}
						$tpl2->set( '[next-link]', $nextpage );
						$tpl2->set( '[/next-link]', "</a>" );
					} else {
						$tpl2->set_block( "'\\[next-link\\](.*?)\\[/next-link\\]'si", "<span>\\1</span>" );
					}
					if ( $news_page > 1 ) {
						$pages = $news_page - 1;
						if ( $config['allow_alt_url'] ) {
							if ( $pages == 1 ) $prevpage = "<a href=\"" . $full_link . "\">";
							else $prevpage = "<a href=\"" . $short_link . "page," . $pages . "," . $row['alt_name'] . ".html\">";
						} else {
							if ( $pages == 1 ) $prevpage = "<a href=\"" . $full_link. "\">";
							else $prevpage = "<a href=\"$PHP_SELF?newsid=" . $row['id'] . "&amp;news_page=" . $pages . "\">";
						}
						$tpl2->set( '[prev-link]', $prevpage );
						$tpl2->set( '[/prev-link]', "</a>" );
					} else {
						$tpl2->set_block( "'\\[prev-link\\](.*?)\\[/prev-link\\]'si", "<span>\\1</span>" );
					}
				} else {
					$tpl2->set_block( "'\\[next-link\\](.*?)\\[/next-link\\]'si", "" );
					$tpl2->set_block( "'\\[prev-link\\](.*?)\\[/prev-link\\]'si", "" );
				}

				$listpages = "";
				if ( $vset['show_asnavigator'] ) {
					if ( $part_count <= 10 ) {
						for ($j = 1; $j <= $part_count; $j++ ) {
							$part_title = $part_titles[ $j-1 ];
							if ( empty( $part_title ) ) $part_title = $vset['prefix'] . " " . $j;

							if ( $j != $news_page ) {
								if ( $config['allow_alt_url'] ) {
									if ( $j == 1 ) {
										$listpages .= "<a href=\"" . $full_link . "\">{$part_title}</a> ";
									} else {
										$listpages .= "<a href=\"" . $short_link . "page," . $j . "," . $row['alt_name'] . ".html\">{$part_title}</a> ";
									}
								} else {
									if ( $j == 1 ) {
										$listpages .= "<a href=\"{$full_link}\">{$part_title}</a> ";
									} else {
										$listpages .= "<a href=\"$PHP_SELF?newsid=" . $row['id'] . "&amp;news_page=" . $j . "\">{$part_title}</a> ";
									}
								}
							} else {
								$listpages .= "<span>{$part_title}</span> ";
							}
						}
					} else {
						$start = 1;
						$end = 10;
						$nav_prefix = "<span class=\"nav_ext\">{$lang['nav_trennen']}</span> ";
						if ( $news_page > 1 ) {
							if ( $news_page > 6 ) {
								$start = $news_page - 4;
								$end = $start + 8;
								if ( $end >= $part_count ) {
									$start = $part_count - 9;
									$end = $part_count - 1;
									$nav_prefix = "";
								} else {
									$nav_prefix = "<span class=\"nav_ext\">{$lang['nav_trennen']}</span> ";
								}
							}
						}
						if ( $start >= 2 ) {
							$part_title = $part_titles[ 0 ];
							$listpages .= "<a href=\"" . $full_link . "\">{$part_title}</a> <span class=\"nav_ext\">{$lang['nav_trennen']}</span> ";
						}
						for ( $j = $start; $j <= $end; $j ++ ) {
							$part_title = $part_titles[ $j-1 ];
							if ( empty( $part_title ) ) $part_title = $vset['prefix'] . " " . $j;
							if ( $j != $news_page ) {
								if ( $config['allow_alt_url'] ) {
									if ( $j == 1 ) {
										$listpages .= "<a href=\"" . $full_link . "\">{$part_title}</a> ";
									} else {
										$listpages .= "<a href=\"" . $short_link . "page," . $j . "," . $row['alt_name'] . ".html\">{$part_title}</a> ";
									}
								} else {
									if ( $j == 1 ) {
										$listpages .= "<a href=\"{$full_link}\">{$part_title}</a> ";
									} else {
										$listpages .= "<a href=\"$PHP_SELF?newsid=" . $row['id'] . "&amp;news_page=" . $j . "\">{$part_title}</a> ";
									}
								}
							} else {
								$listpages .= "<span>{$part_title}</span> ";
							}
						}
						if ( $news_page != $part_count ) {
							if ( $config['allow_alt_url'] ) $listpages .= $nav_prefix . "<a href=\"" . $short_link . "page," . $part_count . "," . $row['alt_name'] . ".html\">" . ( $part_titles[ $part_count-1 ] ) . "</a>";
							else $listpages .= $nav_prefix . "<a href=\"$PHP_SELF?newsid=" . $row['id'] . "&amp;news_page=" . $part_count . "\">" . ( $part_titles[ $part_count-1 ] ) . "</a>";
						} else {
							$listpages .= "<span>" . ( $part_titles[ $part_count-1 ] ) . "</span> ";
						}
					}

				} else {
					for ($j = 1; $j <= $part_count; $j++ ) {
						$part_title = $part_titles[ $j-1 ];
						if ( empty( $part_title ) ) $part_title = $vset['prefix'] . " " . $j;
						if ( $j != $news_page ) {
							if ( $config['allow_alt_url'] ) {
								if ( $j == 1 ) {
									$listpages .= "<a href=\"" . $full_link . "\">{$part_title}</a> ";
								} else {
									$listpages .= "<a href=\"" . $short_link . "page," . $j . "," . $row['alt_name'] . ".html\">{$part_title}</a> ";
								}
							} else {
								if ( $j == 1 ) {
									$listpages .= "<a href=\"{$full_link}\">{$part_title}</a> ";
								} else {
									$listpages .= "<a href=\"$PHP_SELF?newsid=" . $row['id'] . "&amp;news_page=" . $j . "\">{$part_title}</a> ";
								}
							}
						} else {
							$listpages .= "<span>{$part_title}</span> ";
						}
					}
				}
				$tpl2->set( '{pages}', $listpages );
				$tpl2->compile( 'content' );
				$tpl->set( '{part-navigation}', $tpl2->result['content'] );
				unset($tpl2);
				if ( $config['allow_alt_url'] ) {
					$replacepage = "<a href=\"" . $short_link . "page," . "\\1" . "," . $row['alt_name'] . ".html\">\\2</a>";
				} else {
					$replacepage = "<a href=\"$PHP_SELF?newsid=" . $row['id'] . "&amp;news_page=\\1\">\\2</a>";
				}
				if ( $vset['source'] == "0" ) {
					$row['full_story'] = preg_replace( "'\[part=(.*?)\](.*?)\[/part\]'si", $replacepage, $row['full_story'] );
					$tpl->set( '{video}', '' );
				} else {
					$source = preg_replace( "'\[part=(.*?)\](.*?)\[/part\]'si", $replacepage, $source );
					$tpl->set( '{video}', stripslashes( $source ) );
				}
				$tpl->set( '[parts]', "" );
				$tpl->set( '[/parts]', "" );
			} else { $v_clear = true; }
		} else { $v_clear = true; }
	} else { $v_clear = true; }
} else { $v_clear = true; }
if ( $v_clear ) {
	$tpl->set( '{video}', '' );
	$tpl->set( '{part-navigation}', '' );
	$source = preg_replace( "'\[part=(.*?)\](.*?)\[/part\]'si", "", $source );
	$tpl->set_block( "'\\[parts\\](.*?)\\[/parts\\]'si", "" );
}


?>