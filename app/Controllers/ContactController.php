<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Contact;
use App\Models\User;

class ContactController extends BaseController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! getUser()) {
            abort(403, 'Для просмотра контактов необходимо авторизоваться!');
        }
    }

    /**
     * Главная страница
     */
    public function index()
    {
        if (Request::isMethod('post')) {
            $page  = abs(intval(Request::input('page', 1)));
            $token = check(Request::input('token'));
            $login = check(Request::input('user'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!');

            $user = User::query()->where('login', $login)->first();
            $validator->notEmpty($user, 'Данного пользователя не существует!');

            if ($user) {
                $validator->notEqual($user->login, getUser('login'), 'Запрещено добавлять свой логин!');

                $totalContact = Contact::query()->where('user_id', getUser('id'))->count();
                $validator->lte($totalContact, setting('limitcontact'), 'Ошибка! Контакт-лист переполнен (Максимум ' . setting('limitcontact') . ' пользователей!)');

                $validator->false(isContact(getUser(), $user), 'Данный пользователь уже есть в контакт-листе!');
            }

            if ($validator->isValid()) {

                Contact::query()->create([
                    'user_id'    => getUser('id'),
                    'contact_id' => $user->id,
                    'created_at' => SITETIME,
                ]);

                if (! isIgnore($user, getUser())) {
                    $message = 'Пользователь [b]'.getUser('login').'[/b] добавил вас в свой контакт-лист!';
                    sendPrivate($user->id, getUser('id'), $message);
                }

                setFlash('success', 'Пользователь успешно добавлен в контакт-лист!');
                redirect('/contact?page='.$page);

            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $total = Contact::query()->where('user_id', getUser('id'))->count();
        $page = paginate(setting('contactlist'), $total);

        $contacts = Contact::query()
            ->where('user_id', getUser('id'))
            ->orderBy('created_at', 'desc')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->with('contactor')
            ->get();

        return view('contact/index', compact('contacts', 'page'));
    }

    /**
     * Заметка для пользователя
     */
    public function note($id)
    {
        $contact = Contact::query()
            ->where('user_id', getUser('id'))
            ->where('id', $id)
            ->first();

        if (! $contact) {
            abort('default', 'Запись не найдена');
        }

        if (Request::isMethod('post')) {

            $token = check(Request::input('token'));
            $msg   = check(Request::input('msg'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
                ->length($msg, 0, 1000, ['msg' => 'Слишком большая заметка, не более 1000 символов!']);

            if ($validator->isValid()) {

                $contact->update([
                    'text' => $msg,
                ]);

                setFlash('success', 'Заметка успешно отредактирована!');
                redirect("/contact");
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('contact/note', compact('contact'));
    }

    /**
     * Удаление контактов
     */
    public function delete()
    {
        $page  = abs(intval(Request::input('page', 1)));
        $token = check(Request::input('token'));
        $del   = intar(Request::input('del'));

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->true($del, 'Ошибка удаления! Отсутствуют выбранные пользователи');

        if ($validator->isValid()) {

            Contact::query()
                ->where('user_id', getUser('id'))
                ->whereIn('id', $del)
                ->delete();

            setFlash('success', 'Выбранные пользователи успешно удалены!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect("/contact?page=$page");
    }
}
