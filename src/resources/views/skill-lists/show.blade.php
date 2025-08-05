@extends('web::layouts.grids.12')

@section('title', trans('skillchecker::skillchecker.view_skill_list'))
@section('page_header', trans('skillchecker::skillchecker.view_skill_list'))

@section('full')

<div class="card">
  <div class="card-header">
    <h3 class="card-title">{{ $skillList->name }}</h3>
    <div class="card-tools">
      @can('skillchecker.skillchecker.manage_skill_lists')
        <a href="{{ route('skillchecker.skill-lists.edit', $skillList) }}" class="btn btn-warning btn-sm">
          <i class="fas fa-edit"></i> {{ trans('skillchecker::skillchecker.edit') }}
        </a>
      @endcan
      <a href="{{ route('skillchecker.skill-lists.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> {{ trans('web::seat.back') }}
      </a>
    </div>
  </div>

  <div class="card-body">
    <div class="row mb-4">
      <div class="col-md-6">
        <h5>{{ trans('skillchecker::skillchecker.name') }}</h5>
        <p>{{ $skillList->name }}</p>
      </div>
      <div class="col-md-6">
        <h5>{{ trans('skillchecker::skillchecker.created_by') }}</h5>
        <p>{{ $skillList->creator->name ?? 'Unknown' }}</p>
      </div>
    </div>

    @if($skillList->description)
      <div class="row mb-4">
        <div class="col-12">
          <h5>{{ trans('skillchecker::skillchecker.description') }}</h5>
          <p>{{ $skillList->description }}</p>
        </div>
      </div>
    @endif

    <div class="row mb-4">
      <div class="col-md-6">
        <h5>{{ trans('skillchecker::skillchecker.created_at') }}</h5>
        <p>{{ $skillList->created_at->format('Y-m-d H:i:s') }}</p>
      </div>
      <div class="col-md-6">
        <h5>{{ trans('skillchecker::skillchecker.requirements') }}</h5>
        <p><span class="badge badge-info">{{ $skillList->requirements->count() }} {{ trans('skillchecker::skillchecker.skills') }}</span></p>
      </div>
    </div>

    <h5>{{ trans('skillchecker::skillchecker.skill_requirements') }}</h5>
    @if($skillList->requirements->count() > 0)
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>{{ trans('skillchecker::skillchecker.skill_name') }}</th>
              <th>{{ trans('skillchecker::skillchecker.required_level') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach($skillList->requirements->sortBy('priority') as $requirement)
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    <span class="badge badge-secondary mr-2">{{ $loop->iteration }}</span>
                    <strong>{{ $requirement->skill->typeName ?? 'Unknown Skill' }}</strong>
                    @if(!($requirement->is_required ?? true))
                      <span class="badge badge-info ml-2">{{ trans('skillchecker::skillchecker.optional') }}</span>
                    @else
                      <span class="badge badge-warning ml-2">{{ trans('skillchecker::skillchecker.required') }}</span>
                    @endif
                  </div>
                </td>
                <td>
                  <span class="badge badge-primary">
                    {{ trans('skillchecker::skillchecker.level') }} {{ $requirement->required_level }}
                  </span>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @else
      <div class="text-center py-4">
        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
        <h4 class="text-muted">{{ trans('skillchecker::skillchecker.no_requirements_found') }}</h4>
        <p class="text-muted">{{ trans('skillchecker::skillchecker.no_requirements_message') }}</p>
      </div>
    @endif
  </div>

  <div class="card-footer">
    <div class="row">
      <div class="col-md-6">
        @can('skillchecker.skillchecker.manage_skill_lists')
          <a href="{{ route('skillchecker.skill-lists.edit', $skillList) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> {{ trans('skillchecker::skillchecker.edit') }}
          </a>
          <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#delete-modal">
            <i class="fas fa-trash"></i> {{ trans('skillchecker::skillchecker.delete') }}
          </button>
        @endcan
      </div>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
@can('skillchecker.skillchecker.manage_skill_lists')
<div class="modal fade" id="delete-modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">{{ trans('skillchecker::skillchecker.delete_skill_list') }}</h4>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>{{ trans('skillchecker::skillchecker.are_you_sure_delete') }}</p>
        <p><strong>{{ $skillList->name }}</strong></p>
      </div>
      <div class="modal-footer">
        <form action="{{ route('skillchecker.skill-lists.destroy', $skillList) }}" method="POST">
          @csrf
          @method('DELETE')
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            {{ trans('skillchecker::skillchecker.cancel') }}
          </button>
          <button type="submit" class="btn btn-danger">
            <i class="fas fa-trash"></i> {{ trans('skillchecker::skillchecker.delete') }}
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endcan

@endsection