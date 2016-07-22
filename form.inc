<?php
/*=================================================================================

 + 入力画面のテンプレート

 ■スタイル解説
 ・「.req」「.fre」：必須・任意のアイコンが入ります。
 ・「.example_txt」：例文用のスタイル
 ・「.supplemental」：補足説明用のスタイル
 ・「.file_info」：ファイルアップ用の説明文を入れるスタイル。ファイルを使う時は入れてください。

 ■補足
 ※ファイルアップロードを行いたい場合はformタグに「enctype="multipart/form-data"」を必ず入れてください。
 ※デフォルト状態では住所を自動入力するJSのプラグインとファイルから画像を参照するJSプラグインを入れています。
 　ファイル自体は「footer.inc」に書き込んでいるので組込時に外さないよう注意してください。
=================================================================================*/
?>
    <form action="./#mailform_area" method="post" enctype="multipart/form-data">
        <div class="contact_information">
            <p>■各項目を入力していただき、入力が終わりましたら入力内容の確認ボタンを押してください。</p>
        </div>
        <dl>
            <dt class="req"><span>お名前</span></dt>
            <dd>
                <?=$form_name['お名前']?>
                <p class="example_txt"><span>相川優佳</span></p>
            </dd>
        </dl>
        <dl>
            <dt class="req"><span>フリガナ</span></dt>
            <dd>
                <?=$form_name['フリガナ']?>
                <p class="example_txt"><span>アイカワユウカ</span></p>
            </dd>
        </dl>
        <dl>
            <dt class="req"><span>年齢</span></dt>
            <dd>
                <?=$form_name['年齢']?>&nbsp;歳
                <p class="example_txt"><span>20</span></p>
            </dd>
        </dl>
        <dl>
            <dt class="req"><span>性別</span></dt>
            <dd><?=$form_name['性別']?></dd>
        </dl>
        <dl>
            <dt class="req"><span>発信元</span></dt>
            <dd>
                <?=$form_name['発信元']?><br>
                <?=$form_name['その他：詳細']?>
                <p class="supplemental">その他にチェックした方は詳細を記入してください</p>
            </dd>
        </dl>
        <dl>
            <dt class="req"><span>郵便番号</span></dt>
            <dd>
                〒&nbsp;<?=$form_name['郵便番号']?>
                <p class="example_txt"><span>9812133／981-2133</span></p>
                <p class="supplemental">郵便番号をご入力後、自動的に「都道府県」と「市区町村」が入力されます。</p>
            </dd>
        </dl>
        <dl>
            <dt class="req"><span>都道府県</span></dt>
            <dd><?=$form_name['都道府県']?></dd>
        </dl>
        <dl>
            <dt class="req"><span>市区町村</span></dt>
            <dd>
                <?=$form_name['市区町村']?>
                <p class="example_txt"><span>伊具郡丸森町秋柴</span></p>
            </dd>
        </dl>
        <dl>
            <dt class="req"><span>番地・ビル名</span></dt>
            <dd>
                <?=$form_name['番地・ビル名']?>
                <p class="example_txt"><span>4-20-19　手賀プラチナ309</span></p>
            </dd>
        </dl>
        <dl>
            <dt class="req"><span>メールアドレス</span></dt>
            <dd>
                <?=$form_name['メールアドレス']?>
                <p class="example_txt"><span>post@example.co.jp</span></p>
            </dd>
        </dl>
        <dl>
            <dt class="req"><span>メールアドレス（再入力）</span></dt>
            <dd>
                <?=$form_name['メールアドレス（再入力）']?>
                <p class="supplemental">入力ミス防止のため、再度メールアドレスを入力してください。</p>
            </dd>
        </dl>
        <dl>
            <dt class="req"><span>電話番号</span></dt>
            <dd>
                <?=$form_name['電話番号']?>
                <p class="example_txt"><span>0179279963／0179-279-963</span></p>
            </dd>
        </dl>
        <dl>
            <dt class="fre"><span>FAX番号</span></dt>
            <dd>
                <?=$form_name['FAX番号']?>
                <p class="example_txt"><span>0749008185／0749-008-185</span></p>
            </dd>
        </dl>
        <dl>
            <dt class="fre"><span>ファイル</span></dt>
            <dd>
                <?=$form_name['ファイル']?>
                <p class="file_info">
                    <span class="red" style="font-size: 14px;">※ご注意!!</span><br>
                    ①<span class="red" style="font-size: 14px;">画像ファイル名は半角英数字でお願いします。</span><br>
                    (例)front.jpg　left.png　right.gif<br>
                    ②添付頂くお写真は1枚でも複数枚でもトータル３ＭＢ以内でお願いいたします。<br>
                    ③スマートフォンからアップロードする場合、古い機種やOS（４年前の機種等）を使用していますと、アップロードができない場合がございます。<br>
                    その場合はお手数ではございますが、パソコンからのお見積り依頼をお願い致します。
                </p>
            </dd>
        </dl>
        <dl>
            <dt class="fre"><span>お問い合わせ内容</span></dt>
            <dd>
                <?=$form_name['お問い合わせ内容']?>
                <p class="supplemental">お問い合わせ内容を入力してください</p>
            </dd>
        </dl>
        <?php echo post_form(); //送信ボタン ?>
    </form>
