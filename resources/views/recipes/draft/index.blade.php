@extends('layouts.sausage_master')

@section('content-header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-12">
            <h1 class="m-0">{{ $title }} |
                <small>editable working copy of {{ $live_table }}</small>
            </h1>
        </div>
    </div>
</div>
@endsection

@section('content')

{{-- Which table a weighing will actually read. Never leave this to memory. --}}
<div class="col-md-12">
    <div class="alert {{ $active_table === $draft_table ? 'alert-warning' : 'alert-info' }}">
        <h5 class="mb-1">
            <i class="fas fa-random"></i>
            Production orders are currently generated from
            <strong>{{ $active_table }}</strong>
            @if($active_table === $draft_table)
                <span class="badge badge-warning">TEST</span>
            @else
                <span class="badge badge-success">LIVE</span>
            @endif
        </h5>
        <p class="mb-0">
            Draft holds <strong>{{ number_format($draft_count) }}</strong> line(s),
            live {{ $live_table }} holds <strong>{{ number_format($live_count) }}</strong>.
            Switch with <code>PRODUCTION_ORDERS_RECIPE_TABLE</code> in <code>.env</code> &mdash; see
            <code>docs/recipe-draft-and-toggles.md</code>.
        </p>
    </div>
</div>

<!-- Upload -->
<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('recipe_draft_import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload to Draft</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="excel_file">Excel file (.xlsx, .xls, .csv)</label>
                        <input type="file" name="excel_file" id="excel_file" class="form-control-file" required>
                        <small class="form-text text-muted">
                            Either layout is accepted: a download from this screen (leading ID
                            column) or a plain {{ $live_table }} sheet starting at Process.
                        </small>
                    </div>
                    <div class="form-group">
                        <label for="mode">How to apply it</label>
                        <select name="mode" id="mode" class="form-control" required>
                            <option value="replace">Replace &mdash; clear the draft, then load the sheet</option>
                            <option value="merge">Merge &mdash; update rows by ID, add the rest</option>
                        </select>
                        <small class="form-text text-muted">
                            Nothing is written if any row is rejected, so a bad file leaves the
                            draft exactly as it was.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card m-3">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <h3 class="card-title mb-0">Recipe lines</h3>
        <div>
            <a href="{{ route('recipe_draft_create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> New Line
            </a>
            <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#uploadModal">
                <i class="fas fa-file-upload"></i> Upload Excel
            </button>
            <a href="{{ route('recipe_draft_export', request()->only(['recipe','process','output_item','input_item'])) }}"
               class="btn btn-success btn-sm">
                <i class="fas fa-file-excel"></i> Download Excel
            </a>
            <form action="{{ route('recipe_draft_copy_live') }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Replace the whole draft with a fresh copy of {{ $live_table }}?');">
                @csrf
                <button type="submit" class="btn btn-warning btn-sm">
                    <i class="fas fa-copy"></i> Copy from Live
                </button>
            </form>
            <form action="{{ route('recipe_draft_truncate') }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Delete every line in the draft table?');">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="fas fa-trash"></i> Clear Draft
                </button>
            </form>
        </div>
    </div>

    <div class="card-body">
        {{-- Filtering is server side; the draft carries the same few thousand rows
             as live, which is more than a browser side table wants to hold. --}}
        <form method="GET" action="{{ route('recipe_draft_index') }}" class="mb-3">
            <div class="row form-group">
                <div class="col-md-3">
                    <label for="filter_recipe" class="small mb-1">Recipe</label>
                    <input type="text" class="form-control form-control-sm" id="filter_recipe"
                           name="recipe" value="{{ $filters['recipe'] ?? '' }}" placeholder="contains...">
                </div>
                <div class="col-md-2">
                    <label for="filter_process" class="small mb-1">Process</label>
                    <select class="form-control form-control-sm" id="filter_process" name="process">
                        <option value="">All processes</option>
                        @foreach($processes as $process)
                            <option value="{{ $process }}" @if(($filters['process'] ?? '') === $process) selected @endif>
                                {{ $process }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter_output_item" class="small mb-1">Output Item</label>
                    <input type="text" class="form-control form-control-sm" id="filter_output_item"
                           name="output_item" value="{{ $filters['output_item'] ?? '' }}" placeholder="contains...">
                </div>
                <div class="col-md-3">
                    <label for="filter_input_item" class="small mb-1">Input Item</label>
                    <input type="text" class="form-control form-control-sm" id="filter_input_item"
                           name="input_item" value="{{ $filters['input_item'] ?? '' }}" placeholder="contains...">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm btn-block">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            @if(array_filter($filters))
                <a href="{{ route('recipe_draft_index') }}" class="small">Clear filters</a>
            @endif
        </form>

        <div class="table-responsive">
            <table class="table table-sm table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Process</th>
                        <th>Recipe</th>
                        <th>Output Item</th>
                        <th>Output Description</th>
                        <th>Out UOM</th>
                        <th class="text-right">Batch Size</th>
                        <th>Out Location</th>
                        <th>Input Item</th>
                        <th>Input Description</th>
                        <th>In UOM</th>
                        <th class="text-right">Qty Per</th>
                        <th>In Location</th>
                        <th>Routing</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lines as $line)
                        <tr>
                            <td>{{ $line->id }}</td>
                            <td>{{ $line->process }}</td>
                            <td>{{ $line->recipe }}</td>
                            <td>{{ $line->output_item }}</td>
                            <td>{{ $line->output_item_dec }}</td>
                            <td>{{ $line->output_item_uom }}</td>
                            <td class="text-right">{{ number_format($line->batch_size, 4) }}</td>
                            <td>{{ $line->output_item_location }}</td>
                            <td>{{ $line->input_item }}</td>
                            <td>{{ $line->input_item_desc }}</td>
                            <td>{{ $line->input_item_uom }}</td>
                            <td class="text-right">{{ number_format($line->input_item_qt_per, 4) }}</td>
                            <td>{{ $line->input_item_location }}</td>
                            <td>{{ $line->routing }}</td>
                            <td class="text-center text-nowrap">
                                <a href="{{ route('recipe_draft_edit', $line->id) }}"
                                   class="btn btn-primary btn-xs" title="edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('recipe_draft_destroy', $line->id) }}" method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Delete line {{ $line->id }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-xs" title="delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" class="text-center text-muted">
                                The draft table is empty &mdash; use <strong>Copy from Live</strong> to seed it
                                from {{ $live_table }}, or upload a sheet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">
                Showing {{ $lines->firstItem() ?? 0 }}&ndash;{{ $lines->lastItem() ?? 0 }}
                of {{ number_format($lines->total()) }} line(s)
            </small>
            {{ $lines->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>
@endsection
