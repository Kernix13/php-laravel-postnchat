<a href="/post/{{$post->id}}" class="list-group-item">
  <img class="avatar-tiny" src="{{$post->user->avatar}}" />
  <strong>{{$post->title}}</strong> 
  <span class="text-muted small">
    @if(!isset($hideAuthor))
    by {{$post->user->username}} 
    @endif
    on {{$post->created_at->format('n/j/Y')}}
  </span>
</a>