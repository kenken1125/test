<?php
/*#####################################################################################

    ＊ メールフォームテンプレート(mail_template) ＊

    これはメールフォームを簡単に作成できるように制作したテンプレートデータです。
    独自のカスタマイズや値の変更などは自由ですが、もし不具合が発生しても自己責任でお願いします。
    この「index.php」は基本的にプログラムのみをまとめたファイルなのでほぼ触る部分はないかと思います。
    ※かならず送信テストを行ってください。

    ■　設定方法
    [1] - 「setting.inc」で各自必要な設定を入れてください（詳しい内容はファイル内に記述されてあります）。
    [2] - 「form.inc」にsetting.incで入れたフォームの出力用ソースコードはありますので「<?=$form_name['{設定したフォーム名}']?>」の形でフォームデータを埋め込んでください。
    [3] - 必要に応じて「complate.html」や「/frame/」ディレクトリ内にあるheader,footer情報を入れて表示とメールの送信チェックをしてください。

#####################################################################################*/

//PHP5.1.0以上の場合のみタイムゾーンを定義
if (version_compare(PHP_VERSION, '5.1.0', '>=')) {
    //タイムゾーンの設定（日本以外の場合には適宜設定ください）
    date_default_timezone_set('Asia/Tokyo');
}

//セッションを開始
session_start();

//メモリーのリミットを拡張
ini_set('memory_limit', '256M');

//サーバーのPHP情報の確認用
// phpinfo();

//$input_flg = 【入力フラグ】 -- ページ読み込み時に入力のフラグを構築。
//$check_flg = 【確認フラグ】 -- ページ読み込み時に確認のフラグを削除。
//$send_flg  = 【送信フラグ】　-- ページ読み込み時に送信のフラグを削除。
//$err_msg   = 【エラーメッセージ格納変数】 -- エラーがあればこの変数にエラーメッセージが格納される。
$input_flg = true;
$check_flg = false;
$send_flg  = false;
$err_flg  = false;
$err_msg   = array();


/*========================= ▼任意設定ここから▼ ================================================================*/
require_once "./setting.inc";
/*========================= ▲任意設定ここまで▲ ================================================================*/

/*========================= ▼メール送信処理ここから▼ ============================================================*/

//「ブラウザの戻るボタン」対策用コンタクトキーを設定
if ($input_flg && !$_POST || $_POST['back_flg']) {
    // タイムスタンプと推測できない文字列にてキーを発行
    $contact_key = time().rand(10, 99);
    // 発行したキーをセッションに保存
    $_SESSION['contact_key'] = $contact_key;
}

//入力内容が正確に最適化されているかのチェック用
if($_POST["post_flg"] && $_POST) {
    //素のPOSTデータを出力
    if($sanitize_check == "1") {
    	echo "<pre>";
    	echo "オリジナルの \$_POST : <br>\n";
    	print_r($_POST);
    	echo "</pre>";
    }
    //整形後のPOSTデータを格納
    $_POST = sanitize($_POST);
    //整形後のPOSTデータを出力
    if($sanitize_check == "1") {
    	echo "<hr>\n";
    	echo "サニタイズ後の \$_POST : <br>\n";
    	echo "<pre>";
    	print_r($_POST);
    	echo "</pre>";
    }
}

//条件分岐を入れた場合は変数の中身を書き換える
if($_POST && $form_replace && $replace_switch == 1) {
    foreach ($form_replace as $replace_key => $replace_value) {
        if(is_array($_POST[$replace_key])) {
            if(in_array($replace_value["search_value"],$_POST[$replace_key])) {
                $form_input[$replace_value["replace_post"]][$replace_value["replace_type"]] = $replace_value["replace_data"];
            }
        } else {
            if($_POST[$replace_key] == $replace_value["search_value"]) {
                $form_input[$replace_value["replace_post"]][$replace_value["replace_type"]] = $replace_value["replace_data"];
            }
        }
    }
}

//フォームのHTMLタグを生成する
if (!$_POST["post_flg"] && !$_POST["send_flg"] || !$_POST["send_flg"] && !$err_flg) {
if($input_flg) {
    foreach($form_input as $input_name => $input_type ) {
        //入力チェック
        if($_POST["post_flg"] && $input_type["validation"]) {
            foreach ($input_type["validation"] as $validation_key => $validation_value) {
                switch ($validation_value) {
                    case "required":
                    if ($_POST[$input_name] == "") {
                        $form_name[$input_name] .= "<p class=\"err_masse\">" . $input_name . "は必須です。</p>";
                        $err_flg = true;
                    }
                    break;

                    case "mail":
                    if ($_POST[$input_name] != "") {
                        if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $_POST[$input_name])) {
                            $form_name[$input_name] .= "<p class=\"err_masse\">入力されたのメールアドレスの書式が無効です。</p>";
                            $err_flg = true;
                        }
                    }
                    break;

                    case "tel":
                    if ($_POST[$input_name] != "") {
                        if(!preg_match("/^\d{10}$|^\d{11}$|([0-9]{4,})\-([0-9]{3,})\-([0-9]{3,})$|([0-9]{3,})\-([0-9]{4,})\-([0-9]{4,})$/", $_POST[$input_name])) {
                            $form_name[$input_name] .= "<p class=\"err_masse\">" . $input_name ."は10-11桁の数値で入力してください</p>";
                            $err_flg = true;
                        }
                    }
                    break;

                    case "int":
                    if ($_POST[$input_name] != "") {
                        if(!preg_match("/^[0-9]+$/", $_POST[$input_name])) {
                            $form_name[$input_name] .= "<p class=\"err_masse\">" . $input_name ."は数値のみ入力してください</p>";
                            $err_flg = true;
                        }
                    }
                    break;

                    case "kana":
                    if ($_POST[$input_name] != "") {
                        if(!preg_match("/^[ァ-ヶー]+$/u", $_POST[$input_name])) {
                            $form_name[$input_name] .= "<p class=\"err_masse\">" . $input_name ."は全角カタカナのみ入力してください</p>";
                            $err_flg = true;
                        }
                    }
                    break;

                    default:
                        die('フォーム設置中にエラーが発生しました。設定内容を見直してください');
                    break;
                }
            }
        }

        //メールアドレスの２重チェック
        if($input_type["mail_2check"] && $_POST) {
            if($_POST["mail_2check"] == "") {
                $form_name[$input_name] .= "<p class=\"err_masse\">" . $input_name . "は必須です。</p>";
                $err_flg = true;
            } elseif ($_POST["mail_2check"] != $_POST[$input_type["mail_2check"]]) {
                $form_name[$input_name] .= "<p class=\"err_masse\">入力されたメールアドレスが異なっております。</p>";
                $err_flg = true;
            } else {
                unset($_POST[$input_name]);
            }
        }

        switch ($input_type["type"]){
            //テキストその他通常フォーム
            case "text":
            case "tel":
            case "email":
            case "url":
            case "date":
            case "number":
            case "number":
            case "month":
            case "week":
            case "time":
            if($input_type["element"] != "") {
                $element[$input_type["type"]] =  " " . $input_type["element"];
            } else {
                $element[$input_type["type"]] = "";
            }

            if($input_type["mail_2check"] != "") {
                if($form_name[$input_name] == "") {
                    $form_name[$input_name] .= "<input type=\"" . $input_type["type"] . "\" name=\"mail_2check\"" . $element[$input_type["type"]] . " value=\"" . $_POST['mail_2check'] . "\"  oncopy=\"return false\" onpaste=\"return false\" oncontextmenu=\"return false\">";
                } else {
                    $form_name[$input_name] .= "<input type=\"" . $input_type["type"] . "\" name=\"mail_2check\"" . $element[$input_type["type"]] . " value=\"" . $_POST['mail_2check'] . "\"  oncopy=\"return false\" onpaste=\"return false\" oncontextmenu=\"return false\" style=\"border: 1px solid #b60b0b;\">\n";
                }
            } else {
                if($form_name[$input_name] == "") {
                    $form_name[$input_name] .= "<input type=\"" . $input_type["type"] . "\" name=\"" . $input_name . "\"" . $element[$input_type["type"]] . " value=\"" . $_POST[$input_name] . "\">";
                } else {
                    $form_name[$input_name] .= "<input type=\"" . $input_type["type"] . "\" name=\"" . $input_name . "\"" . $element[$input_type["type"]] . " value=\"" . $_POST[$input_name] . "\" style=\"border: 1px solid #b60b0b;\">\n";
                }
            }
            break;

            //チェックボックス
            case "checkbox":
            if($_POST[$input_name] != "") {
                foreach ($_POST[$input_name] as $checkbox_key => $checkbox_value) {
                    if($_POST[$input_name][$checkbox_key] != "") {
                        $checkbox_check[$checkbox_value] = "checked";
                    } else {
                        $checkbox_check[$checkbox_value] = "";
                    }
                }
            }
            if($form_name[$input_name] == "") {
                $form_name[$input_name] .= "<ul class='check_box'>\n";
                for($icheck=0; $icheck<count($input_type["value"]); $icheck++) {
                    $form_name[$input_name] .= "<li><label><input type=\"checkbox\" name=\"".$input_name."[]\" value=\"" . $input_type["value"][$icheck] . "\" " . $checkbox_check[$input_type["value"][$icheck]] . ">" . $input_type["value"][$icheck] . "</label></li>\n";
                }
            } else {
                $form_name[$input_name] .= "<ul class='check_box'>\n";
                for($icheck=0; $icheck<count($input_type["value"]); $icheck++) {
                    $form_name[$input_name] .= "<li><label style=\"border: 1px solid #b60b0b;\"><input type=\"checkbox\" name=\"".$input_name."[]\" value=\"" . $input_type["value"][$icheck] . "\" " . $checkbox_check[$input_type["value"][$icheck]] . ">" . $input_type["value"][$icheck] . "</label></li>\n";
                }
            }
            $form_name[$input_name] .= "</ul>\n";
            break;

            //ラジオ
            case "radio":
            if($_POST[$input_name] != "") {
                $radio_check[$_POST[$input_name]] = "checked";
            } else {
                $radio_check[$_POST[$input_name]] = "";
            }
            if($form_name[$input_name] == "") {
                $form_name[$input_name] .= "<ul class='radio_box'>\n";
                for($iradio=0; $iradio<count($input_type["value"]); $iradio++) {
                    $form_name[$input_name] .= "<li><label><input type=\"radio\" name=\"".$input_name."\" value=\"" . $input_type["value"][$iradio] . "\" " . $radio_check[ $input_type["value"][$iradio]] . ">" . $input_type["value"][$iradio] . "</label></li>\n";
                }
            } else {
                $form_name[$input_name] .= "<ul class='radio_box'>\n";
                for($iradio=0; $iradio<count($input_type["value"]); $iradio++) {
                    $form_name[$input_name] .= "<li><label style=\"border: 1px solid #b60b0b;\"><input type=\"radio\" name=\"".$input_name."\" value=\"" . $input_type["value"][$iradio] . "\" " . $radio_check[ $input_type["value"][$iradio]] . ">" . $input_type["value"][$iradio] . "</label></li>\n";
                }
            }
            $form_name[$input_name] .= "</ul>\n";
            break;

            //セレクトボックス
            case "select":
            if($input_type["element"] != "") {
                $element[$input_name] =  " " .$input_type["element"]; }
                else {
                    $element[$input_name] = "";
                }

                foreach ($input_type["value"] as $select_check_key => $select_check_value) {
                    if(is_array($select_check_value)) {
                        foreach ($select_check_value as $opt_check_key => $opt_check_value) {
                            if($_POST[$input_name] == $opt_check_value) {
                                $select_check[$opt_check_value] = "selected";
                            }
                        }
                    } else {
                        if($_POST[$input_name] == $select_check_value) {
                            $select_check[$select_check_value] = "selected";
                        }
                    }
                }

                if($form_name[$input_name] == "") {
                    $form_name[$input_name] .= "<select name=\"" . $input_name . "\"" . $element[$input_name] . ">\n";
                } else {
                    $form_name[$input_name] .= "<select name=\"" . $input_name . "\"" . $element[$input_name] . " style=\"border: 1px solid #b60b0b;\">\n";
                }
                foreach ($input_type["value"] as $select_key => $select_value) {
                    if(is_array($select_value)) {
                        $form_name[$input_name] .= "<optgroup label=\"" . $select_key . "\">\n";
                        foreach ($select_value as $opt_key => $opt_value) {
                            $form_name[$input_name] .= "<option value=\"" . $opt_value . "\" " . $select_check[$opt_value] . ">" . $opt_value . "</option>\n";
                        }
                        $form_name[$input_name] .= "</optgroup>";
                    } else {
                        if($select_value != $input_type["novalue"]) {
                            $select_value_element[$select_value] = $select_value;
                        } else {
                            $select_value_element[$select_value] = "";
                        }
                        $form_name[$input_name] .= "<option value=\"" . $select_value_element[$select_value] . "\" " . $select_check[$select_value] . ">" . $select_value. "</option>\n";
                    }
                }
                $form_name[$input_name] .= "</select>\n";
                break;

                //テキストエリア
                case "textarea":
                if($input_type["element"] != "") {
                    $element[$input_type["type"]] =  " " . $input_type["element"];
                } else {
                    $element[$input_type["type"]] = "";
                }
                if($form_name[$input_name] == "") {
                    $form_name[$input_name] .= "<textarea name=\"" . $input_name . "\"" . $element[$input_type["type"]] . ">" . $_POST[$input_name] ."</textarea>\n";
                } else {
                    $form_name[$input_name] .= "<textarea name=\"" . $input_name . "\"" . $element[$input_type["type"]] . " style=\"border: 1px solid #b60b0b;\">" . $_POST[$input_name] ."</textarea>\n";
                }
                break;

                //ファイル
                case "file":
                if($input_type["accept"] != "") {
                    $accept =  " accept='" . $input_type["accept"] . "'";
                } else {
                    $accept = "";
                }

                //ファイルのチェック
                if($_FILES) {
                    $err_file = false;
                    for($ifile=0; $ifile<$input_type["box"]; $ifile++) {
                        //アップロード数をチェック
                        //--------------------------------------------------------------------------------
                        if($input_type["up_count"] > 0 && $ifile == 0) {
                            $file_filter = array_filter($_FILES["upfile"]["name"]);
                            //配列の数を取得
                            $file_conut_form = count($file_filter);
                            //配列の数が設定している数値より小さければエラーメッセージを格納
                            if($input_type["up_count"] > $file_conut_form) {
                                $form_name[$input_name] .="<span class=\"err_masse\">フォームに" .  $input_type["up_count"] . "点以上のファイルをアップロードしてください</span>";
                                $err_flg = true;
                                $err_file = true;
                            }
                        }
                    }
                }
                if(!$err_file) {
                    $form_name[$input_name] .= "<ul class='upfile_box'>\n";
                } else {
                    $form_name[$input_name] .= "<ul class='upfile_box' style=\"border: 1px solid #b60b0b;\">\n";
                }
                for($ifile=0; $ifile<$input_type["box"]; $ifile++) {
                    $err_form = false;
                    $form_name[$input_name] .= "<li class=\"imgInput\">";
                    if($_FILES) {
                        //拡張子をチェック
                        //--------------------------------------------------------------------------------
                        //画像が挿入されているかチェック
                        if(strlen($_FILES["upfile"]["name"][$ifile]) > 0) {
                            //拡張子を配列に分ける
                            $arrNm[$ifile] = explode('.',$_FILES["upfile"]["name"][$ifile]);
                            //配列を反転
                            $arrNm[$ifile] = array_reverse($arrNm[$ifile]);
                            //拡張子が大文字だった場合、小文字に変換
                            $arrNm[$ifile][0] = strtolower($arrNm[$ifile][0]);
                            //拡張子のチェック
                            if(in_array($arrNm[$ifile][0],$input_type["extension"])){
                            } else {
                                $form_name[$input_name] .= "<span class=\"err_masse\">許可されていない拡張子です</span>";
                                $err_flg = true;
                                $err_form = true;
                            }
                        }

                        //ファイルサイズチェック
                        //--------------------------------------------------------------------------------
                        //画像が挿入されているかチェック
                        if(strlen($_FILES["upfile"]["name"][$ifile]) > 0) {
                            //サイズのチェック
                            if($_FILES["upfile"]["size"] [$ifile] < $input_type["size"] ) {
                            } else {
                                $form_name[$input_name] .= "<span class=\"err_masse\">ファイルサイズが大きすぎます</span>";
                                $err_flg = true;
                                $err_form = true;
                            }
                        }
                    }
                    if(!$err_form) {
                        $form_name[$input_name] .= "<input type=\"file\" name=\"upfile[]\"" . $accept . ">";
                    } else {
                        $form_name[$input_name] .= "<input type=\"file\" name=\"upfile[]\"" . $accept . " style=\"border: 1px solid #b60b0b;\">";
                    }
                    $form_name[$input_name] .= "</li>\n";
                }
                $form_name[$input_name] .= "</ul>\n";
                break;

                default:
                $form_name[] = "不正なデータです。";
            }
        }
    }
}

// 確認用
// echo "<pre>";
// print_r($form_name);
// echo "</pre>";



/*━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * 確認ボタンを押した後の処理
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━*/
if($_POST["post_flg"] && isset($_SESSION['contact_key']) && isset($_POST['contact_key']) && $_SESSION['contact_key'] == $_POST['contact_key']) {

//チェックが通ったらコンタクトキーを更新する。
unset($_POST['contact_key']);
$contact_key = time().rand(10, 99);
$_SESSION['contact_key'] = $contact_key;

//リファラーチェック
referer($Referer_check,$Referer_check_domain);

//送信ボタンのpost属性を排除
unset($_POST['post_flg']);

//指定削除に入ったフォームのPOSTを削除する
if($_POST && $form_delete &&  $delete_switch == 1) {
    foreach ($form_delete as $delete_key => $delete_value) {
        if(is_array($_POST[$delete_value["search_post"]])) {
            foreach ($delete_value["search_value"] as $search_key => $search_value) {
                if(in_array($search_value, $_POST[$delete_value["search_post"]]) && isset($_POST[$delete_key])) {
                    unset($_POST[$delete_key]);
                }
            }
        } else {
            if($delete_value["search_value"] == $_POST[$delete_value["search_post"]] && isset($_POST[$delete_key])) {
                unset($_POST[$delete_key]);
            } elseif ($delete_value["search_value"] == 1 && $_POST[$delete_value["search_post"]] != "" && isset($_POST[$delete_key])) {
                unset($_POST[$delete_key]);
            }
        }
    }
}


//エラーが無ければ送信
if(!$err_flg) {
    //ファイルをサーバーにアップロード
    if($_FILES) {
        //最後にファイルの中身をすべて格納する変数を用意
        $file_postArr = 1;
        //ファイルアップロードの処理
        foreach ($_FILES["upfile"]["name"] as $f_key => $f_val) {
            //拡張子を配列に分ける
            $arrNm[$f_key] = explode('.',$f_val);
            //配列を反転
            $arrNm[$f_key] = array_reverse($arrNm[$f_key]);
            //拡張子が大文字だった場合、小文字に変換
            $arrNm[$f_key][0] = strtolower($arrNm[$f_key][0]);
            //リネーム用の変数を生成(日付_ランダム数値_画像番号)
            $copyFile[$f_key] = date("Ymd-His") . "_" .rand(10, 99). "_pic0".$f_key."." . $arrNm[$f_key][0];
            //画像が挿入されているかチェック
            if(strlen($f_val) > 0) {
                //画像の保存先を変数に格納
                $filename[$f_key] = $file_folder.'/'.$copyFile[$f_key];
                //画像をアップロードする
                if(! move_uploaded_file($_FILES["upfile"]["tmp_name"][$f_key],$filename[$f_key])) {
                    //失敗した場合は警告
                    die("ファイルのアップロードに失敗しました。お手数ですが最初からやり直すか、もしくは直接HP管理者にご連絡をお願いします。");
                } else {
                    //アップロードした画像を格納する
                    if(file_exists($filename[$f_key])){
                        $_POST["upfile"][$file_postArr] = $filename[$f_key];
                        $img_filename[$f_key] = $filename[$f_key];
                        $img_hiddenpath[$file_postArr] = $img_filename[$f_key];
                        if(exif_imagetype($filename[$f_key])) {
                            $img_filepath[$file_postArr] = "<img src='".$img_filename[$f_key]."' style='max-height: 200px; max-width: 100%;'>";
                        } else {
                            $img_filepath[$file_postArr] = $f_val;
                        }
                        $file_postArr++;
                    }
                }
            } else { }
        }
    }

    //POSTデータの連結処理
    $cons_num  = array();
    $cons_al   = array();
    $cons_post = array();
    $cons_box  = array();
    $p = 0;
    if($consolidated == "1" && $consolidated_list != "") {
        $cons_list = array();
        foreach ($consolidated_list as $cons_k => $cons_v) {
            foreach ($cons_v as $cons_kk => $cons_vv) {
                if(isset($_POST[$cons_vv])) {
                    $f[$p][] = $_POST[$cons_vv];
                }
            }
            $cons_box[] = $cons_k;
            $cons_al[]  = $cons_v;
            $cons_num[] = count($cons_v);
            $p++;
        }

        for($w=0; $w<count($consolidated_list); $w++) {
            $cons_post[$w]   = "<dl><dt>".$cons_box[$w]."</dt><dd>".implode("", $f[$w])."</dd></dl>\n";
            $cons_hidden[$w] = "<input type=\"hidden\" name=\"".$cons_box[$w]."\" value=\"".implode("", $f[$w])."\">";
            $cons_send[$w]   = implode("", $f[$w]);
            $cons_ttl[$w]    = $cons_box[$w];
        }
    }

    //確認画面を解放
    $check_flg = true;
    $input_flg = false;
}

} elseif($_POST["post_flg"] && $err_flg == false) {
    header ("Refresh: 2; URL=./");
    die('送信失敗しました。お手数ですが最初からやり直してください。');
}


/*━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * 送信ボタンを押した後の処理
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━*/
if ($_POST["send_flg"] && isset($_SESSION['contact_key']) && isset($_POST['contact_key']) && $_SESSION['contact_key'] == $_POST['contact_key']) {

    if($form_check == "0") {
        header ("Refresh: 2; URL=".$complate_url."");
    }

    //条件がそろえばリロード対策変数を削除する。
    unset($_SESSION['contact_key']);
    unset($_POST['contact_key']);

	//リファラーチェック
	referer($Referer_check,$Referer_check_domain);

    //送信内容から「send_flg」のみ削除
    unset($_POST['send_flg']);

    //nameが「file」 の場合は送信内容に出力しない
	foreach ($_POST as $post_key => $post_val) {
        if($post_key == 'upfile') {
            $mail_form[] = "";
        } else {
            $mail_form[] = "【" .$post_key."】\n".$post_val."\n";
        }
	}

    //文字コード設定
	mb_language("Japanese");
	mb_internal_encoding("UTF-8");

    //送信エラー時の確認先を指定
    $opt = '-f'.$mail_from;

    //------------------------------------------------------------------
    //送信者宛てメール
    //------------------------------------------------------------------
    if($remail == "1") {

        //送信者情報（メールベース設定）
        $mail_header = 'From:' . mb_encode_mimeheader($mail_title) . " <$mail_from>\r\n";
        $mail_header .= "Reply-to: {$mail_from}}\r\n";
        $mail_header .= "X-Mailer: PHP/". phpversion();

        //送信エラー時の確認先を指定
        $opt = '-f'.$mail_from;

        //メール本文を格納
        if($mail_sender != "") { $c_mbody .= strip_tags(html_entity_decode(mb_convert_kana(htmlspecialchars($_POST[$mail_sender]))))."様"."\n"; }
        $c_mbody .= $mail_body_header."\n";

        //メール本文の内容を展開
        for($i=0; $i<count($mail_form); $i++) {
            $c_mbody .= $mail_form[$i]."\n";
        }
        if($_POST['upfile']) {
            $c_mbody .=  "【ファイル】\n" . count($_POST['upfile']) . "点のファイルを送信しています。\n";
        }
        $c_mbody .= $mail_body_footer;

        if(!mb_send_mail($_POST[$mail_to], $mail_subject." | ".$mail_title, $c_mbody, $mail_header, $opt)){
            die("予期せぬエラーが発生がメールが送信できませんでした。問い合わせをやり直していただくか、管理者宛てに直接をお電話ください。");
        }

        //テスト用コード
    	if($form_check == "1") {
    		echo "※お問い合わせ用メール<br>";
    		print $cus_mbody;
    	}
	}


    //------------------------------------------------------------------
    //管理人宛てメール
    //------------------------------------------------------------------
    //送信者情報（メールベース設定）
    $mail_header = 'From:' . mb_encode_mimeheader($_POST[$mail_sender]) . " <".$_POST[$mail_to].">\n";

    //CCを挿入
    if($switch_cc == 1) {
        $mail_header .= 'Cc:'.$mail_cc."\n";
    }
    //BCCを挿入
    if($switch_bcc == 1) {
        $mail_header .= 'Bcc:'.$mail_bcc."\n";
    }
    $mail_header .= "Reply-to: {".$_POST[$mail_to]."}\r\n";
    $mail_header .= "X-Mailer: PHP/". phpversion();

    //画像がアップロードされていれば添付する
    if(isset($_POST['upfile']) == true) {

        //画像送信に必要なコード
        $mail_header .= "MIME-Version: 1.0\r\n";
        $mail_header .= "Content-Type: multipart/mixed; boundary=\"__PHPRECIPE__\"\r\n";
        //画像の拡張子を分割
        foreach($_POST['upfile'] as $key => $value) {
            $arrNm[$key] = explode('.',$value);
        }

        //画像のリストを主力
        $m_mbody .= "--__PHPRECIPE__\r\n";
        foreach($_POST['upfile'] as $key => $value) {
            //mineタイプを設定
            foreach ($c_type as $c_key => $c_value) {
                if($c_key == $arrNm[$key][1]) {
                    $mime_type[$key] = $c_value;
                }
            }

            $imgNum = $key+1;
            $m_mbody .= "Content-Type: ".$mime_type[$key]."; name=\"pic_0".$imgNum.".".$arrNm[$key][1]."\"\r\n";
            $m_mbody .= "Content-Disposition: attachment; filename=\"pic_0".$imgNum.".".$arrNm[$key][1]."\"\r\n";
            $m_mbody .= "Content-Transfer-Encoding: base64\r\n";
            $m_mbody .= "\r\n";
            $m_mbody .= chunk_split(base64_encode(file_get_contents($value))) . "\r\n";
            $m_mbody .= "\r\n";
            $m_mbody .= "--__PHPRECIPE__\r\n";
            // unlink($value);
        }
        $m_mbody .= "\r\n"; //これがないとメール本文の１行目がエラーになる。
    }

    //送信時の情報を出力
    $m_mbody .= $mail_body_master;
    $m_mbody .= "\r\n";
    if($mail_sender != "") {
        $m_mbody .= "◆差出人\n";
        $m_mbody .= "----------------------------------------------------------------------\n";
        $m_mbody .= strip_tags(html_entity_decode(mb_convert_kana(htmlspecialchars($_POST[$mail_sender]))))."様"."\n";
        $m_mbody .= "\n\n";
    }
    $m_mbody .= "◆送信情報\n";
    $m_mbody .= "----------------------------------------------------------------------\n";
    $m_mbody .= $mail_title ."の『". $page_title."』より送信\n";
    $m_mbody .= "【URL】 http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] . "\n";
    $m_mbody .= "【送信日時】 ".date("Y/m/d H:i:s")."\n";
    $m_mbody .= "【デバイス】 ".ua($_SERVER['HTTP_USER_AGENT'])."\n";
    $m_mbody .= "【送信者のIPアドレス】 ".$_SERVER["REMOTE_ADDR"]."\n";
    $m_mbody .= "\n\n";
    $m_mbody .= "◆".$page_title."内容\n";
    $m_mbody .= "----------------------------------------------------------------------\n";

    //メール本文の内容を展開
    for($i=0; $i<count($mail_form); $i++) {
        $m_mbody .= $mail_form[$i]."\n";
    }

    //ファイルが入っていれば添付数を表示
    if($_POST['upfile']) {
        $m_mbody .= "【ファイル】\n" . count($_POST['upfile']) . "点のファイルが添付されています。\n";
    }

    if(mb_send_mail($mail_from, 'お問い合わせがありました。', $m_mbody, $mail_header, $opt)) {
        //送信エリアを解放
    	$send_flg = true;
    	$input_flg = false;
    } else {
        die("予期せぬエラーが発生しメールが送信できませんでした。再度やり直していただくか、管理者宛てに直接をお電話ください。");
    }

    //テスト用コード
    if($form_check == "1") {
        echo "<br><br>※管理人用メール<br>";
        print $mail_master;
    }

    //送信完了したらセッションを破棄
    session_destroy();

} elseif($_POST["send_flg"]) {
    header ("Refresh: 2; URL=./");
    die('送信失敗しました。お手数ですが最初からやり直してください。');
}


/*========================= ▲メール送信処理ここまで▲ =====================================================*/


/*========================= ▼ユーザー定義関数▼ =========================================================*/

// ◆クロスサイトスクリプティング（入力データの汎用サニタイズ）
// ※htmlタグの無効化
// ※全角数値⇒半角数値に変換
function sanitize($form_sanitize) {
    $_sanitize = array();
    foreach($form_sanitize as $key=>$value) {
        if (is_array($value)) {
            $_sanitize[$key] = sanitize($value);
        } else {
            $_sanitize[$key] = strip_tags(html_entity_decode(mb_convert_kana(htmlspecialchars($value),"n")));
        }
    }
    return $_sanitize;
}

// ◆入力画面のボタン&コンタクトキー
function post_form() {
    global $contact_key;
    $post_form .= '<div id="form_submit">';
    $post_form .= '<input type="hidden" name="contact_key" value="' . $contact_key . '">';
    $post_form .= '<input type="submit" name="post_flg" value="入力内容を確認する">';
    $post_form .= '</div>';
    return $post_form;
}

// ◆入力内容の確認用関数
function post_check($post) {
    global $check_text;
    global $file_postname;
    global $img_filepath;
    global $cons_al;
    global $cons_num;
    global $cons_post;
    $check_i = 0;
    $check_o = 0;
    $check_m = 0;
    $check_f = 0;

    $post_check .= '<div class="contact_information">';
    $post_check .= $check_text;
    $post_check .= '</div>';
    foreach ($post as $post_key => $post_val) {
        if(is_array($post_val)) {
            if($post_key == 'upfile') {
                for($in=0; $in<count($img_filepath); $in++) {
                    $in_ttl = $in+1;
                    $post_check .= "<dl><dt>".$file_postname.$in_ttl ."</dt>";
                    $post_check .= "<dd><p>".$img_filepath[$in_ttl]."</p></dd>";
                    $post_check .= "</dl> \n";
                }
            } else {
                $post_check .= "<dl><dt>".$post_key."</dt><dd>".implode(",", $post[$post_key])."</dd></dl> \n";
            }
        } else if($post_key == $cons_al[$check_i][$check_o]) {
            $check_o++;
            if($check_o >= $cons_num[$check_i]) {
                $post_check .= $cons_post[$check_i];
                $check_i++;
                $check_o = 0;
            }
        } else if($post_key == "post_flg" || $post_key == "mail_2check" ) {

        } else {
            $post_check .= "<dl><dt>".$post_key."</dt><dd>".$post_val."</dd></dl> \n";
        }
    }
    return $post_check;
}

// ◆入力内容の送信用関数
function post_send($post) {
    global $file_postname;
    global $img_filepath;
    global $img_hiddenpath;
    global $cons_al;
    global $cons_num;
    global $cons_post;
    global $cons_hidden;
    global $contact_key;
    $submit_i = 0;
    $submit_o = 0;
    $submit_m = 0;

    $post_send .= '<form action="" method="post">';
    foreach ($post as $post_key => $post_val) {
        if(is_array($post_val)) {
            if($post_key == 'upfile') {
                for($in=0; $in<count($img_filepath); $in++) {
                    $in_ttl = $in+1;
                    $post_send .= "<input type=\"hidden\" name=\"upfile[]\" value=\"".$img_hiddenpath[$in_ttl]."\">";
                }
            } else {
                $post_send .= "<input type=\"hidden\" name=\"".$post_key."\" value=\"".implode(",", $post[$post_key])."\">\n";
            }
        } else if($post_key == $cons_al[$submit_i][$submit_o]) {
            $submit_o++;
            if($submit_o >= $cons_num[$submit_i]) {
                $post_send .= $cons_hidden[$submit_i];
                $submit_i++;
                $submit_o = 0;
            }
        } else if($post_key == "post_flg" || $post_key == "mail_2check" ) {

        } else {
            $post_send .= "<input type=\"hidden\" name=\"".$post_key."\" value=\"".$post_val."\">\n";
        }
    }
    $post_send .= '<input type="hidden" name="contact_key" value="' . $contact_key . '">';
    $post_send .= '<input type="submit" name="send_flg" value="送信する">';
    $post_send .= '</form>';
    return $post_send;
}

// ◆入力内容の修正用関数
function post_prev($post) {
    $post_prev .= '<form action="" method="post">';
    foreach ($_POST as $post_key => $post_val) {
        if(is_array($post_val)) {
            foreach ($post_val as $array_key => $array_val) {
                $post_prev .= "<input type=\"hidden\" name=\"".$post_key."[]\" value=\"".$array_val."\">";
            }
        } else {
            $post_prev .= "<input type=\"hidden\" name=\"".$post_key."\" value=\"".$post_val."\">";
        }
    }
    $post_prev .= '<input type="submit" name="back_flg" value="戻る">' . "\n";
    $post_prev .= '</form>';
    return $post_prev;
}

// ◆リファラーチェック
function referer($referer_sw,$referer_domain) {
    if($referer_sw == "1" && $referer_domain != "") {
        $referer      = $_SERVER["HTTP_REFERER"];
        $referer_url  = parse_url($referer);
        $referer_host = $referer_url['host'];
        if(!strstr($referer_host,$referer_domain)){
            return exit('<p align="center">リファラチェックエラー。フォームページのドメインとこのファイルのドメインが一致しません</p>');
        }
    }
}

// ◆送信者のデバイスチェック
function ua($ua) {
    if ((strpos($ua, 'Android') !== false) && (strpos($ua, 'Mobile') !== false) || (strpos($ua, 'iPhone') !== false) || (strpos($ua, 'Windows Phone') !== false)) {
        // スマートフォンからアクセスされた場合
        $ua_check = "スマートフォンから送信";
    } elseif ((strpos($ua, 'Android') !== false) || (strpos($ua, 'iPad') !== false)) {
        // タブレットからアクセスされた場合
        $ua_check = "タブレットから送信";
    } elseif ((strpos($ua, 'DoCoMo') !== false) || (strpos($ua, 'KDDI') !== false) || (strpos($ua, 'SoftBank') !== false) || (strpos($ua, 'Vodafone') !== false) || (strpos($ua, 'J-PHONE') !== false)) {
        // 携帯からアクセスされた場合
        $ua_check = "携帯から送信";
    } else {
        // その他（PC）からアクセスされた場合
        $ua_check = "パソコンから送信";
    }
    return $ua_check;
}
/*========================= ▲ユーザー定義関数ここまで▲ ========================================================*/


/*========================= ▼HP表示部分▼ =================================================================*/
//head&ヘッダー部分のファイル
require_once "./frame/header.inc";

    //送信内容確認用
    if($form_check == "1") {
        if($check_flg) {
            echo "送信内容";
            echo "<pre>";
            print_r($_POST);
            echo "</pre>";
        }
        if($send_flg) {
            echo "送信内容の最終確認";
            echo "<pre>";
            print_r($_POST);
            echo "</pre>";
        }
    }

	if($send_flg) { //送信中ページ ?>
        <div id="mailform_area">
            <div class="contact_information send">
                <p><img src="ajax-loader.gif" alt=""></p>
                <p><?php echo $_POST[$mail_to] ?>に送信中です……</p>
                <p>しばらくお待ちください。</p>
            </div>
        </div>

    <?php } else if($check_flg) { //入力確認ページ
         // ▼全POSTデータ生成(チェック用)
         echo "<div id=\"mailform_area\">";
         require_once "./frame/step.inc";
         echo post_check($_POST);
         echo "</div>";
         echo "<div id='check_submit'>";
         echo "<div class='send_btn'>";
         // ▼全POSTデータ生成(送信用)
         echo post_send($_POST);
         echo "</div>";
         echo "<div class='prev_btn'>";
         // ▼全POSTデータ生成(戻る用)
         echo post_prev($_POST);
         echo "</div>";
         echo "</div>";

      } else if($input_flg) { //フォーム入力ページ
        echo "<div id=\"mailform_area\">";
        require_once "./frame/step.inc";
        require_once "./form.inc";
        echo "</div>";
     } else {
        //※リファラチェックでエラーが発生した場合
        exit('エラーが発生しました。お手数ですが最初からやり直してください。');
    }

    //フッター部分のファイル
    require_once "./frame/footer.inc";

/*========================= ▲HP表示部分▲ =================================================================*/
    ?>
