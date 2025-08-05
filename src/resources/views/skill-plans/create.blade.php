@extends('web::layouts.grids.12')

@section('title', trans('skillchecker::skillchecker.create_skill_plan'))
@section('page_header', trans('skillchecker::skillchecker.create_skill_plan'))

@section('full')

<div class="card">
  <div class="card-header">
    <h3 class="card-title">{{ trans('skillchecker::skillchecker.create_new_skill_plan') }}</h3>
  </div>

  <form action="{{ route('skillchecker.skill-plans.store') }}" method="POST" id="skill-list-form">
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
                     {{ old('is_required') ? 'checked' : '' }}>
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
        <label>{{ trans('skillchecker::skillchecker.requirements') }} <span class="text-danger">*</span></label>
        <div id="skills-container">
          <!-- Skills will be added here dynamically -->
        </div>
        <button type="button" class="btn btn-success btn-sm" id="add-skill-btn">
          <i class="fas fa-plus"></i> {{ trans('skillchecker::skillchecker.add_skill') }}
        </button>
        @error('skills')
          <div class="text-danger mt-2">{{ $message }}</div>
        @enderror
      </div>
    </div>

    <div class="card-footer">
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-save"></i> {{ trans('skillchecker::skillchecker.save') }}
      </button>
      <a href="{{ route('skillchecker.skill-plans.index') }}" class="btn btn-secondary">
        <i class="fas fa-times"></i> {{ trans('skillchecker::skillchecker.cancel') }}
      </a>
    </div>
  </form>
</div>

<!-- Skill Selection Modal -->
<div class="modal fade" id="skill-modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">{{ trans('skillchecker::skillchecker.select_skill') }}</h4>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <input type="text" class="form-control" id="skill-search" placeholder="Search skills...">
        </div>
        <div class="row">
          @foreach($skillGroups as $group)
            <div class="col-md-6 mb-3">
              <h6>{{ $group->groupName }}</h6>
              <div class="skill-group" data-group="{{ $group->groupID }}">
                @foreach($group->types as $skill)
                  <div class="skill-item" data-skill-id="{{ $skill->typeID }}" data-skill-name="{{ $skill->typeName }}">
                    <a href="#" class="skill-link">{{ $skill->typeName }}</a>
                  </div>
                @endforeach
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@push('head')
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/ui-lightness/jquery-ui.css">
<style>
.skill-row .card {
    transition: all 0.3s ease;
}
.skill-row .card.border-warning {
    border-left: 4px solid #ffc107 !important;
}
.skill-row .card.border-info {
    border-left: 4px solid #17a2b8 !important;
}
.drag-handle:hover {
    color: #007bff !important;
}
.ui-sortable-helper {
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    transform: rotate(2deg);
}
.ui-sortable-placeholder {
    border: 2px dashed #007bff;
    background: rgba(0,123,255,0.1);
    height: 80px;
    margin: 10px 0;
}
</style>
@endpush

@push('javascript')
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script>
$(document).ready(function() {
    let skillIndex = 0;
    
    // Add skill button
    $('#add-skill-btn').click(function() {
        updateModalSkillStates();
        $('#skill-modal').modal('show');
    });
    
    // Skill selection
    $(document).on('click', '.skill-link:not(.disabled)', function(e) {
        e.preventDefault();
        const skillId = $(this).parent().data('skill-id');
        const skillName = $(this).parent().data('skill-name');
        
        addSkillRow(skillId, skillName);
        $('#skill-modal').modal('hide');
        updateModalSkillStates();
    });
    
    // Search functionality
    $('#skill-search').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('.skill-item').each(function() {
            const skillName = $(this).data('skill-name').toLowerCase();
            if (skillName.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    function addSkillRow(skillId, skillName, level = 1, priority = null, isRequired = true) {
        if (priority === null) {
            priority = $('.skill-row').length + 1;
        }
        
        const html = `
            <div class="skill-row mb-2" data-index="${skillIndex}" data-skill-id="${skillId}">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-1">
                                <div class="drag-handle" style="cursor: move;">
                                    <i class="fas fa-grip-vertical text-muted"></i>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <input type="hidden" name="skills[${skillIndex}][skill_id]" value="${skillId}">
                                <input type="hidden" name="skills[${skillIndex}][priority]" value="${priority}" class="priority-input">
                                <input type="text" class="form-control" value="${skillName}" readonly>
                            </div>
                            <div class="col-md-2">
                                <select name="skills[${skillIndex}][required_level]" class="form-control" required>
                                    <option value="1" ${level == 1 ? 'selected' : ''}>Level I</option>
                                    <option value="2" ${level == 2 ? 'selected' : ''}>Level II</option>
                                    <option value="3" ${level == 3 ? 'selected' : ''}>Level III</option>
                                    <option value="4" ${level == 4 ? 'selected' : ''}>Level IV</option>
                                    <option value="5" ${level == 5 ? 'selected' : ''}>Level V</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="skills[${skillIndex}][is_required]" value="0">
                                    <input class="form-check-input" type="checkbox" name="skills[${skillIndex}][is_required]" value="1" ${isRequired ? 'checked' : ''}>
                                    <label class="form-check-label">
                                        <span class="required-label">${isRequired ? 'Required' : 'Optional'}</span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <span class="badge badge-secondary priority-badge">Priority: ${priority}</span>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger btn-sm remove-skill">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('#skills-container').append(html);
        skillIndex++;
        updatePriorities();
    }
    
    // Initialize sortable functionality
    $('#skills-container').sortable({
        handle: '.drag-handle',
        items: '.skill-row',
        update: function(event, ui) {
            updatePriorities();
        }
    });
    
    // Remove skill
    $(document).on('click', '.remove-skill', function() {
        $(this).closest('.skill-row').remove();
        updatePriorities();
        updateModalSkillStates();
    });
    
    // Handle required/optional toggle
    $(document).on('change', 'input[type="checkbox"][name*="[is_required]"]', function() {
        const label = $(this).siblings('label').find('.required-label');
        if ($(this).is(':checked')) {
            label.text('Required');
            $(this).closest('.skill-row').find('.card').removeClass('border-info').addClass('border-warning');
        } else {
            label.text('Optional');
            $(this).closest('.skill-row').find('.card').removeClass('border-warning').addClass('border-info');
        }
    });
    
    // Update priorities based on current order
    function updatePriorities() {
        $('.skill-row').each(function(index) {
            $(this).find('.priority-input').val(index + 1);
            $(this).find('.priority-badge').text('Priority: ' + (index + 1));
        });
    }
    
    // Update modal skill states (disable already added skills)
    function updateModalSkillStates() {
        const addedSkillIds = [];
        $('input[name*="[skill_id]"]').each(function() {
            addedSkillIds.push($(this).val());
        });
        
        $('.skill-item').each(function() {
            const skillId = $(this).data('skill-id').toString();
            const skillLink = $(this).find('.skill-link');
            
            // Remove existing hint first
            $(this).find('small').remove();
            
            if (addedSkillIds.includes(skillId)) {
                skillLink.addClass('disabled text-muted');
                skillLink.css('pointer-events', 'none');
                $(this).append('<small class="text-muted ml-2">(Already added)</small>');
            } else {
                skillLink.removeClass('disabled text-muted');
                skillLink.css('pointer-events', 'auto');
            }
        });
    }
    
    // Form validation
    $('#skill-list-form').submit(function(e) {
        if ($('.skill-row').length === 0) {
            e.preventDefault();
            alert('Please add at least one skill requirement.');
            return false;
        }
    });
});
</script>
@endpush