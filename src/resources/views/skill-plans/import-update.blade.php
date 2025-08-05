@extends('web::layouts.grids.12')

@section('title', trans('skillchecker::skillchecker.update_skill_plan_by_import'))
@section('page_header', trans('skillchecker::skillchecker.update_skill_plan_by_import'))

@section('full')

<div class="card">
  <div class="card-header">
    <h3 class="card-title">{{ trans('skillchecker::skillchecker.update_skill_plan_by_import') }}: {{ $skillplan->name }}</h3>
  </div>

  <form action="{{ route('skillchecker.skill-plans.process-import-update', $skillplan) }}" method="POST" id="import-update-form">
    @csrf
    
    <div class="card-body">
      <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        {{ trans('skillchecker::skillchecker.update_import_info') }}
      </div>

      <div class="form-group">
        <label for="update_mode">{{ trans('skillchecker::skillchecker.update_mode') }} <span class="text-danger">*</span></label>
        <select class="form-control @error('update_mode') is-invalid @enderror" id="update_mode" name="update_mode" required>
          <option value="">{{ trans('skillchecker::skillchecker.select_update_mode') }}</option>
          <option value="replace" {{ old('update_mode') == 'replace' ? 'selected' : '' }}>
            {{ trans('skillchecker::skillchecker.replace_mode') }}
          </option>
          <option value="merge" {{ old('update_mode') == 'merge' ? 'selected' : '' }}>
            {{ trans('skillchecker::skillchecker.merge_mode') }}
          </option>
        </select>
        @error('update_mode')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="form-text text-muted" id="update-mode-help"></small>
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
        <div class="col-md-4">
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
        <div class="col-md-4">
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
        <div class="col-md-4">
          <div class="card bg-light">
            <div class="card-header">
              <h6 class="mb-0">{{ trans('skillchecker::skillchecker.localized_example') }}</h6>
            </div>
            <div class="card-body">
              <pre class="mb-0">&lt;localized hint="Gunnery"&gt;Gunnery&lt;/localized&gt; 1
&lt;localized hint="Gunnery"&gt;Gunnery&lt;/localized&gt; 2
&lt;localized hint="Motion Prediction"&gt;Motion Prediction&lt;/localized&gt; 1
&lt;localized hint="Rapid Firing"&gt;Rapid Firing&lt;/localized&gt; 1
&lt;localized hint="Gunnery"&gt;Gunnery*&lt;/localized&gt; 5</pre>
            </div>
          </div>
        </div>
      </div>

      <!-- Current Skills Preview -->
      <div class="card mt-4">
        <div class="card-header">
          <h6 class="mb-0">{{ trans('skillchecker::skillchecker.current_skills') }} ({{ $skillplan->requirements->count() }})</h6>
        </div>
        <div class="card-body">
          @if($skillplan->requirements->count() > 0)
            <div class="row">
              @foreach($skillplan->requirements->sortBy('priority') as $requirement)
                <div class="col-md-4 mb-2">
                  <span class="badge badge-secondary">{{ $requirement->skill->typeName ?? 'Unknown' }} {{ $requirement->required_level }}</span>
                </div>
              @endforeach
            </div>
          @else
            <p class="text-muted mb-0">{{ trans('skillchecker::skillchecker.no_skills_defined') }}</p>
          @endif
        </div>
      </div>
    </div>

    <div class="card-footer">
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-upload"></i> {{ trans('skillchecker::skillchecker.update_by_import') }}
      </button>
      <a href="{{ route('skillchecker.skill-plans.show', $skillplan) }}" class="btn btn-secondary">
        <i class="fas fa-times"></i> {{ trans('skillchecker::skillchecker.cancel') }}
      </a>
    </div>
  </form>
</div>

@endsection

@push('javascript')
<script>
$(document).ready(function() {
    // Show update mode help text
    $('#update_mode').change(function() {
        var mode = $(this).val();
        var helpText = '';
        
        if (mode === 'replace') {
            helpText = '{{ trans("skillchecker::skillchecker.replace_mode_help") }}';
        } else if (mode === 'merge') {
            helpText = '{{ trans("skillchecker::skillchecker.merge_mode_help") }}';
        }
        
        $('#update-mode-help').text(helpText);
    });
    
    // Trigger change event on page load
    $('#update_mode').trigger('change');
});
</script>
@endpush