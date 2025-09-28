@extends('layouts.app')

@section('title', 'Assign Products to Branch')

@section('content')
<div class="p-6 space-y-8">
	<div class="bg-white rounded-lg shadow p-6">
		<h2 class="text-xl font-semibold mb-4">Assign Products to Branch</h2>
		<livewire:branch-products.assign />
	</div>
</div>
@endsection


