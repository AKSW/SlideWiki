<script src="static/js/questions.js"></script>
<?php if (count($testsTable) && count($listsTable)) : ?>
<h3>Tests scores for <?php echo $user_obj->username;?></h3>
<?php if (count($testsTable)) : ?>
<h4>Automatically created tests</h4>
<table>
	<thead>
		<th>Course</th>
		<th>Max score</th>
		<th>Last attempt</th>
		<th>Number of attempts</th>
	</thead>
	<tbody>
		<?php foreach($testsTable as $testsRow){ ?>
			<tr>
				<td><a href="./?url=main/tests&type=auto&id=<?php echo $testsRow['item_id']?>"><?php echo $testsRow['title']?></a></td>
				<td><?php echo number_format($testsRow['max_score'], 2)?>%</td>
				<td><?php echo $testsRow['timestamp']?></td>
				<td><?php echo $testsRow['count']?></td>
			</tr>
		<?php } ?>
	</tbody>
</table>
<?php endif;?>
<?php if (count($listsTable)) : ?>
<h4>Manually created tests</h4>
<table>
	<thead>
		<th>Title</th>
		<th>Max score</th>
		<th>Last attempt</th>
		<th>Number of attempts</th>
	</thead>
	<tbody>
		<?php foreach($listsTable as $listsRow){ ?>
			<tr>
				<td><a href="./?url=main/test&type=list&id=<?php echo $listsRow['item_id']?>"><?php echo $listsRow['title']?></a></td>
				<td><?php echo number_format($listsRow['max_score'], 2)?>%</td>
				<td><?php echo $listsRow['timestamp']?></td>
				<td><?php echo $listsRow['count']?></td>
			</tr>
		<?php } ?>
	</tbody>
</table>
<?php endif; ?>
<?php else : ?>
<center><div data-alert="alert" class="alert-message error">You don't have any scored tests</div></center>
<?php endif; ?>