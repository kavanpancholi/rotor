<?php
App::view($config['themes'].'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$cid = (isset($_GET['cid'])) ? abs(intval($_GET['cid'])) : 0;
$id = (isset($_GET['id'])) ? abs(intval($_GET['id'])) : 0;
$sort = (isset($_GET['sort'])) ? check($_GET['sort']) : 'date';
$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;

show_title('Загрузки');

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

    if (!empty($cid)) {
        $cats = DB::run() -> queryFetch("SELECT * FROM `cats` WHERE `cats_id`=? LIMIT 1;", array($cid));

        if (!empty($cats)) {
            $config['newtitle'] = $cats['cats_name'];

            echo '<a href="/load">Категории</a>';

            if (!empty($cats['cats_parent'])) {
                $podcats = DB::run() -> queryFetch("SELECT `cats_id`, `cats_name` FROM `cats` WHERE `cats_id`=? LIMIT 1;", array($cats['cats_parent']));

                echo ' / <a href="/load/down?cid='.$podcats['cats_id'].'">'.$podcats['cats_name'].'</a>';
            }

            if (empty($cats['closed'])) {
                echo ' / <a href="/load/add?cid='.$cid.'">Добавить файл</a>';
            }

            echo '<br /><br />';
            echo '<i class="fa fa-folder-open"></i> <b>'.$cats['cats_name'].'</b> (Файлов: '.$cats['cats_count'].')';

            if (is_admin(array(101, 102))) {
                echo ' (<a href="/admin/load?act=down&amp;cid='.$cid.'&amp;start='.$start.'">Управление</a>)';
            }

            switch ($sort) {
                case 'rated': $order = 'downs_rated';
                    break;
                case 'comm': $order = 'downs_comments';
                    break;
                case 'load': $order = 'downs_load';
                    break;
                default: $order = 'downs_time';
            }

            echo '<br />Сортировать: ';

            if ($order == 'downs_time') {
                echo '<b>По дате</b> / ';
            } else {
                echo '<a href="/load/down?cid='.$cid.'&amp;sort=date">По дате</a> / ';
            }

            if ($order == 'downs_load') {
                echo '<b>Скачивания</b> / ';
            } else {
                echo '<a href="/load/down?cid='.$cid.'&amp;sort=load">Скачивания</a> / ';
            }

            if ($order == 'downs_rated') {
                echo '<b>Оценки</b> / ';
            } else {
                echo '<a href="/load/down?cid='.$cid.'&amp;sort=rated">Оценки</a> / ';
            }

            if ($order == 'downs_comments') {
                echo '<b>Комментарии</b>';
            } else {
                echo '<a href="/load/down?cid='.$cid.'&amp;sort=comm">Комментарии</a>';
            }

            echo '<hr />';

            $querysub = DB::run() -> query("SELECT * FROM `cats` WHERE `cats_parent`=?;", array($cid));
            $sub = $querysub -> fetchAll();

            if (count($sub) > 0 && $start == 0) {
                foreach($sub as $subdata) {
                    echo '<div class="b"><i class="fa fa-folder-open"></i> ';
                    echo '<b><a href="/load/down?cid='.$subdata['cats_id'].'">'.$subdata['cats_name'].'</a></b> ('.$subdata['cats_count'].')</div>';
                }
                echo '<hr />';
            }

            $total = DB::run() -> querySingle("SELECT count(*) FROM `downs` WHERE `downs_cats_id`=? AND `downs_active`=?;", array($cid, 1));

            if ($total > 0) {
                if ($start >= $total) {
                    $start = 0;
                }

                $querydown = DB::run() -> query("SELECT * FROM `downs` WHERE `downs_cats_id`=? AND `downs_active`=? ORDER BY ".$order." DESC LIMIT ".$start.", ".$config['downlist'].";", array($cid, 1));

                $folder = $cats['folder'] ? $cats['folder'].'/' : '';

                while ($data = $querydown -> fetch()) {

                    $filesize = (!empty($data['downs_link'])) ? read_file(HOME.'/upload/files/'.$folder.$data['downs_link']) : 0;

                    echo '<div class="b">';
                    echo '<i class="fa fa-archive"></i> ';
                    echo '<b><a href="/load/down?act=view&amp;id='.$data['downs_id'].'">'.$data['downs_title'].'</a></b> ('.$filesize.')</div>';
                    echo '<div>';

                    echo 'Скачиваний: '.$data['downs_load'].'<br />';

                    $raiting = (!empty($data['downs_rated'])) ? round($data['downs_raiting'] / $data['downs_rated'], 1) : 0;

                    echo 'Рейтинг: <b>'.$raiting.'</b> (Голосов: '.$data['downs_rated'].')<br />';
                    echo '<a href="/load/down?act=comments&amp;id='.$data['downs_id'].'">Комментарии</a> ('.$data['downs_comments'].') ';
                    echo '<a href="/load/down?act=end&amp;id='.$data['downs_id'].'">&raquo;</a></div>';
                }

                page_strnavigation('/load/down?cid='.$cid.'&amp;sort='.$sort.'&amp;', $config['downlist'], $start, $total);
            } else {
                if (empty($cats['closed'])) {
                    show_error('В данном разделе еще нет файлов!');
                }
            }

            if (!empty($cats['closed'])) {
                show_error('В данном разделе запрещена загрузка файлов!');
            }

        } else {
            show_error('Ошибка! Данного раздела не существует!');
        }

        echo '<a href="/load/top">Топ файлов</a> / ';
        echo '<a href="/load/search">Поиск</a>';

        if (empty($cats['closed'])) {
            echo ' / <a href="/load/add?cid='.$cid.'">Добавить файл</a>';
        }
        echo '<br />';
    } else {
        redirect("/load");
    }
break;

############################################################################################
##                                    Просмотр файла                                      ##
############################################################################################
case 'view':

    $downs = DB::run() -> queryFetch("SELECT * FROM `downs` LEFT JOIN `cats` ON `downs`.`downs_cats_id`=`cats`.`cats_id` WHERE `downs_id`=? LIMIT 1;", array($id));

    if (!empty($downs)) {
        if (!empty($downs['downs_active']) || $downs['downs_user'] == $log) {

            $config['newtitle'] = $downs['downs_title'];
            $config['description'] = strip_str($downs['downs_text']);

            $folder = $downs['folder'] ? $downs['folder'].'/' : '';

            echo '<a href="/load">Категории</a> / ';

            if (!empty($downs['cats_parent'])) {
                $podcats = DB::run() -> queryFetch("SELECT `cats_id`, `cats_name` FROM `cats` WHERE `cats_id`=? LIMIT 1;", array($downs['cats_parent']));
                echo '<a href="/load/down?cid='.$podcats['cats_id'].'">'.$podcats['cats_name'].'</a> / ';
            }

            echo '<a href="/load/down?cid='.$downs['cats_id'].'">'.$downs['cats_name'].'</a> / <a href="/load/rss?id='.$id.'">RSS-лента</a><br /><br />';

            $filesize = (!empty($downs['downs_link'])) ? read_file(HOME.'/upload/files/'.$folder.$downs['downs_link']) : 0;
            echo '<i class="fa fa-archive"></i> <b>'.$downs['downs_title'].'</b> ('.$filesize.')';

            if (is_admin(array(101, 102))) {
                echo ' (<a href="/admin/load?act=editdown&amp;cid='.$downs['cats_id'].'&amp;id='.$id.'">Редактировать</a> / ';
                echo '<a href="/admin/load?act=movedown&amp;cid='.$downs['cats_id'].'&amp;id='.$id.'">Переместить</a>)';
            }
            echo '<hr />';

            if (empty($downs['downs_active']) && $downs['downs_user'] == $log){
                echo '<div class="info"><b>Внимание!</b> Данная загрузка опубликована, но еще требует модераторской проверки<br />';
                echo '<i class="fa fa-pencil"></i> <a href="/load/add?act=view&amp;id='.$id.'">Перейти к редактированию</a></div><br />';
            }

            $ext = getExtension($downs['downs_link']);

            if (in_array($ext, array('jpg', 'jpeg', 'gif', 'png'))) {
                echo '<a href="/upload/files/'.$folder.$downs['downs_link'].'">'.resize_image('upload/files/'.$folder, $downs['downs_link'], $config['previewsize'], array('alt' => $downs['downs_title'])).'</a><br />';
            }

            echo bb_code($downs['downs_text']).'<br /><br />';

            if (!empty($downs['downs_screen']) && file_exists(HOME.'/upload/screen/'.$folder.$downs['downs_screen'])) {
                echo 'Скриншот:<br />';

                echo '<a href="screen/'.$folder.$downs['downs_screen'].'">'.resize_image('upload/screen/'.$folder, $downs['downs_screen'], $config['previewsize'], array('alt' => $downs['downs_title'])).'</a><br /><br />';
            }

            if (!empty($downs['downs_author'])) {
                echo 'Автор файла: '.$downs['downs_author'];

                if (!empty($downs['downs_site'])) {
                    echo ' (<a href="'.$downs['downs_site'].'">'.$downs['downs_site'].'</a>)';
                }
                echo '<br />';
            }

            if (!empty($downs['downs_site']) && empty($downs['downs_author'])) {
                echo 'Сайт автора: <a href="'.$downs['downs_site'].'">'.$downs['downs_site'].'</a><br />';
            }

            echo 'Добавлено: '.profile($downs['downs_user']).' ('.date_fixed($downs['downs_time']).')<hr />';

            // -----------------------------------------------------------//
            if (!empty($downs['downs_link']) && file_exists(HOME.'/upload/files/'.$folder.$downs['downs_link'])) {

                if ($ext == 'mp3') {?>

                    <script src="/assets/audiojs/audio.min.js"></script>

                    <script>
                        audiojs.events.ready(function() {
                            audiojs.createAll();
                        });
                    </script>

                    <audio src="/upload/files/<?= $folder.$downs['downs_link']?>" preload="auto"></audio><br />
                    <?php
                }

                if ($ext == 'zip') {
                    echo '<i class="fa fa-archive"></i> <b><a href="/load/zip?id='.$id.'">Просмотреть архив</a></b><br />';
                }

                if (is_user()) {
                    echo '<i class="fa fa-download"></i> <b><a href="/load/down?act=load&amp;id='.$id.'">Скачать</a></b>  ('.$filesize.')<br />';
                } else {
                    echo '<div class="form">';
                    echo '<form action="/load/down?act=load&amp;id='.$id.'" method="post">';

                    echo 'Проверочный код:<br /> ';
                    echo '<img src="/captcha" alt="" /><br />';
                    echo '<input name="provkod" size="6" maxlength="6" />';
                    echo '<input type="submit" value="Скачать" /></form>';
                    echo '<em>Чтобы не вводить код при каждом скачивании, советуем <a href="/register">зарегистрироваться</a></em></div><br />';
                }

                echo '<i class="fa fa-comment"></i> <b><a href="/load/down?act=comments&amp;id='.$id.'">Комментарии</a></b> ('.$downs['downs_comments'].') ';
                echo '<a href="/load/down?act=end&amp;id='.$id.'">&raquo;</a><br />';

                $raiting = (!empty($downs['downs_rated'])) ? round($downs['downs_raiting'] / $downs['downs_rated'], 1) : 0;
                echo '<br />Рейтинг: '.raiting_vote($raiting).'<br />';
                echo 'Всего голосов: <b>'.$downs['downs_rated'].'</b><br /><br />';

                if (is_user()) {
                    echo '<form action="/load/down?act=vote&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';
                    echo '<select name="score">';
                    echo '<option value="5">Отлично</option>';
                    echo '<option value="4">Хорошо</option>';
                    echo '<option value="3">Нормально</option>';
                    echo '<option value="2">Плохо</option>';
                    echo '<option value="1">Отстой</option>';
                    echo '</select>';
                    echo '<input type="submit" value="Oценить" /></form>';
                }

                echo 'Всего скачиваний: <b>'.$downs['downs_load'].'</b><br />';
                if (!empty($downs['downs_last_load'])) {
                    echo 'Последнее скачивание: '.date_fixed($downs['downs_last_load']).'<br />';
                }

                if (is_user()) {
                    echo '<br />Скопировать адрес:<br />';
                    echo '<input name="text" size="40" value="'.$config['home'].'/upload/files/'.$folder.$downs['downs_link'].'" /><br />';
                }

                echo '<br />';
            } else {
                show_error('Файл еще не загружен!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/down?cid='.$downs['cats_id'].'">'.$downs['cats_name'].'</a><br />';

        } else {
            show_error('Ошибка! Данный файл еще не проверен модератором!');
        }
    } else {
        show_error('Ошибка! Данного файла не существует!');
    }
break;

############################################################################################
##                                     Скачивание файла                                   ##
############################################################################################
case 'load':

    if (isset($_POST['provkod'])) {
        $provkod = check(strtolower($_POST['provkod']));
    }

    if (is_user() || $provkod == $_SESSION['protect']) {

        $downs = DB::run() -> queryFetch("SELECT downs.*, folder FROM `downs` LEFT JOIN `cats` ON `downs`.`downs_cats_id`=`cats`.`cats_id` WHERE `downs_id`=? LIMIT 1;", array($id));

        if (!empty($downs)) {
            if (!empty($downs['downs_active'])) {

                $folder = $downs['folder'] ? $downs['folder'].'/' : '';

                if (file_exists('upload/files/'.$folder.$downs['downs_link'])) {
                    $queryloads = DB::run() -> querySingle("SELECT loads_ip FROM loads WHERE loads_down=? AND loads_ip=? LIMIT 1;", array($id, App::getClientIp()));
                    if (empty($queryloads)) {
                        $expiresloads = SITETIME + 3600 * $config['expiresloads'];

                        DB::run() -> query("DELETE FROM loads WHERE loads_time<?;", array(SITETIME));
                        DB::run() -> query("INSERT INTO loads (loads_down, loads_ip, loads_time) VALUES (?, ?, ?);", array($id, App::getClientIp(), $expiresloads));
                        DB::run() -> query("UPDATE downs SET downs_load=downs_load+1, downs_last_load=? WHERE downs_id=?", array(SITETIME, $id));
                    }

                    redirect("/upload/files/".$folder.$downs['downs_link']);
                } else {
                    show_error('Ошибка! Файла для скачивания не существует!');
                }
            } else {
                show_error('Ошибка! Данный файл еще не проверен модератором!');
            }
        } else {
            show_error('Ошибка! Данного файла не существует!');
        }
    } else {
        show_error('Ошибка! Проверочное число не совпало с данными на картинке!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/down?act=view&amp;id='.$id.'">Вернуться</a><br />';
break;

############################################################################################
##                                       Оценка файла                                     ##
############################################################################################
case 'vote':

    $uid = check($_GET['uid']);
    if (isset($_POST['score'])) {
        $score = abs(intval($_POST['score']));
    } else {
        $score = 0;
    }

    if (is_user()) {
        if ($uid == $_SESSION['token']) {
            if ($score > 0 && $score <= 5) {
                $downs = DB::run() -> queryFetch("SELECT * FROM `downs` WHERE `downs_id`=? LIMIT 1;", array($id));

                if (!empty($downs)) {
                    if (!empty($downs['downs_active'])) {
                        if ($log != $downs['downs_user']) {
                            $queryrated = DB::run() -> querySingle("SELECT `rated_id` FROM `rateddown` WHERE `rated_down`=? AND `rated_user`=? LIMIT 1;", array($id, $log));

                            if (empty($queryrated)) {
                                $expiresrated = SITETIME + 3600 * $config['expiresrated'];

                                DB::run() -> query("DELETE FROM `rateddown` WHERE `rated_time`<?;", array(SITETIME));
                                DB::run() -> query("INSERT INTO `rateddown` (`rated_down`, `rated_user`, `rated_time`) VALUES (?, ?, ?);", array($id, $log, $expiresrated));
                                DB::run() -> query("UPDATE `downs` SET `downs_raiting`=`downs_raiting`+?, `downs_rated`=`downs_rated`+1 WHERE `downs_id`=?", array($score, $id));

                                echo '<b>Спасибо! Ваша оценка "'.$score.'" принята!</b><br />';
                                echo 'Всего оценивало: '.($downs['downs_rated'] + 1).'<br />';
                                echo 'Средняя оценка: '.round(($downs['downs_raiting'] + $score) / ($downs['downs_rated'] + 1), 1).'<br /><br />';
                            } else {
                                show_error('Ошибка! Вы уже оценивали данный файл!');
                            }
                        } else {
                            show_error('Ошибка! Нельзя голосовать за свой файл!');
                        }
                    } else {
                        show_error('Ошибка! Данный файл еще не проверен модератором!');
                    }
                } else {
                    show_error('Ошибка! Данного файла не существует!');
                }
            } else {
                show_error('Ошибка! Необходимо поставить оценку от 1 до 5 включительно!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_login('Вы не авторизованы, для голосования за файлы, необходимо');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/down?act=view&amp;id='.$id.'">Вернуться</a><br />';
break;

############################################################################################
##                                        Комментарии                                     ##
############################################################################################
case 'comments':

    $downs = DB::run() -> queryFetch("SELECT * FROM `downs` WHERE `downs_id`=? LIMIT 1;", array($id));

    if (!empty($downs)) {
        if (!empty($downs['downs_active'])) {
            $config['newtitle'] = 'Комментарии - '.$downs['downs_title'];

            echo '<i class="fa fa-archive"></i> <b><a href="/load/down?act=view&amp;id='.$id.'">'.$downs['downs_title'].'</a></b><br /><br />';

            echo '<a href="/load/down?act=comments&amp;id='.$id.'&amp;rand='.mt_rand(100, 999).'">Обновить</a> / <a href="/load/rss?id='.$id.'">RSS-лента</a><hr />';

            $total = DB::run() -> querySingle("SELECT count(*) FROM `commload` WHERE `commload_down`=?;", array($id));

            if ($total > 0) {
                if ($start >= $total) {
                    $start = 0;
                }

                $is_admin = is_admin();
                if ($is_admin) {
                    echo '<form action="/load/down?act=del&amp;id='.$id.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';
                }

                $querycomm = DB::run() -> query("SELECT * FROM `commload` WHERE `commload_down`=? ORDER BY `commload_time` ASC LIMIT ".$start.", ".$config['downcomm'].";", array($id));

                while ($data = $querycomm -> fetch()) {
                    echo '<div class="b">';
                    echo '<div class="img">'.user_avatars($data['commload_author']).'</div>';

                    if ($is_admin) {
                        echo '<span class="imgright"><input type="checkbox" name="del[]" value="'.$data['commload_id'].'" /></span>';
                    }

                    echo '<b>'.profile($data['commload_author']).'</b> <small>('.date_fixed($data['commload_time']).')</small><br />';
                    echo user_title($data['commload_author']).' '.user_online($data['commload_author']).'</div>';

                    if (!empty($log) && $log != $data['commload_author']) {
                        echo '<div class="right">';
                        echo '<a href="/load/down?act=reply&amp;id='.$id.'&amp;pid='.$data['commload_id'].'&amp;start='.$start.'">Отв</a> / ';
                        echo '<a href="/load/down?act=quote&amp;id='.$id.'&amp;pid='.$data['commload_id'].'&amp;start='.$start.'">Цит</a> / ';
                        echo '<noindex><a href="/load/down?act=spam&amp;id='.$id.'&amp;pid='.$data['commload_id'].'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" onclick="return confirm(\'Вы подтверждаете факт спама?\')" rel="nofollow">Спам</a></noindex></div>';
                    }

                    if ($log == $data['commload_author'] && $data['commload_time'] + 600 > SITETIME) {
                        echo '<div class="right"><a href="/load/down?act=edit&amp;id='.$id.'&amp;pid='.$data['commload_id'].'&amp;start='.$start.'">Редактировать</a></div>';
                    }

                    echo '<div>'.bb_code($data['commload_text']).'<br />';

                    if (is_admin() || empty($config['anonymity'])) {
                        echo '<span class="data">('.$data['commload_brow'].', '.$data['commload_ip'].')</span>';
                    }
                    echo '</div>';
                }

                if ($is_admin) {
                    echo '<span class="imgright"><input type="submit" value="Удалить выбранное" /></span></form>';
                }

                page_strnavigation('/load/down?act=comments&amp;id='.$id.'&amp;', $config['downcomm'], $start, $total);
            } else {
                show_error('Комментариев еще нет!');
            }

            if (is_user()) {
                echo '<div class="form">';
                echo '<form action="/load/down?act=add&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';
                echo '<b>Сообщение:</b><br />';
                echo '<textarea cols="25" rows="5" name="msg"></textarea><br />';
                echo '<input type="submit" value="Написать" /></form></div><br />';

                echo '<a href="/rules">Правила</a> / ';
                echo '<a href="/smiles">Смайлы</a> / ';
                echo '<a href="/tags">Теги</a><br /><br />';
            } else {
                show_login('Вы не авторизованы, чтобы добавить сообщение, необходимо');
            }
        } else {
            show_error('Ошибка! Данный файл еще не проверен модератором!');
        }
    } else {
        show_error('Ошибка! Данного файла не существует!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/down?act=view&amp;id='.$id.'">Вернуться</a><br />';
break;

############################################################################################
##                                Добавление комментариев                                 ##
############################################################################################
case 'add':

    $uid = check($_GET['uid']);
    $msg = check($_POST['msg']);

    if (is_user()) {
        if ($uid == $_SESSION['token']) {
            if (utf_strlen($msg) >= 5 && utf_strlen($msg) < 1000) {

                $downs = DB::run() -> queryFetch("SELECT * FROM `downs` WHERE `downs_id`=? LIMIT 1;", array($id));

                if (!empty($downs)) {
                    if (!empty($downs['downs_active'])) {
                        if (is_flood($log)) {

                            $msg = antimat($msg);

                            DB::run() -> query("INSERT INTO `commload` (`commload_cats`, `commload_down`, `commload_text`, `commload_author`, `commload_time`, `commload_ip`, `commload_brow`) VALUES (?, ?, ?, ?, ?, ?, ?);", array($downs['downs_cats_id'], $id, $msg, $log, SITETIME, App::getClientIp(), App::getUserAgent()));

                            DB::run() -> query("DELETE FROM `commload` WHERE `commload_down`=? AND `commload_time` < (SELECT MIN(`commload_time`) FROM (SELECT `commload_time` FROM `commload` WHERE `commload_down`=? ORDER BY `commload_time` DESC LIMIT ".$config['maxdowncomm'].") AS del);", array($id, $id));

                            DB::run() -> query("UPDATE `downs` SET `downs_comments`=`downs_comments`+1 WHERE `downs_id`=?;", array($id));
                            DB::run() -> query("UPDATE `users` SET `users_allcomments`=`users_allcomments`+1, `users_point`=`users_point`+1, `users_money`=`users_money`+5 WHERE `users_login`=?", array($log));

                            $_SESSION['note'] = 'Сообщение успешно добавлено!';
                            redirect("/load/down?act=end&id=$id");
                        } else {
                            show_error('Антифлуд! Разрешается отправлять сообщения раз в '.flood_period().' секунд!');
                        }
                    } else {
                        show_error('Ошибка! Данный файл еще не проверен модератором!');
                    }
                } else {
                    show_error('Ошибка! Данного файла не существует!');
                }
            } else {
                show_error('Ошибка! Слишком длинное или короткое сообщение!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы добавить сообщение, необходимо');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/down?act=comments&amp;id='.$id.'">Вернуться</a><br />';
break;

############################################################################################
##                                    Жалоба на спам                                      ##
############################################################################################
case 'spam':

    $uid = check($_GET['uid']);
    $pid = abs(intval($_GET['pid']));

    if (is_user()) {
        if ($uid == $_SESSION['token']) {
            $data = DB::run() -> queryFetch("SELECT * FROM `commload` WHERE `commload_id`=? LIMIT 1;", array($pid));

            if (!empty($data)) {
                $queryspam = DB::run() -> querySingle("SELECT `spam_id` FROM `spam` WHERE `spam_key`=? AND `spam_idnum`=? LIMIT 1;", array(5, $pid));

                if (empty($queryspam)) {
                    if (is_flood($log)) {
                        DB::run() -> query("INSERT INTO `spam` (`spam_key`, `spam_idnum`, `spam_user`, `spam_login`, `spam_text`, `spam_time`, `spam_addtime`, `spam_link`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);", array(5, $data['commload_id'], $log, $data['commload_author'], $data['commload_text'], $data['commload_time'], SITETIME, $config['home'].'/load/down?act=comments&amp;id='.$id.'&amp;start='.$start));

                        $_SESSION['note'] = 'Жалоба успешно отправлена!';
                        redirect("/load/down?act=comments&id=$id&start=$start");
                    } else {
                        show_error('Антифлуд! Разрешается жаловаться на спам не чаще чем раз в '.flood_period().' секунд!');
                    }
                } else {
                    show_error('Ошибка! Жалоба на данное сообщение уже отправлена!');
                }
            } else {
                show_error('Ошибка! Выбранное вами сообщение для жалобы не существует!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы подать жалобу, необходимо');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/down?act=comments&amp;id='.$id.'&amp;start='.$start.'">Вернуться</a><br />';
break;

############################################################################################
##                                   Ответ на сообщение                                   ##
############################################################################################
case 'reply':

    $pid = abs(intval($_GET['pid']));

    echo '<b><big>Ответ на сообщение</big></b><br /><br />';

    if (is_user()) {
        $post = DB::run() -> queryFetch("SELECT * FROM `commload` WHERE `commload_id`=? LIMIT 1;", array($pid));

        if (!empty($post)) {
            echo '<div class="b"><i class="fa fa-pencil"></i> <b>'.profile($post['commload_author']).'</b> '.user_title($post['commload_author']).' '.user_online($post['commload_author']).' <small>('.date_fixed($post['commload_time']).')</small></div>';
            echo '<div>Сообщение: '.bb_code($post['commload_text']).'</div><hr />';

            echo '<div class="form">';
            echo '<form action="/load/down?act=add&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';
            echo 'Сообщение:<br />';
            echo '<textarea cols="25" rows="5" name="msg" id="msg">[b]'.nickname($post['commload_author']).'[/b], </textarea><br />';
            echo '<input type="submit" value="Ответить" /></form></div><br />';
        } else {
            show_error('Ошибка! Выбранное вами сообщение для ответа не существует!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы отвечать на сообщения, необходимо');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/down?act=comments&amp;id='.$id.'&amp;start='.$start.'">Вернуться</a><br />';
break;

############################################################################################
##                                   Цитирование сообщения                                ##
############################################################################################
case 'quote':

    $pid = abs(intval($_GET['pid']));

    echo '<b><big>Цитирование</big></b><br /><br />';
    if (is_user()) {
        $post = DB::run() -> queryFetch("SELECT * FROM `commload` WHERE `commload_id`=? LIMIT 1;", array($pid));

        if (!empty($post)) {
            echo '<div class="form">';
            echo '<form action="/load/down?act=add&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';
            echo 'Сообщение:<br />';
            echo '<textarea cols="25" rows="5" name="msg" id="msg">[quote][b]'.nickname($post['commload_author']).'[/b] ('.date_fixed($post['commload_time']).')'."\r\n".$post['commload_text'].'[/quote]'."\r\n".'</textarea><br />';
            echo '<input type="submit" value="Цитировать" /></form></div><br />';
        } else {
            show_error('Ошибка! Выбранное вами сообщение для цитирования не существует!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы цитировать сообщения, необходимо');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/down?act=comments&amp;id='.$id.'&amp;start='.$start.'">Вернуться</a><br />';
break;

############################################################################################
##                                Подготовка к редактированию                             ##
############################################################################################
case 'edit':

    $config['newtitle'] = 'Редактирование сообщения';

    $pid = abs(intval($_GET['pid']));

    if (is_user()) {
        $post = DB::run() -> queryFetch("SELECT * FROM `commload` WHERE `commload_id`=? AND `commload_author`=? LIMIT 1;", array($pid, $log));

        if (!empty($post)) {
            if ($post['commload_time'] + 600 > SITETIME) {

                echo '<i class="fa fa-pencil"></i> <b>'.nickname($post['commload_author']).'</b> <small>('.date_fixed($post['commload_time']).')</small><br /><br />';

                echo '<div class="form">';
                echo '<form action="/load/down?act=editpost&amp;id='.$post['commload_down'].'&amp;pid='.$pid.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';
                echo 'Редактирование сообщения:<br />';
                echo '<textarea cols="25" rows="5" name="msg" id="msg">'.$post['commload_text'].'</textarea><br />';
                echo '<input type="submit" value="Редактировать" /></form></div><br />';
            } else {
                show_error('Ошибка! Редактирование невозможно, прошло более 10 минут!!');
            }
        } else {
            show_error('Ошибка! Сообщение удалено или вы не автор этого сообщения!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы редактировать сообщения, необходимо');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/down?act=comments&amp;id='.$id.'&amp;start='.$start.'">Вернуться</a><br />';
break;

############################################################################################
##                                    Редактирование сообщения                            ##
############################################################################################
case 'editpost':

    $uid = check($_GET['uid']);
    $pid = abs(intval($_GET['pid']));
    $msg = check($_POST['msg']);

    if (is_user()) {
        if ($uid == $_SESSION['token']) {
            if (utf_strlen($msg) >= 5 && utf_strlen($msg) < 1000) {
                $post = DB::run() -> queryFetch("SELECT * FROM `commload` WHERE `commload_id`=? AND `commload_author`=? LIMIT 1;", array($pid, $log));

                if (!empty($post)) {
                    if ($post['commload_time'] + 600 > SITETIME) {

                        $msg = antimat($msg);

                        DB::run() -> query("UPDATE `commload` SET `commload_text`=? WHERE `commload_id`=?", array($msg, $pid));

                        $_SESSION['note'] = 'Сообщение успешно отредактировано!';
                        redirect("/load/down?act=comments&id=$id&start=$start");
                    } else {
                        show_error('Ошибка! Редактирование невозможно, прошло более 10 минут!!');
                    }
                } else {
                    show_error('Ошибка! Сообщение удалено или вы не автор этого сообщения!');
                }
            } else {
                show_error('Ошибка! Слишком длинное или короткое сообщение!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы редактировать сообщения, необходимо');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/down?act=edit&amp;id='.$id.'&amp;pid='.$pid.'&amp;start='.$start.'">Вернуться</a><br />';
break;

############################################################################################
##                                 Удаление комментариев                                  ##
############################################################################################
case 'del':

    $uid = check($_GET['uid']);
    if (isset($_POST['del'])) {
        $del = intar($_POST['del']);
    } else {
        $del = 0;
    }

    if (is_admin()) {
        if ($uid == $_SESSION['token']) {
            if (!empty($del)) {
                $del = implode(',', $del);

                $delcomments = DB::run() -> exec("DELETE FROM `commload` WHERE `commload_id` IN (".$del.") AND `commload_down`=".$id.";");
                DB::run() -> query("UPDATE `downs` SET `downs_comments`=`downs_comments`-? WHERE `downs_id`=?;", array($delcomments, $id));

                $_SESSION['note'] = 'Выбранные комментарии успешно удалены!';
                redirect("/load/down?act=comments&id=$id&start=$start");
            } else {
                show_error('Ошибка! Отстутствуют выбранные комментарии для удаления!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_error('Ошибка! Удалять комментарии могут только модераторы!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/down?act=comments&amp;id='.$id.'&amp;start='.$start.'">Вернуться</a><br />';
break;

############################################################################################
##                             Переадресация на последнюю страницу                        ##
############################################################################################
case 'end':

    $query = DB::run() -> queryFetch("SELECT count(*) as `total_comments` FROM `commload` WHERE `commload_down`=? LIMIT 1;", array($id));

    if (!empty($query)) {

        $total_comments = (empty($query['total_comments'])) ? 1 : $query['total_comments'];
        $end = last_page($total_comments, $config['downcomm']);

        redirect("/load/down?act=comments&id=$id&start=$end");
    } else {
        show_error('Ошибка! Данного файла не существует!');
    }

break;

endswitch;

echo '<i class="fa fa-arrow-circle-up"></i> <a href="/load">Категории</a><br />';

App::view($config['themes'].'/foot');
