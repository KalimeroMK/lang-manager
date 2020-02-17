@extends('admin::_layouts.crud')

@section('title', trans('translation-manager::models.translations'))

@section('content')

    <div class="wrapper wrapper-content animated fadeInRight" id="{{ $resourceName }}-table">

        <div class="row">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="filterbar">
                                    {!! BootForm::open()->action(route(Route::currentRouteName(), $routeParameters))->get() !!}
                                    {!! BootForm::bind(request()) !!}
                                    <div class="btn-group">
                                        {!! Form::select('limit')->options([15 => 15, 30 => 30, 50 => 50])->select(request()->get('limit'))->defaultValue(config('repository.pagination.limit'))->class('form-control btn')->style('text-align:left!important') !!}
                                    </div>
                                    <div class="btn-group">
                                        {!! Form::text('q')->value(request()->get('q'))->class('form-control btn')->style('text-align:left!important')->placeholder(trans('admin::labels.search')) !!}
                                    </div>
                                    {!! Form::select('group[]', $translationGroups)->select(request()->get('group'))->class('form-control multiselect')->multiple()->data('label', 'Group') !!}
                                    {!! Form::select('state[]', $stateTypes)->select(request()->get('state'))->class('form-control multiselect')->multiple()->data('label', 'State') !!}
                                    <a href="{!! route(Route::currentRouteName(), $routeParameters) !!}" class="btn btn-default cleartablefilter">
                                        <i class="fa fa-close"></i> {{ trans('admin::labels.clear') }}
                                    </a>
                                    {!! BootForm::close() !!}
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="pull-right">
                                    <a href="{{ route($routePrefix.$resourceName.'.index') }}?{{ http_build_query(request()->request->all()) }}" class="btn btn-warning">
                                        <i class=" fa fa-arrow-circle-left"></i>
                                        Back
                                    </a>
                                    @include('translation-manager::translations.tablebuttons')
                                </div>
                            </div>
                        </div>
                    </div>
                    {!! BootForm::open()->put()->action(route($routePrefix.$resourceName.'.batchupdate'))->id('batcheditform')->addClass('form-horizontal') !!}
                    <div class="ibox-content table-responsive no-padding">

                        <table class="table">
                            <thead>
                            <tr>
                                <th>{{ trans('translation-manager::labels.key') }}</th>

                                @foreach((array)enabled_locales() as $locale => $localeName)
                                    <th>{{ $localeName['name'] }}</th>
                                @endforeach
                            </tr>
                            </thead>
                            <style>
                                tbody td .form-control {
                                    margin-bottom: 5px;
                                }
                            </style>
                            <tbody>
                            @foreach($items as $i => $item)
                                <tr>
                                    <td>{{ str_limit($item->key, 30) }}</td>
                                    @foreach((array)enabled_locales() as $locale => $localeName)
                                        <td>
                                            @if($locale === $item->locale)
                                                {!! Form::textarea('item['.$item->getKey().'][value]')->value($item->value)->addClass('form-control input-sm')->rows(4) !!}
                                            @else
                                                <?php $translation = $item->translation($locale) ?>
                                                {!! Form::textarea('item['.$item->getKey().'][locales]['.$locale.'][value]')->value($translation ? $translation->value : '')->addClass('form-control input-sm')->rows(4) !!}

                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="ibox-footer clearfix">
                        <div class="row">
                            <div class="col-md-8 text-left">
                                {{ $items->appends(request()->query())->links('admin::_includes.pagination') }}
                            </div>
                            <div class="col-md-4">
                                <div class="text-right">
                                    <button class="btn btn-primary" type="submit">{{ trans('admin::labels.save') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    {!! BootForm::close() !!}

                </div>
            </div>
        </div>

    </div>
@endsection
