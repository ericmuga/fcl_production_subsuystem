<?php

namespace App\Http\Controllers;

use App\Exports\RecipeDraftExport;
use App\Imports\RecipeDraftImport;
use App\Services\RecipeDraftReplicator;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Manages recipe_data_draft - the editable copy of RecipeData used to prove the
 * stuffing production order generation out before it is pointed at live.
 *
 * Nothing here writes to RecipeData. Which of the two the generation actually
 * reads is decided by config('recipes.table'), set from RECIPE_DATA_TABLE in .env.
 */
class RecipeDraftController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function table(): string
    {
        return config('recipes.draft_table', 'recipe_data_draft');
    }

    private function liveTable(): string
    {
        return config('recipes.live_table', 'RecipeData');
    }

    /**
     * The recipe graph is cached for 10 hours, so every edit has to drop it or the
     * next weighing keeps generating from the recipes as they were.
     */
    private function forgetCaches(): void
    {
        foreach ((array) config('recipes.cache_keys', []) as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Filters are applied server side and the list is paginated - the draft holds
     * the same few thousand rows as live, which is more than a client side table
     * wants to hold.
     */
    private function filtered(Request $request)
    {
        return DB::table($this->table())
            ->when($request->filled('recipe'), fn($q) => $q->where('recipe', 'like', '%' . $request->recipe . '%'))
            ->when($request->filled('process'), fn($q) => $q->where('process', $request->process))
            ->when($request->filled('output_item'), fn($q) => $q->where('output_item', 'like', '%' . $request->output_item . '%'))
            ->when($request->filled('input_item'), fn($q) => $q->where('input_item', 'like', '%' . $request->input_item . '%'));
    }

    private function filterValues(Request $request): array
    {
        return $request->only(['recipe', 'process', 'output_item', 'input_item']);
    }

    public function index(Request $request)
    {
        $title = 'Recipe Data (Draft)';

        $lines = $this->filtered($request)
            ->orderBy('recipe')
            ->orderBy('output_item')
            ->orderBy('input_item')
            ->paginate(50)
            ->appends($this->filterValues($request));

        $processes = DB::table($this->table())
            ->whereNotNull('process')
            ->distinct()
            ->orderBy('process')
            ->pluck('process');

        // Shown on the page so it is never a guess which table a weighing will read.
        $active_table = config('recipes.table', 'RecipeData');
        $draft_table = $this->table();
        $live_table = $this->liveTable();
        $draft_count = DB::table($draft_table)->count();
        $live_count = DB::table($live_table)->count();

        return view('recipes.draft.index', compact(
            'title', 'lines', 'processes', 'active_table', 'draft_table',
            'live_table', 'draft_count', 'live_count'
        ) + ['filters' => $this->filterValues($request)]);
    }

    public function create()
    {
        $title = 'New Recipe Line';

        return view('recipes.draft.form', [
            'title' => $title,
            'line' => null,
            'processes' => DB::table($this->table())->whereNotNull('process')->distinct()->orderBy('process')->pluck('process'),
        ]);
    }

    public function edit($id)
    {
        $line = DB::table($this->table())->where('id', $id)->first();

        if (!$line) {
            Toastr::error('That recipe line no longer exists.', 'Error!');

            return redirect()->route('recipe_draft_index');
        }

        return view('recipes.draft.form', [
            'title' => 'Edit Recipe Line',
            'line' => $line,
            'processes' => DB::table($this->table())->whereNotNull('process')->distinct()->orderBy('process')->pluck('process'),
        ]);
    }

    /**
     * Mirrors the live table's nullability, so a line that saves here is a line that
     * would also be accepted by RecipeData.
     */
    private function rules(): array
    {
        return [
            'process' => 'required|string|max:255',
            'output_item' => 'required|string|max:255',
            'recipe' => 'nullable|string|max:255',
            'output_item_dec' => 'nullable|string|max:255',
            'output_item_uom' => 'required|string|max:50',
            'batch_size' => 'required|numeric|min:0',
            'output_item_location' => 'required|string|max:255',
            'input_item' => 'required|string|max:255',
            'input_item_desc' => 'nullable|string|max:255',
            'input_item_uom' => 'required|string|max:50',
            'input_item_qt_per' => 'required|numeric|min:0',
            'input_item_location' => 'required|string|max:255',
            'process_code' => 'nullable|string|max:20',
            'no_series' => 'nullable|string|max:20',
            'routing' => 'nullable|string|max:20',
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        DB::table($this->table())->insert($data + ['created_at' => now(), 'updated_at' => now()]);

        $this->forgetCaches();

        Toastr::success('Recipe line added.', 'Success');

        return redirect()->route('recipe_draft_index');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate($this->rules());

        $affected = DB::table($this->table())->where('id', $id)->update($data + ['updated_at' => now()]);

        if (!$affected) {
            Toastr::warning('Nothing was changed.', 'Notice');

            return redirect()->route('recipe_draft_index');
        }

        $this->forgetCaches();

        Toastr::success('Recipe line updated.', 'Success');

        return redirect()->route('recipe_draft_index');
    }

    public function destroy($id)
    {
        DB::table($this->table())->where('id', $id)->delete();

        $this->forgetCaches();

        Toastr::success('Recipe line deleted.', 'Success');

        return redirect()->back();
    }

    /**
     * Downloads whatever the current filters select, in the layout the upload reads
     * back - so the round trip is edit in Excel, upload, done.
     */
    public function export(Request $request)
    {
        return Excel::download(
            new RecipeDraftExport($this->filterValues($request), $this->table()),
            'recipe-data-draft-' . now()->format('Y-m-d-Hi') . '.xlsx'
        );
    }

    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls,csv',
            'mode' => 'required|in:replace,merge',
        ]);

        try {
            $import = new RecipeDraftImport($request->mode, $this->table());

            Excel::import($import, $request->file('excel_file'));

            if ($import->errors) {
                // Nothing was written, so the file can be corrected and re-uploaded.
                Toastr::error(
                    'Nothing was imported. ' . count($import->errors) . ' problem(s): '
                    . implode(' ', array_slice($import->errors, 0, 5))
                    . (count($import->errors) > 5 ? ' ...' : ''),
                    'Error!'
                );

                return redirect()->back();
            }

            $this->forgetCaches();

            Toastr::success("Imported {$import->created} new line(s), updated {$import->updated}.", 'Success');

            // Rows that loaded but are worth a second look - a blank output or input
            // item, which live itself carries on the deboning step definitions.
            if ($import->warnings) {
                Toastr::warning(
                    count($import->warnings) . ' line(s) have a blank Output or Input Item: '
                    . implode(' ', array_slice($import->warnings, 0, 3))
                    . (count($import->warnings) > 3 ? ' ...' : ''),
                    'Notice'
                );
            }
        } catch (\Exception $e) {
            Log::error('Recipe draft import failed: ' . $e->getMessage());
            Toastr::error($e->getMessage(), 'Error!');
        }

        return redirect()->back();
    }

    /**
     * Seeds the draft from live, so testing starts from the recipes as they really
     * are rather than an empty table. Replaces whatever is in the draft.
     *
     * Same replicator the migration and `php artisan recipes:replicate-draft` use.
     */
    public function copyFromLive(RecipeDraftReplicator $replicator)
    {
        try {
            $result = $replicator->replicate();

            Toastr::success(
                number_format($result['copied']) . " line(s) copied from {$replicator->liveTable()}.",
                'Success'
            );
        } catch (\Exception $e) {
            Log::error('Copy from live RecipeData failed: ' . $e->getMessage());
            Toastr::error($e->getMessage(), 'Error!');
        }

        return redirect()->route('recipe_draft_index');
    }

    public function truncate()
    {
        DB::table($this->table())->delete();

        $this->forgetCaches();

        Toastr::success('Draft recipe table cleared.', 'Success');

        return redirect()->route('recipe_draft_index');
    }
}
