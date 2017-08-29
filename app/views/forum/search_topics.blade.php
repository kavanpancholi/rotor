@extends('layout')

@section('title')
    Поиск запроса {{ $find }} - @parent
@stop

@section('content')

    <h1>Поиск запроса {{ $find }}</h1>

    <p>Найдено совпадений в темах: {{ $page['total'] }}</p>

    @foreach ($topics as $topic)
        <div class="b">
            <i class="fa {{ $topic->getIcon() }} text-muted"></i>
            <b><a href="/topic/{{ $topic['id'] }}">{{ $topic['title'] }}</a></b> ({{ $topic['posts'] }})
        </div>
        <div>
            {{ Forum::pagination($topic) }}
            Сообщение: {{ $topic->getLastPost()->getUser()->login }} ({{ date_fixed($topic->getLastPost()->created_at) }})
        </div>
    @endforeach

    {{ App::pagination($page) }}

    <i class="fa fa-arrow-circle-left"></i> <a href="/forum/search">Вернуться</a>
@stop
