<?php

namespace Zenobio93\Seat\SkillChecker\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Seat\Eveapi\Models\Character\CharacterSkill;
use Seat\Web\Models\User;

class SkillList extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'skill_lists';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who created this skill list.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the skill requirements for this skill list.
     *
     * @return HasMany
     */
    public function requirements(): HasMany
    {
        return $this->hasMany(SkillListRequirement::class);
    }

    /**
     * Check if a character meets all requirements in this skill list.
     *
     * @param int $character_id
     * @return array
     */
    public function checkCharacterSkills(int $character_id): array
    {
        $requirements = $this->requirements()->with('skill')->get();
        $characterSkills = CharacterSkill::where('character_id', $character_id)
            ->pluck('trained_skill_level', 'skill_id');

        $results = [];
        $totalMet = 0;
        $totalRequired = $requirements->count();

        foreach ($requirements as $requirement) {
            $characterLevel = $characterSkills->get($requirement->skill_id, 0);
            $met = $characterLevel >= $requirement->required_level;
            
            if ($met) {
                $totalMet++;
            }

            $results[] = [
                'skill_id' => $requirement->skill_id,
                'skill_name' => $requirement->skill->typeName ?? 'Unknown Skill',
                'required_level' => $requirement->required_level,
                'character_level' => $characterLevel,
                'met' => $met,
            ];
        }

        return [
            'requirements' => $results,
            'total_met' => $totalMet,
            'total_required' => $totalRequired,
            'percentage' => $totalRequired > 0 ? round(($totalMet / $totalRequired) * 100, 2) : 100,
            'all_met' => $totalMet === $totalRequired,
        ];
    }
}