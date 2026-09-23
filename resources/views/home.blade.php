@extends('layouts.public')

@section('title', 'Beranda — '.config('app.name', 'E-Wedu'))
@section('meta_description', 'E-Wedu: katalog produk UMKM Magelang, literasi kewirausahaan, dan pengiriman logistik mandiri dari Kampus Tuguran. Gratis ongkir di 4 titik pengambilan.')

@section('content')
    @include('home.hero')
    @include('home.kategori')
    @include('home.produk-unggulan')
    @include('home.mitra')
    @include('home.literasi')
    @include('home.cta')
@endsection
