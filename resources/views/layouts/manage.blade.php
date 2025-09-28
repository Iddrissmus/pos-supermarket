@extends('layouts.app')

@section('title', 'Manage Business & Branches')

@section('content')
<div class="p-6 space-y-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Create Business</h2>
        <livewire:business.create-business />
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Create Branch</h2>
        <livewire:branch.create-branch />
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Assign Products to Branch</h2>
        <livewire:branch-products.assign />
    </div>
</div>
@endsection


