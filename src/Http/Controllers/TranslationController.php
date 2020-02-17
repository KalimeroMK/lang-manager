<?php

namespace Novatio\TranslationManager\Http\Controllers;

use Illuminate\Http\Request;
use Novatio\TranslationManager\Manager;
use Maatwebsite\Excel\Readers\LaravelExcelReader;
use Maatwebsite\Excel\Writers\LaravelExcelWriter;
use Novatio\TranslationManager\Models\Translation;
use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;
use Novatio\TranslationManager\Http\TranslationFilter;
use Novatio\Admin\Http\Controllers\AdminModelController;
use Novatio\TranslationManager\Http\Requests\TranslationRequest;

class TranslationController extends AdminModelController
{
    /**
     * @var string
     */
    protected $modelName = Translation::class;

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;

        parent::__construct();
    }

    /**
     * @param TranslationFilter $filters
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(TranslationFilter $filter)
    {
        return view('translation-manager::translations.index', ['items' => $this->paginated($filter)]);
    }

    /**
     * @param TranslationFilter $filters
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function batchedit(TranslationFilter $filter)
    {
        return view('translation-manager::translations.batchedit', ['items' => $this->paginated($filter)]);
    }

    /**
     * @param \Novatio\TranslationManager\Http\TranslationFilter $filter
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|mixed
     */
    public function export(TranslationFilter $filter)
    {
        $items = $this->all($filter);

        \Excel::create('translations', function (LaravelExcelWriter $excel) use ($items) {
            $excel->sheet('translations', function (LaravelExcelWorksheet $sheet) use ($items) {

                $headings = array_merge(['group', 'key'], enabled_locales(true));

                $sheet->appendRow($headings);

                $items->each(function (Translation $item) use ($sheet) {
                    $sheet->appendRow($item->toCsvArray());
                });
            });
        })->download('xlsx');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function importform()
    {
        return view('translation-manager::translations.importform');
    }

    /**
     * @param \Illuminate\Http\Request $request
     */
    public function importexcel(Request $request)
    {
        $this->validate($request, ['file' => 'required|file']);

        $upload  = $request->file('file');
        $locales = enabled_locales(true);
        $count = 0;

        \Excel::load($upload->getRealPath(), function (LaravelExcelReader $reader) use ($locales, &$count) {

            $reader->each(function ($row) use ($locales, &$count) {
                foreach ($locales as $locale => $localeName) {
                    $localeName  = strtolower($localeName);
                    $translation = Translation::withoutGlobalScope('locale')->where('group', $row->group)
                        ->where('key', $row->key)
                        ->where('locale', $locale)
                        ->first();
                    if ($translation) {
                        $translation->value = $row->$localeName;
                        $translation->save();
                        $count++;
                    }
                }
            });
        });

        flash($count . ' translations saved. Please Publish to see the changes');
        return redirect(route('admin.translations.importform'));
    }

    /**
     * @param TranslationRequest $request
     *
     * @return mixed
     */
    public function store(TranslationRequest $request)
    {
        $translation = new Translation($request->all());
        $translation->save();
        $this->flash('created');
        return redirect(route('admin.translations.edit', $translation));
    }

    /**
     * @param TranslationRequest $request
     * @param Translation        $translation
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(TranslationRequest $request, Translation $translation)
    {
        $translation->update($request->all());
        $this->flash('updated');
        return redirect(route('admin.files.edit', $translation));
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function batchupdate(Request $request)
    {
        foreach ($request->get('item') as $id => $data) {
            /** @var Translation $item */
            if ($item = Translation::find($id)) {
                $item->timestamps = false;
                $item->update(array_filter($data));
                $item->saveTranslations($data);
            }
        }

        $this->flash('updated');

        return redirect()->back();
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(Request $request)
    {
        $replace = $request->get('replace', false);
        $counter = $this->manager->importTranslations($replace);

        flash(trans('translation-manager::notices.done_importing', ['counter' => $counter]), 'success');

        return redirect()->back();
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function publish(Request $request)
    {
        // export all groups, use "*", request not use for now, will be used if exporting filtered groups
        $this->manager->exportTranslations('*');

        flash(trans('translation-manager::notices.done_publishing'), 'success');

        return redirect()->back();
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function find()
    {
        $counter = $this->manager->findTranslations();

        // TODO: translate + error handling.
        flash(trans('translation-manager::notices.done_finding', ['counter' => $counter]), 'success');

        return redirect()->back();
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function appendViewData(array $data = [])
    {
        $groups         = Translation::groupBy('group');
        $excludedGroups = $this->manager->getConfig('exclude_groups');
        if ($excludedGroups) {
            $groups->whereNotIn('group', $excludedGroups);
        }

        $this->viewData['translationGroups'] = $groups->pluck('group', 'group');
        $this->viewData['stateTypes']        = [
            'translated'   => 'Translated',
            'untranslated' => 'Not translated',
        ];

        return parent::appendViewData($data);
    }
}
