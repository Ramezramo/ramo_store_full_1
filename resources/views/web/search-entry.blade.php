@extends('layouts.app')

@php($searchRtl = session('locale', 'en') === 'ar')

@section('title', ($searchRtl ? 'البحث' : 'Search') . ' — Ramo Store')

@section('content')
<div class="page search-page search-entry-page" dir="{{ $searchRtl ? 'rtl' : 'ltr' }}">
  @include('web.partials.search-form', ['q' => '', 'filters' => []])
</div>
@endsection
