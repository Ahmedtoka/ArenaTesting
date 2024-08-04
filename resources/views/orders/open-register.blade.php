@extends('layouts.app')
@section('content')
    <section class="forms">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h4>Open Register</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('open.register.store') }}">
                                @csrf
                                <div class="form-group">
                                    <input type="text" name="amount" class="form-control" placeholder="Opening Amount">
                                </div>
                                <div class="form-group">
                                    <button class="btn btn-primary" type="submit">submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
