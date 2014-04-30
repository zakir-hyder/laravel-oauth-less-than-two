@extends('layouts.main')
@section('content')

  @if ($err_msg != '')
    {{ $err_msg }}
  @endif

  @if ($response != '')
    {{ print_r($response) }}
  @endif

@stop