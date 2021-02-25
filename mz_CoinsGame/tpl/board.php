{php}
$sign = (object) array();
$sign->info = $isSign ? "已签到" : "签到 +1";
if (!empty($tip->info)) {
$tip->on = "show";
}
{/php}

<form id="jwt-form" action="{$board}" method="POST">
  <input class="act" type="hidden" name="act" value="update">
  <div class="mz-box mz-cg">
    <div class="mz-field is-horizontal">
      <div class="field-label">

      </div>
      <div class="field-body">
        <p class="mz-note fade-out {$tip.on}">{$tip.info}</p>
      </div>
    </div>
    <div class="mz-field is-horizontal">
      <div class="field-label">
        <label class="label font-14">用户名</label>
      </div>
      <div class="field-body">
        <div class="control">
          <input class="input" type="text" id="jwt-name" name="jwt-name" placeholder="用户名…" value="{$jwt.name}">
        </div>
      </div>
    </div>
    <div class="mz-field is-horizontal">
      <div class="field-label">
        <label class="label font-14">积分</label>
      </div>
      <div class="field-body">
        {$jwt.coins} <div class="padding-left"><button id="jwt-btn-sign" type="button" class="button is-info small">{$sign.info}</button></div>
      </div>
    </div>
    <div class="mz-field is-horizontal">
      <div class="field-label">
        <label class="label font-14">解锁文章</label>
      </div>
      <div class="field-body">
        {count($jwt.log.bot)} / 37 （{$jwt.periods}）
      </div>
    </div>
    <div class="mz-field is-horizontal">
      <div class="field-label">
        <label class="label font-14">JWT密钥<br>（只读）</label>
      </div>
      <div class="field-body">
        <div class="control">
          <i class="readonly"></i>
          <textarea class="textarea" placeholder="JWT…" rows="5" readonly>{$jwt.token}</textarea>
        </div>
      </div>
    </div>
    <div class="mz-field is-horizontal">
      <div class="field-label">
        <!--  -->
      </div>
      <div class="field-body">
        <div class="control">
          <button id="jwt-btn-submit" type="submit" class="button is-info">
            更新
          </button>
        </div>
      </div>
    </div>
    <hr>
    <div class="mz-field is-horizontal">
      <div class="field-label">
        <!--  -->
      </div>
      <div class="field-body">
        <div>-help-</div>
      </div>
    </div>
    <!-- end .mz-field -->
  </div>
</form>

<!--
{php}
var_dump($jwt);
{/php}
 -->
