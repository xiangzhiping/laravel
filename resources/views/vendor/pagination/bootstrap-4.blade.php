@if ($paginator->hasPages())
    <ul class="pagination" role="navigation">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                <span class="page-link" aria-hidden="true">@lang('pagination.previous')</span>
            </li>
        @else
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">@lang('pagination.previous')</a>
            </li>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">@lang('pagination.previous')</a>
            </li>
        @else
            <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                <span class="page-link" aria-hidden="true">@lang('pagination.next')</span>
            </li>
        @endif
        <li class="disabled"><span>共 {{ $paginator->total() }} 条{{$paginator->perPage()}}</span></li>
        <li>
            <select class="form-control" style="position:relative;float:left;display: inline-block;width:90px;" name="perPage">
                <option value="15" @if($paginator->perPage() == 15) selected @endif >15条</option>
                <option value="30" @if($paginator->perPage() == 30) selected @endif >30条</option>
                <option value="50" @if($paginator->perPage() == 50) selected @endif >50条</option>
                <option value="100" @if($paginator->perPage() == 100) selected @endif >100条</option>
            </select>
        </li>
        <li>
            <input type="text" style="position:relative;float:left;display: inline-block;width:50px;" name="gotoNum" value="{{ $paginator->currentPage() }}" class="form-control"/>
        </li>
        <li><a style="40px;" class="gotoPage">GO</a></li>
    </ul>
    <script type="text/javascript">
        $(function(){
            $("select[name=perPage]").change(function(){
                var perPage = parseInt($(this).val());
                if(!perPage || perPage > 1000) return;
                $.removeCookie("perPage");
                $.cookie("perPage",perPage,{ expires: 1 });
                window.location.reload();
            });
            $(".pagination .gotoPage").click(function(){
                var maxPage = '.$lastPage.';
                var page = $(".pagination input[name=gotoNum]").val();
                if(!page) return;
                if(page > maxPage){
                    alert("输入的页数过大");return;
                }
                var url = window.location.href;
                var pattern = "page=([^&]*)";
                if (url.match(pattern)) {
                    var tmp = "/(page=)([^&]*)/gi";
                    url = url.replace(eval(tmp), "page="+page);
                }else{
                    url = url.match("[\?]") ? url + "&page="+page : url + "?page="+page;
                }
                window.location.href=url;
            });
        });
    </script>
@endif
