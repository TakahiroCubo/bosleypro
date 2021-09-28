<?php
	/**
	 * webapp.php
	 *
	 * メイン実行ファイルです。ブラウザから直接呼び出されるのは
	 * このPHPスクリプトです。
	 *
	 **/



	/**
	 * PHP設定
	 * 主にiniパラメータやコールバックを設定
	 */
	ini_set('mbstring.http_input', 'pass');
	ini_set('mbstring.http_output', 'pass');
	ini_set('mbstring.script_encoding', 'UTF-8');
	ini_set('display_errors', 'On');
	ini_set('error_reporting',E_ERROR | E_WARNING | E_PARSE);

	/**
	 * フレームワークルート
	 * 通常「application」フォルダの位置を記入
	 */
	define("XD_ROOT", '../../application');

	/**
	 * サブシステムルート
	 * 各サブシステムのモジュールフォルダを記入
	 */
	define("XD_SUBSYS_ROOT", '/publicity');

	// インクルード
	require_once XD_ROOT . '/xdaeda.inc.php';
	// サブシステムルート
	require_once XD_MODULES_DIR . XD_SUBSYS_ROOT . '/subsystem.inc.php';


	// コンテキスト取得
	$path_info = $_SERVER['PATH_INFO'];
	$path_info = preg_replace("/^\//", "", $path_info);
	$path_info = preg_replace("/\/$/", "", $path_info);
	$ctx_array = explode("/", $path_info);
	$name_task = $ctx_array[0];
	$name_module = $ctx_array[1];

	// ---------------------------------------------------------------
	// モジュール定義配列 デフォルトコンテキスト

	require_once XD_SUBSYS_DIR . '/modules.inc.php';
	// ---------------------------------------------------------------

	// モジュールない場合
	if(Mis_empty($DISPATCH_CLASS_NAMES[$name_task][$name_module]) == 1)
	{
		issue_404();
		
		exit;
	}


	// タスク名／モジュール名／フラグを格納
	// フラグは、カテゴリのような使い方で、DB接続処理やセッションの省略などの
	// 処理わけに利用する

	$g_name_task = $name_task;
	$g_name_module = $name_module;
	$ltmpar = explode(';', $DISPATCH_CLASS_NAMES[$name_task][$name_module]);
	if(is_array($ltmpar))
	{
		$g_name_flgmark = $ltmpar[1];
	}else{
		$g_name_flgmark = "";
	}



	// --------------------------------------
	// サイトのIDを設定してください。
	// --------------------------------------
	// サイトID
	define("SITE_ID", 19, FALSE);
	// ゼウス
	if(preg_match('/rueone/i', $_SERVER['HTTP_HOST']))
	{
		$zeus_host = 'https://hkun.wedding.trueone.co.jp';
		$auth_user = 'haika';
		$auth_pass = 'nlc987';
	}elseif(preg_match('/dev/i', $_SERVER['HTTP_HOST']))
	{
		$zeus_host = 'https://koala.naturelab.co.jp';
		$auth_user = "";
		$auth_pass = "";
	}else{
		$zeus_host = 'https://koala.naturelab.co.jp';
		$auth_user = "";
		$auth_pass = "";
	}
	// --------------------------------------

	// アイコン
	$ENUM_PUBLICITY_ICON = array(
		'1' => 'ico_new.gif',
		'2' => 'ico_media.gif',
		'3' => 'ico_campaign.gif',
		'4' => 'ico_info.gif',
		'5' => 'ico_other.gif'
	);
	
	
	
	// --------------------------------------
	// 開始
	// --------------------------------------
	$log_msg = "";
	
	$log_msg .= date('Y/m/d H:i:s') . " SITE_ID：" . SITE_ID . " ";
	$log_file = './resource/log/update.log';
	$log_fp = fopen($log_file, "a+");
	if(!$log_fp)
	{
		echo 'ログファイルに書き込みできません。<br/>';
		exit;
	}
	
	$log_flg = true;
	
	//登録データとファイル生成の日付をチェック
	//最終更新日を取得
	//$lastmod_url = $zeus_host . '/publicity/webapp.php/artcle/lastmod/?id_site=' . SITE_ID;
	//$lastmod_url = $zeus_host . '/publicity/publicity_lastmod.php?id_site=' . SITE_ID;
	$lastmod_url = $zeus_host . '/modules/publicity_lastmod?id_site=' . SITE_ID;
	$lastmod = curl_get_contents($lastmod_url, NULL, FALSE, $auth_user, $auth_pass);
	$db_time = strtotime($lastmod);
	
	if(Mis_empty($lastmod) == 1)
	{
		$log_msg .= "error:get the lastmod data \n";
		fputs($log_fp, $log_msg, strlen($log_msg));
		fclose($log_fp);
		echo '更新日付を取得できませんでした。';
		exit;
	}
	
	//ファイルの作成日時
	$file = './top.html';
	$file_time = filemtime($file);
	
	//生成したファイルが古い場合は処理をストップ
	if($db_time < $file_time)
	{
		$log_msg .= "stop:not update\n";
		fputs($log_fp, $log_msg, strlen($log_msg));
		fclose($log_fp);
		echo '新着情報は更新されておりません。';
		exit;
	}
	
	//$url = $zeus_host . '/publicity/webapp.php/artcle/xml?to=' . date('Y-m-d') . '&id_site=' . SITE_ID;
	//$url = $zeus_host . '/publicity/publicity_xml.php?to=' . date('Y-m-d') . '&id_site=' . SITE_ID;
	$url = $zeus_host . '/modules/publicity_xml?to=' . date('Y-m-d') . '&id_site=' . SITE_ID;
	//XMLファイルをパースし、オブジェクトを取得
	$xml = simplexml_load_curl($url, NULL, FALSE, $auth_user, $auth_pass);

	$articles = array();
	$params = array(
		'id_publicity', 'pub_date', 'division', 'fixity_flg',
		'title', 'body', 'img_class',
		'id_attachment1', 'thumbnail1', 'file_name1', 'alt1',
		'id_attachment2', 'thumbnail2', 'file_name2', 'alt2',
		'id_attachment3', 'thumbnail3', 'file_name3',
		'link1', 'link_title1',
		'link2', 'link_title2',
		'link3', 'link_title3',
		'link4', 'link_title4',
		'del_flg', 'created', 'lastmod'
	);


	$fix_items = array();
	$fixn_items = array();
	foreach($xml->detail as $detail)
	{
		$articles[] = $detail;

		if($detail->fixity_flg == 1)
		{
			$fix_items[] = $detail;
		}else{
			$fixn_items[] = $detail;
		}

	}
	
	// ------------------------------------------------
	// TOPページ用SSIファイル作成
	// ------------------------------------------------
	
	// ファイル書き出し
	$top_tmpl = new CTagTemplate('./resource/template/top.html', CTT_FILE, CTT_UTF8);
	$top_cnt = 0;
	$entry_mstr = $top_tmpl->GetContainer('ENTRIES');
	$strEntries = "";

	// 固定
	if(Mis_array($fix_items) == 1)
	{
		foreach($fix_items as $fitem)
		{
			if($fitem->del_flg != 0)
			{
				continue;
			}
			$top_cnt++;
			if($top_cnt > 5)
			{
				break;
			}
			$entry = new CTagTemplate($entry_mstr, CTT_DATA, CTT_UTF8);
			$entry = articleReplace($entry, $fitem);
			$strEntries .= $entry->Generate();
		}
	}
	// 非固定
	if(Mis_array($fixn_items) == 1)
	{
		foreach($fixn_items as $fitem)
		{
			if($fitem->del_flg != 0)
			{
				continue;
			}
			$top_cnt++;
			if($top_cnt > 5)
			{
				break;
			}
			$entry = new CTagTemplate($entry_mstr, CTT_DATA, CTT_UTF8);
			$entry = articleReplace($entry, $fitem);
			$strEntries .= $entry->Generate();
		}
	}

	$top_tmpl->ReplaceContainer('ENTRIES', $strEntries);
	$topout = $top_tmpl->Generate();
	$fp = fopen('./top.html', "w");
	
	if($fp)
	{
		if(fputs($fp, $topout, strlen($topout)))
		{
		}else{
			$log_msg .= 'error:top.html fputs execute'. "\n";
			fputs($log_fp, $log_msg, strlen($log_msg));
			fclose($log_fp);
			echo '【index.html】書込エラー';
			exit;
		}
		
		fclose($fp);
		chmod('./top.html', 0666);
		
	}else{
		$log_msg .= 'error:top.html fopen execute'. "\n";
		fputs($log_fp, $log_msg, strlen($log_msg));
		fclose($log_fp);
			echo '【top.html】読込エラー';
		exit;
	}
	
	// ------------------------------------------------
	// 一覧ファイル作成
	// ------------------------------------------------
	$top_tmpl = new CTagTemplate('./resource/template/index.html', CTT_FILE, CTT_UTF8);
	$top_cnt = 0;
	$entry_mstr = $top_tmpl->GetContainer('ENTRIES');
	$strEntries = "";

	// 固定
	if(Mis_array($fix_items) == 1)
	{
		foreach($fix_items as $fitem)
		{
			if($fitem->del_flg != 0)
			{
				continue;
			}
			$top_cnt++;
			if($top_cnt > 20)
			{
				break;
			}
			$entry = new CTagTemplate($entry_mstr, CTT_DATA, CTT_UTF8);
			$entry = articleReplace($entry, $fitem);
			$strEntries .= $entry->Generate();
		}
	}
	// 非固定
	if(Mis_array($fixn_items) == 1)
	{
		foreach($fixn_items as $fitem)
		{
			if($fitem->del_flg != 0)
			{
				continue;
			}
			$top_cnt++;
			if($top_cnt > 20)
			{
				break;
			}
			$entry = new CTagTemplate($entry_mstr, CTT_DATA, CTT_UTF8);
			$entry = articleReplace($entry, $fitem);
			$strEntries .= $entry->Generate();
		}
	}

	$top_tmpl->ReplaceContainer('ENTRIES', $strEntries);
	$topout = $top_tmpl->Generate();
	$fp = fopen('./index.html', "w");
	
	if($fp)
	{
		if(fputs($fp, $topout, strlen($topout)))
		{
		}else{
			$log_msg .= 'error:index.html fputs execute'. "\n";
			fputs($log_fp, $log_msg, strlen($log_msg));
			fclose($log_fp);
			echo '【index.html】書込エラー';
			exit;
		}
		
		fclose($fp);
		chmod('./index.html', 0666);
		
	}else{
		$log_msg .= 'error:index.html fopen execute'. "\n";
		fputs($log_fp, $log_msg, strlen($log_msg));
		fclose($log_fp);
		echo '【index.html】読込エラー';
		exit;
	}
	
	// ------------------------------------------------
	// バックナンバーのリンクファイル作成
	// ------------------------------------------------
	$top_tmpl = new CTagTemplate('./resource/template/backnumber.html', CTT_FILE, CTT_UTF8);
	$top_cnt = 0;
	$entry_mstr = $top_tmpl->GetContainer('ENTRIES');
	$strEntries = "";

	$monthcounter = array();
	$monthitems = array();

	// 記事一覧
	if(Mis_array($articles) == 1)
	{
		foreach($articles as $fitem)
		{
			if($fitem->del_flg != 0)
			{
				continue;
			}

			$ym = date('Ym', strtotime($fitem->pub_date));
			$monthcounter[$ym]++;

			$monthitems[$ym][] = $fitem;
		}
	}
	
	if(Mis_array($monthcounter) == 1)
	{
		foreach($monthcounter as $mon => $cnt)
		{
			$month_j = substr($mon, 0, 4).'年'.substr($mon, 4, 2) . '月';

			$entry = new CTagTemplate($entry_mstr, CTT_DATA, CTT_UTF8);
			$entry->Replace('month', $mon);
			$entry->Replace('month_j', $month_j);
			$entry->Replace('cnt', $cnt);

			$strEntries .= $entry->Generate();
		}
	}


	$top_tmpl->ReplaceContainer('ENTRIES', $strEntries);
	$topout = $top_tmpl->Generate();
	$fp = fopen('./backnumber.html', "w");
	
	if($fp)
	{
		if(fputs($fp, $topout, strlen($topout)))
		{
		}else{
			$log_msg .= 'error:backnumber.html fputs execute'. "\n";
			fputs($log_fp, $log_msg, strlen($log_msg));
			fclose($log_fp);
			echo '【backnumber.html】書込エラー';
			exit;
		}
		fclose($fp);
		chmod('./backnumber.html', 0666);
	}else{
		$log_msg .= 'error:backnumber.html fopen execute'. "\n";
		fputs($log_fp, $log_msg, strlen($log_msg));
		fclose($log_fp);
		echo '【backnumber.html】読込エラー';
		exit;
	}
	
	// ------------------------------------------------
	// バックナンバーファイル作成
	// ------------------------------------------------
	if(Mis_array($monthitems) == 1)
	{
		foreach($monthitems as $mon => $items)
		{
			
			$top_tmpl = new CTagTemplate('./resource/template/yyyymm.html', CTT_FILE, CTT_UTF8);
			$top_cnt = 0;
			$entry_mstr = $top_tmpl->GetContainer('ENTRIES');
			$strEntries = "";
			$cnt = 0;
			
			foreach($items as $item)
			{
				$cnt++;
				$entry = new CTagTemplate($entry_mstr, CTT_DATA, CTT_UTF8);
				$entry = articleReplace($entry, $item);
				$strEntries .= $entry->Generate();
			}
			
			
			$top_tmpl->Replace('month_j', $month_j = substr($mon, 0, 4).'年'.substr($mon, 4, 2) . '月');
			
			$top_tmpl->ReplaceContainer('ENTRIES', $strEntries);
			$topout = $top_tmpl->Generate();
			$fp = fopen('./' . $mon . '.html', "w");

			if($fp)
			{
				if(fputs($fp, $topout, strlen($topout)))
				{
				}else{
					$log_msg .= 'error:' . $mon . '.html' . ' fputs execute '. "\n";
					fputs($log_fp, $log_msg, strlen($log_msg));
					fclose($log_fp);
					echo '【' . $mon . '.html】書込エラー';
					exit;
				}
				fclose($fp);
				chmod('./' . $mon . '.html', 0666);
				
			}else{
				$log_msg .= 'error:' . $mon . '.html' . ' fopen execute '. "\n";
				fputs($log_fp, $log_msg, strlen($log_msg));
				fclose($log_fp);
				echo '【' . $mon . '.html】読込エラー';
				exit;
			}
		}
	}
	
	$log_msg .= 'execute'. "\n";
	fputs($log_fp, $log_msg, strlen($log_msg));
	fclose($log_fp);

	echo '正常終了';
	exit;



	// 記事リプレイサー
	function articleReplace($tmpl, $xml)
	{
		global $params;
		global $auth_user, $auth_pass;
		global $ENUM_PUBLICITY_ICON;


		// ファイル確認
		if((Mis_empty(trim($xml->id_attachment1)) != 1) && (Mis_empty(trim($xml->file_name1)) != 1))
		{
			if(!file_exists('./user_images/' . trim($xml->file_name1)))
			{
				$img = curl_get_contents(trim($xml->id_attachment1), NULL, FALSE, $auth_user, $auth_pass);
				$fp = fopen('./user_images/' . trim($xml->file_name1), "w");
				fwrite($fp, $img, strlen($img));
				fclose($fp);
			}
		}
		if((Mis_empty($xml->thumbnail1) != 1) && (Mis_empty($xml->file_name_thum1) != 1))
		{
			if(!file_exists('./user_images/' . trim($xml->file_name_thum1)))
			{
				$img = curl_get_contents(trim($xml->thumbnail1), NULL, FALSE, $auth_user, $auth_pass);
				$fp = fopen('./user_images/' . trim($xml->file_name_thum1), "w");
				fwrite($fp, $img, strlen($img));
				fclose($fp);
			}
		}
		if((Mis_empty($xml->id_attachment2) != 1) && (Mis_empty($xml->file_name2) != 1))
		{
			if(!file_exists('./user_images/' . trim($xml->file_name2)))
			{
				$img = curl_get_contents(trim($xml->id_attachment2), NULL, FALSE, $auth_user, $auth_pass);
				$fp = fopen('./user_images/' . trim($xml->file_name2), "w");
				fwrite($fp, $img, strlen($img));
				fclose($fp);
			}
		}
		if((Mis_empty($xml->thumbnail2) != 1) && (Mis_empty($xml->file_name_thum2) != 1))
		{
			if(!file_exists('./user_images/' . trim($xml->file_name_thum2)))
			{
				$img = curl_get_contents(trim($xml->thumbnail2), NULL, FALSE, $auth_user, $auth_pass);
				$fp = fopen('./user_images/' . trim($xml->file_name_thum2), "w");
				fwrite($fp, $img, strlen($img));
				fclose($fp);
			}
		}

		foreach($params as $p)
		{
			if($p == 'division')
			{
				$icon = $ENUM_PUBLICITY_ICON[(int)($xml->division)];
				if(Mis_empty($icon) == 1)
				{
					$icon = $ENUM_PUBLICITY_ICON[5]; // デフォルト
				}
				$tmpl->Replace($p, $icon);
			}elseif($p == 'body'){
				$tmpl->Replace($p, nl2br($xml->$p));
			}elseif($p == 'pub_date')
			{
				$tmpl->Replace($p, date('y.m.d', strtotime($xml->$p)));
			}elseif($p == 'img_class')
			{
				$img_cnt = 0;
				if((Mis_empty($xml->file_name1) != 1) && (Mis_empty($xml->file_name_thum1) != 1)) {$img_cnt+=1;}
				if((Mis_empty($xml->file_name2) != 1) && (Mis_empty($xml->file_name_thum2) != 1)) {$img_cnt+=1;}
				
				switch ($img_cnt){
				case 1:
					$tmpl->Replace($p, '');
					break;
				case 2:
					$tmpl->Replace($p, '');
					break;
				default:
					$tmpl->Replace($p, 'thumnail-logo');
				}
				
			}else{
				//$tmpl->Replace($p, htmlspecialchars($xml->$p, ENT_QUOTES));
				$tmpl->Replace($p, htmlspecialchars($xml->$p, ENT_QUOTES));
			}
		}

		// 画像
		$image_mstr = $tmpl->GetContainer('image');
		$image_entries = "";
		if((Mis_empty($xml->file_name1) != 1) && (Mis_empty($xml->file_name_thum1) != 1))
		{
			$entry = new CTagTemplate($image_mstr, CTT_DATA, CTT_UTF8);
			$entry->Replace('attachment', trim($xml->file_name1));
			$entry->Replace('thumb', trim($xml->file_name_thum1));
			$entry->Replace('alt', trim($xml->alt1));
			$image_entries .= $entry->Generate();
		}
		if((Mis_empty($xml->file_name2) != 1) && (Mis_empty($xml->file_name_thum2) != 1))
		{
			$entry = new CTagTemplate($image_mstr, CTT_DATA, CTT_UTF8);
			$entry->Replace('attachment', trim($xml->file_name2));
			$entry->Replace('thumb', trim($xml->file_name_thum2));
			$entry->Replace('alt', trim($xml->alt2));
			$image_entries .= $entry->Generate();
		}
		$tmpl->ReplaceContainer('image', $image_entries);

		// 外部リンク
		$link_mstr = $tmpl->GetContainer('link');
		$link_entries = "";
		for($iCnt = 1; $iCnt <= 5; $iCnt++)
		{
			$lname = 'anchor_link' . $iCnt;
			$tname = 'anchor_title' . $iCnt;

			if((Mis_empty($xml->$lname) != 1) && (Mis_empty($xml->$tname) != 1))
			{
				$entry = new CTagTemplate($link_mstr, CTT_DATA, CTT_UTF8);
				$entry->Replace('link_url', $xml->$lname);
				$entry->Replace('link_title', htmlspecialchars($xml->$tname));
				$link_entries .= $entry->Generate();
			}
		}
		if(Mis_empty($link_entries) != 1)
		{
			$tmpl->ReplaceContainer('link', $link_entries);
			$tmpl->ContainerActivate('has_link');
		}else{
			$tmpl->ContainerDeactivate('link');
			$tmpl->ContainerDeactivate('has_link');
		}

		// バックナンバーファイル
		$tmpl->Replace('mon', date('Ym', strtotime($xml->pub_date)));

		return $tmpl;

	}


	// cURL汎用関数
	function curl_get_contents($url, $params=NULL, $post=FALSE, $auth_user=NULL, $auth_pass=NULL, $sslverify=TRUE, $conntimeout=5, $timeout=60)
	{
	
		if(preg_match('/https:\/\/zeus./i', $url))
		{
			$url = str_replace('https:', 'http:', $url);
		}

	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);

	    if (is_array($params))
	    {
	        $query = array();
	        foreach ($params as $name => $value) {
	            $query[] = urlencode($name) . '=' . urlencode($value);
	        }
	        $query_string = implode('&', $query);
	        curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
	    }
	    curl_setopt($ch, CURLOPT_POST, $post);
	    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $sslverify);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    if ($auth_user and $auth_pass)
	    {
	        curl_setopt($ch, CURLOPT_USERPWD, $auth_user . ':' . $auth_pass);
	    }
	    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $conntimeout);
	    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	    //set_time_limit($conntimeout + $timeout + 3);

	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);


	    $contents = curl_exec($ch);
	    curl_close($ch);

	    return $contents;
	}

	// cURL汎用関数のラッパ
	function simplexml_load_curl($url, $params=NULL, $post=FALSE, $auth_user=NULL, $auth_pass=NULL, $sslverify=TRUE, $conntimeout=5, $timeout=60)
	{
	    $contents = curl_get_contents($url, $params, $post, $auth_user, $auth_pass, $sslverify, $conntimeout, $timeout);
	    $xml = simplexml_load_string($contents);
	    return $xml;
	}



?>
