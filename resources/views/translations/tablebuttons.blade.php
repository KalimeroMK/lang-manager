
<div class="btn-group">
    <a href="{{ route($routePrefix.$resourceName.'.publish') }}" class="btn btn-primary postme">
        <i class="fa fa-play-circle"></i>
        &nbsp;{{ trans('translation-manager::labels.publish') }}
    </a>
    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
        <span class="caret"></span>
        <span class="sr-only">{{ trans('translation-manager::labels.other_actions') }}</span>
    </button>
    <ul class="dropdown-menu" role="menu">
        <li>
            <a href="{{ route($routePrefix.$resourceName.'.publish') }}" class="postme">
                <i class="fa fa-play-circle"></i>
                &nbsp;{{ trans('translation-manager::labels.publish') }}
            </a>
        </li>
        <li>
            <a href="{{ route($routePrefix.$resourceName.'.import') }}" class="postme">
                <i class="fa fa-download"></i> &nbsp;{{ trans('translation-manager::labels.import') }}
            </a>
        </li>
        <li>
            <a href="{{ route($routePrefix.$resourceName.'.find') }}" class="postme">
                <i class="fa fa-search"></i> &nbsp;{{ trans('translation-manager::labels.find') }}
            </a>
        </li>
        <li>
            <a href="{{ route($routePrefix.$resourceName.'.export') }}?{{ http_build_query(request()->request->all()) }}">
                <i class="fa fa-file-excel-o"></i> &nbsp; Export Excel
            </a>
        </li>
        <li>
            <a href="{{ route($routePrefix.$resourceName.'.importform') }}">
                <i class="fa fa-file-excel-o"></i> &nbsp; Import Excel
            </a>
        </li>
    </ul>
</div>
