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
	?>
	<div class="ui-tabs ui-widget ui-widget-content ui-corner-all b10">
		<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
			<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminMessages&amp;action=Preview&amp;id=<?php echo $tpl['arr']['id']; ?>"><?php __('lblPreview'); ?></a></li>
		</ul>
	</div>
	
	<div class="form form">

		<p>
			<label class="title"><?php __('lblSubject'); ?></label>
			<label class="content"><?php echo htmlspecialchars(stripslashes($tpl['arr']['subject'])); ?></label>
			
		</p>
		<br/>
		<div id="tabs">
			<ul>
				<li><a href="#tabs-1"><?php __('lblHTMLMessage'); ?></a></li>
				<li><a href="#tabs-2"><?php __('lblPlainMessage'); ?></a></li>
			</ul>
			<div id="tabs-1">			
				<span class="inline_block">
					<?php echo stripslashes($tpl['arr']['tinymce_message']); ?>
				</span>
			</div>
			<div id="tabs-2">
				
				<span class="inline_block">
					<?php echo nl2br(htmlspecialchars(stripslashes($tpl['arr']['plain_message']))); ?>
				</span>
				
			</div>
		</div>
		<?php
		if(!empty($tpl['file_arr']))
		{ 
			?>
			<p>
				<label class="title"><?php __('lblAttachFiles'); ?></label>
				<span class="block float_left">
					<?php
					foreach($tpl['file_arr'] as $f)
					{
						?>
						<span class="file-container">
							<label class="block float_left r10"><?php echo $f['file_name'];?></label>
							<a class="download-file" target="_blank" href="<?php echo BASE_URL;?>index.php?controllerAdminMessages&action=DownloadFile&id=<?php echo $f['id']; ?>"><?php __('lblDownload');?></a>
						</span>
						<?php
					}
					?>
				</span>
			</p>
			<?php
		} 
		?>
		
	</div>
	<?php
}
?>