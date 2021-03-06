<?php
view(setting('themes').'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}

if (isset($_POST['uz'])) {
    $uz = check($_POST['uz']);
} elseif (isset($_GET['uz'])) {
    $uz = check($_GET['uz']);
} else {
    $uz = '';
}

if (isAdmin([101, 102])) {
    //show_title('Управление пользователями');

    switch ($action):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            echo '<div class="form">';
            echo 'Логин пользователя:<br>';
            echo '<form action="/admin/users?act=edit" method="post">';
            echo '<input type="text" name="uz" maxlength="20">';
            echo '<input value="Редактировать" type="submit"></form></div><br>';

            echo '<a href="/admin/users?act=sort&amp;q=1">0-9</a> / <a href="/admin/users?act=sort&amp;q=a">A</a> / <a href="/admin/users?act=sort&amp;q=b">B</a> / <a href="/admin/users?act=sort&amp;q=c">C</a> / <a href="/admin/users?act=sort&amp;q=d">D</a> / <a href="/admin/users?act=sort&amp;q=e">E</a> / <a href="/admin/users?act=sort&amp;q=f">F</a> / <a href="/admin/users?act=sort&amp;q=g">G</a> / <a href="/admin/users?act=sort&amp;q=h">H</a> / <a href="/admin/users?act=sort&amp;q=i">I</a> / <a href="/admin/users?act=sort&amp;q=j">J</a> / <a href="/admin/users?act=sort&amp;q=k">K</a> / <a href="/admin/users?act=sort&amp;q=l">L</a> / <a href="/admin/users?act=sort&amp;q=m">M</a> / <a href="/admin/users?act=sort&amp;q=n">N</a> / <a href="/admin/users?act=sort&amp;q=o">O</a> / <a href="/admin/users?act=sort&amp;q=p">P</a> / <a href="/admin/users?act=sort&amp;q=q">Q</a> / <a href="/admin/users?act=sort&amp;q=r">R</a> / <a href="/admin/users?act=sort&amp;q=s">S</a> / <a href="/admin/users?act=sort&amp;q=t">T</a> / <a href="/admin/users?act=sort&amp;q=u">U</a> / <a href="/admin/users?act=sort&amp;q=v">V</a> / <a href="/admin/users?act=sort&amp;q=w">W</a> / <a href="/admin/users?act=sort&amp;q=x">X</a> / <a href="/admin/users?act=sort&amp;q=y">Y</a> / <a href="/admin/users?act=sort&amp;q=z">Z</a><br>';

            echo 'Введите логин пользователя который необходимо отредактировать<br><br>';

            echo '<b>Cписок последних зарегистрированных</b><br>';

            $total = DB::run() -> querySingle("SELECT count(*) FROM `users`;");
            $page = paginate(setting('userlist'), $total);

            if ($total > 0) {

                $queryusers = DB::select("SELECT * FROM `users` ORDER BY `joined` DESC LIMIT ".$page['offset'].", ".setting('userlist').";");

                while ($data = $queryusers -> fetch()) {
                    if (empty($data['email'])) {
                        $data['email'] = 'Не указан';
                    }

                    echo '<hr><div>'.userGender($data['login']).' <b><a href="/admin/users?act=edit&amp;uz='.$data['login'].'">'.$data['login'].'</a></b> (email: '.$data['email'].')<br>';

                    echo 'Зарегистрирован: '.dateFixed($data['joined']).'</div>';
                }

                pagination($page);


            } else {
                showError('Пользователей еще нет!');
            }
            echo '<br><br>';
        break;

        ############################################################################################
        ##                                  Сортировка профилей                                   ##
        ############################################################################################
        case 'sort':
            if (isset($_POST['q'])) {
                $q = check(strtolower($_POST['q']));
            } else {
                $q = check(strtolower($_GET['q']));
            }

            if (!empty($q)) {
                if ($q == 1) {
                    $search = "RLIKE '^[-0-9]'";
                } else {
                    $search = "LIKE '$q%'";
                }

                $total = DB::run() -> querySingle("SELECT count(*) FROM `users` WHERE LOWER(`login`) ".$search.";");
                $page = paginate(setting('usersearch'), $total);

                if ($total > 0) {

                    $queryuser = DB::select("SELECT `login`, `point` FROM `users` WHERE LOWER(`login`) ".$search." ORDER BY `point` DESC LIMIT ".$page['offset'].", ".setting('usersearch').";");

                    while ($data = $queryuser -> fetch()) {

                        echo userGender($data['login']).' <b><a href="/admin/users?act=edit&amp;uz='.$data['login'].'">'.$data['login'].'</a></b> ';

                        echo userOnline($data['login']).' ('.plural($data['point'], setting('scorename')).')<br>';
                    }

                    pagination($page);

                    echo 'Найдено совпадений: '.$total.'<br><br>';
                } else {
                    showError('Совпадений не найдено!');
                }
            } else {
                showError('Ошибка! Не выбраны критерии поиска пользователей!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/users">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                    Просмотр профиля                                    ##
        ############################################################################################
        case 'edit':

            $user = DB::run() -> queryFetch("SELECT * FROM `users` WHERE LOWER(`login`)=? LIMIT 1;", [strtolower($uz)]);

            if (!empty($user)) {
                $uz = $user['login'];

                echo userGender($user['login']).' <b>Профиль '.profile($user['login']).'</b><br><br>';

                if (getUser('login') == env('SITE_ADMIN') || getUser('login') == $user['login'] || ($user['level'] < 101 || $user['level'] > 105)) {
                    if ($user['login'] == getUser('login')) {
                        echo '<b><span style="color:#ff0000">Внимание! Вы редактируете cобственный аккаунт!</span></b><br><br>';
                    }

                    echo '<div class="form">';
                    echo '<form method="post" action="/admin/users?act=upgrade&amp;uz='.$user['login'].'&amp;uid='.$_SESSION['token'].'">';

                    if (getUser('login') == env('SITE_ADMIN')) {
                        $arr_access = [101, 102, 103, 105, 107];

                        echo 'Уровень доступа:<br>';
                        echo '<select name="level">';
                        foreach ($arr_access as $value) {
                            $selected = ($user['level'] == $value) ? ' selected="selected"' : '';
                            echo '<option value="'.$value.'"'.$selected.'>'.userLevel($value).'</option>';
                        }
                        echo '</select><br>';
                    }

                    echo 'Новый пароль: (Oставьте пустым если не надо менять)<br>';
                    echo '<input type="text" name="password" maxlength="30"><br>';
                    echo 'Страна:<br>';
                    echo '<input type="text" name="country" maxlength="30" value="'.$user['country'].'"><br>';
                    echo 'Откуда:<br>';
                    echo '<input type="text" name="city" maxlength="50" value="'.$user['city'].'"><br>';
                    echo 'Email:<br>';
                    echo '<input type="text" name="email" maxlength="50" value="'.$user['email'].'"><br>';
                    echo 'Сайт:<br>';
                    echo '<input type="text" name="site" maxlength="50" value="'.$user['site'].'"><br>';
                    echo 'Зарегистрирован:<br>';
                    echo '<input type="text" name="joined" maxlength="10" value="'.dateFixed($user['joined'], "d.m.Y").'"><br>';
                    echo 'Дата рождения:<br>';
                    echo '<input type="text" name="birthday" maxlength="10" value="'.$user['birthday'].'"><br>';
                    echo 'ICQ:<br>';
                    echo '<input type="text" name="icq" maxlength="10" value="'.$user['icq'].'"><br>';
                    echo 'Имя пользователя:<br>';
                    echo '<input type="text" name="name" maxlength="20" value="'.$user['name'].'"><br>';
                    echo 'Актив:<br>';
                    echo '<input type="text" name="point" value="'.$user['point'].'"><br>';
                    echo 'Деньги:<br>';
                    echo '<input type="text" name="money" value="'.$user['money'].'"><br>';
                    echo 'Особый статус:<br>';
                    echo '<input type="text" name="status" maxlength="25" value="'.$user['status'].'"><br>';
                    echo 'Репутация (плюсы):<br>';
                    echo '<input type="text" name="posrating" value="'.$user['posrating'].'"><br>';
                    echo 'Репутация (минусы):<br>';
                    echo '<input type="text" name="negrating" value="'.$user['negrating'].'"><br>';
                    echo 'Скин:<br>';
                    echo '<input type="text" name="themes" value="'.$user['themes'].'"><br>';

                    echo 'Пол:<br>';
                    echo '<select name="gender">';
                    $selected = ($user['gender'] == 1) ? ' selected="selected"' : '';
                    echo '<option value="1"'.$selected.'>Мужской</option>';
                    $selected = ($user['gender'] == 2) ? ' selected="selected"' : '';
                    echo '<option value="2"'.$selected.'>Женский</option>';
                    echo '</select><br>';

                    echo 'О себе:<br>';
                    echo '<textarea cols="25" rows="5" name="info">'.$user['info'].'</textarea><br>';

                    echo '<input value="Изменить" type="submit"></form></div><br>';

                    echo '<div class="b"><b>Дополнительная информация</b></div>';
                    if ($user['level'] == 'pended') {
                        echo '<span style="color:#ff0000"><b>Аккаунт не активирован</b></span><br>';
                    }

                    $visit = DB::run() -> queryFetch("SELECT `ip`, `nowtime` FROM `visit` WHERE `user`=? LIMIT 1;", [$uz]);
                    if (!empty($visit)) {
                        echo '<b>Последний визит:</b> '.dateFixed($visit['nowtime'], 'j F Y / H:i').'<br>';
                        echo '<b>Последний IP:</b> '.$visit['ip'].'<br>';
                    }

                    if ($user['level'] == 'banned' && $user['timeban'] > SITETIME) {
                        echo '<span style="color:#ff0000"><b>Пользователь забанен</b></span><br>';
                    }
                    if (!empty($user['timelastban']) && !empty($user['reasonban'])) {
                        echo '<div class="form">';
                        echo 'Последний бан: '.dateFixed($user['timelastban'], 'j F Y / H:i').'<br>';
                        echo 'Последняя причина: '.bbCode($user['reasonban']).'<br>';
                        echo 'Забанил: '.profile($user['loginsendban']).'</div>';
                    }
                    echo 'Строгих банов: <b>'.$user['totalban'].'</b><br><br>';

                    if ($user['level'] < 101 || $user['level'] > 105) {
                        echo '<i class="fa fa-times"></i> <b><a href="/admin/users?act=poddel&amp;uz='.$uz.'">Удалить профиль</a></b><br>';
                    }
                } else {
                    showError('Ошибка! У вас недостаточно прав для редактирования этого профиля!');
                }
            } else {
                showError('Ошибка! Пользователя с данным логином не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/users">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                   Изменение профиля                                    ##
        ############################################################################################
        case 'upgrade':

            $uid = check($_GET['uid']);

            if (!empty($_POST['level'])) {
                $level = intval($_POST['level']);
            }

            $pass = check($_POST['password']);
            $email = check($_POST['email']);
            $joined = check($_POST['joined']);
            $name = check($_POST['name']);
            $country = check($_POST['country']);
            $city = check($_POST['city']);
            $info = check($_POST['info']);
            $site = check($_POST['site']);
            $icq = intval($_POST['icq']);
            $gender = intval($_POST['gender']);
            $birthday = check($_POST['birthday']);
            $themes = check($_POST['themes']);
            $point = intval($_POST['point']);
            $money = intval($_POST['money']);
            $status = check($_POST['status']);
            $posrating = intval($_POST['posrating']);
            $negrating = intval($_POST['negrating']);

            if ($uid == $_SESSION['token']) {
                $user = DB::run() -> queryFetch("SELECT * FROM `users` WHERE `login`=? LIMIT 1;", [$uz]);

                if (!empty($user)) {
                    if (getUser('login') == env('SITE_ADMIN') || getUser('login') == $user['login'] || ($user['level'] < 101 || $user['level'] > 105)) {

                        if (preg_match('#^([a-z0-9_\-\.])+\@([a-z0-9_\-\.])+(\.([a-z0-9])+)+$#', $email) || empty($email)) {
                            if (preg_match('#^https?://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/])+)+$#u', $site) || empty($site)) {
                                if (preg_match('#^[0-9]{2}+\.[0-9]{2}+\.[0-9]{4}$#', $joined)) {
                                    if (preg_match('#^[0-9]{2}+\.[0-9]{2}+\.[0-9]{4}$#', $birthday) || empty($birthday)) {
                                        if ($gender == 1 || $gender == 2) {
                                            if (utfStrlen($info) <= 1000) {
                                                if (getUser('login') == env('SITE_ADMIN')) {
                                                    $access = $level;
                                                } else {
                                                    $access = $user['level'];
                                                }

                                                if (!empty($pass)) {
                                                    echo '<b><span style="color:#ff0000">Внимание! Вы изменили пароль пользователя!</span></b><br>';
                                                    echo 'Не забудьте ему напомнить его новый пароль: <b>'.$pass.'</b><br><br>';
                                                    $mdpass = password_hash($pass, PASSWORD_BCRYPT);
                                                } else {
                                                    $mdpass = $user['password'];
                                                }

                                                list($uday, $umonth, $uyear) = explode(".", $joined);
                                                $joined = mktime('0', '0', '0', $umonth, $uday, $uyear);

                                                $name = utfSubstr($name, 0, 20);
                                                $country = utfSubstr($country, 0, 30);
                                                $city = utfSubstr($city, 0, 50);
                                                $rating = $posrating - $negrating;

                                                DB::update("UPDATE `users` SET `password`=?, `email`=?, `joined`=?, `level`=?, `name`=? `country`=?, `city`=?, `info`=?, `site`=?, `icq`=?, `gender`=?, `birthday`=?, `themes`=?, `point`=?, `money`=?, `status`=?, `rating`=?, `posrating`=?, `negrating`=? WHERE `login`=? LIMIT 1;", [$mdpass, $email, $joined, $access, $name, $country, $city, $info, $site, $icq, $gender, $birthday, $themes, $point, $money, $status, $rating, $posrating, $negrating, $uz]);

                                                saveStatus();


                                                echo '<i class="fa fa-check"></i> <b>Данные пользователя успешно изменены!</b><br><br>';
                                            } else {
                                                showError('Ошибка! Слишком большая информация в графе о себе, не более 1000 символов!');
                                            }
                                        } else {
                                            showError('Ошибка! Вы не указали пол пользователя!');
                                        }
                                    } else {
                                        showError('Ошибка! Недопустимая дата дня рождения, необходим формат (дд.мм.гггг)!');
                                    }
                                } else {
                                    showError('Ошибка! Недопустимая дата регистрации, необходим формат (дд.мм.гггг)!');
                                }
                            } else {
                                showError('Ошибка! Недопустимый адрес сайта, необходим формат http://site.domen!');
                            }
                        } else {
                            showError('Ошибка! Вы ввели неверный адрес email, необходим формат name@site.domen!');
                        }
                    } else {
                        showError('Ошибка! У вас недостаточно прав для редактирования этого профиля!');
                    }
                } else {
                    showError('Ошибка! Пользователя с данным логином не существует!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/users?act=edit&amp;uz='.$uz.'">Вернуться</a><br>';
            echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/users">Выбор юзера</a><br>';
        break;

        ############################################################################################
        ##                           Подтверждение удаление профиля                               ##
        ############################################################################################
        case 'poddel':

            echo '<i class="fa fa-times"></i> Вы подтверждаете, что хотите полностью удалить аккаунт пользователя <b>'.$uz.'</b>?<br><br>';

            echo '<div class="form">';
            echo '<form action="/admin/users?act=deluser&amp;uz='.$uz.'&amp;uid='.$_SESSION['token'].'" method="post">';

            echo '<b>Добавить в черный список:</b><br>';
            echo 'Логин пользователя: <input name="loginblack" type="checkbox" value="1"  checked="checked"><br>';
            echo 'Email пользователя: <input name="mailblack" type="checkbox" value="1"  checked="checked"><br><br>';

            echo '<b>Удаление сообщений:</b><br>';
            echo 'Темы в форуме: <input name="deltopicforum" type="checkbox" value="1"><br>';
            echo 'Темы и сообщения: <input name="delpostforum" type="checkbox" value="1"><br>';
            echo 'Комментарии в галерее: <input name="delcommphoto" type="checkbox" value="1"><br>';
            echo 'Комментарии в новостях: <input name="delcommnews" type="checkbox" value="1"><br>';
            echo 'Комментарии в блогах: <input name="delcommblog" type="checkbox" value="1"><br>';
            echo 'Комментарии в загрузках: <input name="delcommload" type="checkbox" value="1"><br>';
            echo 'Фотографии в галерее: <input name="delimages" type="checkbox" value="1"><br><br>';

            echo '<input type="submit" value="Удалить профиль"></form></div><br>';

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/users?act=edit&amp;uz='.$uz.'">Вернуться</a><br>';
            echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/users">Выбор юзера</a><br>';
            break;
        ############################################################################################
        ##                                   Удаление профиля                                     ##
        ############################################################################################
        case 'deluser':

            $uid = check($_GET['uid']);
            $loginblack = (empty($_POST['loginblack'])) ? 0 : 1;
            $mailblack = (empty($_POST['mailblack'])) ? 0 : 1;
            $deltopicforum = (empty($_POST['deltopicforum'])) ? 0 : 1;
            $delpostforum = (empty($_POST['delpostforum'])) ? 0 : 1;
            $delcommphoto = (empty($_POST['delcommphoto'])) ? 0 : 1;
            $delcommnews = (empty($_POST['delcommnews'])) ? 0 : 1;
            $delcommblog = (empty($_POST['delcommblog'])) ? 0 : 1;
            $delcommload = (empty($_POST['delcommload'])) ? 0 : 1;
            $delimages = (empty($_POST['delimages'])) ? 0 : 1;

            if ($uid == $_SESSION['token']) {
                $user = DB::run() -> queryFetch("SELECT * FROM `users` WHERE `login`=? LIMIT 1;", [$uz]);

                if (!empty($user)) {
                    if ($user['level'] < 101 || $user['level'] > 105) {

                        // -------------//
                        if (!empty($mailblack)) {
                            $blackmail = DB::run() -> querySingle("SELECT `id` FROM `blacklist` WHERE `type`=? AND `value`=? LIMIT 1;", [1, $user['email']]);
                            if (empty($blackmail) && !empty($user['email'])) {
                                DB::insert("INSERT INTO `blacklist` (`type`, `value`, `user`, `time`) VALUES (?, ?, ?, ?);", [1, $user['email'], getUser('login'), SITETIME]);
                            }
                        }

                        // -------------//
                        if (!empty($loginblack)) {
                            $blacklogin = DB::run() -> querySingle("SELECT `id` FROM `blacklist` WHERE `type`=? AND `value`=? LIMIT 1;", [2, strtolower($user['login'])]);
                            if (empty($blacklogin)) {
                                DB::insert("INSERT INTO `blacklist` (`type`, `value`, `user`, `time`) VALUES (?, ?, ?, ?);", [2, $user['login'], getUser('login'), SITETIME]);
                            }
                        }

                        // ------ Удаление фотографий в галерее -------//
                        if (!empty($delimages)) {
                            deleteAlbum($uz);
                        }

                        // ------ Удаление тем в форуме -------//
                        if (!empty($delpostforum) || !empty($deltopicforum)) {

                            $query = DB::select("SELECT `id` FROM `topics` WHERE `author`=?;", [$uz]);
                            $topics = $query -> fetchAll(PDO::FETCH_COLUMN);

                            if (!empty($topics)){
                                $strtopics = implode(',', $topics);

                                // ------ Удаление загруженных файлов -------//
                                foreach($topics as $delDir){
                                    removeDir(HOME.'/uploads/forum/'.$delDir);
                                }
                                DB::delete("DELETE FROM `files_forum` WHERE `post_id` IN (".$strtopics.");");
                                // ------ Удаление загруженных файлов -------//

                                DB::delete("DELETE FROM `posts` WHERE `topic_id` IN (".$strtopics.");");
                                DB::delete("DELETE FROM `topics` WHERE `id` IN (".$strtopics.");");
                            }

                            // ------ Удаление сообщений в форуме -------//
                            if (!empty($delpostforum)) {
                                DB::delete("DELETE FROM `posts` WHERE `user`=?;", [$uz]);

                                // ------ Удаление загруженных файлов -------//
                                $queryfiles = DB::select("SELECT * FROM `files_forum` WHERE `user`=?;", [$uz]);
                                $files = $queryfiles->fetchAll();

                                if (!empty($files)){
                                    foreach ($files as $file){
                                        if (file_exists(HOME.'/uploads/forum/'.$file['topic_id'].'/'.$file['hash'])){
                                            unlink(HOME.'/uploads/forum/'.$file['topic_id'].'/'.$file['hash']);
                                        }
                                    }
                                    DB::delete("DELETE FROM `files_forum` WHERE `user`=?;", [$uz]);
                                }
                                // ------ Удаление загруженных файлов -------//
                            }

                            restatement('forum');
                        }

                        // ------ Удаление коментарий -------//
                        if (!empty($delcommblog)) {
                            DB::delete("DELETE FROM `comments` WHERE relate_type=? AND `user`=?;", ['blog', $uz]);
                            restatement('blog');
                        }

                        if (!empty($delcommload)) {
                            DB::delete("DELETE FROM `comments` WHERE relate_type=? AND `user`=?;", ['down', $uz]);
                            restatement('load');
                        }

                        if (!empty($delcommphoto)) {
                            DB::delete("DELETE FROM `comments` WHERE relate_type=? AND `user`=?;", [Photo::class, $uz]);
                            restatement('photo');
                        }

                        if (!empty($delcommnews)) {
                            DB::delete("DELETE FROM `comments` WHERE relate_type=? AND `user`=?;", ['news', $uz]);
                            restatement('news');
                        }
// @TODO: добавит остальные комментарии всего их 6 тут 4
                        // Удаление профиля
                        deleteUser($uz);

                        echo '<i class="fa fa-check"></i> <b>Профиль пользователя успешно удален!</b><br><br>';
                    } else {
                        showError('Ошибка! У вас недостаточно прав для удаления этого профиля!');
                    }
                } else {
                    showError('Ошибка! Пользователя с данным логином не существует!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/users">Вернуться</a><br>';
        break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>';

} else {
    redirect('/');
}

view(setting('themes').'/foot');
