@extends('web::layouts.grids.12')

@section('title', trans('skillchecker::skillchecker.edit_skill_list'))
@section('page_header', trans('skillchecker::skillchecker.edit_skill_list'))

@section('full')

<div class="card">
  <div class="card-header">
    <h3 class="card-title">{{ trans('skillchecker::skillchecker.edit_skill_list') }}: {{ $skillList->name }}</h3>
  </div>

  <form action="{{ route('skillchecker.skill-lists.update', $skillList) }}" method="POST" id="skill-list-form">
    @csrf
    @method('PUT')
    
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="name">{{ trans('skillchecker::skillchecker.name') }} <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                   id="name" name="name" value="{{ old('name', $skillList->name) }}" required>
            @error('name')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>

      <div class="form-group">
        <label for="description">{{ trans('skillchecker::skillchecker.description') }}</label>
        <textarea class="form-control @error('description') is-invalid @enderror" 
                  id="description" name="description" rows="3">{{ old('description', $skillList->description) }}</textarea>
        @error('description')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="form-group">
        <label>{{ trans('skillchecker::skillchecker.requirements') }} <span class="text-danger">*</span></label>
        <div id="skills-container">
          <!-- Existing skills will be loaded here -->
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
      <a href="{{ route('skillchecker.skill-lists.show', $skillList) }}" class="btn btn-secondary">
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

@push('javascript')
<script>
$(document).ready(function() {
    let skillIndex = 0;
    
    // Load existing skills
    @foreach($skillList->requirements as $requirement)
        addSkillRow({{ $requirement->skill_id }}, '{{ $requirement->skill->typeName ?? "Unknown Skill" }}', {{ $requirement->required_level }});
    @endforeach
    
    // Initialize modal states after loading existing skills
    updateModalSkillStates();
    
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
    
    function addSkillRow(skillId, skillName, level = 1) {
        const html = `
            <div class="skill-row mb-2" data-index="${skillIndex}">
                <div class="row">
                    <div class="col-md-6">
                        <input type="hidden" name="skills[${skillIndex}][skill_id]" value="${skillId}">
                        <input type="text" class="form-control" value="${skillName}" readonly>
                    </div>
                    <div class="col-md-3">
                        <select name="skills[${skillIndex}][required_level]" class="form-control" required>
                            <option value="1" ${level == 1 ? 'selected' : ''}>Level I</option>
                            <option value="2" ${level == 2 ? 'selected' : ''}>Level II</option>
                            <option value="3" ${level == 3 ? 'selected' : ''}>Level III</option>
                            <option value="4" ${level == 4 ? 'selected' : ''}>Level IV</option>
                            <option value="5" ${level == 5 ? 'selected' : ''}>Level V</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-danger btn-sm remove-skill">
                            <i class="fas fa-trash"></i> {{ trans('skillchecker::skillchecker.remove_skill') }}
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        $('#skills-container').append(html);
        skillIndex++;
    }
    
    // Remove skill
    $(document).on('click', '.remove-skill', function() {
        $(this).closest('.skill-row').remove();
        updateModalSkillStates();
    });
    
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