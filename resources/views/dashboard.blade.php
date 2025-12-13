@extends('layouts.app')

@section('content')
  <div class="grid grid-cols-12 gap-4 md:gap-6">
    <div class="col-span-12 space-y-6 xl:col-span-12">
    <h1 x-data="{ message: 'I ❤️ Alpine' }" x-text="message"></h1>
      <x-ecommerce.ecommerce-metrics />
    </div>
  </div>
@endsection
