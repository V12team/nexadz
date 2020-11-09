{{-- Extends layout --}}
@extends('layout.dashboard')

{{-- Content --}}
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
                <h3 class="text-center pt-5 pb-5"><strong>Monthly Performance</strong></h3>
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Active Customers</th>
                            <th>Amount Spent</th>
                            <th>Growth</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{$monthly_performance}} 
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

@endsection