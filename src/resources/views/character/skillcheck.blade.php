@extends('web::character.layouts.view', ['viewname' => 'skillcheck'])

@section('title', trans('skillchecker::skillchecker.character_skills'))
@section('page_header', trans('skillchecker::skillchecker.character_skills') . ': ' . $character->name)

@section('character_content')
<div class="card">
  <div class="card-header">
    <h3 class="card-title">
      <i class="fas fa-user"></i> {{ $character->name }} - {{ trans('skillchecker::skillchecker.character_skills') }}
    </h3>
  </div>

  <div class="card-body">
    @if($skillplans->count() > 0)
      <div class="row mb-4">
        <div class="col-12">
          <h5>{{ trans('skillchecker::skillchecker.skill_plan_overview') }}</h5>
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>{{ trans('skillchecker::skillchecker.skill_plan') }}</th>
                  <th>{{ trans('skillchecker::skillchecker.completion') }}</th>
                  <th>{{ trans('skillchecker::skillchecker.requirements') }}</th>
                  <th>{{ trans('skillchecker::skillchecker.status') }}</th>
                  <th>{{ trans('skillchecker::skillchecker.actions') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach($skillplans as $skillplan)
                  @php
                    $results = $skillCheckResults[$skillplan->id] ?? null;
                  @endphp
                  @if($results)
                    <tr class="{{ $results['all_met'] ? 'table-success' : 'table-warning' }}">
                      <td>
                        <div class="d-flex align-items-center">
                          <span class="badge badge-secondary mr-2">{{ $skillplan->priority }}</span>
                          @if($skillplan->is_required)
                            <span class="badge badge-warning mr-2">{{ trans('skillchecker::skillchecker.required') }}</span>
                          @else
                            <span class="badge badge-info mr-2">{{ trans('skillchecker::skillchecker.optional') }}</span>
                          @endif
                          <div>
                            <strong>{{ $skillplan->name }}</strong>
                            @if($skillplan->description)
                              <br><small class="text-muted">{{ Str::limit($skillplan->description, 50) }}</small>
                            @endif
                          </div>
                        </div>
                      </td>
                      <td>
                        <div class="progress">
                          <div class="progress-bar bg-{{ $results['all_met'] ? 'success' : 'warning' }}" 
                               role="progressbar" 
                               style="width: {{ $results['percentage'] }}%"
                               aria-valuenow="{{ $results['percentage'] }}" 
                               aria-valuemin="0" 
                               aria-valuemax="100">
                            {{ $results['percentage'] }}%
                          </div>
                        </div>
                      </td>
                      <td>
                        <span class="badge badge-info">
                          {{ $results['total_met'] }}/{{ $results['total_required'] }}
                        </span>
                      </td>
                      <td>
                        @if($results['all_met'])
                          <span class="badge badge-success">
                            <i class="fas fa-check"></i> {{ trans('skillchecker::skillchecker.all_requirements_met') }}
                          </span>
                        @else
                          <span class="badge badge-warning">
                            <i class="fas fa-exclamation-triangle"></i> {{ trans('skillchecker::skillchecker.missing_skills') }}
                          </span>
                        @endif
                      </td>
                      <td>
                        <button type="button" class="btn btn-sm btn-info me-1" 
                                data-toggle="collapse" 
                                data-target="#details-{{ $skillplan->id }}">
                          <i class="fas fa-eye"></i> {{ trans('skillchecker::skillchecker.view') }}
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary" 
                                onclick="copyToClipboard({{ $skillplan->id }})">
                          <i class="fas fa-copy"></i> {{ trans('skillchecker::skillchecker.copy_to_clipboard') }}
                        </button>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="5" class="p-0">
                        <div class="collapse" id="details-{{ $skillplan->id }}">
                          <div class="card-body">
                            <h6>{{ trans('skillchecker::skillchecker.skill_requirements') }}</h6>
                            <div class="table-responsive">
                              <table class="table table-sm">
                                <thead>
                                  <tr>
                                    <th>{{ trans('skillchecker::skillchecker.skill_name') }}</th>
                                    <th>{{ trans('skillchecker::skillchecker.required_level') }}</th>
                                    <th>{{ trans('skillchecker::skillchecker.character_level') }}</th>
                                    <th>{{ trans('skillchecker::skillchecker.status') }}</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  @foreach($results['requirements'] as $requirement)
                                    <tr class="{{ $requirement['met'] ? 'table-success' : 'table-danger' }}">
                                      <td>
                                        <div class="d-flex align-items-center">
                                          @if(isset($requirement['priority']))
                                            <span class="badge badge-secondary mr-2">{{ $requirement['priority'] + 1 }}</span>
                                          @endif
                                          @if(isset($requirement['is_required']))
                                            @if($requirement['is_required'])
                                              <span class="badge badge-warning mr-2">{{ trans('skillchecker::skillchecker.required') }}</span>
                                            @else
                                              <span class="badge badge-info mr-2">{{ trans('skillchecker::skillchecker.optional') }}</span>
                                            @endif
                                          @endif
                                          <strong>{{ $requirement['skill_name'] }}</strong>
                                        </div>
                                      </td>
                                      <td>
                                        <span class="badge badge-primary">
                                          {{ trans('skillchecker::skillchecker.level') }} {{ $requirement['required_level'] }}
                                        </span>
                                      </td>
                                      <td>
                                        <span class="badge badge-{{ $requirement['met'] ? 'success' : 'secondary' }}">
                                          {{ trans('skillchecker::skillchecker.level') }} {{ $requirement['character_level'] }}
                                        </span>
                                      </td>
                                      <td>
                                        @if($requirement['met'])
                                          <span class="badge badge-success">
                                            <i class="fas fa-check"></i> {{ trans('skillchecker::skillchecker.met') }}
                                          </span>
                                        @else
                                          <span class="badge badge-danger">
                                            <i class="fas fa-times"></i> {{ trans('skillchecker::skillchecker.not_met') }}
                                          </span>
                                        @endif
                                      </td>
                                    </tr>
                                  @endforeach
                                </tbody>
                              </table>
                            </div>
                          </div>
                        </div>
                      </td>
                    </tr>
                  @endif
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Summary Statistics -->
      <div class="row">
        <div class="col-md-3">
          <div class="info-box">
            <span class="info-box-icon bg-info">
              <i class="fas fa-list"></i>
            </span>
            <div class="info-box-content">
              <span class="info-box-text">{{ trans('skillchecker::skillchecker.total_skill_plans') }}</span>
              <span class="info-box-number">{{ $skillplans->count() }}</span>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="info-box">
            <span class="info-box-icon bg-success">
              <i class="fas fa-check"></i>
            </span>
            <div class="info-box-content">
              <span class="info-box-text">{{ trans('skillchecker::skillchecker.completed_plans') }}</span>
              <span class="info-box-number">
                @php
                  $completedCount = 0;
                  foreach($skillCheckResults as $result) {
                    if($result['all_met']) $completedCount++;
                  }
                @endphp
                {{ $completedCount }}
              </span>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="info-box">
            <span class="info-box-icon bg-warning">
              <i class="fas fa-exclamation-triangle"></i>
            </span>
            <div class="info-box-content">
              <span class="info-box-text">{{ trans('skillchecker::skillchecker.incomplete_plans') }}</span>
              <span class="info-box-number">{{ $skillplans->count() - $completedCount }}</span>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="info-box">
            <span class="info-box-icon bg-primary">
              <i class="fas fa-percentage"></i>
            </span>
            <div class="info-box-content">
              <span class="info-box-text">{{ trans('skillchecker::skillchecker.average_completion') }}</span>
              <span class="info-box-number">
                @php
                  $totalPercentage = 0;
                  $count = 0;
                  foreach($skillCheckResults as $result) {
                    $totalPercentage += $result['percentage'];
                    $count++;
                  }
                  $averagePercentage = $count > 0 ? round($totalPercentage / $count, 1) : 0;
                @endphp
                {{ $averagePercentage }}%
              </span>
            </div>
          </div>
        </div>
      </div>
    @else
      <div class="text-center py-4">
        <i class="fas fa-list-check fa-3x text-muted mb-3"></i>
        <h4 class="text-muted">{{ trans('skillchecker::skillchecker.no_skill_plans_found') }}</h4>
        <p class="text-muted">{{ trans('skillchecker::skillchecker.no_skill_plans_message') }}</p>
        @can('skillchecker.manage_skill_plans')
          <a href="{{ route('skillchecker.skill-plans.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> {{ trans('skillchecker::skillchecker.create_skill_plan') }}
          </a>
        @endcan
      </div>
    @endif
  </div>

  <div class="card-footer">
    <div class="row">
      <div class="col-md-6">
        <small class="text-muted">
          <i class="fas fa-info-circle"></i> 
          {{ trans('skillchecker::skillchecker.skill_check_info') }}
        </small>
      </div>
    </div>
  </div>
</div>

<script>
function copyToClipboard(skillPlanId) {
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    
    // Show loading state
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ trans("skillchecker::skillchecker.copying") }}...';
    button.disabled = true;
    
    // Make AJAX request to get clipboard content
    fetch(`{{ route('skillchecker.skill-plans.copy-eve', '') }}/${skillPlanId}?clipboard=1`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Copy to clipboard
            navigator.clipboard.writeText(data.content).then(() => {
                // Show success state
                button.innerHTML = '<i class="fas fa-check"></i> {{ trans("skillchecker::skillchecker.copied_to_clipboard") }}';
                button.classList.remove('btn-secondary');
                button.classList.add('btn-success');
                
                // Show toast notification
                if (typeof toastr !== 'undefined') {
                    toastr.success(data.message);
                }
                
                // Reset button after 3 seconds
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.classList.remove('btn-success');
                    button.classList.add('btn-secondary');
                    button.disabled = false;
                }, 3000);
            }).catch(err => {
                console.error('Failed to copy to clipboard:', err);
                showCopyError(button, originalText);
            });
        } else {
            showCopyError(button, originalText);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showCopyError(button, originalText);
    });
}

function showCopyError(button, originalText) {
    button.innerHTML = '<i class="fas fa-exclamation-triangle"></i> {{ trans("skillchecker::skillchecker.copy_failed") }}';
    button.classList.remove('btn-secondary');
    button.classList.add('btn-danger');
    
    if (typeof toastr !== 'undefined') {
        toastr.error('{{ trans("skillchecker::skillchecker.copy_failed") }}');
    }
    
    setTimeout(() => {
        button.innerHTML = originalText;
        button.classList.remove('btn-danger');
        button.classList.add('btn-secondary');
        button.disabled = false;
    }, 3000);
}
</script>

@endsection