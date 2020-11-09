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
                    <div class="col-md-12" style="">
                        <form id="_form" class="form-inline" method="get">
                            <label class="sr-only">Start date</label>
                            <input type="text" name="date_start" value="{{ $start_date }}"
                                   class="form-control form-control-sm mb-2 mr-sm-2 mb-sm-0" placeholder="Start date" readonly>

                            <label class="sr-only">End date</label>
                            <input id="dateEnd" type="text" name="date_end" value="{{ $end_date }}"
                                   class="form-control form-control-sm" id="inlineFormInputGroup" placeholder="End date"
                                   required>
                            &nbsp;
                            <button type="submit" class="btn btn-default btn-sm" style="cursor: pointer">Submit</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <h3 class="text-center pt-5 pb-5"><strong>Weekly Performance</strong></h3>
                <table class="table table-bordered table-hover">
                <thead>
                <tr>
                    <td colspan="4" align="center"><strong>Weekly Performance</strong></td>
                </tr>
                <tr>
                    <th>Week</th>
                    <th>Active Customers</th>
                    <th>Amount Spent</th>
                    <th>Average Spent</th>
                </tr>
                </thead>
                <tbody>
                {{$weekly_performance}}
                </tbody>
            </table>
            </div>

        </div>
    </div>
</div>

@endsection