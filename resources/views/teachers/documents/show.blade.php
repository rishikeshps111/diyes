@extends('layouts.app')

@section('title', 'Teacher Document')

@section('content')
  <div class="page-title">
    <h3>Teacher Document</h3>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('teachers.index') }}">Teachers</a></li>
        <li class="breadcrumb-item"><a href="{{ route('teachers.show', $teacher) }}">{{ $teacher->name }}</a></li>
        <li class="breadcrumb-item active">Document</li>
      </ol>
    </nav>
  </div>

  <section class="section dashboard">
    <div class="main-table-container mb-3">
      <div class="row g-3">
        <x-teacher-detail label="Document Type" :value="$document->document_type" />
        <x-teacher-detail label="Verification Status" :value="$document->verification_status" />
        <x-teacher-detail label="Verified By" :value="$document->verifier?->name" />
        <x-teacher-detail label="Verified At" :value="$document->verified_at?->format('d M Y h:i A')" />
      </div>
    </div>

    <div class="main-table-container mb-3">
      @php
        $fileUrl = $document->fileUrl();
        $isPdf = str_ends_with(strtolower($fileUrl), '.pdf');
      @endphp

      @if ($isPdf)
        <iframe src="{{ $fileUrl }}" class="w-100 teacher-document-frame"></iframe>
      @else
        <img src="{{ $fileUrl }}" alt="{{ $document->document_type }}" class="img-fluid d-block mx-auto">
      @endif
    </div>

    <div class="d-flex justify-content-center">
      <a href="{{ route('teachers.index') }}" class="btn btn-danger">Back</a>
    </div>
  </section>
@endsection
