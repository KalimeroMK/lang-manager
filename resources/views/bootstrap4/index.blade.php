@extends(config('translation-manager.layout'))
@php($controller = \Kalimero\TranslationManager\Controller::class)

@section('documentTitle')
    Translation Manager
@stop
@include('translation-manager::bootstrap4._notifications')
@section('content')

    @include('translation-manager::bootstrap4.blocks._mainBlock')
    @if(!$selectedModel)
        @include('translation-manager::bootstrap4.blocks._addEditGroupKeys')
    @else
        @include('translation-manager::bootstrap4.blocks._selectEditModel')
    @endif
    @if($group)
        @include('translation-manager::bootstrap4.blocks._edit')
    @elseif($selectedModel)
        @include('translation-manager::bootstrap4.blocks._editModel')
    @else
        @include('translation-manager::bootstrap4.blocks._selectEditModel')
        @include('translation-manager::bootstrap4.blocks._supportedLocales')
        @include('translation-manager::bootstrap4.blocks._publishAll')
    @endif
@stop

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/JoseVte/x-editable@1.5.3/dist/bootstrap4-editable/css/bootstrap-editable.css"/>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/JoseVte/x-editable@1.5.3/dist/bootstrap4-editable/js/bootstrap-editable.min.js"></script>
    @include('translation-manager::jsScript')
@endpush
