@extends('layouts.app')
@section('content')
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close"
                data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{!! session()->get('message') !!}
        </div>
    @endif
    @if (session()->has('not_permitted'))
        <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert"
                aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}
        </div>
    @endif

    <section>
        <div class="table-responsive">
            <table id="sale-table" class="table sale-list" style="width: 100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ trans('file.Date') }}</th>
                        <th>{{ trans('file.Close Date') }}</th>
                        <th>{{ trans('file.Register Status') }}</th>
                        <th>{{ trans('file.Warehouse') }}</th>
                        <th>{{ trans('file.Employee') }}</th>
                        <th>{{ trans('file.Close Amount') }}</th>
                        <th>{{ trans('file.Close Status') }}</th>
                        <th>{{ trans('file.Close Status Amount') }}</th>
                    </tr>
                </thead>
                <tbody>


                    @foreach ($registers as $index => $register)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $register->created_at }}</td>
                            <td>
                                @if ($register->closed_at)
                                    <a href="{{ route('register.show', $register->id) }}">{{ $register->closed_at }}</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $register->status }}</td>
                            <td>{{ $register->branch->name }}</td>
                            <td>{{ $register->user->name }}</td>
                            <td>{{ $register->closing_amount }}</td>
                            <td>
                                @if ($register->close_status == 'negative')
                                    يوجد عجز
                                @elseif($register->close_status == 'positive')
                                    يوجد زيادة
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $register->close_status_amount }}</td>
                        </tr>
                    @endforeach
                </tbody>
                {{ $registers->links() }}
            </table>
        </div>
    </section>
@endsection

@section('scripts')
    <script type="text/javascript" src="https://js.stripe.com/v3/"></script>
@endsection
