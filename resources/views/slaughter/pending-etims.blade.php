@extends('layouts.slaughter_master')

@section('content')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Settlements Pending Etims Update| <span id="subtext-h1-title"><small> Update Per
                            settlement</small> </span></h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example1" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Settlement No </th>
                                <th>Vendor No.</th>
                                <th>Vendor Name </th>
                                <th>Phonenumber Unformatted </th>
                                <th>Phonenumber </th>
                                <th>Total Weight </th>
                                <th>Unit Price</th>
                                <th>Net Amount</th>
                                <th>SMS sent?</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Settlement No </th>
                                <th>Vendor No.</th>
                                <th>Vendor Name </th>
                                <th>Phonenumber Unformatted </th>
                                <th>Phonenumber </th>
                                <th>Total Weight </th>
                                <th>Unit Price</th>
                                <th>Net Amount</th>
                                <th>SMS sent?</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($results as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $data->settlement_no }}</td>
                                    <td>{{ $data->vendor_no }}</td>
                                    <td>{{ $data->vendor_name }}</td>
                                    <td>{{ $data->phonenumber }}</td>
                                    <td>{{ preg_replace('/[^0-9\/]|\/.*$/', '', $data->phonenumber) }}
                                    </td>
                                    <td>{{ number_format(floatval($data->totalWeight), 2) }}</td>
                                    <td>{{ number_format(floatval($data->unitPrice), 2) }}</td>
                                    <td>{{ number_format(floatval($data->netAmount), 2) }}</td>
                                    <td>
                                        <p
                                            class="{{ $data->is_sms_sent ? 'text-success' : 'text-warning' }}">
                                            {{ $data->is_sms_sent ? 'Yes' : 'No' }}
                                        </p>
                                        <button type="button" data-settlement_ref="{{ $data->settlement_no }}" data-phone_number="{{ preg_replace('/[^0-9\/]|\/.*$/', '', $data->phonenumber) }}" data-weight="{{ number_format(floatval($data->totalWeight), 2) }}" data-unit_price="{{ number_format(floatval($data->unitPrice), 2) }}" data-total="{{ number_format(floatval($data->netAmount), 2) }}"
                                            class="btn btn-primary btn-xs" id="sendSMSModalShow">
                                            <i class="nav-icon fas fa-send"></i>
                                            {{ $data->is_sms_sent ? 'Resend?' : 'Send Sms' }}
                                        </button>
                                    </td>
                                    <td>
                                        <button type="button" data-settlement_ref="{{ $data->settlement_no }}"
                                            class="btn btn-primary btn-sm " id="editScaleModalShow"><i
                                                class="nav-icon fas fa-edit"></i>
                                            Edit</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col -->
</div>

<!-- Start Edit Scale Modal -->
<div id="editScaleModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <form id="form-edit-scale" class="form-prevent-multiple-submits"
            action="{{ route('update_pending_etims') }}" method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Update eTims for Settlement: <code><strong><input
                                    style="border:none" type="text" id="item_name" name="item_name"
                                    readonly></strong></code></h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <label for="cu_inv_no">Cu Invoice No:</label>
                        <input type="text" class="form-control" id="cu_inv_no" name="cu_inv_no" autocomplete="off"
                            value="" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="form-group">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button class="btn btn-warning btn-lg btn-prevent-multiple-submits"
                            type="submit">
                            <i class="fa fa-save"></i> Update
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- End Edit Scale modal-->

<!-- Start SMS Scale Modal -->
<div id="sendSMSModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <form id="form-send-sms" class="form-prevent-multiple-submits">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Send SMS: <code><strong><input
                                    style="border:none" type="text" id="item_name" name="item_name"
                                    readonly></strong></code></h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <label for="cu_inv_no">Phone No:</label>
                        <input type="text" class="form-control" id="send_to_number" name="send_to_number" autocomplete="off"
                            value="" required>
                        <input type="" id="settlement_ref" value="">
                        <input type="" id="weight" value="">
                        <input type="" id="price" value="">
                        <input type="" id="amount" value="">
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="form-group">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button id="sendSms" class="btn btn-warning btn-lg btn-prevent-multiple-submits">
                            <i class="fa fa-save"></i> Update
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- End SMS modal-->

@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        $('.form-prevent-multiple-submits').on('submit', function () {
            $(".btn-prevent-multiple-submits").attr('disabled', true);
        });

        // edit
        $("body").on("click", "#editScaleModalShow", function (a) {
            a.preventDefault();

            var settlement = $(this).data('settlement_ref');

            $('#item_name').val(settlement);
            $('#item_id').val(settlement);

            // Set focus on the desired field
            $('#editScaleModal').on('shown.bs.modal', function () {
                $('#cu_inv_no').focus();
            });

            $('#editScaleModal').modal('show');
        });

        // sms modal
        $("body").on("click", "#sendSMSModalShow", function (a) {
            a.preventDefault();

            var phonenumber = $(this).data('phone_number');
            var settlementNo = $(this).data('settlement_ref');
            var weight = $(this).data('weight');
            var unitPrice = $(this).data('unit_price');
            var total = $(this).data('total');

            $('#send_to_number').val(phonenumber);
            $('#settlement_ref').val(settlementNo);
            $('#weight').val(weight);
            $('#price').val(unitPrice);
            $('#amount').val(total);
            
            $('#sendSMSModal').modal('show');
        });

        $('#sendSms').on("click", function(a){
            a.preventDefault()
            sendSMS()
        })

    });

    const sendSMS = () => {

        const url = '/send-sms'
        const senderId = "{{ config('app.sms_sender_id')}}"
        const apiKey = "{{ config('app.sms_api_key') }}"
        const clientId = "{{ config('app.sms_client_id') }}"
        const whatsappNo = "{{ config('app.sms_whatsapp_no') }}"

        const sendToNo = document.getElementById('send_to_number').value;
        const phoneNumberWithCountryCode = '254' + sendToNo.slice(-9);
        const settlementNo = document.getElementById('settlement_ref').value;
        const qty = document.getElementById('weight').value;
        const unitPrice = document.getElementById('price').value;
        const amount = document.getElementById('amount').value;

        // Constructing the message parameters object
        const messageParameters = [
            {
                Number: `${phoneNumberWithCountryCode}`,
                Text: `${settlementNo} CONFIRMED. GET eTIMS Invoice on *222#, share by WhatsApp on ${whatsappNo} by 9am.P051521001E, Qty:${qty} Price: ${unitPrice} Amount: ${amount}`
            }
        ];

        // Constructing the request body
        const requestBody = {
            SenderId: senderId,
            MessageParameters: messageParameters,
            ApiKey: apiKey,
            ClientId: clientId
        };

        console.log(requestBody)

        axios.post(url, requestBody)
            .then(response => {
                console.log('Request successful:', response);
                // Handle success response here
            })
            .catch(error => {
                console.error('Error:', error);
                // Handle error here
            });
    }


</script>
@endsection
