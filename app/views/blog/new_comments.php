<?php foreach ($comments as $data): ?>

	<div class="b">
		<i class="fa fa-comment"></i> <b><a href="/blog/blog?act=comments&amp;id=<?=$data['commblog_blog']?>"><?=$data['blogs_title']?></a></b> (<?=$data['blogs_comments']?>)
	</div>

	<div>
		<?=bb_code($data['commblog_text'])?><br />
		Написал: <?=profile($data['commblog_author'])?> <small>(<?=date_fixed($data['commblog_time'])?>)</small><br />

		<?php if (is_admin() || empty($config['anonymity'])): ?>
			<span class="data">(<?=$data['commblog_brow']?>, <?=$data['commblog_ip']?>)</span>
		<?php endif; ?>
	</div>

<?php endforeach; ?>
