@extends('web::layouts.grids.12')

@section('title', trans('skillchecker::skillchecker.skill_plans'))
@section('page_header', trans('skillchecker::skillchecker.skill_plans'))

@section('full')

<div class="card">
  <div class="card-header">
    <h3 class="card-title">{{ trans('skillchecker::skillchecker.skill_plans') }}</h3>
    <div class="card-tools">
      @can('skillchecker.manage_skill_plans')
        <a href="{{ route('skillchecker.skill-plans.import') }}" class="btn btn-success btn-sm me-2">
          <i class="fas fa-upload"></i> {{ trans('skillchecker::skillchecker.import_skill_plan') }}
        </a>
        <a href="{{ route('skillchecker.skill-plans.create') }}" class="btn btn-primary btn-sm">
          <i class="fas fa-plus"></i> {{ trans('skillchecker::skillchecker.create_new_skill_plan') }}
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