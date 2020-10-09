<?php
use App\Controllers\Components\UtilComponent;
use Core\Framework\Components\SanitizeComponent;
use App\Models\AHistoryModel;

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
	<div class="dashboard_header">
		<div class="dashboard_header_item">
			<div class="dashboard_info">
				<abbr><?php echo (int) @$tpl['info_arr'][0]['today_article']; ?></abbr>
				<?php (int) @$tpl['info_arr'][0]['today_article'] !== 1 ? __('dashboard_news_today') : __('dashboard_new_today'); ?>
			</div>
		</div>
		<div class="dashboard_header_item">
			<div class="dashboard_info">
				<abbr><?php echo (int) @$tpl['info_arr'][0]['week_article']; ?></abbr>
				<?php (int) @$tpl['info_arr'][0]['week_article'] !== 1 ? __('lblNewsWeek') : __('lblNewsWeek'); ?>
			</div>
		</div>
		<div class="dashboard_header_item dashboard_header_item_last">
			<div class="dashboard_info">
				<abbr><?php echo (int) @$tpl['info_arr'][0]['total_article']; ?></abbr>
				<br/>
				<?php (int) @$tpl['info_arr'][0]['total_article'] !== 1 ? __('lblTotalNews') : __('lblTotalNew'); ?>
			</div>
		</div>
	</div>
	
	<div class="dashboard_box">
		<div class="dashboard_top">
			<div class="dashboard_column_top"><?php __('dashboard_last_news'); ?> (<a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminStockOrders&amp;action=Index"><?php __('lblViewAll');?></a>)</div>
			<div class="dashboard_column_top"><?php __('dashboard_news_max_view'); ?></div>
			<div class="dashboard_column_top dashboard_column_top_last"><?php __('infoSectionHistoryTitle'); ?></div>
		</div>
		<div class="dashboard_middle">
			<div class="dashboard_column">
				<?php
				if(!empty($tpl['article_arr']))
				{
					foreach ($tpl['article_arr'] as $k => $article)
					{
						?>
						<div class="dashboard_item">
							<div class="bold fs16">
								<?php 
									$realController = 'AdminArticle';
								?>
								<a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=<?php echo $realController;?>&amp;action=Update&amp;id=<?php echo $article['id']; ?>"><?php echo $article['name']; ?></a>
							</div>
							<div><?php echo __('dashboard_date_time', true);?>: <?php echo date($tpl['option_arr']['o_date_format'], strtotime($article['created'])); ?></div>
							<div><?php echo __('dashboard_author', true);?>: <a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminUsers&amp;action=Update&amp;id=<?php echo $article['user_id']; ?>"><?php echo $article['author']; ?></a></div>
						</div>
						<?php
					}
				}else{
					?>
					<div class="dashboard_item">
						<div><?php __('lblDashNoOrdersFound');?></div>
					</div>
					<?php
				}
				?>
			</div>
			<div class="dashboard_column">
				<?php
				if(!empty($tpl['topview_article_arr']))
				{
					foreach ($tpl['topview_article_arr'] as $article)
					{
						?>
						<div class="dashboard_item">
							<div class="fs14 bold">
								<?php 
									$realController = 'AdminArticle';
								?>
								<a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=<?php echo $realController;?>&amp;action=Update&amp;id=<?php echo $article['id']; ?>"><?php echo SanitizeComponent::html($article['name']); ?></a>
							</div>
							<div>
								<?php __('lblSectionView');?>: <?php echo $article['num_view'];?>
							</div>
						</div>
						<?php
					}
				}else{
					?>
					<div class="dashboard_item">
						<div><?php __('lblDashNoProductsOrderedToday');?></div>
					</div>
					<?php
				}
				?>
			</div>
			<div class="dashboard_column dashboard_brief dashboard_column_last">
				<?php
				if(!empty($tpl['article_history_arr']))
				{
					foreach ($tpl['article_history_arr'] as $history)
					{
						?>
						<div class="dashboard_item">
							<?php
							$userName = $history['user_name'];
							$articleName = $history['article_name'];
							$articleId = $history['article_id'];
							$userId = $history['user_id'];
							$action = '';
							$modified = $history['modified'];
							switch ($history['action']){
								case AHistoryModel::ACTION_ADD:
									$action = __('article_actions_ARRAY_add', true, true);
									break;
								case AHistoryModel::ACTION_UPDATE:
									$action = __('article_actions_ARRAY_update', true, true);
									break;
								case AHistoryModel::ACTION_DELETE:
									$action = __('article_actions_ARRAY_delete', true, true);
									break;
							}
							?>
							<div class="bold fs16">
								<?php echo date($tpl['option_arr']['o_date_format'], strtotime($modified)); ?>
							</div>
							<div><?php echo __('dashboard_author', true);?>: <a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminUsers&amp;action=Update&amp;id=<?php echo $userId; ?>" ><?php echo $userName;?></a></div>
							<div><?php echo __('lblSectionAction', true);?>: <b><?php echo $action;?></b></div>
							<div> <?php echo __('lblWidgetContent', true);?>: <a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=<?php echo $realController;?>&amp;action=Update&amp;id=<?php echo $articleId; ?>" ><?php echo $articleName;?></a></div>
						</div>
						<?php
					}
				}else{
					?>
					<div class="dashboard_item">
						<div><?php __('lblDashNoProductsOrderedToday');?></div>
					</div>
					<?php
				}
				?>				
			</div>
		</div>
		<div class="dashboard_bottom"></div>
	</div>
	<?php
	$months = __('months', true);
	?>
	<div class="clear_left t20 overflow">
		<div class="float_left black pt15">
			<span class="gray"><?php echo ucfirst(__('dashboard_last_login', true)); ?>:</span>
			<?php
			list($month_index, $other) = explode("_", date("n_d, Y H:i", strtotime($_SESSION[$controller->defaultUser]['last_login'])));
			printf("%s %s", $months[$month_index], $other);
			?>
		</div>
		<div class="float_right overflow">
		<?php
		list($hour, $day, $month_index, $other) = explode("_", date("H:i_l_n_d, Y"));
		?>
			<div class="dashboard_date">
				<abbr><?php echo $day; ?></abbr>
				<?php printf("%s %s", $months[$month_index], $other); ?>
			</div>
			<div class="dashboard_hour"><?php echo $hour; ?></div>
		</div>
	</div>
	<?php
}
?>