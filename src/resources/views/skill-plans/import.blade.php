@extends('web::layouts.grids.12')

@section('title', trans('skillchecker::skillchecker.import_skill_plan'))
@section('page_header', trans('skillchecker::skillchecker.import_skill_plan'))

@section('full')

<div class="card">
  <div class="card-header">
    <h3 class="card-title">{{ trans('skillchecker::skillchecker.import_skill_plan') }}</h3>
  </div>

  <form action="{{ route('skillchecker.skill-plans.process-import') }}" method="POST" id="import-form">
    @csrf
    
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="name">{{ trans('skillchecker::skillchecker.name') }} <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                   id="name" name="name" value="{{ old('name') }}" required>
            @error('name')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label for="priority">{{ trans('skillchecker::skillchecker.priority') }}</label>
            <input type="number" class="form-control @error('priority') is-invalid @enderror" 
                   id="priority" name="priority" value="{{ old('priority', 1) }}" min="1">
            @error('priority')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label for="is_required">{{ trans('skillchecker::skillchecker.required') }}</label>
            <div class="form-check form-switch mt-2">
              <input type="hidden" name="is_required" value="0">
              <input class="form-check-input @error('is_required') is-invalid @enderror" 
                     type="checkbox" id="is_required" name="is_required" value="1" 
                     {{ old('is_required', false) ? 'checked' : '' }}>
              <label class="form-check-label" for="is_required">
                {{ trans('skillchecker::skillchecker.mark_as_required') }}
              </label>
              @error('is_required')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label for="description">{{ trans('skillchecker::skillchecker.description') }}</label>
        <textarea class="form-control @error('description') is-invalid @enderror" 
                  id="description" name="description" rows="3">{{ old('description') }}</textarea>
        @error('description')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>


      <div class="form-group">
        <label for="import_data">{{ trans('skillchecker::skillchecker.skill_data') }} <span class="text-danger">*</span></label>
        <textarea class="form-control @error('import_data') is-invalid @enderror" 
                  id="import_data" name="import_data" rows="15" required 
                  placeholder="{{ trans('skillchecker::skillchecker.import_data_placeholder') }}">{{ old('import_data') }}</textarea>
        @error('import_data')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <!-- Format Examples -->
      <div class="row">
        <div class="col-md-6">
          <div class="card bg-light">
            <div class="card-header">
              <h6 class="mb-0">{{ trans('skillchecker::skillchecker.line_by_line_example') }}</h6>
            </div>
            <div class="card-body">
              <pre class="mb-0">Science 1
Science 2
Science 3
Power Grid Management 1
Power Grid Management 2
Power Grid Management 3</pre>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card bg-light">
            <div class="card-header">
              <h6 class="mb-0">{{ trans('skillchecker::skillchecker.json_example') }}</h6>
            </div>
            <div class="card-body">
              <pre class="mb-0">{
  "Science": "3",
  "Power Grid Management": "4",
  "CPU Management": "4",
  "Spaceship Command": "3"
}</pre>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card-footer">
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-upload"></i> {{ trans('skillchecker::skillchecker.import') }}
      </button>
      <a href="{{ route('skillchecker.skill-plans.index') }}" class="btn btn-secondary">
        <i class="fas fa-times"></i> {{ trans('skillchecker::skillchecker.cancel') }}
      </a>
    </div>
  </form>
</div>

@endsection
