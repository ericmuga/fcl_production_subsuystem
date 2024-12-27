<form id="form-issue-idt" class="card-group text-center form-prevent-multiple-submits" action="{{ route('despatch_save_issued_idt') }}" method="post">
    @csrf
    <div class="card">
        <div class="card-body from-group">
            <div class="form-group">
                <label for="product_code" >Product</label>
                <select class="form-control select2" name="product_code" id="product_code" onchange="loadProductDetails(event)" required>
                    <option selected disabled value>Select product</option>
                    @foreach($products as $product)
                    <option value="{{ $product->code }}">
                        {{ $product->code }} - {{ $product->description }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label class="form-label" for="location_code">Transfer To</label>
                    <select class="form-control" name="location_code" id="location_code" required readonly>
                        <option value="2595" {{ $send_to_location == 'highcare' ? 'selected' : '' }}>High Care</option>
                        <option value="2055" {{ $send_to_location == 'sausage' ? 'selected' : '' }}>Sausage</option>
                        <option value="1570" {{ $send_to_location == 'butchery' ? 'selected' : '' }}>Butchery</option>
                        <option value="3035" {{ $send_to_location == 'petfood' ? 'selected' : '' }}>PetFood</option>
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label for="carriage_type">Carriage Type</label>
                    <select class="form-control" name="carriage_type" id="carriage_type" onchange="updateCarriage(event)" required>
                        <option disabled selected value> -- select an option -- </option>
                        <option value="crate">Crate</option>
                        <option value="van">Van</option>
                    </select>
                </div>
            </div>
            <div hidden id="crates_div" class="form-row">
                <div class="col-md-6 from-group">
                    <label for="total_crates">Total Crates </label>
                    <input type="number" class="form-control" id="kg_total_crates" value="1" name="total_crates" min="1" oninput="updateTare()">
                </div>
                <div class="col-md-6 form-group">
                    <label for="black_crates">Black Crates </label>
                    <input type="number" class="form-control" id="black_crates" value="1" name="black_crates" min="0" oninput="updateTare()">
                </div>
            </div>
        </div>
    </div>
    <div id="pcWeightInputs" class="card">
        <div class="card-body form-group">
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="unit_crate_count">Unit Count Per Crate</label>
                    <input type="number" readonly class="form-control input_params crates" value="0"
                        id="unit_crate_count" name="unit_crate_count" placeholder="">
                </div>
                <div class="col-md-6 form-gorup">
                    <label for="unit_measure">Item Unit Measure</label>
                    <input type="text" readonly class="form-control input_params" value="0"
                        id="unit_measure" name="unit_measure" placeholder="">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="total_crates">Total Crates</label>
                    <input type="number" class="form-control crates" id="pc_total_crates" name="total_crates" value="" onkeyup="handleChange()" placeholder="">
                </div>
                <div class="col-md-6 form-check">
                    <input type="checkbox" class="form-check-input" id="incomplete_crates" name="incomplete_crates" onchange="togglePiecesInput()">
                    <label class="form-check-label" for="incomplete_crates">Incomplete Crate</label>
                </div>
                <div id="incomplete_pieces_group" class="col-md-6 form-group">
                    <label for="incomplete_pieces">Pieces in incomplete Crate</label>
                    <input type="number" class="form-control crates" min="0" id="incomplete_pieces" name="incomplete_pieces" readonly>
                </div>
                <input type="hidden" name="no_of_pieces" id="no_of_pieces" value="0">
                <div class="col-md-6 form-group">
                    <label for="calculated_weight">Calculated Weight (kgs)</label>
                    <input type="number" class="form-control crates" value="0" id="calculated_weight"
                        name="calculated_weight" placeholder="" readonly>
                </div>
            </div>
            <span class="text-danger" id="err1"></span>
            <span class="text-success" id="succ1"></span>
            <input type="hidden" name="crates_valid" id="crates_valid" value="0">
            <input type="hidden" name="" id="crates_valid" value="0">
        </div>
    </div>
    <div id="scaleInputs" class="card" hidden>
        <div class="card-body">
            <div class="form-group">
                <label for="reading">Scale Reading</label>
                <input type="number" step="0.01" class="form-control" id="reading" name="reading" value="0.00"
                    oninput="getNet()" placeholder="" readonly>
            </div>
            @if(count($configs) === 0)
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="manual_weight" name="manual_weight">
                <label class="form-check-label" for="manual_weight">Enter Manual weight</label>
            </div> <br>
            @endif
            <input type="hidden" id="old_manual" value="{{ old('manual_weight') }}">
            <div class="form-row">
                <div class="col-md-6 form-group">
                    <label for="tareweight">Tare-Weight</label>
                    <input type="number" class="form-control" id="tareweight" name="tareweight" value="0.00"
                        step=".01" placeholder="" readonly>
                </div>
                <div class="col-md-6 form-group">
                    <label for="net">Net Weight</label>
                    <input type="number" class="form-control" id="net" name="net" value="0.00" step=".01"
                        placeholder="" readonly>
                </div>
            </div>
            <div class="form-group mt-3">
                <button type="button" onclick="getScaleReading()" id="weigh" value=""
                    class="btn btn-primary btn-lg"><i class="fas fa-balance-scale"></i> Weigh
                </button>
                @if(count($configs) > 0)
                    <small class="d-block">Reading from : <input style="font-weight: bold; border: none" type="text" id="comport_value"
                            value="{{ $configs[0]->comport }}" style="border:none" disabled></small>
                @else
                    <small class="d-block">No comport configured</small>
                @endif
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body text-center form-group">
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="inputEmail3">Batch No </label>
                    <input type="text" class="form-control" value="" id="batch_no" name="batch_no" required placeholder="">
                </div>
                <div class="form-group col-md-6">
                    <label for="inputEmail3">Description (optional)</label>
                    <input type="number" class="form-control" value="" id="description" name="description" placeholder="">
                </div>

            </div>
            
            <div class="div" style="padding-top: 5%">
                <button type="submit" id="submit-btn" class="btn btn-primary btn-lg btn-prevent-multiple-submits"
                    onclick="return validateSubmitValues()"><i
                        class="fa fa-paper-plane single-click" aria-hidden="true"></i> Save</button>
            </div>
        </div>
    </div>
</form>