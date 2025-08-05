@extends('web::layouts.grids.12')

@section('title', trans('skillchecker::skillchecker.skill_lists'))
@section('page_header', trans('skillchecker::skillchecker.skill_lists'))

@section('full')

<div class="card">
  <div class="card-header">
    <h3 class="card-title">{{ trans('skillchecker::skillchecker.skill_lists') }}</h3>
    <div class="card-tools">
      @can('skillchecker.manage_skill_lists')
        <a href="{{ route('skillchecker.skill-lists.create') }}" class="btn btn-primary btn-sm">
          <i class="fas fa-plus"></i> {{ trans('skillchecker::skillchecker.create_new_skill_list') }}
        </a>
      @endcan
    </div>
  </div>

  <div class="card-body">
    {!! $dataTable->table(['class' => 'table table-striped table-hover']) !!}
  </div>
</div>

@endsection

@push('javascript')
  {!! $dataTable->scripts() !!}
@endpush