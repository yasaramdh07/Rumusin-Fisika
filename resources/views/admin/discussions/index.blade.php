@extends('layouts.admin')

@section('title', 'Moderasi Diskusi')
@section('heading', 'Moderasi Diskusi')

@section('content')
<div class="rf-chips mb-4" aria-label="Filter materi diskusi">
    <a class="rf-chip @if (! $selectedLessonId) is-active @endif" href="{{ route('admin.discussions.index') }}" @if (! $selectedLessonId) aria-current="true" @endif>
        <x-icon class="rf-chip__check" name="check" /> Semua materi
    </a>
    @foreach ($lessons as $lesson)
        @php $active = $selectedLessonId === $lesson->id; @endphp
        <a class="rf-chip @if ($active) is-active @endif" href="{{ route('admin.discussions.index', ['lesson_id' => $lesson->id]) }}" @if ($active) aria-current="true" @endif>
            <x-icon class="rf-chip__check" name="check" /> {{ $lesson->title }}
        </a>
    @endforeach
</div>

@if ($threads->isEmpty())
    <div class="rf-slab">
        <div class="rf-empty">Belum ada diskusi untuk dimoderasi.</div>
    </div>
@else
    <div class="rf-slab">
        <ul class="rf-thread-list">
            @foreach ($threads as $thread)
                <li class="rf-thread">
                    <div class="rf-thread__head">
                        <div class="rf-thread__meta">
                            <strong>{{ $thread->user?->name ?? 'Pelajar' }}</strong>
                            <span class="rf-muted rf-small"><code>{{ $thread->user?->username }}</code></span>
                            @if ($thread->lesson)
                                <span class="rf-tag">{{ $thread->lesson->title }}</span>
                            @endif
                            <span class="rf-muted rf-small rf-num">· {{ $thread->created_at?->format('d M Y, H:i') }}</span>
                        </div>

                        <div class="rf-admin-actions">
                            @if ($thread->lesson)
                                <a class="rf-btn rf-btn--tertiary" href="{{ route('lessons.show', $thread->lesson) }}#thread-{{ $thread->id }}" target="_blank" rel="noopener">
                                    <x-icon name="eye" /> Buka di materi
                                </a>
                            @endif
                            <form method="POST" action="{{ route('admin.discussions.threads.destroy', $thread) }}" data-confirm="Hapus thread diskusi '{{ $thread->title }}'?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rf-btn rf-btn--tertiary"><x-icon name="trash" /> Hapus thread</button>
                            </form>
                        </div>
                    </div>

                    <h2 class="h4 rf-thread__title">{{ $thread->title }}</h2>
                    <div class="rf-thread__body">{!! nl2br(e($thread->body)) !!}</div>

                    @if ($thread->replies->isNotEmpty())
                        <ul class="rf-replies">
                            @foreach ($thread->replies as $reply)
                                <li class="rf-reply">
                                    <div class="rf-thread__head">
                                        <div class="rf-thread__meta">
                                            <strong>{{ $reply->user?->name ?? 'Pelajar' }}</strong>
                                            <span class="rf-muted rf-small"><code>{{ $reply->user?->username }}</code></span>
                                            <span class="rf-muted rf-small rf-num">· {{ $reply->created_at?->format('d M Y, H:i') }}</span>
                                        </div>

                                        <form method="POST" action="{{ route('admin.discussions.replies.destroy', $reply) }}" data-confirm="Hapus balasan ini?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rf-btn rf-btn--tertiary"><x-icon name="trash" /> Hapus balasan</button>
                                        </form>
                                    </div>

                                    <div class="rf-thread__body">{!! nl2br(e($reply->body)) !!}</div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>

        @include('admin.partials.pagination', ['paginator' => $threads])
    </div>
@endif
@endsection
