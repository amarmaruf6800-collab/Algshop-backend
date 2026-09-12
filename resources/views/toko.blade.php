<!DOCTYPE html>
@extends('template')

@section('content')
    <h1>Daftar Toko di Algshop</h1>
    <hr>
    
    <ul>
        @foreach ($shops as $toko)
            <li style="margin-bottom: 15px;">
                <a href="/toko/{{ $toko->id }}" style="font-size: 1.2em; font-weight: bold; text-decoration: none;">{{ $toko->name }}</a><br>
                <span style="color: gray;">Deskripsi: {{ $toko->description }}</span> <br>
                Pemilik: {{ $toko->user->name }}
            </li>
        @endforeach
    </ul>
@endsection