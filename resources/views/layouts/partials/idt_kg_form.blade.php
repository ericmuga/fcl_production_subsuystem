<form id="form-issue-idt" class="card-group text-center form-prevent-multiple-submits" action="{{ route('despatch_save_issued_idt') }}" method="post">
    @csrf
    <div class="card">
        <div class="card-body">
            <div class="form-group">
                <label for="product_code"> Product</label>
                <select class="form-control select2" name="product_code" id="product_code" required>
                    <option selected disabled value>Select product</option>
                    @foreach($products as $product)
                        <option value="{{ $product->code }}">
                            {{ $product->code }} - {{ $product->description }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-row">
                <div class="col-md-6 form-group">
                    <label for="location_code">Transfer To</label>
                    <select class="form-control" name="location_code" id="location_code" required readonly>
                        <option value="2595" {{ $send_to_location == 'highcare' ? 'selected' : '' }}>High Care</option>
                        <option value="2055" {{ $send_to_location == 'sausage' ? 'selected' : '' }}>Sausage</option>
                        <option value="1570" {{ $send_to_location == 'butchery' ? 'selected' : '' }}>Butchery</option>
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
                    <label for="kg_total_crates">Total Crates </label>
                    <input type="number" class="form-control" id="kg_total_crates" value="1" name="total_crates" min="1" oninput="updateTare()">
                </div>
                <div class="col-md-6 form-group">
                    <label for="black_crates">Black Crates </label>
                    <input type="number" class="form-control" id="black_crates" value="1" name="black_crates" min="0" oninput="updateTare()">
                </div>
            </div>
        </div>
    </div>
    <div class="card">
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
    <div class="card ">
        <div class="card-body">
            <div class="row">
                @if(count($chillers) > 0)
                    <div class="col-md-6">
                        <label for="chiller_code">Transfer To Chiller </label>
                        <select class="form-control locations" name="chiller_code" id="chiller_code" required>
                            <option disabled selected value> -- select an option -- </option>
                            @foreach($chillers as $chiller)
                                <option value="{{ $chiller->chiller_code }}">
                                    {{ $chiller->chiller_code }} - {{ $chiller->description }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-6">
                    <label for="no_of_pieces">No. of pieces </label>
                    <input type="number" class="form-control" value="" id="no_of_pieces" name="no_of_pieces"
                        required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-12 col-md-6">
                    <label for="batch_no">Batch No </label>
                    <input type="text" class="form-control" id="batch_no" value="" name="batch_no" required>
                </div>
                <div class="form-group col-12 col-md-6">
                    <label for="description">Description (optional)</label>
                    <input type="text" class="form-control" id="description" value="" name="description">
                </div>
            </div> 
            
            <div hidden id="export_desc_div" class="row form-group">
                <div class="col-md-6">
                    <label for="desc">Export Customer </label>
                    <input type="text" class="form-control" id="desc" value="" name="desc" placeholder="">
                </div>
                <div class="col-md-6">
                    <label for="order_no">Order No </label>
                    <input type="text" class="form-control" id="order_no" value="" name="order_no" placeholder="">
                </div>
            </div>
            <div class="form-group" style="padding-top: 5%">
                <button type="submit" onclick="return validateOnSubmit()"
                    class="btn btn-primary btn-lg btn-prevent-multiple-submits"><i
                        class="fa fa-paper-plane single-click" aria-hidden="true"></i> Save</button>
            </div>
        </div>
    </div>
</form>