{{-- Extends layout --}}
@extends('layout.dashboard')

{{-- Content --}}
@section('content')

    <div class="card card-custom">
        <div class="card-header flex-wrap border-0 pt-6 pb-0">
            <div class="card-title">
                {{--<h3 class="card-label">HTML Table
                    <div class="text-muted pt-2 font-size-sm">Datatable initialized from HTML table</div>
                </h3>--}}
            </div>
            <div class="card-toolbar">
            </div>
        </div>

        <div class="card-body">

            <!--begin::Search Form-->
            <div class="mt-2 mb-5 mt-lg-5 mb-lg-10">
                <div class="row align-items-center">
                    <div class="col-lg-9 col-xl-8">
                        <div class="row align-items-center">
                            <div class="col-md-4 my-2 my-md-0">
                                <div class="input-icon">
                                    <input type="text" class="form-control" placeholder="Search..." id="kt_datatable_search_query"/>
                                    <span><i class="flaticon2-search-1 text-muted"></i></span>
                                </div>
                            </div>

                            <div class="col-md-4 my-2 my-md-0">
                                <div class="d-flex align-items-center">
                                    <label class="mr-3 mb-0 d-none d-md-block">Client status:</label>
                                    <select class="form-control" id="kt_datatable_search_status">
                                        <option value="">All</option>
                                        <option value="1">Active</option>
                                        <option value="1">Inactive</option>
                                        <option value="2">Suspend payment</option>
                                        <option value="3">Canceled</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 my-2 my-md-0">
                                <div class="d-flex align-items-center">
                                    <label class="mr-3 mb-0 d-none d-md-block">Payment:</label>
                                    <select class="form-control" id="kt_datatable_search_type">
                                        <option value="">All</option>
                                        <option value="1">Rejected</option>
                                        <option value="2">Paid</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-xl-4 mt-5 mt-lg-0">
                        <a href="#" class="btn btn-light-primary px-6 font-weight-bold">
                            Search
                        </a>
                    </div>
                </div>
            </div>
            <!--end::Search Form-->

            <table class="table table-bordered table-hover" id="kt_datatable">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Client Name</th>
                    <th>Status</th>
                    <th>Signup</th>
                    <th>Plan</th>
                    <th>Budget</th>
                    <th>Payment</th>
                    <th>Last Billing</th>
                    <th>Next Billing</th>
                    <th>FB Admin</th>
                    <th>Catalog</th>
                    <th>Ads design</th>
                    <th>Camp Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($customers as $customer)
                    <tr>
                        <td>{{ $customer->id }}</td>
                        <td>{{ $customer->first_name }} {{ $customer->last_name }}</td>
                        <td style="{{ $customer->status == 'active' ? 'color:#228B22' : ($customer->status == 'inactive' ? 'color:orange' : 'color:red') }}">
                            {{ ucfirst($customer->status) }}
                        </td>
                        <td>{{ $customer->created_at->format('M d, Y') }}</td>
                        <td style="{{ isset($customer->plan) ? 'color:#228B22' : '' }}">{{ isset($customer->plan) ? '$'.$customer->plan : '--' }}</td>
                        <td>{{ isset($customer->weekly_budget) ? '$'.$customer->weekly_budget : '--' }}</td>
                        <td style="{{ $customer->payment == 'Paid' ? 'color:#228B22' : 'color:red' }}">
                            {{ $customer->payment }}
                        </td>
                        <td>{{ isset($customer->last_billing) ? $customer->last_billing->format('M d, Y') : '--' }}</td>
                        <td>{{ isset($customer->next_billing) ? $customer->next_billing->format('M d, Y') : '--' }}</td>
                        @if($customer->fb_grant_status == 'yes')
                            <td>
                                <?xml version="1.0" encoding="UTF-8"?>
                                <svg width="24px" height="24px" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                    <!-- Generator: Sketch 50.2 (55047) - http://www.bohemiancoding.com/sketch -->
                                    <title>Stockholm-icons / Code / Done-circle</title>
                                    <desc>Created with Sketch.</desc>
                                    <defs></defs>
                                    <g id="Stockholm-icons-/-Code-/-Done-circle" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                        <rect id="bound" x="0" y="0" width="24" height="24"></rect>
                                        <circle id="Oval-5" fill="#ffffff" opacity="0.3" cx="12" cy="12" r="10"></circle>
                                        <path d="M16.7689447,7.81768175 C17.1457787,7.41393107 17.7785676,7.39211077 18.1823183,7.76894473 C18.5860689,8.1457787 18.6078892,8.77856757 18.2310553,9.18231825 L11.2310553,16.6823183 C10.8654446,17.0740439 10.2560456,17.107974 9.84920863,16.7592566 L6.34920863,13.7592566 C5.92988278,13.3998345 5.88132125,12.7685345 6.2407434,12.3492086 C6.60016555,11.9298828 7.23146553,11.8813212 7.65079137,12.2407434 L10.4229928,14.616916 L16.7689447,7.81768175 Z" id="Path-92" fill="#228B22" fill-rule="nonzero"></path>
                                    </g>
                                </svg>
                            </td>
                        @else
                            <td>--</td>
                        @endif
                        <td>--</td>
                        <td>--</td>
                        <td style="{{ $customer->campaign_status == 'Launched' ? 'color:#228B22' : '' }}{{ $customer->campaign_status == 'Stopped' ? 'color:red' : '' }}">
                            {{ isset($customer->campaign_status) ? $customer->campaign_status : '--' }}
                        </td>
                        <td></td>
                    </tr>
                @endforeach
                </tbody>
            </table>

        </div>

    </div>

@endsection

{{-- Styles Section --}}
@section('styles')
    <link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css"/>
@endsection


{{-- Scripts Section --}}
@section('scripts')
    {{-- vendors --}}
    <script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script>

    {{-- page scripts --}}
    <script src="{{ asset('js/pages/crud/datatables/basic/basic.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/app.js') }}" type="text/javascript"></script>
@endsection
