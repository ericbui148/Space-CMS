<?php
use App\Controllers\Components\UtilComponent;

if (isset($tpl['status']))
{
	$status = __('status', true);
	switch ($tpl['status'])
	{
		case 2:
			UtilComponent::printNotice(NULL, $status[2]);
			break;
	}
} else {
	if (isset($_GET['err']))
	{
		$titles = __('error_titles', true);
		$bodies = __('error_bodies', true);
		UtilComponent::printNotice(@$titles[$_GET['err']], @$bodies[$_GET['err']]);
	}
	$jqDateFormat = UtilComponent::jqDateFormat($tpl['option_arr']['o_date_format']);
	$jqTimeFormat = UtilComponent::jqTimeFormat($tpl['option_arr']['o_time_format']);
	?>
	<div class="ui-tabs ui-widget ui-widget-content ui-corner-all b10">
		<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
			<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminMessages&amp;action=Send"><?php __('lblSend'); ?></a></li>
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminQueues&amp;action=Index"><?php __('lblMailQueue'); ?></a></li>
		</ul>
	</div>
	<?php
	UtilComponent::printNotice(__('infoSendTitle', true), __('infoSendBody', true), false); 
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminMessages&amp;action=Send" method="post" id="frmSendMessage" class="form form" autocomplete="off">
		<input type="hidden" name="message_send" value="1" />
		
		<p>
			<label class="title"><?php __('lblMessage'); ?></label>
			<span class="inline_block">
				<?php
				if(count($tpl['message_arr']) > 0)
				{ 
					?>
					<select id="message_id" name="message_id" class="form-field w400 required">
						<option value="">-- <?php __('lblChoose'); ?> --</option>
						<?php
						foreach ($tpl['message_arr'] as $k => $v)
						{
							?><option value="<?php echo $v['id']; ?>" <?php echo isset($_GET['id']) ? ($v['id'] == $_GET['id'] ? 'selected="selected"' : null) : null;?>><?php echo $v['subject']; ?></option><?php
						}
						?>
					</select>
					<?php
					if(isset($_GET['id']))
					{
						?>
						<a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminMessages&amp;action=Update&amp;id=<?php echo $_GET['id']; ?>" class="edit-icon"></a>
						<a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminMessages&amp;action=Preview&amp;id=<?php echo $_GET['id']; ?>" target="_blank" class="preview-icon"></a>
						<?php
					}else{
						?>
						<a href="javascript:void(0);" class="edit-icon"></a>
						<a href="javascript:void(0);" class="preview-icon"></a>
						<?php
					} 
				}else{
					?>
					<label class="content"><?php __('lblNoMessageFound');?> <a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminMessages&amp;action=Create"><?php __('lblAddAMessage');?></a></label>
					<input type="hidden" name="message_id" class="required" />
					<?php
				}
				?>
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblGroup'); ?></label>
			<span class="iblock">
				<?php
				if(count($tpl['group_arr']) > 0)
				{ 
					?>
					<select id="group_id" name="group_id[]" class="form-field w400 required" data-placeholder="--<?php __('lblChoose'); ?>--" multiple="multiple">
						<?php
						foreach ($tpl['group_arr'] as $k => $v)
						{
							?><option value="<?php echo $v['id']; ?>" <?php echo isset($_GET['group_id']) ? ($v['id'] == $_GET['group_id'] ? 'selected="selected"' : null) : null;?>><?php echo $v['group_title']; ?></option><?php
						}
						?>
					</select>
					<?php
				}else{
					?>
					<label class="content"><?php __('lblNoGroupFound');?> <a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminGroups&amp;action=Create"><?php __('lblAddAGroup');?></a></label>
					<input type="hidden" name="group_id[]" class="required" />
					<?php
				} 
				?>
			</span>
		</p>
		<p id="total_subscribers_container">
			<label class="title"><?php __('lblTotalSubscribers'); ?></label>
			<label id="total_subscribers" class="content"></label>
		</p>
		<p>
			<label class="title"><?php __('lblSchedule'); ?></label>
			<span class="block float_left">
				<span class="block float_left t5 r10">
					<input type="radio" name="schedule" id="send_now" value="now" checked="checked" class="block float_left r3" /><label for="send_now" class="block float_left"><?php __('lblSendNow');?></label>
				</span>
				<span class="block float_left t5 r10">
					<input type="radio" name="schedule" id="send_later" value="later" class="block float_left r3"/><label for="send_later" class="block float_left"><?php __('lblSendLater');?></label>
				</span>
			</span>
		</p>
		<p class="send-on-container">
			<label class="title"><?php __('lblCurrentServerTime'); ?></label>
			<label class="content"><?php echo UtilComponent::formatDate(date('Y-m-d'), 'Y-m-d', $tpl['option_arr']['o_date_format']) . ' ' . UtilComponent::formatTime(date('H:i:s'), 'H:i:s', $tpl['option_arr']['o_time_format']); ?></label>
		</p>
		<p class="send-on-container">
			<label class="title"><?php __('lblSendOn'); ?></label>
			<span class="inline_block">
				<span class="form-field-custom form-field-custom-after">
					<input type="text" name="send_on" id="send_on" class="form-field pointer w120 datepick" readonly="readonly" rel="1" rev="<?php echo $jqDateFormat; ?>" lang="<?php echo $jqTimeFormat; ?>" value="<?php echo date($tpl['option_arr']['o_date_format']) . ' ' . date($tpl['option_arr']['o_time_format'], strtotime(date('Y-m-d 00:00:00'))); ?>" />
					<span class="form-field-after"><abbr class="form-field-icon-date"></abbr></span>
				</span>
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblSendInBatches'); ?></label>
			<span class="block float_left">
				<span class="block float_left t5 r10">
					<input type="radio" name="send_in_batches" id="no_in_batches" value="no" checked="checked" class="block float_left r3" /><label for="no_in_batches" class="block float_left"><?php __('lblNo');?></label>
				</span>
				<span class="block float_left t5 r10">
					<input type="radio" name="send_in_batches" id="yes_in_batches" value="yes" class="block float_left r3"/><label for="yes_in_batches" class="block float_left"><?php __('lblYes');?></label>
				</span>
			</span>
		</p>
		<p id="send_in_batches_container">
			<label class="title"><?php __('lblSend'); ?></label>
			<span class="block float_left nsBatches">
				<input type="text" name="send_messages" id="send_messages" class="form-field field-int w60 block float_left r10 digits" value="5" />
				<label class="block float_left r10 t5"><?php __('lblEmailsEvery'); ?></label>
				<input type="text" name="send_minutes" id="send_minutes" class="form-field field-int w60 block float_left r10 digits" value="10" />
				<label class="block float_left t5"><?php __('lblMinutes'); ?></label>
			</span>
		</p>
		<p>
			<label class="title">&nbsp;</label>
			<input type="submit" value="<?php __('lblSend'); ?>" class="button" />
		</p>
	</form>
	<?php
}
?>