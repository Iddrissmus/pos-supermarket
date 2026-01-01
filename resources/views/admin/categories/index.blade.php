@extends('layouts.app')

@section('title', 'Category Management')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

    <!-- Page Header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl font-bold text-slate-800">Categories</h1>
            <p class="mt-1 text-sm text-slate-500">Organize your products with categories and subcategories.</p>
        </div>
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <a href="{{ route('categories.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Create Category
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-sm text-sm border bg-emerald-100 border-emerald-200 text-emerald-600">
            <div class="flex">
                <div class="alert-content ml-2">
                    {{ session('success') }}
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 px-4 py-3 rounded-sm text-sm border bg-rose-100 border-rose-200 text-rose-600">
            <div class="flex">
                <div class="alert-content ml-2">
                    {{ session('error') }}
                </div>
            </div>
        </div>
    @endif

    <!-- Categories Table -->
    <div class="bg-white border border-slate-200 rounded-sm shadow-sm relative">
        <header class="px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-slate-800">All Categories</h2>
        </header>
        <div class="overflow-x-auto">
            <table class="table-auto w-full">
                <!-- Table Header -->
                <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                    <tr>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <span class="sr-only">Expand</span>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-left">
                            <div class="font-semibold text-left">Category Name</div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-left">
                            <div class="font-semibold text-left">Description</div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-center">
                            <div class="font-semibold">Items</div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-center">
                            <div class="font-semibold">Subcategories</div>
                        </th>
                         <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-center">
                            <div class="font-semibold">Status</div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-center">
                            <div class="font-semibold">Actions</div>
                        </th>
                    </tr>
                </thead>
                <!-- Table Body -->
                <tbody class="text-sm divide-y divide-slate-200">
                    @forelse($categories as $category)
                        <!-- Parent Category Row -->
                        <tr class="hover:bg-slate-50 transition-colors cursor-pointer" onclick="toggleSubcategories({{ $category->id }})">
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($category->subcategories->count() > 0)
                                    <button class="text-slate-400 hover:text-slate-500 transform transition-transform duration-200" id="icon-category-{{ $category->id }}">
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 16 16">
                                            <path d="M5.4 12c-.3 0-.6-.1-.8-.4-.4-.4-.4-1 0-1.4l4-4-4-4c-.4-.4-.4-1 0-1.4.4-.4 1-.4 1.4 0l4.7 4.7c.4.4.4 1 0 1.4l-4.7 4.7c-.2.2-.5.4-.6.4z"/>
                                        </svg>
                                    </button>
                                    @else
                                    <div class="w-4 h-4"></div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-medium text-slate-800">{{ $category->name }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="text-slate-500">{{ Str::limit($category->description, 50) ?: '-' }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-center">
                                <div class="text-slate-600">{{ $category->products_count ?? 0 }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-center">
                                <div class="inline-flex font-medium rounded-full text-center px-2.5 py-0.5 bg-slate-100 text-slate-500">
                                    {{ $category->subcategories->count() }}
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-center">
                                <div class="inline-flex font-medium rounded-full text-center px-2.5 py-0.5 {{ $category->is_active ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }}">
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </div>
                            </td>
                             <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-2" onclick="event.stopPropagation()">
                                    <a href="{{ route('categories.edit', $category->id) }}" class="text-slate-400 hover:text-slate-500 rounded-full">
                                        <span class="sr-only">Edit</span>
                                        <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                                            <path d="M19.7 8.3c-.4-.4-1-.4-1.4 0l-10 10c-.2.2-.3.4-.3.7v4c0 .6.4 1 1 1h4c.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4l-4-4zM12.6 22H10v-2.6l6-6 2.6 2.6-6 6zm7.4-7.4L17.4 12l1.6-1.6 2.6 2.6-1.6 1.6z" />
                                        </svg>
                                    </a>
                                    <form action="{{ route('categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-rose-500 hover:text-rose-600 rounded-full">
                                            <span class="sr-only">Delete</span>
                                            <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                                                <path d="M13 15h2v6h-2zM17 15h2v6h-2z" />
                                                <path d="M20 9c0-.6-.4-1-1-1h-6c-.6 0-1 .4-1 1v2H8v2h1v10c0 .6.4 1 1 1h12c.6 0 1-.4 1-1V13h1v-2h-4V9zm-6 1h4v1h-4v-1zm7 3v9H11v-9h10z" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- Subcategories Row (Hidden by default) -->
                        @if($category->subcategories->count() > 0)
                        <tr id="subcategories-{{ $category->id }}" class="hidden bg-slate-50">
                            <td colspan="7" class="p-0">
                                <div class="px-4 py-3">
                                    <table class="w-full">
                                        <tbody class="text-sm divide-y divide-slate-100">
                                            @foreach($category->subcategories as $subcategory)
                                            <tr class="hover:bg-slate-100 transition-colors">
                                                <td class="pl-12 py-3 whitespace-nowrap w-px">
                                                     <svg class="w-4 h-4 fill-current text-slate-400 flip-x" viewBox="0 0 16 16">
                                                        <path d="M1 13h11a1 1 0 0 0 1-1V1H1v12zM12 2v10H2V2h10z" style="fill-opacity: .4"/>
                                                        <path d="M11 13V2H2v10h8v1h-1v-2h2v2h1v-1a2 2 0 0 0-2-2z"/>
                                                     </svg>
                                                </td>
                                                <td class="px-2 py-3 whitespace-nowrap text-left">
                                                    <div class="font-medium text-slate-700">{{ $subcategory->name }}</div>
                                                </td>
                                                <td class="px-2 py-3 whitespace-nowrap text-left">
                                                    <div class="text-slate-500 text-xs">{{ Str::limit($subcategory->description, 50) ?: '-' }}</div>
                                                </td>
                                                <td class="px-2 py-3 whitespace-nowrap text-center">
                                                     <div class="text-slate-600 text-xs">{{ $subcategory->products_count ?? 0 }} items</div>
                                                </td>
                                                <td class="px-2 py-3 whitespace-nowrap text-center">
                                                    <span class="text-slate-400">-</span>
                                                </td>
                                                <td class="px-2 py-3 whitespace-nowrap text-center">
                                                    <div class="inline-flex font-medium rounded-full text-center px-2 py-0.5 text-xs {{ $subcategory->is_active ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }}">
                                                        {{ $subcategory->is_active ? 'Active' : 'Inactive' }}
                                                    </div>
                                                </td>
                                                <td class="px-2 py-3 whitespace-nowrap text-center">
                                                    <div class="flex items-center justify-center space-x-2">
                                                        <a href="{{ route('categories.edit', $subcategory->id) }}" class="text-indigo-500 hover:text-indigo-600 font-medium text-xs">Edit</a>
                                                        <span class="text-slate-300">|</span>
                                                        <form action="{{ route('categories.destroy', $subcategory->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this subcategory?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="text-rose-500 hover:text-rose-600 font-medium text-xs">Delete</button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="7" class="px-2 first:pl-5 last:pr-5 py-12 text-center">
                                <div class="justify-center flex mb-4">
                                     <svg class="w-16 h-16 fill-current text-slate-200" viewBox="0 0 64 64">
                                        <g stroke-width="2" fill="none" class="nc-icon-wrapper">
                                            <circle cx="32" cy="32" r="30" stroke="currentColor"></circle>
                                            <path d="M32 12v40" stroke="currentColor"></path>
                                            <path d="M12 32h40" stroke="currentColor"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="text-slate-500 font-medium mb-2">No categories found</div>
                                <div class="text-sm text-slate-400 mb-6">Create categories to organize your products efficiently.</div>
                                <a href="{{ route('categories.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Add First Category
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function toggleSubcategories(id) {
        const row = document.getElementById(`subcategories-${id}`);
        const icon = document.getElementById(`icon-category-${id}`);
        
        if (row) {
            if (row.classList.contains('hidden')) {
                row.classList.remove('hidden');
                icon.style.transform = 'rotate(90deg)';
            } else {
                row.classList.add('hidden');
                icon.style.transform = 'rotate(0deg)';
            }
        }
    }
</script>
@endsection
