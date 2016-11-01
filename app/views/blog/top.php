Сортировать:

<?php $active = ($order == 'blogs_read') ? ' style="font-weight: bold;"' : ''; ?>
<a href="/blog/top?sort=read"<?=$active?>>Просмотры</a>,

<?php $active = ($order == 'blogs_rating') ? ' style="font-weight: bold;"' : ''; ?>
<a href="/blog/top?sort=rated"<?=$active?>>Оценки</a>,

<?php $active = ($order == 'blogs_comments') ? ' style="font-weight: bold;"' : ''; ?>
<a href="/blog/top?sort=comm"<?=$active?>>Комментарии</a>
<hr />

<?php foreach ($blogs as $data): ?>

	<div class="b">
		<i class="fa fa-pencil"></i>
		<b><a href="/blog/blog?act=view&amp;id=<?=$data['blogs_id']?>"><?=$data['blogs_title']?></a></b> (<?=format_num($data['blogs_rating'])?>)
	</div>

	<div>
		Категория: <a href="/blog/blog?cid=<?=$data['cats_id']?>"><?=$data['cats_name']?></a><br />
		Просмотров: <?=$data['blogs_read']?><br />
		Рейтинг: <b><?=format_num($data['blogs_rating'])?></b><br />
		<a href="/blog/blog?act=comments&amp;id=<?=$data['blogs_id']?>">Комментарии</a> (<?=$data['blogs_comments']?>)
		<a href="/blog/blog?act=end&amp;id=<?=$data['blogs_id']?>">&raquo;</a>
	</div>
<?php endforeach; ?>
