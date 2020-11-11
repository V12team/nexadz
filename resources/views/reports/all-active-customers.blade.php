{{-- Extends layout --}}
@extends('layout.dashboard')


@section('style')
<style>
    .table tr th:nth-child(4), .table tr th:nth-child(5), .table tr th:nth-child(6), .table tr th:nth-child(7), .table tr th:nth-child(8){
        border-top: 2px solid #ffa502;
    }
    .table tr th:nth-child(9), .table tr th:nth-child(10), .table tr th:nth-child(11), .table tr th:nth-child(12), .table tr th:nth-child(13){
        border-top: 2px solid #0275d8;
    }
    .table tr th:nth-child(4), .table tr td:nth-child(4) {
        border-left: 2px solid #ffa502;
    }
    .table tr th:nth-child(8), .table tr td:nth-child(8) {
        border-right: 2px solid #ffa502;
    }
    .table tr th:nth-child(9), .table tr td:nth-child(9) {
        border-left: 2px solid #0275d8;
    }
    .table tr th:nth-child(13), .table tr td:nth-child(13) {
        border-right: 2px solid #0275d8;
    }
</style>
@endsection
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
                            <input id="dateStart" type="text" name="date_start" value="{{ $start_date }}"
                                   class="form-control form-control-sm mb-2 mr-sm-2 mb-sm-0" placeholder="Start date" required>

                            <label class="sr-only">End date</label>
                            <input id="dateEnd" type="text" name="date_end" value="{{ $end_date }}"
                                   class="form-control form-control-sm" id="inlineFormInputGroup" placeholder="End date" required>
                            &nbsp;
                            <button type="submit" class="btn btn-default btn-sm" style="cursor: pointer">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <h3 class="text-center pt-5 pb-5">NEXADZ performance
                    From <?= date('d M Y', strtotime($start_date)) ?>
                    To <?= date('d M Y', strtotime($end_date)) ?></h3>
                <table class="table table-bordered table-hover" id="kt_datatable">
                    <thead>
                        <tr>
                            <th>Customer ID</th>
                            <th>Customer L.Name</th>
                            <th>BUDGET</th>
                            <th>G CLICKS</th>
                            <th>G LEADS</th>
                            <th>G SPENT</th>
                            <th>G CPC</th>
                            <th>G CPL</th>
                            <th>FB CLICKS</th>
                            <th>FB LEADS</th>
                            <th>FB SPENT</th>
                            <th>FB CPC</th>
                            <th>FB CPL</th>
                        </tr>
                    </thead>
                    <tbody>
                        {!! $table !!}
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>
@endsection