@extends('web::layouts.grids.12')

@section('title', trans('skillchecker::skillchecker.skill_checker'))
@section('page_header', trans('skillchecker::skillchecker.skill_checker'))

@section('full')

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-search"></i> {{ trans('skillchecker::skillchecker.skill_checker') }}
        </h3>
      </div>
      <div class="card-body">
        <form id="unified-check-form">
          <div class="row">
            <!-- Filter Selection -->
            <div class="col-md-3">
              <div class="form-group">
                <label for="user-select">{{ trans('skillchecker::skillchecker.select_user') }}</label>
                <select class="form-control filter-select" id="user-select" name="user_id" data-type="user">
                  <option value="">{{ trans('skillchecker::skillchecker.select_user') }}</option>
                  @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->main_character ? $user->main_character->name : 'No Main Character' }})</option>
                  @endforeach
                </select>
              </div>
            </div>
            
            <div class="col-md-3">
              <div class="form-group">
                <label for="squad-select">{{ trans('skillchecker::skillchecker.select_squad') }}</label>
                <select class="form-control filter-select" id="squad-select" name="squad_id" data-type="squad">
                  <option value="">{{ trans('skillchecker::skillchecker.select_squad') }}</option>
                  @foreach($squads as $squad)
                    <option value="{{ $squad->id }}">{{ $squad->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            
            <div class="col-md-3">
              <div class="form-group">
                <label for="corporation-select">{{ trans('skillchecker::skillchecker.select_corporation') }}</label>
                <select class="form-control filter-select" id="corporation-select" name="corporation_id" data-type="corporation">
                  <option value="">{{ trans('skillchecker::skillchecker.select_corporation') }}</option>
                  @foreach($corporations as $corporation)
                    <option value="{{ $corporation->corporation_id }}">{{ $corporation->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            
            <!-- Skill List Selection -->
            <div class="col-md-3">
              <div class="form-group">
                <label for="skill-list-select">{{ trans('skillchecker::skillchecker.select_skill_list') }}</label>
                <select class="form-control" id="skill-list-select" name="skill_list_id" required>
                  <option value="">{{ trans('skillchecker::skillchecker.select_skill_list') }}</option>
                  @foreach($skillLists as $skillList)
                    <option value="{{ $skillList->id }}">{{ $skillList->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-12 text-center">
              <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-search"></i> {{ trans('skillchecker::skillchecker.check') }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Results Section -->
<div class="row mt-4" id="results-section" style="display: none;">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-chart-bar"></i> {{ trans('skillchecker::skillchecker.results') }}
        </h3>
        <div class="card-tools">
          <button type="button" class="btn btn-tool" id="close-results">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>
      <div class="card-body" id="results-content">
        <!-- Results will be loaded here -->
      </div>
    </div>
  </div>
</div>

@endsection

@push('javascript')
<script>
$(document).ready(function() {
    // Filter reset functionality - when one filter is selected, clear others
    $('.filter-select').change(function() {
        if ($(this).val()) {
            $('.filter-select').not(this).val('');
        }
    });

    // Unified form submission
    $('#unified-check-form').submit(function(e) {
        e.preventDefault();
        
        const userId = $('#user-select').val();
        const squadId = $('#squad-select').val();
        const corporationId = $('#corporation-select').val();
        const skillListId = $('#skill-list-select').val();
        
        // Validate that a skill list is selected
        if (!skillListId) {
            alert('Please select a skill list.');
            return;
        }
        
        // Determine which filter is selected and call appropriate function
        if (userId) {
            checkUser(userId, skillListId);
        } else if (squadId) {
            checkSquad(squadId, skillListId);
        } else if (corporationId) {
            checkCorporation(corporationId, skillListId);
        } else {
            alert('Please select a user, squad, or corporation to check.');
            return;
        }
    });
    
    // Close results
    $('#close-results').click(function() {
        $('#results-section').hide();
    });
    
    function checkUser(userId, skillListId) {
        showLoading();
        
        $.ajax({
            url: '{{ route("skillchecker.checker.check-character") }}',
            method: 'POST',
            data: {
                user_id: userId,
                skill_list_id: skillListId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                displayResults(response);
            },
            error: function(xhr) {
                hideLoading();
                alert('Error checking user skills. Please try again.');
            }
        });
    }
    
    function checkSquad(squadId, skillListId) {
        showLoading();
        
        $.ajax({
            url: '{{ route("skillchecker.checker.check-squad") }}',
            method: 'POST',
            data: {
                squad_id: squadId,
                skill_list_id: skillListId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                displayResults(response);
            },
            error: function(xhr) {
                hideLoading();
                alert('Error checking squad skills. Please try again.');
            }
        });
    }
    
    function checkCorporation(corporationId, skillListId) {
        showLoading();
        
        $.ajax({
            url: '{{ route("skillchecker.checker.check-corporation") }}',
            method: 'POST',
            data: {
                corporation_id: corporationId,
                skill_list_id: skillListId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                displayResults(response);
            },
            error: function(xhr) {
                hideLoading();
                alert('Error checking corporation skills. Please try again.');
            }
        });
    }
    
    function displayCharacterResults(data) {
        const results = data.results;
        let html = `
            <h4>${data.character.name} - ${data.skill_list.name}</h4>
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-${results.all_met ? 'success' : 'warning'}">
                            <i class="fas fa-${results.all_met ? 'check' : 'exclamation-triangle'}"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">{{ trans('skillchecker::skillchecker.completion') }}</span>
                            <span class="info-box-number">${results.percentage}%</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-list"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">{{ trans('skillchecker::skillchecker.requirements') }}</span>
                            <span class="info-box-number">${results.total_met}/${results.total_required}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>{{ trans('skillchecker::skillchecker.skill_name') }}</th>
                            <th>{{ trans('skillchecker::skillchecker.required_level') }}</th>
                            <th>{{ trans('skillchecker::skillchecker.character_level') }}</th>
                            <th>{{ trans('skillchecker::skillchecker.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        results.requirements.forEach(function(req) {
            html += `
                <tr class="${req.met ? 'table-success' : 'table-warning'}">
                    <td>${req.skill_name}</td>
                    <td>${req.required_level}</td>
                    <td>${req.character_level}</td>
                    <td>
                        <span class="badge badge-${req.met ? 'success' : 'warning'}">
                            ${req.met ? '{{ trans("skillchecker::skillchecker.met") }}' : '{{ trans("skillchecker::skillchecker.not_met") }}'}
                        </span>
                    </td>
                </tr>
            `;
        });
        
        html += '</tbody></table></div>';
        
        $('#results-content').html(html);
        $('#results-section').show();
        hideLoading();
    }
    
    function displayResults(data) {
        let html = `
            <h4>${data.results_meta.name} - ${data.skill_list.name}</h4>
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-users"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Characters</span>
                            <span class="info-box-number">${data.summary.total_characters}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-success">
                            <i class="fas fa-check"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Meeting All</span>
                            <span class="info-box-number">${data.summary.characters_meeting_all}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning">
                            <i class="fas fa-percentage"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">% Meeting All</span>
                            <span class="info-box-number">${data.summary.percentage_meeting_all}%</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-primary">
                            <i class="fas fa-chart-line"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Avg Completion</span>
                            <span class="info-box-number">${data.summary.average_completion}%</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        html += '<div class="accordion" id="main-character-accordion">';
        
        // Group results by main character
        if (data.grouped_characters && data.grouped_characters.length > 0) {
            data.grouped_characters.forEach(function(group, groupIndex) {
                const mainChar = group.main_character;
                html += `
                    <div class="card">
                        <div class="card-header" id="main-heading${groupIndex}">
                            <h2 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#main-collapse${groupIndex}">
                                    <i class="fas fa-user-tie"></i> ${mainChar.name} (Main Character)
                                    <span class="badge badge-info ml-2">${group.characters.length} characters</span>
                                </button>
                            </h2>
                        </div>
                        <div id="main-collapse${groupIndex}" class="collapse" data-parent="#main-character-accordion">
                            <div class="card-body">
                                <div class="accordion" id="character-accordion-${groupIndex}">
                `;
                
                group.characters.forEach(function(char, charIndex) {
                    const results = char.results;
                    const uniqueId = `${groupIndex}-${charIndex}`;
                    html += `
                        <div class="card">
                            <div class="card-header" id="char-heading${uniqueId}">
                                <h3 class="mb-0">
                                    <button class="btn btn-link btn-sm" type="button" data-toggle="collapse" data-target="#char-collapse${uniqueId}">
                                        ${char.character.name} 
                                        <span class="badge badge-${results.all_met ? 'success' : 'warning'} ml-2">
                                            ${results.percentage}%
                                        </span>
                                    </button>
                                </h3>
                            </div>
                            <div id="char-collapse${uniqueId}" class="collapse" data-parent="#character-accordion-${groupIndex}">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Skill</th>
                                                    <th>Required</th>
                                                    <th>Current</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                    `;
                    
                    results.requirements.forEach(function(req) {
                        html += `
                            <tr class="${req.met ? 'table-success' : 'table-warning'}">
                                <td>${req.skill_name}</td>
                                <td>${req.required_level}</td>
                                <td>${req.character_level}</td>
                                <td>
                                    <span class="badge badge-${req.met ? 'success' : 'warning'}">
                                        ${req.met ? '{{ trans("skillchecker::skillchecker.met") }}' : '{{ trans("skillchecker::skillchecker.not_met") }}'}
                                    </span>
                                </td>
                            </tr>
                        `;
                    });
                    
                    html += '</tbody></table></div></div></div></div>';
                });
                
                html += '</div></div></div></div>';
            });
        } else {
            // Fallback to old format if grouped data is not available
            data.characters.forEach(function(char, index) {
                const results = char.results;
                html += `
                    <div class="card">
                        <div class="card-header" id="heading${index}">
                            <h2 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse${index}">
                                    ${char.character.name} 
                                    <span class="badge badge-${results.all_met ? 'success' : 'warning'} ml-2">
                                        ${results.percentage}%
                                    </span>
                                </button>
                            </h2>
                        </div>
                        <div id="collapse${index}" class="collapse" data-parent="#main-character-accordion">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Skill</th>
                                                <th>Required</th>
                                                <th>Current</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                `;
                
                results.requirements.forEach(function(req) {
                    html += `
                        <tr class="${req.met ? 'table-success' : 'table-warning'}">
                            <td>${req.skill_name}</td>
                            <td>${req.required_level}</td>
                            <td>${req.character_level}</td>
                            <td>
                                <span class="badge badge-${req.met ? 'success' : 'warning'}">
                                    ${req.met ? '{{ trans("skillchecker::skillchecker.met") }}' : '{{ trans("skillchecker::skillchecker.not_met") }}'}
                                </span>
                            </td>
                        </tr>
                    `;
                });
                
                html += '</tbody></table></div></div></div></div>';
            });
        }
        
        html += '</div>';
        
        $('#results-content').html(html);
        $('#results-section').show();
        hideLoading();
    }
    
    function showLoading() {
        $('#results-content').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x"></i><p>Loading...</p></div>');
        $('#results-section').show();
    }
    
    function hideLoading() {
        // Loading is hidden when results are displayed
    }
});
</script>
@endpush