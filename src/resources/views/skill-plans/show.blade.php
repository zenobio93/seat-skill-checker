@extends('web::layouts.grids.12')

@section('title', trans('skillchecker::skillchecker.view_skill_plan'))
@section('page_header', trans('skillchecker::skillchecker.view_skill_plan'))

@section('full')

<div class="card">
  <div class="card-header">
    <h3 class="card-title">{{ $skillplan->name }}</h3>
    <div class="card-tools">
      <button type="button" class="btn btn-info btn-sm me-2" onclick="copyToClipboard({{ $skillplan->id }})">
        <i class="fas fa-copy"></i> {{ trans('skillchecker::skillchecker.copy_to_clipboard') }}
      </button>
      @can('skillchecker.manage_skill_plans')
        <a href="{{ route('skillchecker.skill-plans.import-update', $skillplan) }}" class="btn btn-success btn-sm me-2">
          <i class="fas fa-upload"></i> {{ trans('skillchecker::skillchecker.update_by_import') }}
        </a>
        <a href="{{ route('skillchecker.skill-plans.edit', $skillplan) }}" class="btn btn-warning btn-sm">
          <i class="fas fa-edit"></i> {{ trans('skillchecker::skillchecker.edit') }}
        </a>
      @endcan
      <a href="{{ route('skillchecker.skill-plans.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> {{ trans('web::seat.back') }}
      </a>
    </div>
  </div>

  <div class="card-body">
    <div class="row mb-4">
      <div class="col-md-6">
        <h5>{{ trans('skillchecker::skillchecker.name') }}</h5>
        <p>{{ $skillplan->name }}</p>
      </div>
      <div class="col-md-6">
        <h5>{{ trans('skillchecker::skillchecker.created_by') }}</h5>
        <p>{{ $skillplan->creator->name ?? 'Unknown' }}</p>
      </div>
    </div>

    @if($skillplan->description)
      <div class="row mb-4">
        <div class="col-12">
          <h5>{{ trans('skillchecker::skillchecker.description') }}</h5>
          <p>{{ $skillplan->description }}</p>
        </div>
      </div>
    @endif

    <div class="row mb-4">
      <div class="col-md-6">
        <h5>{{ trans('skillchecker::skillchecker.created_at') }}</h5>
        <p>{{ $skillplan->created_at->format('Y-m-d H:i:s') }}</p>
      </div>
      <div class="col-md-6">
        <h5>{{ trans('skillchecker::skillchecker.requirements') }}</h5>
        <p><span class="badge badge-info">{{ $skillplan->requirements->count() }} {{ trans('skillchecker::skillchecker.skills') }}</span></p>
      </div>
    </div>

    <h5>{{ trans('skillchecker::skillchecker.skill_requirements') }}</h5>
    @if($skillplan->requirements->count() > 0)
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>{{ trans('skillchecker::skillchecker.skill_name') }}</th>
              <th>{{ trans('skillchecker::skillchecker.required_level') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach($skillplan->requirements->sortBy('priority') as $requirement)
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
        @can('skillchecker.manage_skill_plans')
          <a href="{{ route('skillchecker.skill-plans.edit', $skillplan) }}" class="btn btn-warning">
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
@can('skillchecker.manage_skill_plans')
<div class="modal fade" id="delete-modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">{{ trans('skillchecker::skillchecker.delete_skill_plan') }}</h4>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>{{ trans('skillchecker::skillchecker.are_you_sure_delete') }}</p>
        <p><strong>{{ $skillplan->name }}</strong></p>
      </div>
      <div class="modal-footer">
        <form action="{{ route('skillchecker.skill-plans.destroy', $skillplan) }}" method="POST">
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
                button.classList.remove('btn-info');
                button.classList.add('btn-success');
                
                // Show toast notification
                if (typeof toastr !== 'undefined') {
                    toastr.success(data.message);
                }
                
                // Reset button after 3 seconds
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.classList.remove('btn-success');
                    button.classList.add('btn-info');
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
    button.classList.remove('btn-info');
    button.classList.add('btn-danger');
    
    if (typeof toastr !== 'undefined') {
        toastr.error('{{ trans("skillchecker::skillchecker.copy_failed") }}');
    }
    
    setTimeout(() => {
        button.innerHTML = originalText;
        button.classList.remove('btn-danger');
        button.classList.add('btn-info');
        button.disabled = false;
    }, 3000);
}
</script>

@endsection