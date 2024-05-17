            <div class="card">
                <div class="card-body">
                    <p>Qiita：Laravelに関する記事</p>
                    <ul class="posts mx-3 mt-1 mb-0 list-unstyled list-inline">
                        @if (!empty($articles))
                        @foreach ($articles as $article)
                            <li class="list-inline-item mb-2" style="border-radius: 10px; padding: 1px 20px; font-size: 0.9em;
                                    background-color: rgba(250, 250, 250, 0.5);
                                    color: rgba(33, 37, 41, 0.8);
                                    border-radius: 10px;
                            ">
                                @if (is_array($article) && isset($article['url']) && isset($article['title']))
                                    <a href="#" onclick="event.preventDefault(); window.open('{{ $article['url'] }}'); window.location.href = '{{ url('items/add/' . $article['url']) }}';">{{ $article['title'] }}</a>
                                @endif
                            </li>
                        @endforeach
                        @else
                            <p>No articles found.</p>
                        @endif
                    </ul>
                </div>
            </div>
