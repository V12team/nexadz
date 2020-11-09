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

            <div class="card-body" style="min-height: 700px">
                @if($form)
                <h3 class="text-center pt-5 pb-5"><strong>Create Facebook Campaign</strong></h3>
                <form action="/dashboard/create-campaign" method="post" style="margin: 20px auto; display: block; width: 400px">
                    <div class="form-group">
                        <label class="font-size-h6">Campaign type</label>
                        <select class="form-control" size="" name="campaign_type">
                            <option value="LINK_CLICKS">Display ads</option>
                            <option value="LINK_CLICKS">Retargeting</option>
                            <option value="PRODUCT_CATALOG_SALES">Dynamic Retargeting</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-size-h6">Network</label>
                        <select class="form-control" size="" name="network">
                            <option value="">Facebook</option>
                            <option value="">Instagram</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-size-h6">Campaign name</label>
                        <input class="form-control" type="text" name="campaign_name" autocomplete="off" />
                    </div>
                    <div class="form-group">
                        <label class="font-size-h6">Raduis</label>
                        <input class="form-control" type="text" name="raduis" autocomplete="off" />
                    </div>
                    <div class="form-group">
                        <label class="font-size-h6">Zip code</label>
                        <input class="form-control" type="text" name="zipcode" autocomplete="off" />
                    </div>
                    <div class="form-group">
                        <label class="font-size-h6">Choose the language</label>
                        <select class="form-control" size="" name="language">
                            <option value="en">EN</option>
                            <option value="es">ES</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-size-h6">Fb page</label>
                        <select class="form-control" size="" name="page_id">
                            <option value="1631430760520458">1631430760520458</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-size-h6">Website</label>
                        <input class="form-control" type="text" name="website" autocomplete="off" />
                    </div>
                    <div class="form-group">
                        <label class="font-size-h6">Campaign headline</label>
                        <textarea class="form-control" name="headline" autocomplete="off">
                        </textarea>
                    </div>
                    <div class="form-group">
                        <label class="font-size-h6">Upload Ad Image</label>
                        <input class="form-control" type="file" name="image" autocomplete="off" />
                    </div>
                    <div class="form-group">
                        <button type="submit" id="kt_login_signin_submit" class="btn btn-primary">Launch</button>
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </div>
                </form>
                @else
                <div class="alert alert-success" role="alert">
                    Facebook campaign successfully created, it will be launched soon
                </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection