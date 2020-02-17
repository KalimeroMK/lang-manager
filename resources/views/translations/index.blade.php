@extends('admin::_layouts.crud')

@section('title', trans('translation-manager::models.translations'))

@section('tableactions')
    <a href="{{ route($routePrefix.$resourceName.'.batchedit') }}?{{ http_build_query(request()->request->all()) }}" class="btn btn-warning">
        <i class="fa fa-edit"></i>
        Batchedit
    </a>
    @include('translation-manager::translations.tablebuttons')
@endsection

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight">
        @section('tablefilter')
            {!! Form::select('group[]', $translationGroups)->select(request()->get('group'))->class('form-control multiselect')->multiple()->data('label', 'Group') !!}
            {!! Form::select('state[]', $stateTypes)->select(request()->get('state'))->class('form-control multiselect')->multiple()->data('label', 'State') !!}
        @endsection

        @include('admin::_table.open')

        @if($items->count())
            <thead>
            <tr>
                <th>@include('admin::_table.sortlink', ['column' => 'id', 'label' => '#'])</th>
                <th>@include('admin::_table.sortlink', ['column' => 'key', 'label' => trans('translation-manager::labels.key')])</th>
                <th>@include('admin::_table.sortlink', ['column' => 'group', 'label' => trans('translation-manager::labels.group')])</th>
                <th>{{ trans('translation-manager::labels.value') }}</th>

                <th></th>
            </tr>
            </thead>

            <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->admin_view_key }}</td>
                    <td>{{ $item->group }}</td>
                    <td>{{ summary($item->value) }}</td>

                    <td>@include('admin::_table.rowactions')</td>
                </tr>
            @endforeach
            </tbody>
        @endif
        @include('admin::_table.close')
    </div>
@endsection
