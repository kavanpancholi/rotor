<?php
view(setting('themes').'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}

if (isAdmin([101, 102, 103])) {
    //show_title('Управление антиматом');

    switch ($action):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case "index":

            echo 'Все слова в списке будут заменяться на ***<br>';
            echo 'Чтобы удалить слово нажмите на него, добавить слово можно в форме ниже<br><br>';

            $querymat = DB::select("SELECT * FROM antimat;");
            $arrmat = $querymat -> fetchAll();
            $total = count($arrmat);

            if ($total > 0) {
                foreach($arrmat as $key => $value) {
                    if ($key == 0) {
                        $comma = '';
                    } else {
                        $comma = ', ';
                    }
                    echo $comma.'<a href="/admin/antimat?act=del&amp;id='.$value['id'].'&amp;uid='.$_SESSION['token'].'">'.$value['string'].'</a>';
                }

                echo '<br><br>';
            } else {
                showError('Список пуст, добавьте слово!');
            }

            echo '<div class="b">';
            echo 'Добавить слово:<br>';
            echo '<form action="/admin/antimat?act=add&amp;uid='.$_SESSION['token'].'" method="post">';
            echo '<input type="text" name="mat">';
            echo '<input type="submit" value="Добавить"></form></div><br>';

            echo 'Всего слов в базе: <b>'.$total.'</b><br><br>';

            if (isAdmin([101]) && $total > 0) {
                echo '<i class="fa fa-times"></i> <a href="/admin/antimat?act=prodel">Очистить</a><br>';
            }
        break;

        ############################################################################################
        ##                                Добавление в список                                     ##
        ############################################################################################
        case "add":

            $uid = check($_GET['uid']);
            $mat = check(utfLower($_POST['mat']));

            if ($uid == $_SESSION['token']) {
                if (!empty($mat)) {
                    $querymat = DB::run() -> querySingle("SELECT id FROM antimat WHERE string=? LIMIT 1;", [$mat]);
                    if (empty($querymat)) {
                        DB::insert("INSERT INTO antimat (string) VALUES (?);", [$mat]);

                        setFlash('success', 'Слово успешно добавлено в список антимата!');
                        redirect("/admin/antimat");

                    } else {
                        showError('Ошибка! Введенное слово уже имеетеся в списке!');
                    }
                } else {
                    showError('Ошибка! Вы не ввели слово для занесения в список!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/antimat">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                   Удаление из списка                                   ##
        ############################################################################################
        case "del":

            $uid = check($_GET['uid']);
            $id = intval($_GET['id']);

            if ($uid == $_SESSION['token']) {
                if (!empty($id)) {
                    DB::delete("DELETE FROM antimat WHERE id=?;", [$id]);

                    setFlash('success', 'Слово успешно удалено из списка антимата!');
                    redirect("/admin/antimat");

                } else {
                    showError('Ошибка удаления! Отсутствуют выбранное слово!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/antimat">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                 Подтверждение очистки                                  ##
        ############################################################################################
        case "prodel":

            echo 'Вы уверены что хотите удалить все слова в антимате?<br>';
            echo '<i class="fa fa-times"></i> <b><a href="/admin/antimat?act=clear&amp;uid='.$_SESSION['token'].'">Да уверен!</a></b><br><br>';

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/antimat">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                   Очистка антимата                                    ##
        ############################################################################################
        case "clear":

            $uid = check($_GET['uid']);

            if (isAdmin([101])) {
                if ($uid == $_SESSION['token']) {
                    DB::delete("DELETE FROM antimat;");

                    setFlash('success', 'Список антимата успешно очищен!');
                    redirect("/admin/antimat");

                } else {
                    showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                showError('Ошибка! Очищать гостевую могут только суперадмины!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/antimat">Вернуться</a><br>';
        break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>';

} else {
    redirect("/");
}

view(setting('themes').'/foot');
