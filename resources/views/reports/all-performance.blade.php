@extends('layout.dashboard')
@section('content')
<div class="d-flex flex-column-fluid">
    <div class="fullwidth">
        <div class="card card-custom">
            <div class="card-header flex-wrap border-0 pt-6 pb-0">
                <div class="card-title">
                </div>
                <div class="card-toolbar">
                </div>
            </div>

            <div class="card-body">
                <h3 class="text-center pt-5 pb-5"><strong>All Customers Performance</strong></h3>
                <table class="table table-bordered table-hover" id="kt_datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer First Name</th>
                            <th>Customer Last Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Company</th>
                            <th>FB Page</th>
                            <th>FB Grant Status</th>
                            <th>Country</th>
                            <th>State</th>
                            <th>Zip Code</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($customers as $customer)
                        <tr>
                            <th>{{$customer->id}}</th>
                            <th>{{$customer->first_name}}</th>
                            <th>{{$customer->last_name}}</th>
                            <th>{{$customer->email}}</th>
                            <th>{{$customer->phone}}</th>
                            <th>{{$customer->company}}</th>
                            <th>{{$customer->fb_page}}</th>
                            <th>{{$customer->fb_grant_status}}</th>
                            <th>{{$customer->country}}</th>
                            <th>
                                @if ($customer === "IN_PROGRESS")
                                    In Progress
                                @elseif ($customer === "ACTIVE")
                                    Active
                                @elseif ($customer === "PAUSED")
                                    Paused
                                @else
                                    Unfunded
                                @endif
                            </th>
                            <th>{{$customer->zipcode}}</th>
                            <th>{{$customer->address}}</th>
                            <th>{{$customer->status}}</th>
                            <th>{{$customer->balance}}</th>
                        </tr>
                    @endforeach
                        
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
@endsection