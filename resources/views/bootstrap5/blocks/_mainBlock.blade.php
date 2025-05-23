{{-- TODO: Update markup/classes for Bootstrap 5 as needed --}}
<div class="card mb-3">
    <div class="card-body">
        <p>Warning, translations are not visible until they are exported back to the app/lang file, using
            <code>php artisan translation:export</code> command or publish button.</p>

        @if(!isset($group))
            <form class="form-import" method="POST" action="<?php echo action($controller . '@postImport') ?>" data-remote="true" role="form">
                @csrf()
                <div class="row g-2 align-items-center mb-3">
                    <div class="col-auto">
                        <select name="replace" class="form-select">
                            <option value="0">Append new translations</option>
                            <option value="1">Replace existing translations</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-success w-100" data-disable-with="Loading..">Import groups</button>
                    </div>
                </div>
            </form>
            <form class="form-find" method="POST" action="{!! action($controller.'@postFind') !!}" data-remote="true" role="form"
                  data-confirm="Are you sure you want to scan you app folder? All found translation keys will be added to the database.">
                @csrf()
                <div class="mb-3">
                    <button type="submit" class="btn btn-info" data-disable-with="Searching..">Find translations in files</button>
                </div>
            </form>
        @else
            <form class="row g-2 align-items-center form-publish" method="POST" action="{!! action($controller.'@postPublish', $group)  !!}" data-remote="true"
                  role="form" data-confirm="Are you sure you want to publish the translations group '{{$group}}? This will overwrite existing language files.">
                @csrf()
                <div class="btn-group" role="group">
                    <button type="submit" class="btn btn-info" data-disable-with="Publishing..">Publish translations</button>
                    <a href="{{action($controller.'@getIndex') }}" class="btn btn-secondary">Back</a>
                </div>
            </form>
        @endif
    </div>
</div>
