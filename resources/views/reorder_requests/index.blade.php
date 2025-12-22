@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Pending Reorder Requests</h1>

    @if($transfers->isEmpty())
        <p>No pending requests.</p>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th>Branch</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Requested At</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transfers as $t)
                <tr>
                    <td>{{ optional($t->toBranch)->display_label ?? 'Branch '.$t->to_branch_id }}</td>
                    <td>{{ $t->product->name ?? 'Product '.$t->product_id }}</td>
                    <td>{{ $t->quantity }}</td>
                    <td>{{ $t->created_at->format('Y-m-d H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-3">
            {{ $transfers->links() }}
        </div>
    @endif
</div>
@endsection
