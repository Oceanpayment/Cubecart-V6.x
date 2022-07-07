<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 http://opensource.org/licenses/GPL-3.0
 */
?>
<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
	<div id="{$GENERAL_TAB_ID}" class="tab_content">
  		<h3>{$TITLE}</h3>
  		<fieldset><legend>{$LANG.module.cubecart_settings}</legend>
			<div><label for="status">{$LANG.common.status}</label><span><input type="hidden" name="module[status]" id="status" class="toggle" value="{$MODULE.status}" /></span></div>
			<div><label for="position">{$LANG.module.position}</label><span><input type="text" name="module[position]" id="position" class="textbox number" value="{$MODULE.position}" /></span></div>

			<div><label for="account">{$LANG.oceanpayment.account}</label><span><input name="module[account]" id="account" class="textbox" type="text" value="{$MODULE.account}" /></span></div>
			<div><label for="terminal">{$LANG.oceanpayment.terminal}</label><span><input name="module[terminal]" id="terminal" class="textbox" type="text" value="{$MODULE.terminal}" /></span></div>
			<div><label for="securecode">{$LANG.oceanpayment.securecode}</label><span><input name="module[securecode]" id="securecode" class="textbox" type="text" value="{$MODULE.securecode}" /></span></div>
			<div><label for="default">{$LANG.common.default}</label><span><input type="hidden" name="module[default]" id="default" class="toggle" value="{$MODULE.default}" /></span></div>
			<div><label for="description">{$LANG.common.description} *</label><span><input name="module[desc]" id="description" class="textbox" type="text" value="{$MODULE.desc}" /></span></div>
			<div><label for="payment_page_url">{$LANG.oceanpayment.payment_page_url}</label><span><input name="module[payment_page_url]" id="payment_page_url" class="textbox" type="text" value="{$MODULE.payment_page_url}" /></span> {$LANG.oceanpayment.payment_page_url_default}</div>
		</fieldset>
		<p>{$LANG.module.description_options}</p>
		</div>
  		{$MODULE_ZONES}
  		<div class="form_control">
			<input type="submit" name="save" value="{$LANG.common.save}" />
  		</div>
  	<input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>