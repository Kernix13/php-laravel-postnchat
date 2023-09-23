<div class="list-group">
  <h3>Posts:</h3>
  @foreach($posts as $post)
  <x-post :post="$post" hideAuthor />
  @endforeach
</div>