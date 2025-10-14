<div class="card">
    <div class="card-body">
        <div class="alert alert-warning" role="alert">
            <strong>Warning:</strong> Translations are not visible until they are exported back to the app/lang file, using <code>php artisan translation:export</code> command or publish button.
        </div>

        @if(!isset($group))
            <form class="form-import mb-4" method="POST" action="{{ action($controller . '@postImport') }}" data-remote="true" role="form">
                @csrf()
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="replace" class="form-label">Import Mode</label>
                        <select name="replace" id="replace" class="form-select">
                            <option value="0">Append new translations</option>
                            <option value="1">Replace existing translations</option>
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <button type="submit" class="btn btn-success w-100" data-disable-with="Loading...">
                            <i class="fas fa-upload me-2"></i>Import Groups
                        </button>
                    </div>
                </div>
            </form>
            <form class="form-find" method="POST" action="{{ action($controller.'@postFind') }}" data-remote="true" role="form"
                  data-confirm="Are you sure you want to scan you app folder? All found translation keys will be added to the database.">
                @csrf()
                <div class="d-flex gap-2" role="group">
                    <button type="submit" class="btn btn-info" data-disable-with="Searching...">
                        <i class="fas fa-search me-2"></i>Find translations in files
                    </button>
                    @if($selectedModel)
                        <a href="{{ action($controller.'@getIndex') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back
                        </a>
                    @endif
                </div>
            </form>
        @else
            <form class="form-publish" method="POST" action="{{ action($controller.'@postPublish', $group) }}" data-remote="true" role="form"
                  data-confirm="Are you sure you want to publish the translations group '{{ $group }}'? This will overwrite existing language files.">
                @csrf()
                <div class="d-flex gap-2" role="group">
                    <button type="submit" class="btn btn-info" data-disable-with="Publishing...">
                        <i class="fas fa-publish me-2"></i>Publish translations
                    </button>
                    <a href="{{ action($controller.'@getIndex') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>
            </form>
        @endif
    </div>
</div>
