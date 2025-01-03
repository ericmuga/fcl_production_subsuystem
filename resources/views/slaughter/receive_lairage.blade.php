@extends('layouts.slaughter_master')

@section('content-header')
<div class="container">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0"> {{ $title }} |<small> receive </small></h1>
        </div>
    </div>
</div>

@endsection

@section('content')
<div class="card">
<table class="table">
    <thead>
      <tr>
        <th>Animal Type</th>
        <th>Count</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody id="sentPigsTable">
      <tr id="message-row">
        <td col-span="2" id="message-text">Loading...</td>
      </tr>
    </tbody>
  </table>
</div>

<button class="btn btn-primary my-4" data-toggle="collapse" data-target="#receivedEntries">Received Entries</button>

<div id="receivedEntries" class="collapse card p-4">
    <table id="example1" class="table table-bordered">
        <thead>
          <tr>
            <th>Animal Type</th>
            <th>Count</th>
            <th>Date Time Received</th>
            <th>Received By</th>
          </tr>
        </thead>
        <tbody class="reeceivedEntriesTableBody">
            @foreach ($received as $entry)
            <tr>
                <th> {{ $animalTypes[$entry->product_code] }}</th>
                <th>{{ $entry->count  }}</th>
                <th>{{ $entry->received_date_time }}</th>
                <th>{{ $entry->received_username }}</th>
              </tr>
            @endforeach
        </tbody>
      </table>
</div>


@endsection


@section('scripts')
<script>
    const pollUrl = '/lairage_transfers/poll';
    let newPigs = [];
    const animalTypes = {
        G0101: 'Baconer',
        G0102:  'Sow',
        G0104: 'Suckling',
    }; 

    // Function to update the table based on fetched data
    function updateTable(newData) {
        const tableBody = document.querySelector('#sentPigsTable');
        const existingRows = Array.from(tableBody.querySelectorAll('tr'));
        let oldPigs = newPigs;
        newPigs = newData;

        // Create a Set of existing row IDs
        const existingIds = oldPigs.map(sent => sent.id);

        // Create a Set of new data IDs
        const newIds = newPigs.map(sent => sent.id);

        // Remove message 
        if(oldPigs.length == 0) {
            row = document.getElementById('message-row');
            if (row) {
                row.remove();
            }
        }

        // Remove rows that no longer exist in the new data
        existingRows.forEach(row => {
            if (!newIds.includes(row.dataset.id)) {
                row.remove(); // Remove row if its ID is not in new data
            }
        });

        // Add or update rows based on the new data
        newData.forEach(item => {
            let existingRow = document.querySelector(`tr[data-id='${item.id}']`);

            if (!existingRow) {
                // Create a new row if it doesn't exist
                const newRow = document.createElement('tr');
                newRow.dataset.id = item.id;

                newRow.innerHTML = `
                    <td><h4>${animalTypes[item.product_code]}</h4></td>
                    <td><h4>${item.count}</h4></td>
                    <td class="name">
                        <button class="btn btn-lg btn-primary" data-product="${item.product_code}" data-id="${item.id}" data-qty="${item.count}" onclick="acceptPigs(event)">Accept</button>
                        <button class="btn btn-lg btn-danger" data-product="${item.product_code}" data-id="${item.id}" data-qty="${item.count}" onclick="rejectPigs(event)">Reject</button>
                    </td>
                    
                `;

                tableBody.appendChild(newRow);
            }
        });

        if(newPigs.length == 0) {
            const newRow = document.createElement('tr');
            newRow.id = 'message-row';

            newRow.innerHTML = `
                    <td col-span"2">All sent acknowledged</td>
                `;

            tableBody.appendChild(newRow);
        }
    }

    function acceptPigs(event) {
        console.log('Accepting pigs');
        btn = event.currentTarget;
        btn.disabled = true
        const id = btn.dataset.id;
        const qty = btn.dataset.qty;
        const product_code = btn.dataset.product;
        const url = "{{ route('lairage_transfer_receive') }}"

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                    .attr('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id, qty, product_code, reject: 0
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success('Accepted Pig Transfer');
                location.reload();
            } else {
                console.error(data);
                toastr.error(data.message);
            }
        })
    }

    function rejectPigs(event) {
        console.log('Rejecting pigs');
        btn = event.currentTarget;
        btn.disabled = true
        const id = btn.dataset.id;
        const qty = btn.dataset.qty;
        const product_code = btn.dataset.product;
        const url = "{{ route('lairage_transfer_receive') }}"

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                    .attr('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id, qty, product_code, reject: 1
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success('Accepted Pig Transfer');
                location.reload();
            } else {
                console.error(data);
                toastr.error(data.message);
            }
        })
    }


    // Polling function
    setInterval(function() {
        fetch(pollUrl)
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message);
                    return;
                }        
                updateTable(data.sent);  // Call function to update the table
            })
            .catch(error => {
                console.error('Error loading data:', error);
                toastr.error(`Error loading data: ${error.message}`);
            });
    }, 6000); // Poll every 60 seconds (1 minute)

</script>
@endsection