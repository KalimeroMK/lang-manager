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
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <div class="text-right">
                    <a href="{{ route($routePrefix.$resourceName.'.batchedit') }}?{{ http_build_query(request()->request->all()) }}" class="btn btn-warning">
                        <i class="fa fa-edit"></i>
                        Batchedit
                    </a>
                    @include('translation-manager::translations.tablebuttons')
                </div>
            </div>
            <div class="ibox-content">
                {!! BootForm::openHorizontal()->action(route('admin.translations.importexcel'))->multipart() !!}
                {!! BootForm::file(_t('labels.excel_file', 'Excel file'), 'file')->required() !!}
                <div class="hr-line-dashed"></div>
                <button type="submit" class="btn btn-primary">Import</button>
                {!! BootForm::close() !!}
            </div>
        </div>
    </div>
@endsection
