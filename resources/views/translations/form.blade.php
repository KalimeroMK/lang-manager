@extends('admin::_layouts.crud')

@section('title', trans('translation-manager::models.translations'))

@section('content')

    @include('admin::_form.open')

    <div class="row">
        <div class="{{ bootform_columns([], true) }}"></div>
        <div class="{{ bootform_columns([]) }}">
            <h2>{{ trans('translation-manager::labels.key') }}: <b>{{ $item->admin_view_key }}</b></h2>
            <p>
                {{ $item->context }}
            </p>
        </div>
    </div>

    <br>
    @foreach((array)enabled_locales() as $locale => $localeName)
        <?php  $type = $item->field_type ? $item->field_type : 'text' ?>
        @if($locale === $item->locale)
            @if($type === 'editor')
                {!! BootForm::textarea($localeName['name'], 'value')
                    ->addClass('ckeditor')
                    ->group()->addClass('ck-wrapper')
                    ->required() !!}
            @else
                {!! BootForm::$type($localeName['name'], 'value')->required() !!}
            @endif
        @else
            <?php $translation = $item->translation($locale) ?>
            @if($type === 'editor')
                {!! BootForm::textarea($localeName['name'], "locales[{$locale}][value]")
                    ->addClass('ckeditor')
                    ->group()->addClass('ck-wrapper')
                    ->value($translation ? $translation->value : '') !!}
            @else
                {!! BootForm::$type($localeName['name'], "locales[{$locale}][value]")
                    ->value($translation ? $translation->value : '') !!}
            @endif
        @endif
    @endforeach

    @include('admin::_form.close')
@endsection

{{--TODO: make this a global admin thing.--}}
@push('scripts')
    @include('pages::pages._includes.ckscripts', [ 'sitestyles' => '/css/app.css' ])
@endpush