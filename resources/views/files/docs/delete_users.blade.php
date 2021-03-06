<?php //show_title('Функция delete_users'); ?>

Полностью удаляет пользователя с сайта, удаляются все записи в таблицах, а также некоторые загруженные файлы, к примеру аватар, персональное фото и фотографии в галерее<br>
При выполнении функции вызывается вспомогательная функция <a href="/files/docs/delete_album">delete_album</a>
<br><br>

<pre class="d">
<b>delete_users</b>(
	string user
);
</pre><br>

<b>Параметры функции</b><br>

<b>user</b> - Логин пользователя<br><br>

<b>Примеры использования</b><br>

<?php
echo bbCode(check('[code]<?php
deleteUser("Vantuz"); /* Полностью удаляет пользователя Vantuz */
?>[/code]'));
?>

<br>
<i class="fa fa-arrow-circle-left"></i> <a href="/files/docs">Вернуться</a><br>
