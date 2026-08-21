@extends('layouts.sausage_master')

@section('content-header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-12">
            <h1 class="m-0">{{ $title }} | <small>draft recipe table</small></h1>
        </div>
    </div>
</div>
@endsection

@section('content')

<div class="card m-3">
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
              action="{{ $line ? route('recipe_draft_update', $line->id) : route('recipe_draft_store') }}">
            @csrf
            @if($line)
                @method('PUT')
            @endif

            <h6 class="text-muted">Step</h6>
            <div class="row form-group">
                <div class="col-md-4">
                    <label for="process">Process <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="process" name="process" list="process_options"
                           value="{{ old('process', $line->process ?? '') }}" maxlength="255" required>
                    <datalist id="process_options">
                        @foreach($processes as $process)
                            <option value="{{ $process }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div class="col-md-4">
                    <label for="recipe">Recipe</label>
                    <input type="text" class="form-control" id="recipe" name="recipe"
                           value="{{ old('recipe', $line->recipe ?? '') }}" maxlength="255">
                </div>
                <div class="col-md-4">
                    <label for="routing">Routing</label>
                    <input type="text" class="form-control" id="routing" name="routing"
                           value="{{ old('routing', $line->routing ?? '') }}" maxlength="20">
                </div>
            </div>

            <hr>
            <h6 class="text-muted">Output &mdash; what this step produces</h6>
            <div class="row form-group">
                <div class="col-md-3">
                    <label for="output_item">Output Item <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="output_item" name="output_item"
                           value="{{ old('output_item', $line->output_item ?? '') }}" maxlength="255" required>
                </div>
                <div class="col-md-4">
                    <label for="output_item_dec">Output Description</label>
                    <input type="text" class="form-control" id="output_item_dec" name="output_item_dec"
                           value="{{ old('output_item_dec', $line->output_item_dec ?? '') }}" maxlength="255">
                </div>
                <div class="col-md-2">
                    <label for="output_item_uom">Output UOM <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="output_item_uom" name="output_item_uom"
                           value="{{ old('output_item_uom', $line->output_item_uom ?? 'KG') }}" maxlength="50" required>
                </div>
                <div class="col-md-3">
                    <label for="output_item_location">Output Location <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="output_item_location" name="output_item_location"
                           value="{{ old('output_item_location', $line->output_item_location ?? '') }}" maxlength="255" required>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-3">
                    <label for="batch_size">Batch Size <span class="text-danger">*</span></label>
                    <input type="number" step="0.00001" min="0" class="form-control" id="batch_size" name="batch_size"
                           value="{{ old('batch_size', $line->batch_size ?? '') }}" required>
                    <small class="form-text text-muted">
                        Quantity of the output produced by one batch of the recipe.
                    </small>
                </div>
            </div>

            <hr>
            <h6 class="text-muted">Input &mdash; one line per item the step consumes</h6>
            <div class="row form-group">
                <div class="col-md-3">
                    <label for="input_item">Input Item <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="input_item" name="input_item"
                           value="{{ old('input_item', $line->input_item ?? '') }}" maxlength="255" required>
                </div>
                <div class="col-md-4">
                    <label for="input_item_desc">Input Description</label>
                    <input type="text" class="form-control" id="input_item_desc" name="input_item_desc"
                           value="{{ old('input_item_desc', $line->input_item_desc ?? '') }}" maxlength="255">
                </div>
                <div class="col-md-2">
                    <label for="input_item_uom">Input UOM <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="input_item_uom" name="input_item_uom"
                           value="{{ old('input_item_uom', $line->input_item_uom ?? 'KG') }}" maxlength="50" required>
                </div>
                <div class="col-md-3">
                    <label for="input_item_location">Input Location <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="input_item_location" name="input_item_location"
                           value="{{ old('input_item_location', $line->input_item_location ?? '') }}" maxlength="255" required>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-3">
                    <label for="input_item_qt_per">Qty Per Batch <span class="text-danger">*</span></label>
                    <input type="number" step="0.00001" min="0" class="form-control" id="input_item_qt_per"
                           name="input_item_qt_per" value="{{ old('input_item_qt_per', $line->input_item_qt_per ?? '') }}" required>
                    <small class="form-text text-muted">
                        How much of this input one batch consumes. The weighed quantity is
                        divided by this to scale the whole order.
                    </small>
                </div>
            </div>

            <hr>
            <h6 class="text-muted">BC references</h6>
            <div class="row form-group">
                <div class="col-md-3">
                    <label for="process_code">Process Code</label>
                    <input type="text" class="form-control" id="process_code" name="process_code"
                           value="{{ old('process_code', $line->process_code ?? '') }}" maxlength="20">
                </div>
                <div class="col-md-3">
                    <label for="no_series">No. Series</label>
                    <input type="text" class="form-control" id="no_series" name="no_series"
                           value="{{ old('no_series', $line->no_series ?? '') }}" maxlength="20">
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-paper-plane"></i> {{ $line ? 'Update' : 'Save' }}
                </button>
                <a href="{{ route('recipe_draft_index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
