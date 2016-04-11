@extends('laradrop.modals.preview.preview_template_container')

@section('content')

<img  class="img-thumbnail" src="{{ $fileSrc . '?t=' . time() }}" />

@endsection
