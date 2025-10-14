@extends(config('translation-manager.layout'))
@php($controller = \Kalimero\TranslationManager\Controller::class)

@section('documentTitle')
    Translation Manager
@stop

@include('translation-manager::tailwind3._notifications')

@section('content')
    @include('translation-manager::tailwind3.blocks._mainBlock')
    @if(!$selectedModel)
        @include('translation-manager::tailwind3.blocks._addEditGroupKeys')
    @else
        @include('translation-manager::tailwind3.blocks._selectEditModel')
    @endif
    @if($group)
        @include('translation-manager::tailwind3.blocks._edit')
    @elseif($selectedModel)
        @include('translation-manager::tailwind3.blocks._editModel')
    @else
        @include('translation-manager::tailwind3.blocks._selectEditModel')
        @include('translation-manager::tailwind3.blocks._supportedLocales')
        @include('translation-manager::tailwind3.blocks._publishAll')
    @endif
@stop

@push('styles')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/JoseVte/x-editable@1.5.3/dist/jquery-editable/css/jquery-editable.css"/>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/vadikom/poshytip@master/src/jquery.poshytip.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/JoseVte/x-editable@1.5.3/dist/jquery-editable/js/jquery-editable-poshytip.min.js"></script>
    @include('translation-manager::jsScript')
@endpush
