<?php
use App\Controllers\Components\UtilComponent;
use Core\Framework\Components\SanitizeComponent;

$info = __('info', true);
UtilComponent::printNotice("Lịch sử", "Dưới đây là lịch sử của sản phẩm");
if (count($tpl['history_arr']) > 0)
{
	?>
	<table class="table" cellpadding="0" cellspacing="0" style="width: 100%">
		<thead>
			<tr>
				<th>Ngày</th>
				<th>Sản phẩm</th>
				<th>Số lượng đầu ngày</th>
				<th>Số lượng cuối ngày</th>
			</tr>
		</thead>
		<tbody>
		<?php
		foreach ($tpl['history_arr'] as $k => $history)
		{
			$before = unserialize(base64_decode($history['before']));
			$after = unserialize(base64_decode($history['after']));
			?>
			<tr class="<?php echo $k % 2 === 0 ? 'table-row-odd' : 'table-row-even'; ?>">
				<td><?php echo date($tpl['option_arr']['o_datetime_format'], strtotime($history['created'])); ?></td>
				<td><?php echo SanitizeComponent::html($history['name']); ?></td>
				<td><?php echo $before['qty']; ?></td>
				<td><?php echo $after['qty']; ?></td>
			</tr>
			<?php
		}
		?>
		</tbody>
	</table>
	<?php
} else {
    ?>
	<table class="table" cellpadding="0" cellspacing="0" style="width: 100%">
		<thead>
			<tr>
				<th>Ngày</th>
				<th>Sản phẩm</th>
				<th>Số lượng đầu ngày</th>
				<th>Số lượng cuối ngày</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
	<?php
}
?>