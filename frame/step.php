<?php if($check_flg) { //入力確認ページ ?>
<ol class="stepBar step3">
    <li class="step">入力<span>画面</span></li>
    <li class="step current"><span>内容</span>確認</li>
    <li class="step"><span>送信</span>完了</li>
</ol>
<?php } elseif($input_flg) { //フォーム入力ページ ?>
<ol class="stepBar step3">
    <li class="step">入力<span>画面</span></li>
    <li class="step current"><span>内容</span>確認</li>
    <li class="step"><span>送信</span>完了</li>
</ol>
<?php } else { //完了ページ ?>
<ol class="stepBar step3">
    <li class="step normal">入力<span>画面</span></li>
    <li class="step"><span>内容</span>確認</li>
    <li class="step current"><span>送信</span>完了</li>
</ol>
<?php } ?>
