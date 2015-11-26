RotorCMS 4.1
=========

Добро пожаловать!
Мы благодарим Вас за то, что Вы решили использовать наш скрипт для своего сайта. RotorCMS - функционально законченная система управления контентом с открытым кодом написанная на PHP. Она использует базу данных MySQL для хранения содержимого вашего сайта.

**RotorCMS** является гибкой, мощной и интуитивно понятной системой с минимальными требованиями к хостингу, высоким уровнем защиты и является превосходным выбором для построения сайта любой степени сложности

Главной особенностью RotorCMS является низкая нагрузка на системные ресурсы и высокая скорость работы, даже при очень большой аудитории сайта нагрузка на сервер будет минимальной, и вы не будете испытывать каких-либо проблем с отображением информации.

###Действия при первой установке движка RotorCMS

Прежде чем начать установку убедитесь, что все файлы дистрибутива загружены на сервер, а также выставлены необходимые права доступа для папок и файлов

 * **includes/connect.php (chmod 666)**
 * **upload/avatars (chmod 777)**
 * **upload/counters (chmod 777)**
 * **upload/events (chmod 777)**
 * **upload/forum (chmod 777)**
 * **upload/news (chmod 777)**
 * **upload/photos (chmod 777)**
 * **upload/pictures (chmod 777)**
 * **upload/thumbnail (chmod 777)**
 * **images/avatars (chmod 777)**
 * **images/smiles (chmod 777)**
 * **load/files (chmod 777)**
 * **load/screen (chmod 777)**
 * **load/loader (chmod 777)**
 * **local/antidos (chmod 777)**
 * **local/backup (chmod 777)**
 * **local/main (chmod 777)**
 * **local/temp (chmod 777)**
 * **А также всем файлам внутри папки local/main (chmod 666)**


1. Создайте базу данных и пользователя для нее из панели управления на вашем сервере, во время установки скрипта необходимо будет вписать эти данные для соединения с БД MySQL

2. Перейдите на главную страницу вашего сайта. Следуйте инструкциям автоматического инсталлятора, скрипт проверит все необходимые файлы, настроит подключение к базе данных и создаст аккаунт администратора

Во время установки движка скрипт автоматически присвоит права CHMOD 644 файлу includes/connect.php
Если этого не произошло, то вы можете вручную выставить файлу права запрещающие запись в него

После завершения установки вы сможете посмотреть работу скрипта на главной странице вашего сайта

### Действия при повторной установке движка RotorCMS
 1. Загрузите из дистрибутива на сайт директорию install со всем ее содержимым
 2. Очистите таблицу setting в базе данных
 3. Удалите профиль администратора в таблице users
 4. Перейдите по адресу http://ваш_сайт/install и переустановите движок
После этих действий можно повторно установить движок на ваш сайт

**Внимание:** при установке скрипта создается структура базы данных, создается аккаунт администратора, а также прописываются основные настройки системы, поэтому после успешной установки удалите директорию install во избежание повторной установки скрипта!

Надеемся, что работа с нашим скриптом доставит вам только удовольствие.

Приятной Вам работы
