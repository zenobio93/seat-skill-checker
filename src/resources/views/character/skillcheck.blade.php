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
    @if($skillLists->count() > 0)
      <div class="row mb-4">
        <div class="col-12">
          <h5>{{ trans('skillchecker::skillchecker.skill_list_overview') }}</h5>
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>{{ trans('skillchecker::skillchecker.skill_list') }}</th>
                  <th>{{ trans('skillchecker::skillchecker.completion') }}</th>
                  <th>{{ trans('skillchecker::skillchecker.requirements') }}</th>
                  <th>{{ trans('skillchecker::skillchecker.status') }}</th>
                  <th>{{ trans('skillchecker::skillchecker.actions') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach($skillLists as $skillList)
                  @php
                    $results = $skillCheckResults[$skillList->id] ?? null;
                  @endphp
                  @if($results)
                    <tr class="{{ $results['all_met'] ? 'table-success' : 'table-warning' }}">
                      <td>
                        <strong>{{ $skillList->name }}</strong>
                        @if($skillList->description)
                          <br><small class="text-muted">{{ Str::limit($skillList->description, 50) }}</small>
                        @endif
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
                        <button type="button" class="btn btn-sm btn-info" 
                                data-toggle="collapse" 
                                data-target="#details-{{ $skillList->id }}">
                          <i class="fas fa-eye"></i> {{ trans('skillchecker::skillchecker.view') }}
                        </button>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="5" class="p-0">
                        <div class="collapse" id="details-{{ $skillList->id }}">
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
                                      <td>{{ $requirement['skill_name'] }}</td>
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
              <span class="info-box-text">{{ trans('skillchecker::skillchecker.total_skill_lists') }}</span>
              <span class="info-box-number">{{ $skillLists->count() }}</span>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="info-box">
            <span class="info-box-icon bg-success">
              <i class="fas fa-check"></i>
            </span>
            <div class="info-box-content">
              <span class="info-box-text">{{ trans('skillchecker::skillchecker.completed_lists') }}</span>
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
              <span class="info-box-text">{{ trans('skillchecker::skillchecker.incomplete_lists') }}</span>
              <span class="info-box-number">{{ $skillLists->count() - $completedCount }}</span>
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
        <h4 class="text-muted">{{ trans('skillchecker::skillchecker.no_skill_lists_found') }}</h4>
        <p class="text-muted">{{ trans('skillchecker::skillchecker.no_skill_lists_message') }}</p>
        @can('skillchecker.skillchecker.manage_skill_lists')
          <a href="{{ route('skillchecker.skill-lists.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> {{ trans('skillchecker::skillchecker.create_skill_list') }}
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

@endsection