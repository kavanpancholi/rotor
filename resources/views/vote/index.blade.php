@extends('layout')

@section('title')
    Голосования (Стр. {{ $page['current'] }})
@stop

@section('content')

    <h1>Голосования</h1>

    @if ($votes->isNotEmpty())
        @foreach ($votes as $vote)
            <div class="b">
                <i class="fa fa-bar-chart"></i>
                <b><a href="/votes/{{ $vote['id'] }}">{{ $vote['title'] }}</a></b>
            </div>
            <div>
                @if ($vote->topic)
                    Тема: <a href="/topic/{{ $vote->topic->id }}">{{ $vote->topic->title }}</a><br>
                @endif

                Создано: {{ dateFixed($vote['created_at']) }}<br>
                Всего голосов: {{ $vote['count'] }}<br>
            </div>
        @endforeach

        {{ pagination($page) }}
    @else
        {{ showError('Открытых голосований еще нет!') }}
    @endif

    <i class="fa fa-briefcase"></i> <a href="/votes/history">Архив голосований</a><br>
@stop
