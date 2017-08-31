<?php
view(setting('themes').'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';

//show_title('Мои настройки');

if (isUser()) {
switch ($action):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

    echo '<i class="fa fa-book"></i> ';
    echo '<a href="/user/'.getUsername().'">Моя анкета</a> / ';
    echo '<a href="/profile">Мой профиль</a> / ';
    echo '<a href="/account">Мои данные</a> / ';
    echo '<b>Настройки</b><hr>';

    echo '<div class="form">';
    echo '<form method="post" action="setting?act=edit">';
    echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';

    echo 'Wap-тема по умолчанию:<br>';
    echo '<select name="themes">';
    echo '<option value="0">Автоматически</option>';
    $globthemes = glob(HOME."/themes/*", GLOB_ONLYDIR);
    foreach ($globthemes as $themes) {
        $selected = (user('themes') == basename($themes)) ? ' selected="selected"' : '';
        echo '<option value="'.basename($themes).'"'.$selected.'>'.basename($themes).'</option>';
    }
    echo '</select><br>';

    $langShort = [
        'ru' => 'русский',
        'en' => 'English',
    ];

    echo 'Язык:<br>';
    echo '<select name="lang">';
    $languages = glob(APP."/lang/*", GLOB_ONLYDIR);
    foreach ($languages as $lang) {
        $selected = (user('lang') == basename($lang)) ? ' selected="selected"' : '';
        echo '<option value="'.basename($lang).'"'.$selected.'>'.$langShort[basename($lang)].'</option>';
    }
    echo '</select><br>';

    $arrtimezone = range(-12, 12);

    echo 'Временной сдвиг:<br>';
    echo '<select name="timezone">';
    foreach($arrtimezone as $zone) {
        $selected = (user('timezone') == $zone) ? ' selected="selected"' : '';
        echo '<option value="'.$zone.'"'.$selected.'>'.$zone.'</option>';
    }
    echo '</select> - '.dateFixed(SITETIME, 'H:i').'<br>';

?>
    <?php $checked = (user('privacy') == 1) ? ' checked="checked"' : ''; ?>
    <div class="checkbox">
        <label data-toggle="tooltip" title="Писать в приват и на стену смогут только пользователи из контактов">
            <input name="privacy" type="checkbox" value="1"<?= $checked?>> Режим приватности
        </label>
    </div>

    <?php $checked = (user('notify') == 1) ? ' checked="checked"' : ''; ?>
    <div class="checkbox">
        <label data-toggle="tooltip" title="Уведомления об ответах будут приходить в личные сообщения">
            <input name="notify" type="checkbox" value="1"<?= $checked?>> Получать уведомления об ответах
        </label>
    </div>

    <?php $checked = (! empty(user('subscribe'))) ? ' checked="checked"' : ''; ?>
    <div class="checkbox">
        <label data-toggle="tooltip" title="Получение информационных писем с сайта на email">
            <input name="subscribe" type="checkbox" value="1"<?= $checked?>> Получать информационные письма
        </label>
    </div>

    <button class="btn btn-primary">Изменить</button>
    </form></div><br>

    * Значение всех полей (max.50)<br><br>

    <?php
break;

############################################################################################
##                                       Изменение                                        ##
############################################################################################
case 'edit':

    $token   = check(Request::input('token'));
    $themes   = check(Request::input('themes'));
    $timezone   = check(Request::input('timezone'), 0);
    $privacy = Request::has('privacy') ? 1 : 0;
    $notify   = Request::has('notify') ? 1 : 0;
    $subscribe = Request::has('subscribe') ? str_random(32) : null;

    $validation = new Validation();

    $validation -> addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
        -> addRule('regex', [$themes, '|^[a-z0-9_\-]+$|i'], 'Недопустимое название темы!', true)
        -> addRule('regex', [$timezone, '|^[\-\+]{0,1}[0-9]{1,2}$|'], 'Недопустимое значение временного сдвига. (Допустимый диапазон -12 — +12 часов)!', true);

    if ($validation->run()) {
        if (file_exists(HOME."/themes/$themes/index.php") || $themes==0) {

            $user = User::find(getUserId());
            $user->update([
                'themes'    => $themes,
                'timezone'  => $timezone,
                'privacy'   => $privacy,
                'notify'    => $notify,
                'subscribe' => $subscribe,
            ]);

            setFlash('success', 'Настройки успешно изменены!');
            redirect("/setting");

        } else {
            showError('Ошибка! Данный скин не установлен на сайте!');
        }
    } else {
        showError($validation->getErrors());
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/setting">Вернуться</a><br>';
break;

endswitch;
} else {
    showError('Для изменения настроек необходимо авторизоваться');
}

view(setting('themes').'/foot');
